<?php

namespace KKsonFramework\CRUD\FieldType;


use KKsonFramework\Utils\StringUtils;
use KKsonFramework\CRUD\FieldType\FieldType;

class PercentageType extends FieldType
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

        $step = round(1/pow(10, $this->dp), $this->dp);
        $html  = <<< EOF
                <div class="form-group">
                    <label for="field-$name">$display</label>
                        <div class="input-group">
                            <input step="$step" type="number" class="form-control" name="$name" value="$value" $readOnly $required />
                            <span class="input-group-addon">%</span>
                        </div>
        </div>

EOF;

        if ($echo)
            echo $html;

        return $html;
    }

    public function beforeStoreValue($valueFromUser)
    {
        $value = parent::beforeStoreValue($valueFromUser);
        if(trim($value) === "") {
            return null;
        }
        if(!is_null($value)) {
            $value *= 0.01;
            $value = round($value, $this->dp + 2);
        }
        return  $value;
    }

    public function beforeRenderValue($valueFromDatabase)
    {
        $value = parent::beforeRenderValue($valueFromDatabase);
        if(!is_null($value)) {
            $value *= 100;
            $value = round($value, $this->dp);
        }
        return  $value;
    }


}