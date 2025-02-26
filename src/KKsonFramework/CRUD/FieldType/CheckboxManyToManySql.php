<?php

namespace KKsonFramework\CRUD\FieldType;


use KKsonFramework\CRUD\Field;
use RedBeanPHP\R;

class CheckboxManyToManySql extends CheckboxList
{

    /**
     * @param string $tableName
     * @param callable $nameClosure function ($bean) {}
     * @param string $valueField The field name that used to be value. The default field is "id".
     */
    public function __construct($tableName, $sql = "", $data = [], callable $nameClosure = null, $valueField = "id") {
        $result = R::getAll($sql, $data);

        $options = [];

        foreach ($result as $row) {

            if ($nameClosure != null) {
                $options[$row[$valueField]] = $nameClosure($row);
            } else {
                $options[$row[$valueField]] = $row["name"];
            }
        }

        parent::__construct($tableName, $options);

    }


}