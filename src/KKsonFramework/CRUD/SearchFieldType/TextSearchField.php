<?php

namespace KKsonFramework\CRUD\SearchFieldType;


class TextSearchField extends SearchFieldBase
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
                "type" => "text"
            ]
        ];
    }
}