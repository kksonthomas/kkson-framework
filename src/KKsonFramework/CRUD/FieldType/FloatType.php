<?php

namespace KKsonFramework\CRUD\FieldType;


use KKsonFramework\CRUD\FieldType\FieldType;

class FloatType extends FieldType
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
       <input step="$step" type="number" class="form-control" name="$name" value="$value" $readOnly $required />
        </div>

EOF;

        if ($echo)
            echo $html;

        return $html;
    }

    public function beforeStoreValue($valueFromUser)
    {
        if($valueFromUser === "") {
            $valueFromUser = null;
        }
        return parent::beforeStoreValue($valueFromUser);
    }


}