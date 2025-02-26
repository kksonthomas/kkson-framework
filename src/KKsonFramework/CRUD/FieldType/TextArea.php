<?php

namespace KKsonFramework\CRUD\FieldType;


class TextArea extends FieldType
{

    /**
     * Render Field for Create/Edit
     * @param bool|true $echo
     * @return string
     */
    public function render($echo = false)
    {
        $name = $this->field->getName();
        $display = $this->field->getDisplayName();
        $defaultValue = $this->field->getDefaultValue();
        $bean = $this->field->getBean();
        $value = $this->getValue();
        $readOnly = $this->getReadOnlyString();
        $required = $this->getRequiredString();


        $html  = <<< EOF
                <div class="form-group">
        <label for="field-$name">$display</label><textarea class="form-control" id="field-$name" name="$name" $readOnly $required>$value</textarea>
</div>
EOF;

        if ($echo)
            echo $html;

        return $html;
    }

    public function renderCell($value)
    {
        $value = trim(strip_tags($value));
        return mb_strimwidth($value, 0, 60, "...", "UTF-8");
    }
}