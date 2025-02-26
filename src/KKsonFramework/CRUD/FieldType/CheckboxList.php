<?php

namespace KKsonFramework\CRUD\FieldType;


use KKsonFramework\CRUD\Field;
use RedBeanPHP\R;

class CheckboxList extends FieldType
{

    /**
     * @var string[]
     */
    protected $options;

    protected $tableName;

    /**
     * @param string $tableName
     * @param string[] $options
     * @param callable $nameClosure
     */
    public function __construct($tableName, array $options = null)
    {
        $this->tableName = $tableName;
        $this->options = $options;
        $this->setFieldRelation(Field::MANY_TO_MANY);
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

        $crud = $this->field->getCrud();
        $totalCount = count($this->options);
        $selectAllJs = !$bean ? "selectAllCb.prop('checked', true).change();" : "";
        $crud->addJavaScriptCode(
            <<<JavaScript
                $(document).ready(function() {
                    var group = $("#form-group-$name");
                    var nonSelectAllCb = group.find('input[type="checkbox"]').not('.select-all');
                    var selectAllCb = group.find('.select-all');
                    selectAllCb.change(function() {
                        var checked = $(this).prop('checked');
                        nonSelectAllCb.prop('checked', checked);
                    });
                    nonSelectAllCb.change(function() {
                        selectAllCb.prop('checked', nonSelectAllCb.length == nonSelectAllCb.filter(':checked').length);
                    }).change();

                    $selectAllJs;
                });
JavaScript
        );


        $html = <<<TAG
       <div class="form-group checkboxes-group" id="form-group-$name">
            <label for="field-$name" >$display</label>
            <div class="checkbox form-check">
                <input type="checkbox" class="form-check-input select-all" />
                <label class="form-check-label">全選 ($totalCount)</label>
            </div>
            <hr class="my-1">
TAG;

        foreach ($this->options as $v => $optionName) {

            if (isset($valueList[$v])) {
                $selected  = "checked";
            } else {
                $selected = "";
            }

            $nameAttr = 'name="' . $name . '[]"';

            $html  .= <<< HTML
                <div class="checkbox form-check">
                    <input type="checkbox" value="$v" class="form-check-input" $nameAttr $required $readOnly $selected />
                    <label class="form-check-label">$optionName</label>
                </div>
HTML;
        }

        $html .= " </div>";

        if ($echo)
            echo $html;

        return $html;
    }

    public function renderCell($value)
    {

        try {
            return $this->options[$value];
        } catch (\ErrorException $ex) {
            return $value;
        }
    }
}
