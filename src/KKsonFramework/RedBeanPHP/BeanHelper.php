<?php

namespace KKsonFramework\RedBeanPHP;


use RedBeanPHP\BeanHelper\SimpleFacadeBeanHelper;
use RedBeanPHP\OODBBean;
use KKsonFramework\CRUD\KKsonCRUD;

class BeanHelper extends SimpleFacadeBeanHelper
{
    protected static $mapList = [];

    public function __construct()
    {
        $prefix =  __namespace__ . '\\Model\\';
        $dir = __DIR__ . "/Model";
        self::addModelsFromDirectory($dir, $prefix);
    }

    public static function addModelsFromDirectory($dir, $prefix) {
        foreach (glob("$dir/*.php") as $filename) {
            $filename = pathinfo($filename, PATHINFO_FILENAME);
            self::$mapList[call_user_func("$prefix$filename::_getTableName")] = [
                "prefix" => $prefix,
                "filename" => $filename
            ];
        }
    }

    public static function isCurrentTableEnabledMimicDelete(KKsonCRUD $crud) {
        $modelName = self::getModelNameFromTableName($crud->getTableName());
        if($modelName && call_user_func([$modelName, "_enabledMimicDelete"])) {
            return true;
        }
        return false;
    }

    public function getModelForBean(OODBBean $bean)
    {
        $mapList = self::$mapList;
        $modelSettings = null;

        $model = $bean->getMeta( 'type' );
        $prefix = "";


        if (isset(self::$mapList[$model])) {
            $modelSettings = $mapList[$model];
            $model = $modelSettings["filename"];
            $prefix = $modelSettings["prefix"];
        }
    
        if ( strpos( $model, '_' ) !== FALSE ) {
            $modelParts = explode( '_', $model );
            $modelName = '';
            foreach( $modelParts as $part ) {
                $modelName .= ucfirst( $part );
            }
            $modelName = $prefix . $modelName;
        
            if ( !class_exists( $modelName ) ) {
                //second try
                $modelName = $prefix . ucfirst( $model );
            
                if ( !class_exists( $modelName ) ) {
                    return NULL;
                }
            }
        
        } else {
            
            $modelName = $prefix . ucfirst( $model );
            if ( !class_exists( $modelName ) ) {
                return NULL;
            }
        }
        $obj = self::factory( $modelName );
        $obj->loadBean( $bean );
    
        return $obj;
    }

    public static function getModelNameFromTableName($tableName) {
        if(isset(self::$mapList[$tableName])) {
            $info = self::$mapList[$tableName];
            return $info["prefix"] . $info["filename"];
        } else {
            return null;
        }

    }
    
}