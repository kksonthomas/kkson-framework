<?php

namespace KKsonFramework\CRUD\FieldType;


use KKsonFramework\CRUD\KKsonCRUD;
use RedBeanPHP\R;

class DropDownManyToOneFast extends Dropdown
{
    private static $cache = [];
    /**
     * ManyToOne constructor.
     * @param string $tableName
     * @param string $clause
     * @param array $data
     * @param string $nameField
     * @param string $valueField The field name that used to be value. The default field is "id".
     * @param bool $nullOption
     * @internal param callable $nameClosure
     */
    public function __construct( $tableName,  $clause = null,  $data = [],  $nameField = "name",  $valueField = "id", $nullOption = true) {
        $key = "$tableName|$clause|".json_encode($data)."|$nameField|$valueField|$nullOption";
        if(!isset(self::$cache[$key])){
            $options = [];
            if ($nullOption) {
                $options[KKsonCRUD::NULL] = "--";
            }
            $options = $options + R::getAssoc("SELECT $valueField, $nameField FROM $tableName".($clause == null?"":" WHERE $clause"), $data);
            self::$cache[$key] = $options;
        }

        parent::__construct(self::$cache[$key]);
        $this->enableSelect2(true);
    }

    public function render($echo = false)
    {
        $html = "<div class='form-group'>";
        $html .= parent::render($echo);
        $html .= "</div>";
        return $html;
    }

}