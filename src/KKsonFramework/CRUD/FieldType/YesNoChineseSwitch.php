<?php

namespace KKsonFramework\CRUD\FieldType;


class YesNoChineseSwitch extends YesNoSwitch
{
    public function __construct($yesText = "是", $noText = "否") {
        parent::__construct($yesText, $noText);
    }
}