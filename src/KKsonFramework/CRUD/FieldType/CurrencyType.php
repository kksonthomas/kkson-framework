<?php

namespace KKsonFramework\CRUD\FieldType;

use KKsonFramework\Utils\StringUtils;
use KKsonFramework\CRUD\FieldType\FieldType;

class CurrencyType extends FieldType
{
    private $dp;

    public function __construct($dp = 2)
    {
        $this->dp = $dp;
    }

    /**
     * Render Field for Create/Edit
     * @param bool|true $echo
     * @return string
     */
    public function render($echo = false)
    {
        $name = $this->field->getName();
        $display = $this->field->getDisplayName();
        $bean = $this->field->getBean();
        $value = $this->getValue();
        $readOnly = $this->getReadOnlyString();
        $required = $this->getRequiredString();

        $formattedValue = StringUtils::numberToCurrencyString($value, $this->dp, true);
        $html  = <<< EOF
                <div class="form-group">
            <label for="field-$name">{$this->getRequiredStar()} $display</label>
       <input data-type="currency" type="text" class="form-control" name="$name" value="$formattedValue" $readOnly $required />
        </div>

EOF;

        if ($echo)
            echo $html;

        return $html;
    }

    public function renderCell($value)
    {
        if($value < 0) {
            return "<span class='text-danger'>".StringUtils::numberToCurrencyString($value, $this->dp, true)."</span>";
        } else {
            return StringUtils::numberToCurrencyString($value, $this->dp, true);
        }
    }

    public function beforeStoreValue($valueFromUser)
    {
        return StringUtils::currencyStringToNumber($valueFromUser);
    }


}