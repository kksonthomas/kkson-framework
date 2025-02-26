<?php


namespace KKsonFramework\CRUD\FieldType;


use KKsonFramework\Utils\DateUtils;
use KKsonFramework\Utils\UrlUtils;
use RedBeanPHP\R;

class DateTimePicker extends DatePicker
{
    /**
     * Email constructor.
     * @param array $options
     */
    public function __construct($options = [])
    {
        $classOptions = [
            "locale" => array_merge(self::defaultOptions["locale"], [
                "format" => "YYYY年M月D日 hh:mm A"
            ]),
            "singleDatePicker" => true,
            "timePicker" => true,
            "showDropdowns" => true,
        ];
        $this->options = array_merge(self::defaultOptions, $classOptions, $options);
    }

    public function beforeRenderValue($valueFromDatabase)
    {
        return DateUtils::toChineseFormatDate($valueFromDatabase, true);
    }

    public function beforeStoreValue($valueFromUser)
    {
        return DateUtils::createFromChineseFormatDate($valueFromUser, true);
    }

    public function renderCell($value)
    {
        if($value) {
            return DateUtils::toChineseFormatDate($value, true);
        } else {
            return "";
        }
    }
}