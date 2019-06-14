<?php
/**
 * Created by PhpStorm.
 * User: winlans
 * Date: 2018/7/24
 * Time: 10:26
 */
namespace App\Library\Util;

class StringsUtil
{
    const FORMAT_HUMP = 'hump';
    const FORMAT_UNDERLINE = 'underline';

    /**
     * @param $data
     * @param string $dst
     * @return array | string
     */
    public static function convertFormat($data, $dst = self::FORMAT_HUMP){

        $algorithm[self::FORMAT_UNDERLINE] = function ($str) {
            $length = mb_strlen($str);
            $new = '';
            for($i = 0; $i < $length; $i++)
            {
                $num = ord($str[$i]);
                $pre = $i > 0 ? ord($str[$i - 1]) : null;
                $new .= ($i > 0 && ($num >= 65 && $num <= 90) && ($pre >= 97 && $pre <= 122)) ? "_{$str[$i]}" : $str[$i];
            }

            return strtolower($new);
        };

        $algorithm[self::FORMAT_HUMP] = function ($str) {
            $length = mb_strlen($str);
            $new = '';
            for($i = 0; $i < $length; $i++)
            {
                $pre = $i > 0 ? $str[$i - 1] : null;
                if ($str[$i] == '_') continue;
                $new .= ($i > 0 && ($pre == '_')) ? strtoupper($str[$i]) : $str[$i];
            }

            return($new);
        };

        if (is_string($data))
            return $algorithm[$dst]($data);
        $result = [];
        foreach ($data as $key => $item) {
            if (is_array($item) || is_object($item)) {
                $result[$algorithm[$dst]($key)] = self::convertFormat((array)$item, $dst);
            } else {
                $result[$algorithm[$dst]($key)] = $algorithm[$dst]($item);
            }
        }
        return $result;
    }

}
