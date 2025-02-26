<?php

namespace KKsonFramework\CRUD\FieldType;


class TrueFalse extends RadioButton
{

    /**
     * RadioButton constructor.
     * @param string $true
     * @param string $false
     */
    public function __construct($true = "Yes", $false = "No") {
        parent::__construct([
            1 => $true,
            0 => $false
        ]);
    }

}