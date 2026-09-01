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
    private static $hasClientIpColumn = null;

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
     * Client IP using the same header priority as getHeaderIpData(true).
     * Splits comma-separated proxy chains and returns the first valid IPv4/IPv6 address.
     *
     * @return string|null
     */
    public static function getClientIp() {
        foreach (self::getHeaderIpData(true) as $headerValue) {
            foreach (explode(",", (string)$headerValue) as $candidate) {
                $candidate = trim($candidate);
                if ($candidate !== "" && filter_var($candidate, FILTER_VALIDATE_IP)) {
                    return $candidate;
                }
            }
        }
        return null;
    }

    /**
     * Whether system_log.client_ip exists (cached per request).
     */
    public static function hasClientIpColumn() {
        if (self::$hasClientIpColumn !== null) {
            return self::$hasClientIpColumn;
        }
        try {
            $helper = new MySQLiHelper();
            $rows = $helper->query(
                "SHOW COLUMNS FROM `" . self::_getTableName() . "` LIKE 'client_ip'"
            );
            self::$hasClientIpColumn = is_array($rows) && count($rows) > 0;
        } catch (\Throwable $e) {
            self::$hasClientIpColumn = false;
        }
        return self::$hasClientIpColumn;
    }

    public static function resetClientIpColumnCache() {
        self::$hasClientIpColumn = null;
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
     */
    public static function createLog($type, $log) {
        try {
            $headerIpData = self::getHeaderIpData(true);
            $helper = new MySQLiHelper();
            $requestUri = isset($_SERVER["REQUEST_URI"]) ? $_SERVER["REQUEST_URI"] : null;
            if ($requestUri !== null && mb_strlen($requestUri) > 1000) {
                $requestUri = mb_substr($requestUri, 0, 1000);
            }
            $params = [
                $type,
                isset($_SERVER["SERVER_NAME"]) ? $_SERVER["SERVER_NAME"] : null,
                isset($_SERVER["SERVER_ADDR"]) ? $_SERVER["SERVER_ADDR"] : null,
                $requestUri,
                json_encode($headerIpData),
                $log,
                Auth::getUser() ? Auth::getUser()->id : null,
                Auth::getRealUser() ? Auth::getRealUser()->id : null
            ];
            if (self::hasClientIpColumn()) {
                $clientIp = self::getClientIp();
                if ($clientIp !== null && strlen($clientIp) > 45) {
                    $clientIp = substr($clientIp, 0, 45);
                }
                $params[] = $clientIp;
                $helper->exec("INSERT INTO ".self::_getTableName()." (
                `type`,
                `server_name`,
                `server_addr`,
                `request_uri`,
                `header_ip_data`,
                `log`,
                `creation_user_id`,
                `creation_real_user_id`,
                `client_ip`
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?);", $params);
            } else {
                $helper->exec("INSERT INTO ".self::_getTableName()." (
                `type`,
                `server_name`,
                `server_addr`,
                `request_uri`,
                `header_ip_data`,
                `log`,
                `creation_user_id`,
                `creation_real_user_id`
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?);", $params);
            }
        } catch (\Throwable $e) {
            error_log("SystemLog::createLog failed: " . $e->getMessage());
        }
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