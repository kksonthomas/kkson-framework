<?php

namespace KKsonFramework\CRUD\FieldType;


use KKsonFramework\CRUD\FieldType\TrueFalse;

class YesNoCheckboxChinese extends TrueFalse
{
    public function __construct()
    {
        parent::__construct("是", "否");
    }

}