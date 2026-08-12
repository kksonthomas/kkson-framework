<?php

namespace App\Auth;

use App\Model\SystemLog;
use KKsonFramework\Auth\Auth;
use KKsonFramework\Auth\AuthBasic;
use KKsonFramework\RedBeanPHP\Model\Permission;
use KKsonFramework\RedBeanPHP\Model\Session;

class BackendAuth extends AuthBasic
{
    /**
     * @var bool
     */
    private $isLoggingIn = false;

    public function __construct()
    {
        parent::__construct();
    }

    protected function isAuthSessionValid($user, $authSession, &$error = null)
    {
        $result = parent::isAuthSessionValid($user, $authSession, $error);
        if ($result) {
            if (
                $user->isSystemAdmin()
                || $user->isAdmin()
            ) {
                $result = true;
            } else {
                $result = false;
            }

            if (!$this->isLoggingIn && !$user->isSystemAdmin()) {
                $session = Session::getLastSessionByUser($user);
                if ($session && !$session->isMatchedToCurrentSession()) {
                    return false;
                }
            }
        }
        return $result;
    }

    public function login($username, $password, &$error = null)
    {
        $this->isLoggingIn = true;
        $result = parent::login($username, $password, $error);
        $this->isLoggingIn = false;
        if ($result) {
            $session = Session::getLastSessionByUser($result);
            if (!$session || !$session->isMatchedToCurrentSession()) {
                Session::createSessionByCurrentUser();
            }
        }
        return $result;
    }

    public function loginAs($userId)
    {
        if (!Auth::isPermitted(Permission::PERMISSION_BACKEND_LOGIN_AS)) {
            SystemLog::createInsufficientPermissionLog("permission", Permission::PERMISSION_BACKEND_LOGIN_AS);
            throw new \Exception("Access denied.");
        }

        return parent::loginAs($userId);
    }
}
