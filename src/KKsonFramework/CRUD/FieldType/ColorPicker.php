<?php

namespace KKsonFramework\CRUD\FieldType;

class ColorPicker extends FieldType
{
    protected $defaultValue;

    public function __construct($defaultValue = "#000000") {
        $this->defaultValue = $defaultValue;
    }

    public function setDefaultValue($defaultValue) {
        $this->defaultValue = $defaultValue;
    }

    public function render($echo = false)
    {
        $name = $this->field->getName();
        $display = $this->field->getDisplayName();
        $bean = $this->field->getBean();
        $value = $this->getValue();
        $readOnly = $this->getReadOnlyString();
        $required = $this->getRequiredString();

        if(empty($value)) {
            $value = $this->defaultValue;
        }
        $html  = <<< EOF
                <div class="form-group">
                    <label for="field-$name">$display</label>
                    <input type="color" class="form-control" name="$name" value="$value" $readOnly $required />
                </div>
EOF;
        if ($echo)
            echo $html;

        return $html;
    }

    public function renderCell($value)
    {
        return "<div style='width: 100px; height: 100px; color: $value;'>$value</div>";
    }
}