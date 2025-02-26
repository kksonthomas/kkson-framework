<?php

namespace KKsonFramework\CRUD\SearchFieldType;


class NumberSearchField extends SearchFieldBase
{
    public function __construct($name, $displayName = null, $fieldSql = null, callable $processSearchToSqlCallback = null)
    {
        parent::__construct($name, $displayName, $fieldSql, $processSearchToSqlCallback);
    }

    public function render()
    {
//        return "<input type=\"text\" class=\"form-control inputKeyword\"/>";
        return [
            "tag" => "input",
            "attr" => [
                "type" => "number",
                "step" => 1,
                "min" => 0
            ]
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
        ];
    }
}