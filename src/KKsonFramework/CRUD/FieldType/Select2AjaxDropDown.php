<?php

namespace KKsonFramework\CRUD\FieldType;


use KKsonFramework\CRUD\Field;
use KKsonFramework\CRUD\FieldType\Dropdown;
use KKsonFramework\CRUD\FieldType\FieldType;
use KKsonFramework\CRUD\KKsonCRUD;
use RedBeanPHP\R;

class Select2AjaxDropDown extends FieldType
{
    private $nameCallback;
    private $nullOption;
    private $ajaxUrl;

    /**
     * ManyToOne constructor.
     * @param $ajaxUrl
     * @param callable $nameCallback
     * @param bool $nullOption
     * @internal param callable $nameClosure
     */
    public function __construct($ajaxUrl, $nameCallback = null, $nullOption = true) {
        $this->ajaxUrl = $ajaxUrl;
        $this->nameCallback = $nameCallback;
        $this->nullOption = $nullOption;
    }

    public function render($echo = false)
    {

        $name = $this->field->getName();
        $display = $this->field->getDisplayName();
        $bean = $this->field->getBean();
        $value = $this->getValue();
        $readOnly = $this->getReadOnlyString();
        $disabled = $this->getDisabledString();
        $required = $this->getRequiredString();

        $nameCallback = $this->nameCallback;

        $html = <<<TAG
<div class="form-group"><label for="field-$name" >{$this->getRequiredStar()} $display</label>
TAG;

        // data-live-search="true"   Search box for bootstrap-select

        $html .= <<<TAG
<select id="field-$name" name="$name" $required $readOnly $disabled class="form-control">
TAG;

        if($this->nullOption) {
            $html .= "<option value=\"".KKsonCRUD::NULL."\"> -- </option>";
        }

        if($nameCallback && $value != null) {
            $optionName = $nameCallback($value);
            $html .= "<option value=\"$value\" selected>$optionName</option>";
        }

        $html .= "</select></div>";

        $allowClear = $required == "required" ? "false" : "true";

        $this->field->getCRUD()->addBodyEndHTML(<<<HTML
            <script>
                $(function() {
                    $("#field-$name").select2({
                        allowClear: $allowClear,
                        placeholder :'選擇$display',
                        ajax: {
                            url: "$this->ajaxUrl",
                            dataType: 'json',
                            data: function (params) {
                                return {
                                    q: params.term,
                                    page: params.page || 1
                                };
                            }
                        }
                    })
                });
            </script>
HTML
        );

        if ($echo)
            echo $html;

        return $html;
    }

    public function renderCell($value) {
        return $value;
    }

}