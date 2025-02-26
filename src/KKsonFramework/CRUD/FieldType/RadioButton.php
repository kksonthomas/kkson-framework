<?php

namespace KKsonFramework\CRUD\FieldType;


class RadioButton extends FieldType
{

    /**
     * @var string[]
     */
    private $options;

    /**
     * RadioButton constructor.
     * @param string[] $options
     */
    public function __construct($options) {
        $this->options = $options;
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
        $disabled = $this->getDisabledString();
        $required = $this->getRequiredString();
        $readOnly = $this->getReadOnlyString(); // No used actually, because radio is not supported.



        $html = "<label>$display</label>";

        foreach ($this->options as $v =>$optionName) {

            if ($value == $v) {
                $selected  = "checked";
            } else {
                $selected = "";
            }


            $html  .= <<< EOF
                <div class="radio">
                <label><input type="radio" name="$name" value="$v" $disabled $required $selected /> $optionName</label>
                </div>
EOF;
        }

        if ($echo)
            echo $html;

        return $html;
    }

    public function renderCell($value) {
        try {
            return $this->options[$value];
        } catch (\ErrorException $ex) {
            return $value;
        }
    }

}

