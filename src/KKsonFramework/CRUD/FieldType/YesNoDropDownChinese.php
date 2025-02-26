<?php

namespace KKsonFramework\CRUD\FieldType;


use KKsonFramework\CRUD\FieldType\Dropdown;
use KKsonFramework\CRUD\KKsonCRUD;
use RedBeanPHP\R;

class YesNoDropDownChinese extends Dropdown
{
    public function __construct($nullOption = false) {
        $options = [];
        if($nullOption) {
            $options[KKsonCRUD::NULL] = "--";
        }
        $options += [
            1 => "是",
            0 => "否",
        ];
        parent::__construct($options);
    }

    public function getValue()
    {
        return parent::getValue();
    }


}