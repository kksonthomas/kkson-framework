<?php

namespace KKsonFramework\Utils;


class StringUtils
{
    public static function numberToPercent($v) {
        if(is_numeric($v)) {
            return round($v * 100, 2) . "%";
        }
        return $v;
    }

    public static function currencyStringToNumber($value)
    {
        return $value != null ? preg_replace('/[\$\,]/', '', $value) : null;
    }

    public static function numberToChinese($n)
    {
        return ["零", "一", "二", "三", "四", "五", "六", "七", "八", "九", "十"][$n];
    }

    public static function generateRandomNumberString($length = 10)
    {
        return self::generateRandomStringCustom('0123456789', $length);
    }

    public static function generateRandomString($length = 10)
    {
        return self::generateRandomStringCustom('0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', $length);
    }

    public static function generateRandomSymbol($length = 10)
    {
        return self::generateRandomStringCustom('!@#$%^&*()-=+_', $length);
    }

    public static function generateRandomStringCustom($characters, $length = 10)
    {
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }

    public static function generatePassword($length = 8, $symbolCount = 2) {
        if($symbolCount > $length) {
            $symbolCount = $length;
        }
        $str = array_merge(str_split(self::generateRandomString($length-$symbolCount)), str_split(self::generateRandomSymbol($symbolCount)));
        shuffle($str);
        return implode("", $str);
    }

    public static function numberToCurrencyString($value, $dp = 2, $showZero = false, $prefix = '$')
    {
        return !empty($value) || $showZero ? $prefix. number_format(!empty($value) ? $value : 0, $dp) : "";
    }

    public static function numberFormat($value, $dp = 0, $showZero = true)
    {
        return self::numberToCurrencyString($value, $dp, $showZero, "");
    }

    public static function censorString($string, $start, $length = null, $replace = "X")
    {
        if ($length == null) {
            $length = strlen($string) - $start;
        }
        if ($length < 0) {
            $length = strlen($string) - $start + $length;
        }
        $result = "";

        for ($i = 0; $i < strlen($string); $i++) {
            if ($i >= $start && $i < $start + $length) {
                $result .= $replace;
            } else {
                $result .= substr($string, $i, 1);
            }
        }
        return $result;


    }

    public static function removeNonPrintableChar(&$str) {
        $str = preg_replace('/[[:^print:]]/', '', $str);
        return $str;
    }

    public static function maskEmail($email, $maskChar = '*') {
        $re = '/(?<=.)[^@\n](?=[^@\n]*?[^@\n]@)|(?:(?<=@.)|(?!^)\G(?=[^@\n]*$)).(?=.*[^@\n]\.)/m';
        return preg_replace($re, $maskChar, $email);
    }

    public static function maskTel($tel, $maskChar = '*') {
        $telArray = str_split($tel, 1);
        for($i = 2; $i < count($telArray) - 2; $i++) {
            $telArray[$i] = $maskChar;
        }
        return implode("", $telArray);
    }

    public static function isAscii($str) {
        return mb_check_encoding($str, 'ASCII');
    }
}