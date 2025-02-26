<?php

namespace KKsonFramework\Utils;

use DateTime;

class DateUtils
{
    const CHINESE_DATE_FORMAT = "Y年n月j日";
    const CHINESE_DATETIME_FORMAT = "Y年n月j日 h:i A";

    public static function today()
    {
        return date("Y-m-d");
    }

    public static function now()
    {
        return date("Y-m-d H:i:s");
    }
    public static function getDay($dateString) {
        return (new DateTime($dateString))->format("d");
    }

    public static function getMonth($dateString) {
        return (new DateTime($dateString))->format("m");
    }

    public static function getYear($dateString) {
        return (new DateTime($dateString))->format("Y");
    }

    public static function toDate($dateString) {
        return (new DateTime($dateString))->format("Y-m-d");
    }

    public static function toTimeWithoutSecond($dateString) {
        return (new DateTime($dateString))->format("H:i");
    }

    public static function toDateTimeWithoutSecond($dateString) {
        return (new DateTime($dateString))->format("Y-m-d H:i");
    }

    public static function toChineseFormatDate($date, $dateTimeFormat = false)
    {
        if($date) {
            return (new DateTime($date))->format($dateTimeFormat ? self::CHINESE_DATETIME_FORMAT : self::CHINESE_DATE_FORMAT);
        } else {
            return "";
        }
    }

    public static function createFromChineseFormatDate($dateString, $dateTimeFormat = false)
    {
        return DateTime::createFromFormat($dateTimeFormat ? self::CHINESE_DATETIME_FORMAT : self::CHINESE_DATE_FORMAT, $dateString);
    }
}
