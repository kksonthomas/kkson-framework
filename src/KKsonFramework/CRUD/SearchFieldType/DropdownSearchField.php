<?php

namespace KKsonFramework\CRUD\SearchFieldType;


use KKsonFramework\CRUD\FieldType\DatePicker;

class DropdownSearchField extends SearchFieldBase
{
    private $options;
    private $placeholder;

    /**
     * DropdownSearchField constructor.
     * @param $name
     * @param $options
     * @param string $placeholder
     * @param string $displayName
     * @param string $fieldSql
     * @param callable|null $processSearchToSqlCallback
     */
    public function __construct($name, $options, $placeholder = null, $displayName = null, $fieldSql = null, callable $processSearchToSqlCallback = null)
    {
        $this->options = $options;
        $this->placeholder = $placeholder;
        parent::__construct($name, $displayName, $fieldSql, $processSearchToSqlCallback);
    }

    public function render()
    {
        return [
            "tag" => "select",
            "attr" => [],
            "options" => $this->options,
            "placeholder" => $this->placeholder
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