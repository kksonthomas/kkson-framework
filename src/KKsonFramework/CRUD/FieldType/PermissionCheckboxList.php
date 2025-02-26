<?php

namespace KKsonFramework\CRUD\FieldType;


use KKsonFramework\CRUD\FieldType\CheckboxList;
use KKsonFramework\RedBeanPHP\Model\Permission;
use RedBeanPHP\R;

class PermissionCheckboxList extends CheckboxList
{
    /**
     * @param callable $nameClosure function ($bean) {}
     * @param string $valueField The field name that used to be value. The default field is "id".
     */
    public function __construct(callable $nameClosure = null, $valueField = "id") {
        $tableName = Permission::_getTableName();
        $beans = R::find($tableName, "1=1 ORDER BY display_weight");

        $options = [];

        foreach ($beans as $bean) {

            if ($nameClosure != null) {
                $options[$bean->{$valueField}] = $nameClosure($bean);
            } else {
                $options[$bean->{$valueField}] = $bean->name;
            }
        }

        parent::__construct($tableName, $options);

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
        $valueList = $this->getValue();

        $readOnly = $this->getDisabledString();
        $required = $this->getRequiredString();

        $html = <<<TAG
<label for="field-$name" >$display</label>
TAG;

        $html .= <<<TAG
       <div class="form-group checkboxes-group">
TAG;

        foreach ($this->options as $v =>$optionName) {

            if (isset($valueList[$v])) {
                $selected  = "checked";
            } else {
                $selected = "";
            }

            $nameAttr = 'name="'. $name .'[]"';

            $html  .= <<< HTML
                <div class="checkbox">
                    <label>
                        <input type="checkbox" value="$v" $nameAttr $required $readOnly $selected /> $optionName
                    </label>
                </div>
HTML;
        }

        $html .= " </div><br />";

        if ($echo)
            echo $html;

        return $html;
    }


}