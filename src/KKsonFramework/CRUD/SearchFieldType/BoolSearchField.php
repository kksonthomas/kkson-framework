<?php

namespace KKsonFramework\CRUD\SearchFieldType;


class BoolSearchField extends DropdownSearchField
{
    public function __construct($name, $yesText, $noText, $placeholder = null, $displayName = null, $fieldSql = null, callable $processSearchToSqlCallback = null)
    {

        parent::__construct($name, [
            0 => $noText,
            1 => $yesText
        ], $placeholder, $displayName, $fieldSql, $processSearchToSqlCallback);
    }

    public function getConditionList() {
        return [
            SearchCriteriaClasses\SearchCriteria::COND_EQUAL,
            SearchCriteriaClasses\SearchCriteria::COND_NOT_EQUAL
        ];
    }
}