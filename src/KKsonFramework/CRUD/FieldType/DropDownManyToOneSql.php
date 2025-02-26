<?php

namespace KKsonFramework\CRUD\FieldType;


use KKsonFramework\CRUD\KKsonCRUD;
use RedBeanPHP\R;

class DropDownManyToOneSql extends Dropdown
{
    private static $cache = [];

    /**
     * ManyToOne constructor.
     * @param null $sql
     * @param array $data
     * @param bool $nullOption
     * @internal param callable $nameClosure
     */
    public function __construct( $sql = null,  $data = [], $nullOption = true) {
        $key = "$sql|".json_encode($data)."|$nullOption";
        if(!isset(self::$cache[$key])){
            $options = [];
            if ($nullOption) {
                $options[KKsonCRUD::NULL] = "--";
            }
            $options = $options + R::getAssoc($sql, $data);
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