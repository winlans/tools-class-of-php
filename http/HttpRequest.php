<?php

namespace App\Library;


use App\Library\Traits\ServiceTrait;
use \CURLFile;

class HttpRequest
{
    use ServiceTrait;

    const METHOD_GET = 'GET';
    const METHOD_POST = 'POST';
    const METHOD_PUT = 'PUT';
    const METHOD_DELETE = 'DELETE';
    const METHOD_PATCH = 'PATCH';
    const METHOD_HEAD = 'HEAD';

    private $postFields;
    private $postFiles;
    private $queryFields;
    private $rawData;

    private $httpHeaders;
    private $method;
    private $url;

    private $baseAuthUsername;
    private $baseAuthPassword;
    private $closeVerify = true;

    /**
     * @param bool $closeVerify
     * @return $this
     */
    public function setCloseVerify(bool $closeVerify)
    {
        $this->closeVerify = $closeVerify;
        return $this;
    }

    private $lastRequestStatus;

    private $error;

    public function getError()
    {
        return $this->error;
    }

    /**
     * @return mixed
     */
    public function getLastRequestStatus()
    {
        return $this->lastRequestStatus;
    }


    public function __construct($url = null, $requestMethod = null)
    {
        $this->url = $url;
        $this->method = $requestMethod;
        $this->init();
    }

    private function init()
    {
        $this->postFields = array();
        $this->postFiles = array();
        $this->queryFields = array();
        $this->rawData = null;
        $this->httpHeaders = array();
        $this->method = null;
    }

    public function setPostFields($postFields)
    {
        $this->postFields = $postFields;
        return $this;
    }

    public function addPostField($field, $value)
    {
        $this->postFields[$field] = $value;
        return $this;
    }

    public function setPostFiles(array $postFiles)
    {
        $this->postFiles = $postFiles;
        return $this;
    }

    public function addPostFile($key, $value)
    {
        $this->postFiles[$key] = $value;
        return $this;
    }

    public function setUrl($url)
    {
        $this->url = $url;
        return $this;
    }

    public function setMethod($requestMethod)
    {
        $this->method = $requestMethod;
        return $this;
    }

    public function setQueryFields($queryFields)
    {
        $this->queryFields = $queryFields;
        return $this;
    }

    public function addQueryField($key, $value)
    {
        $this->queryFields[$key] = $value;
        return $this;
    }

    public function setHttpHeaders(array $httpHeaders)
    {
        $this->httpHeaders = $httpHeaders;
        return $this;
    }

    public function addHttpHeader($key, $value)
    {
        $this->httpHeaders[$key] = $value;
        return $this;
    }

    public function addJsonHeader() {
        $this->addHttpHeader('Content-Type', 'application/json; charset=utf-8');
        $this->addHttpHeader('Content-Length', strlen($this->postFields));
        return $this;
    }

    public function setBaseAuth($username, $password)
    {
        $this->baseAuthUsername = $username;
        $this->baseAuthPassword = $password;
        return $this;
    }

    /**
     * Notice: if you set any raw data there would be no post fields in the request.
     * @param string $rawData
     * @return $this;
     */
    public function setRawData($rawData)
    {
        $this->rawData = $rawData;
        return $this;
    }

    /**
     * Get rawData
     */
    public function getRawData()
    {
        return $this->rawData;
    }

    /**
     * @param bool $initialAfterSent clear current request object after the request sent.
     * @return mixed
     */
    public function send($initialAfterSent = true)
    {
        if ($this->queryFields) {
            $queryFields = array();
            foreach ($this->queryFields as $k => $v) {
                $queryFields[] = $k . '=' . $v;
            }
            $queryFields = implode('&', $queryFields);
            $this->url .= (strpos($this->url, '?') ? '&' : '?') . $queryFields;
        }

        $ch = curl_init($this->url);

        $this->setRequestMethod($ch);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);


        if ($this->rawData)
            curl_setopt($ch, CURLOPT_POSTFIELDS, $this->rawData);
        else {
            if ($this->postFiles)
                foreach ($this->postFiles as $k => $file) {
                    if (!($file instanceof CURLFile))
                        $file = new CURLFile($file, mime_content_type($file), basename($file));

                    $this->postFields[$k] = $file;
                }

            if ($this->postFields)
                curl_setopt($ch, CURLOPT_POSTFIELDS, $this->postFields);
        }

        if ($this->closeVerify) {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        }

        if ($this->httpHeaders) {
            $headers = array();
            foreach ($this->httpHeaders as $key => $value) {
                $headers[] = "$key: $value";
            }
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        }

        if ($this->baseAuthUsername) {
            curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
            curl_setopt($ch, CURLOPT_USERPWD, "$this->baseAuthUsername:$this->baseAuthPassword");
        }

        $result = curl_exec($ch);
        $this->lastRequestStatus = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if ($result == false) {
            $this->error = curl_error($ch);
//            self::loggerError('error occur when request url {%s} request data {}')
        }

        curl_close($ch);

        if ($initialAfterSent)
            $this->init();

        return $result;
    }

    /**
     * @param resource $ch cURL handler
     */
    private function setRequestMethod($ch)
    {
        if (!$this->method || !in_array($this->method, array(
                HttpRequest::METHOD_POST,
                HttpRequest::METHOD_GET,
                HttpRequest::METHOD_PUT,
                HttpRequest::METHOD_DELETE,
                HttpRequest::METHOD_PATCH,
                HttpRequest::METHOD_HEAD)))
            $this->method = HttpRequest::METHOD_POST;

        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $this->method);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('X-HTTP-Method-Override: ' . $this->method));
    }
}