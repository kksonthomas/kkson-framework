<?php

namespace KKsonFramework\Utils;


class Math
{
    public static function floor($value , $dp = 2) {
        $dp = pow(10, $dp);
        return floor($value * $dp) / $dp;
    }

    public static function ceil($value , $dp = 2) {
        $dp = pow(10, $dp);
        return ceil($value * $dp) / $dp;
    }

    public static function round($value , $dp = 2) {
        $dp = pow(10, $dp);
        return round($value * $dp) / $dp;
    }

    public static function roundString($value, $dp = 2) {
        return sprintf("%.{$dp}f",  self::round($value, $dp));
    }
}