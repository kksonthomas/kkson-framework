<?php

namespace App\Auth;

use App\Model\SystemLog;
use App\Model\User;
use KKsonFramework\Auth\Auth;
use KKsonFramework\Auth\AuthBasic;
use KKsonFramework\Auth\AuthSession;
use KKsonFramework\RedBeanPHP\Model\Permission;
use KKsonFramework\RedBeanPHP\Model\Session;

class FrontendAuth extends AuthBasic
{
    /**
     * @var bool
     */
    private $isLoggingIn = false;

    public function __construct()
    {
        parent::__construct("fn");
    }

    /**
     * @param User $user
     * @param AuthSession $authSession
     * @return bool
     */
    protected function isAuthSessionValid($user, $authSession, &$error = null)
    {
        $result = parent::isAuthSessionValid($user, $authSession, $error);
        if ($result) {
            if ($user->isRole([User::ROLE_GENERAL_USER])) {
                $result = true;
            } else {
                $result = false;
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
        if (!Auth::isPermitted(Permission::PERMISSION_FRONTEND_LOGIN_AS)) {
            SystemLog::createInsufficientPermissionLog("permission", Permission::PERMISSION_FRONTEND_LOGIN_AS);
            throw new \Exception("Access denied.");
        }

        return parent::loginAs($userId);
    }
}
