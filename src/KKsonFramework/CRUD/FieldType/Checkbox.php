<?php
namespace KKsonFramework\CRUD\FieldType;

class Checkbox extends FieldType
{

    public function __construct() {

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

        $readOnly = $this->getDisabledString();
        $required = $this->getRequiredString();


        if ($value) {
            $selected = "checked";
        } else {
            $selected = "";
        }

        $nameAttr = 'name="' . $name.'"';

        $html  = <<< HTML
        <div class="form-group">
            <div class="checkbox">
              <label>
                <input type="checkbox" value="1" $nameAttr $required $readOnly $selected />  $display
              </label>
            </div>
        </div>
        
HTML;

        if ($echo)
            echo $html;

        return $html;
    }

    public function renderCell($value) {
        return "";
    }



}