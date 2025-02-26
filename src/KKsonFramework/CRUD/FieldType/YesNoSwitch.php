<?php

namespace KKsonFramework\CRUD\FieldType;


use KKsonFramework\CRUD\FieldType\TrueFalse;

class YesNoSwitch extends FieldType
{
    private $yesText;
    private $noText;

    public function __construct($yesText = "ON", $noText = "OFF") {
        $this->yesText = $yesText;
        $this->noText = $noText;
    }

    public function render($echo = false)
    {
        $name = $this->field->getName();
        $display = $this->field->getDisplayName();
        $bean = $this->field->getBean();
        $value = $this->getValue();
        $disabled = $this->getDisabledString();
        $required = $this->getRequiredString();
        $star = $this->getRequiredStar();
        $readOnly = $this->getReadOnlyString(); // No used actually, because radio is not supported.



        $html = "";

        if ($value) {
            $selected = "checked";
        } else {
            $selected = "";
        }

        $html  .= <<< EOF
                 <div class="form-group">
                    <label for="field-$name">$star $display</label>  
                    <div class="">
                        <input type="hidden" name="$name" value="0" $disabled>
                        <input type="checkbox" data-toggle="switch" name="$name" value="1"  data-on-text="$this->yesText" data-off-text="$this->noText" $disabled $required $selected >
                    </div>
                </div>
EOF;

        if ($echo)
            echo $html;

        return $html;
    }

    public function renderCell($value) {
        return $value ? $this->yesText : $this->noText;
    }

    public function beforeStoreValue($valueFromUser)
    {
        return $valueFromUser ?? 0;
    }


}