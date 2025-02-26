<?php

namespace KKsonFramework\CRUD\SearchFieldType;


use KKsonFramework\CRUD\FieldType\DatePicker;

class DateSearchField extends SearchFieldBase
{
    public function __construct($name, $displayName = null, $fieldSql = null, callable $processSearchToSqlCallback = null)
    {
        parent::__construct($name, $displayName, $fieldSql, $processSearchToSqlCallback);
    }

    public function render()
    {
//        return "<input type=\"text\" class=\"form-control inputKeyword\"/>";
        $datePickerOption = json_encode(array_merge(DatePicker::defaultOptions, [
            "singleDatePicker" => true,
            "showDropdowns" => true
        ]));
        return [
            "tag" => "input",
            "attr" => [
                "type" => "text"
            ],
            "js" => <<<JS
                function(field, initValue) {
                    let options = $datePickerOption;
                    field.val(initValue).daterangepicker(options, function(start, end, label) {
                        //do nothing on change
                    }).on('apply.daterangepicker', function(ev, picker) {
                        if(!picker.singleDatePicker) {
                            $(this).val(picker.startDate.format(picker.locale.format) + picker.locale.separator + picker.endDate.format(picker.locale.format));
                            $(this).data("value", picker.startDate.format(options.system.date_format) + options.system.separator + picker.endDate.format(options.system.date_format))
                        } else {
                            $(this).val(picker.startDate.format(picker.locale.format));
                            $(this).data("value", picker.startDate.format(options.system.date_format));
                        }
                    }).on('cancel.daterangepicker', function(ev, picker) {
                        $(this).val('');
                        $(this).data("value","");
                    }).on('hide.daterangepicker', function(ev, picker) {
                        $(this).trigger("apply.daterangepicker", picker);
                    });
                             
                    let picker = field.data("daterangepicker");
                    field.trigger("apply.daterangepicker", picker);
                }
JS,
            "manualInitValue" => true

        ];
    }

    public function getConditionList() {
        return [
            SearchCriteriaClasses\SearchCriteria::COND_EQUAL,
            SearchCriteriaClasses\SearchCriteria::COND_NOT_EQUAL,
            SearchCriteriaClasses\SearchCriteria::COND_LARGER_THAN_OR_EQUAL,
            SearchCriteriaClasses\SearchCriteria::COND_LARGER_THAN,
            SearchCriteriaClasses\SearchCriteria::COND_SMALLER_THAN_OR_EQUAL,
            SearchCriteriaClasses\SearchCriteria::COND_SMALLER_THAN,
            SearchCriteriaClasses\SearchCriteria::COND_EMPTY,
            SearchCriteriaClasses\SearchCriteria::COND_NOT_EMPTY
        ];
    }
}