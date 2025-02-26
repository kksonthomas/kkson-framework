<?php

namespace KKsonFramework\App;

use KKsonFramework\Conf\DbConfig;
use RedBeanPHP\R;
use RedBeanPHP\RedException\SQL;
use KKsonFramework\RedBeanPHP\BeanHelper;

class DB
{
    public static function init(BeanHelper $beanHelper) {
        $config = DbConfig::get();
        $connectionString = "mysql:host={$config->dbHost()};dbname={$config->dbDatabase()}";
        R::setup($connectionString, $config->dbUsername(), $config->dbPassword(), TRUE);
    
        R::ext('xdispense', function ($type) {
            return R::getRedBean()->dispense($type);
        });

        R::getRedBean()->setBeanHelper($beanHelper);
    }

    public static function isConnected() {
        return R::testConnection();
    }

    //use this function when using export, exportAll and convertBean functions for better performance
    //notice that database should be frozen after this function called to prevent exceptions
    public static function fixSchema() {
        R::freeze(true);
        $schema = R::getDuplicationManager()->getSchema();
        R::getDuplicationManager()->setTables($schema);
    }

    public static function requestLongTimeoutQuery() {
        //R::exec('SET SESSION connect_timeout=28800');
        R::exec('SET SESSION wait_timeout=28800');
        R::exec('SET SESSION interactive_timeout=28800');
    }

    /**
     * @param SQL $ex
     * @param $keyName
     */
    public static function getDuplicateKeyFromSQLException($ex, $keyName) {
        preg_match("/(?<=Duplicate\sentry\s\')(.+)(?=\'\sfor\skey\s\'$keyName\')/", $ex->getMessage(), $match);
        $dupCode = $match[0];
        return $dupCode;
    }

    public static function setForeignKeyCheck($b)
    {
        R::exec("SET FOREIGN_KEY_CHECKS=".($b ? 1 : 0).";");
    }
}