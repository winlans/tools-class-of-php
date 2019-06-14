<?php
/**
 * Created by PhpStorm.
 * User: winlans
 * Date: 2018/7/31
 * Time: 9:57
 */
namespace App\Library\Util;

class UserAgentUtil
{
    public static function isMobile() :bool
    {
        // 如果有HTTP_X_WAP_PROFILE则一定是移动设备
        if (isset ($_SERVER['HTTP_X_WAP_PROFILE']))
            return true;

        //此条摘自TPM智能切换模板引擎，适合TPM开发
        if(isset ($_SERVER['HTTP_CLIENT']) &&'PhoneClient'==$_SERVER['HTTP_CLIENT'])
            return true;
        //如果via信息含有wap则一定是移动设备,部分服务商会屏蔽该信息
        if (isset ($_SERVER['HTTP_VIA']))
            //找不到为flase,否则为true
            return stristr($_SERVER['HTTP_VIA'], 'wap') ? true : false;
        //判断手机发送的客户端标志,兼容性有待提高
        if (isset ($_SERVER['HTTP_USER_AGENT'])) {
            $clientkeywords = array(
                'nokia','sony','ericsson','mot','samsung','htc','sgh','lg','sharp','sie-','philips','panasonic','alcatel','lenovo','iphone','ipod','blackberry','meizu','android','netfront','symbian','ucweb','windowsce','palm','operamini','operamobi','openwave','nexusone','cldc','midp','wap','mobile'
            );
            //从HTTP_USER_AGENT中查找手机浏览器的关键字
            if (preg_match("/(" . implode('|', $clientkeywords) . ")/i", strtolower($_SERVER['HTTP_USER_AGENT']))) {
                return true;
            }
        }
        //协议法，因为有可能不准确，放到最后判断
        if (isset ($_SERVER['HTTP_ACCEPT'])) {
            // 如果只支持wml并且不支持html那一定是移动设备
            // 如果支持wml和html但是wml在html之前则是移动设备
            if ((strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') !== false) && (strpos($_SERVER['HTTP_ACCEPT'], 'text/html') === false || (strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') < strpos($_SERVER['HTTP_ACCEPT'], 'text/html')))) {
                return true;
            }
        }
        return false;
    }

    public static function isAndroid() :bool
    {
        $userAgent =strtolower($_SERVER['HTTP_USER_AGENT']);

        if (strpos($userAgent, 'android') !== false)
            return true;

        return false;
    }

    public static function isIos() :bool
    {
        $userAgent =strtolower($_SERVER['HTTP_USER_AGENT']);

        if (strpos($userAgent, 'iphone') !== false || strpos($userAgent, 'ipad') !== false)
            return true;

        return false;
    }

    public static function isWechat() :bool
    {
        return ! (false === stripos(strtolower($_SERVER['HTTP_USER_AGENT']), 'micromessenger'));
    }

    public static function getRealClientIP() {
        $address = "";
        /**
         * 检查是否带有私有的属性
         */
        if (isset($_SERVER['HTTP_X_REAL_IP']) && $_SERVER['HTTP_X_REAL_IP'] != "") {
            $address = $_SERVER['HTTP_X_REAL_IP'];
        } else if (isset($_SERVER['REMOTE_ADDR']) AND isset($_SERVER['HTTP_CLIENT_IP'])) {
            $address = $_SERVER['HTTP_CLIENT_IP'];
        } else if ($_SERVER['REMOTE_ADDR']) {
            $address = $_SERVER['REMOTE_ADDR'];
        } elseif ($_SERVER['HTTP_CLIENT_IP']) {
            $address = $_SERVER['HTTP_CLIENT_IP'];
        } elseif ($_SERVER['HTTP_X_FORWARDED_FOR']) {
            $address = $_SERVER['HTTP_X_FORWARDED_FOR'];
        }

        if ($address === "") {
            $address = '0.0.0.0';
            return $address;
        }

        if (strpos($address, ',') !== FALSE) {
            $x = explode(',', $address);
            $address = trim(current($x));
        }

        return $address;
    }
}