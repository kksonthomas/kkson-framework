<?php


namespace KKsonFramework\CRUD\SearchFieldType\SearchCriteriaClasses;


class SearchCriteria
{
    public const COND_SMALLER_THAN = '<';
    public const COND_ENDS = 'ends';
    public const COND_NOT_EQUAL = '!=';
    public const COND_NOT_CONTAINS = '!contains';
    public const COND_START = 'start';
    public const COND_NOT_START = '!start';
    public const COND_LARGER_THAN_OR_EQUAL = '>=';
    public const COND_NOT_ENDS = '!ends';
    public const COND_NOT_EMPTY = '!null';
    public const COND_EMPTY = 'null';
    public const COND_CONTAINS = 'contains';
    public const COND_LARGER_THAN = '>';
    public const COND_EQUAL = '=';
    public const COND_SMALLER_THAN_OR_EQUAL = '<=';
    protected $field;
    protected $condition;
    protected $keyword;

    /**
     * SearchCriteria constructor.
     * @param $field
     * @param $condition
     * @param $keyword
     */
    public function __construct($field, $condition, $keyword)
    {
        $this->field = $field;
        $this->condition = $condition;
        $this->keyword = $keyword;
    }

    public function toDataObject() {
        return [$this->field, $this->condition, $this->keyword];
    }

}