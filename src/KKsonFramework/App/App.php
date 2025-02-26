<?php

namespace KKsonFramework\App;

use KKsonFramework\Conf\AppConfig;
use DateTime;
use KKsonFramework\Auth\Auth;
use KKsonFramework\CRUD\Middleware\CSRFGuard;
use KKsonFramework\CRUD\SlimKKsonCRUD;
use KKsonFramework\RedBeanPHP\Model\BanIpList;
use KKsonFramework\RedBeanPHP\Model\SystemLog;
use RedBeanPHP\R;
use KKsonFramework\Utils\UrlUtils;

class App
{
    public static $translate;
    private static $crud = null;
    private static $maxLoginFailureBeforeBan = 8;
    private static $isInitiated = false;

    public static function init()
    {
        if(self::$isInitiated) {
            return false;
        }
        self::$isInitiated = true;
        error_reporting(E_ALL ^ E_DEPRECATED);
        ini_set('display_errors', 'On');

        CSRFGuard::setActive(false);
        if(!self::isCli()) {
            register_shutdown_function([App::class, 'onShutdown']);


            if(AppConfig::get()->forceSsl() && !UrlUtils::isSSL()){
                $port = isset($_SERVER['SERVER_PORT']) ? ':' . $_SERVER['SERVER_PORT'] : '';
                $redirect = 'https://' . $_SERVER['HTTP_HOST'] . $port . $_SERVER['REQUEST_URI'];
                header('HTTP/1.1 302 Moved Temporarily');
                header('Location: ' . $redirect);
                exit();
            }

            if(!self::isEnabledBackend() && !self::isUAT()) {
                if(self::isEnabledFrontend()) {
                    header("Location: /");
                } else {
                    http_response_code(404);
                }

                die();
            }

            if (!self::isUAT()) {
                error_reporting(E_ERROR ^ E_DEPRECATED);
                ini_set('display_errors', 'Off');
            } else {
                $whoops = new \Whoops\Run;
                $whoops->pushHandler(new \Whoops\Handler\PrettyPageHandler);
                $whoops->register();
            }
        }

        mb_internal_encoding('utf-8');
        date_default_timezone_set("Asia/Hong_Kong");
        ini_set('post_max_size', '64M');
        ini_set('upload_max_filesize', '64M');

        return true;
    }

    public static function setCrud(SlimKKsonCRUD $crud) {
        self::$crud = $crud;
    }

    public static function checkIp()
    {
        if(!Auth::isLoggedIn()) {
            $bannedIpList = self::isCurrentIpBanned();
            if(self::$crud  && $bannedIpList) {
                //banned
                http_response_code(403);
                die(self::$crud->render("ip_banned_page", ["banIpList" => $bannedIpList]));
            }
        }
    }

    public static function isUAT()
    {
        return AppConfig::get()->env() === "uat";
    }

    public static function getFrontendBaseUrl() {
        return @(AppConfig::get()->frontendBaseUrl());
    }

    public static function getBackendBaseUrl() {
        return @(AppConfig::get()->backendBaseUrl());
    }

    public static function isBackend()
    {
        return basename($_SERVER["SCRIPT_FILENAME"], '.php') == "backend";
    }

    public static function isFrontend()
    {
        return basename($_SERVER["SCRIPT_FILENAME"], '.php') == "index";
    }

    public static function isEnabledBackend()
    {
        return @(AppConfig::get()->backend());
    }

    public static function isEnabledFrontend()
    {
        return @(AppConfig::get()->frontend());;
    }

    public static function onShutdown() {
        if(isset($_SERVER['DOCUMENT_ROOT']) && !empty($_SERVER['DOCUMENT_ROOT'])) {
            chdir($_SERVER['DOCUMENT_ROOT']);
        }
        $error = error_get_last();
        if (@$error['type'] === E_ERROR ) {
            SystemLog::createLog("FATAL_ERROR", json_encode($error, JSON_PRETTY_PRINT));
        }
    }

    public static function isCli()
    {
        if( empty($_SERVER['REMOTE_ADDR']) && !isset($_SERVER['HTTP_USER_AGENT']) && is_array($_SERVER['argv']) && count($_SERVER['argv']) > 0)
        {
            return true;
        }
        return false;
    }

    /**
     * @return SlimKKsonCRUD
     */
    public static function getCrud()
    {
        return self::$crud;
    }

    public static function isCurrentIpBanned() {
        if(App::isCli()) {
            return false;
        }
        $ipData = SystemLog::getHeaderIpData(true);
        $ip = reset($ipData);
        if(!$ip) {
            throw new \Exception("Unknown Client Ip Address");
        }

        $bannedIpList = BanIpList::getBannedIpList($ip);
        if($bannedIpList) {
            return $bannedIpList;
        }
        return self::updateIpLoginFailedBanStatus($ip);
    }

    public static function updateIpLoginFailedBanStatus($ip = null) {
        if(!$ip) {
            $ipData = SystemLog::getHeaderIpData(true);
            $ip = reset($ipData);
        }

        if($ip) {
            $failCount = self::checkIpLoginFailedCount($ip);

            if($failCount >= self::$maxLoginFailureBeforeBan) {
                if(isset($_SESSION['msg'])) {
                    unset($_SESSION['msg']);
                }
                return BanIpList::createBanIp($ip, "Login failed ".self::$maxLoginFailureBeforeBan." times", self::$maxLoginFailureBeforeBan." 次登入失敗", BanIpList::getNextIpAutoUnbannedDate($ip) , 1);
            }
            return false;
        }
        return false;
    }

    public static function getIpLastLoginSucceedDate($ip) {
        return R::getCell("SELECT creation_date FROM system_log WHERE `type`= ? AND header_ip_data LIKE ? ORDER BY id DESC LIMIT 1", [SystemLog::TYPE_LOGIN, "%$ip%"]);
    }

    public static function checkIpLoginFailedCount($ip) {
        $lastUnbannedDateString = BanIpList::getIpLastUnbannedDate($ip);

        if(!$lastUnbannedDateString) {
            $lastUnbannedDate = new DateTime("0000-00-00 00:00:00");
        } else {
            $lastUnbannedDate = new DateTime($lastUnbannedDateString);
        }

        $generalCheckRangeDate = (new DateTime())->sub(new \DateInterval("P1D"));

        $lastLoginSuccessDateString = self::getIpLastLoginSucceedDate($ip);
        if(!$lastLoginSuccessDateString) {
            $lastLoginSuccessDate = new DateTime("0000-00-00 00:00:00");
        } else {
            $lastLoginSuccessDate = new DateTime($lastLoginSuccessDateString);
        }

        if($lastUnbannedDate < $generalCheckRangeDate) {
            $lastUnbannedDate = $generalCheckRangeDate;
        }

        $date = max($lastUnbannedDate, $generalCheckRangeDate, $lastLoginSuccessDate)->format("Y-m-d H:i:s");


        $failCountSql = "SELECT COUNT(*) FROM system_log WHERE `type` = 'LOGIN_FAILED' AND header_ip_data LIKE ? AND creation_date > ?";
        $failCountData = ["%$ip%", $date];


        $failCount = R::getCell($failCountSql, $failCountData) - 0;
        return $failCount;
    }


}