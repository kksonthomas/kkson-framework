<?php

namespace KKsonFramework\CRUD\SearchFieldType;


use KKsonFramework\CRUD\FieldType\DatePicker;

class DropdownAjaxSearchField extends SearchFieldBase
{
    private $ajaxUrl;
    private $placeholder;

    /**
     * DropdownSearchField constructor.
     * @param $name
     * @param string $ajaxUrl
     * @param string $placeholder
     * @param string $displayName
     * @param string $fieldSql
     * @param callable|null $processSearchToSqlCallback
     */
    public function __construct($name, $ajaxUrl, $placeholder = null, $displayName = null, $fieldSql = null, ?callable $processSearchToSqlCallback = null)
    {
        $this->ajaxUrl = $ajaxUrl;
        $this->placeholder = $placeholder;
        parent::__construct($name, $displayName, $fieldSql, $processSearchToSqlCallback);
    }

    public function render()
    {
        return [
            "tag" => "select",
            "attr" => ["class" => "select2-ajax"],
            "options" => [],
            "placeholder" => $this->placeholder,
            "js" => <<<JS
            function(elem, keyword) {
                $(elem).select2({
                    ajax: {
                        url: '{$this->ajaxUrl}',
                        dataType: 'json',
                    }
                });
            }
            JS
        ];
    }

    public function getConditionList() {
        return [
            SearchCriteriaClasses\SearchCriteria::COND_EQUAL,
            SearchCriteriaClasses\SearchCriteria::COND_NOT_EQUAL,
            SearchCriteriaClasses\SearchCriteria::COND_EMPTY,
            SearchCriteriaClasses\SearchCriteria::COND_NOT_EMPTY
        ];
    }
}