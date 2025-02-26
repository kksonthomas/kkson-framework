<?php

namespace KKsonFramework\CRUD\FieldType;


class Hidden extends FieldType
{

    protected $type = "hidden";

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
        $type = $this->type;

        $html  = <<< EOF
       <input type="$type" id="field-$name" name="$name" value="$value" $readOnly $required />
EOF;

        if ($echo)
            echo $html;

        return $html;
    }


}