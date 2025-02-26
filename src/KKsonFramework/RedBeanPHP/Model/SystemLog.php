<?php

namespace KKsonFramework\RedBeanPHP\Model;

use KKsonFramework\App\MySQLiHelper;
use KKsonFramework\Auth\Auth;
use KKsonFramework\RedBeanPHP\ModelBase\BaseModelBase;
use Slim\Exception\Stop;

/**
 * @property mixed type
 */
class SystemLog extends BaseModelBase
{
    const TYPE_EXCEPTION = "EXCEPTION";
    const TYPE_INSUFFICIENT_PERMISSION = "INSUFFICIENT_PERMISSION";
    const TYPE_LOGIN = "LOGIN";
    const TYPE_LOGIN_FAILED = "LOGIN_FAILED";
    const TYPE_LOGIN_AS = "LOGIN_AS";
    const TYPE_ACCESS = "ACCESS";

    public static function _getTableName()
    {
        return "system_log";
    }

    //override base update method to do nothing
    public function update() {
    }

    public static function getHeaderIpData($filterNull = false) {
        $headerIpData = [
            isset($_SERVER["HTTP_CLIENT_IP"]) ? $_SERVER["HTTP_CLIENT_IP"] : null,
            isset($_SERVER["HTTP_X_FORWARDED_FOR"]) ? $_SERVER["HTTP_X_FORWARDED_FOR"] : null,
            isset($_SERVER["HTTP_X_FORWARDED"]) ? $_SERVER["HTTP_X_FORWARDED"] : null,
            isset($_SERVER["HTTP_X_CLUSTER_CLIENT_IP"]) ? $_SERVER["HTTP_X_CLUSTER_CLIENT_IP"] : null,
            isset($_SERVER["HTTP_FORWARDED_FOR"]) ? $_SERVER["HTTP_FORWARDED_FOR"] : null,
            isset($_SERVER["HTTP_X_FORWARDED"]) ? $_SERVER["HTTP_X_FORWARDED"] : null,
            isset($_SERVER["HTTP_FORWARDED"]) ? $_SERVER["HTTP_FORWARDED"] : null,
            isset($_SERVER["REMOTE_ADDR"]) ? $_SERVER["REMOTE_ADDR"] : null,
            isset($_SERVER["HTTP_VIA"]) ? $_SERVER["HTTP_VIA"] : null
        ];
        if($filterNull) {
            $headerIpData = array_filter($headerIpData);
        }
        return $headerIpData;
    }

    /**
     * @param \Exception $ex
     * @throws \Exception
     */
    public static function createException($ex) {
        if($ex instanceof Stop) {
            return;
        }
        self::createLog(self::TYPE_EXCEPTION, json_encode([
            "code" => $ex->getCode(),
            "file" => $ex->getFile(),
            "line" => $ex->getLine(),
            "message" => $ex->getMessage(),
            "trace" => $ex->getTrace()
        ], JSON_PRETTY_PRINT));
    }

    public static function createInsufficientPermissionLog($authType, $requiredAuthName) {
        self::createLog(self::TYPE_INSUFFICIENT_PERMISSION, json_encode([
            "type" => $authType,
            "required" => $requiredAuthName
        ], JSON_PRETTY_PRINT));
    }

    /**
     * @param $type
     * @param $log
     * @throws \Exception
     */
    public static function createLog($type, $log) {
        $headerIpData = self::getHeaderIpData(true);
        $helper = new MySQLiHelper();
        $helper->exec("INSERT INTO ".self::_getTableName()." (
            `type`,
            `server_name`,
            `server_addr`,
            `request_uri`,
            `header_ip_data`,
            `log`,
            `creation_user_id`,
            `creation_real_user_id`
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?);", [
            $type,
            isset($_SERVER["SERVER_NAME"]) ? $_SERVER["SERVER_NAME"] : null,
            isset($_SERVER["SERVER_ADDR"]) ? $_SERVER["SERVER_ADDR"] : null,
            isset($_SERVER["REQUEST_URI"]) ? $_SERVER["REQUEST_URI"] : null,
            json_encode($headerIpData),
            $log,
            Auth::getUser() ? Auth::getUser()->id : null,
            Auth::getRealUser() ? Auth::getRealUser()->id : null
        ]);
    }

    public static function createLoginLog($username, $result) {
        self::createLog($result ? self::TYPE_LOGIN : self::TYPE_LOGIN_FAILED, json_encode([
            "username" => $username
        ]));
    }
    public static function createLoginAsLog($userId) {
        $user = User::load($userId);
        self::createLog(self::TYPE_LOGIN_AS, json_encode([
            "userId" => $userId,
            "username" => $user ? $user->username : null
        ]));
    }

    public static function createAccessLog($log = []) {
        if(php_sapi_name() != "cli") {
            self::createLog(self::TYPE_ACCESS, json_encode($log));
        }
    }

    /*
     * Helper functions
     */

}