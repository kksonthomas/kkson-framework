<?php

namespace KKsonFramework\Auth;


use KKsonFramework\RedBeanPHP\Model\SystemLog;
use KKsonFramework\RedBeanPHP\Model\User;

class Auth
{
    /**
     * @var AuthBasic
     */
    private static $authLogic = null;

    /**
     * @return AuthBasic
     */
    public static function getAuthLogic()
    {
        return self::$authLogic;
    }

    /**
     * @param AuthBasic $authLogic
     */
    public static function setAuthLogic($authLogic)
    {
        self::$authLogic = $authLogic;
        SystemLog::createAccessLog();
    }

    /**
     * @param string $username
     * @param string $password
     * @return mixed
     */
    public static function login($username, $password, &$error = null) {
        $result = self::getAuthLogic()->login($username, $password, $error);
        SystemLog::createLoginLog($username, $result);
        return $result;

    }

    public static function setEncryptPasswordFunction($func) {
        self::getAuthLogic()->setEncryptPasswordFunction($func);
    }

    /**
     * @param bool $force
     * @return User
     */
    public static function getUser($force = false) {
        return self::getAuthLogic() ? self::getAuthLogic()->getUser($force) : null;
    }

    public static function isLoggedIn() {
        return self::getAuthLogic()->isLoggedIn();
    }

    public static function checkLogin($callback) {
        self::getAuthLogic()->checkLogin($callback);
    }

    public static function checkRole($roles, $callback) {
        self::getAuthLogic()->checkRole($roles, $callback);
    }

    public static function logout() {
        self::getAuthLogic()->logout();
    }

    /**
     * @return string
     */
    public static function getRole() {
        return self::getAuthLogic()->getRole();
    }
    public static function setRole($role) {
        self::getAuthLogic()->setRole($role);
    }

    public static function isLoginAs() {
        return self::getAuthLogic()->isLoginAs();
    }

    public static function isCurrentRole($array)
    {
        $user = Auth::getUser();
        if (!$user) {
            return false;
        }
        if (!is_array($array)) {
            $array = [$array];
        }
        foreach ($array as $role) {
            switch ($role) {
                default:
                    if (Auth::getRole() == $role) {
                        return true;
                    }
            }
        }
        return false;
    }

    public static function isPermitted($permissionName) {
        $user = Auth::getUser();
        if($user) {
            return $user->isPermitted(Auth::getRole(), $permissionName);
        }
        return false;
    }

    public static function loginAs($userId)
    {
        SystemLog::createLoginAsLog($userId);
        return self::getAuthLogic()->loginAs($userId);
    }

    public static function getRealUser() {
        return self::getAuthLogic() ? self::getAuthLogic()->getRealUser() : null;
    }
}