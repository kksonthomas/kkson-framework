<?php

namespace KKsonFramework\CRUD\FieldType;


use KKsonFramework\CRUD\KKsonCRUD;
use RedBeanPHP\R;

class ManyToOne extends Dropdown
{

    private static $optionsCacheList;

    /**
     * ManyToOne constructor.
     * @param string $tableName
     * @param string $clause
     * @param array $data
     * @param callable $nameClosure
     * @param string $valueField The field name that used to be value. The default field is "id".
     * @param bool $nullOption
     */
    public function __construct( $tableName,  $clause = null,  $data = [],  $nameClosure = null,  $valueField = "id", $nullOption = true) {

        $cacheKey = md5($tableName . "|" . $clause . "|"  . json_encode($data) . "|"  . $valueField . "|"  . $nullOption);

        if (! isset(self::$optionsCacheList[$cacheKey])) {
            $beans = R::find($tableName, $clause, $data);

            $options = [];

            if ($nullOption) {
                $options[KKsonCRUD::NULL] = "--";
            }

            foreach ($beans as $bean) {
                if ($nameClosure != null) {
                    $options[$bean->{$valueField}] = $nameClosure($bean);
                } else {
                    $options[$bean->{$valueField}] = $bean->name;
                }
            }

            self::$optionsCacheList[$cacheKey] = $options;
        }

        parent::__construct(self::$optionsCacheList[$cacheKey]);
    }

}