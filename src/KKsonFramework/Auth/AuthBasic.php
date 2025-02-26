<?php

namespace KKsonFramework\Auth;

use KKsonFramework\RedBeanPHP\Model\User;
use KKsonFramework\RedBeanPHP\Model\UserToken;

abstract class AuthBasic
{
    const AUTH_TYPE_PASSWORD = "password";
    const AUTH_TYPE_USER_TOKEN = "user_token";

    /**
     * @var callable
     */
    protected $encryptPasswordFunction = null;

    /**
     * @var User
     */
    protected $user;
    /**
     * @var User
     */
    protected $realUser;

    /**
     * @var AuthSession
     */
    protected $userSession;
    /**
     * @var AuthSession
     */
    protected $priorUserSession;

    public function __construct($prefix = null)
    {
        $this->userSession = new AuthSession($prefix);
        $this->priorUserSession = new AuthSession(($prefix ? $prefix."_" : "") . "prior");
    }

    /**
     * @param User $user
     * @param AuthSession $authSession
     * @return bool
     */
    protected function isAuthSessionValid($user, $authSession, &$error = null) {
        
        if(!$user) {
            $error = "user not found";
            return false;
        }
        if($user->_deleted) {
            $error = "user deleted";
            return false;
        }
        if(!$user->active) {
            $error = "user not active";
            return false;
        }
        //verify role if set
        if($authSession->getRole() !== null && !$user->isRole([$authSession->getRole()])) {
            $error = "user role not match";
            return false;
        }
       
        switch ($authSession->getAuthType()) {
            case self::AUTH_TYPE_PASSWORD:
                //password === false to ignore password check
                if($authSession->getPassword() === false) {
                    return true;
                } else if(!password_verify($authSession->getPassword(), $user->password)) {
                    $error = "密碼錯誤";
                    return false;
                }
                return true;
            case self::AUTH_TYPE_USER_TOKEN:
                $userToken = UserToken::findByToken($authSession->getUserToken());
                if(!$userToken) {
                    $error = "user token not found";
                    return false;
                }
                if($userToken->getUser()->username !== $authSession->getUsername()) {
                    $error = "user token not match";
                    return false;
                }
                return true;
        }
        return false;
    }

    public function login($username, $password, &$error = null) {
        ///password === false to ignore password check
        $this->userSession->setUsername($username);
        $this->userSession->setPassword($password);
        $this->userSession->setAuthType(self::AUTH_TYPE_PASSWORD);
        $user = $this->getUser(true, $error);
        if($user) {
            $this->userSession->setRole($user->role);
            $this->setRole($user->role);
        } else {
            $this->logout();
        }
        return $user;
    }

    /**
     * @param bool $force
     * @return User
     */
    public function getUser($force = false, &$error = null)
    {
        if (!$this->userSession->getUsername()) {
            $this->logout();
            return null;
        }

        if ($force || $this->user == null) {
            $user = User::findByUsername($this->userSession->getUsername());

            if ($user != null && $this->isAuthSessionValid($user, $this->userSession, $error)) {
                $this->user = $user;
            } else {
                $this->logout();
            }
        }

        return $this->user;

    }

    public function isLoggedIn($force = false)
    {
        return $this->getUser($force) !== null;
    }

    /**
     * @return bool
     */
    public function isLoginAs() {
        return $this->priorUserSession->getUsername() !== null;
    }

    public function checkLogin($callback)
    {
        if (! $this->isLoggedIn()) {
            $this->logout();
            $callback();
        }
    }

    public function checkRole($roles, $callback)
    {
        $this->checkLogin($callback);
        if (!in_array($this->getRole(), $roles)) {
            $callback();
        }
    }

    public function logout($clearAll = false)
    {
        if($clearAll) {
            $this->userSession->clearAll();
        } else {
            $this->userSession->clear();
            if($this->isLoginAs()) {
                $this->userSession->import($this->priorUserSession);
            }
            $this->priorUserSession->clear();
        }
        $this->user = null;
        $this->realUser = null;
    }

    /**
     * @param $userId
     * @return bool
     * @throws \Exception
     */
    public function loginAs($userId)
    {
        if($this->isLoginAs()) {
            throw new \Exception("Already login as.");
        }

        $user = User::load($userId);
        if(!$user) {
            throw new \Exception("User id $userId not found");
        }

        $this->priorUserSession->clear();
        $this->priorUserSession->import($this->userSession);
        $this->userSession->clear();

        return $this->login($user->username, false);
    }

    /**
     * @param bool $force
     * @return User
     */
    public function getRealUser($force = false, &$error = null) {
        if($this->isLoginAs()) {
            if($force || !$this->realUser) {
                $user = User::findByUsername($this->priorUserSession->getUsername());
                if ($user != null && $this->isAuthSessionValid($user, $this->priorUserSession, $error)) {
                    $this->realUser = $user;
                } else {
                    $this->logout();
                }
            }
            return $this->realUser;
        } else {
            return $this->getUser($force, $error);
        }
    }

    /**
     * @param $token
     * @param bool $useTokenIfSuccess
     * @param null $error
     * @return User|null
     */
    public function tokenLogin($token, $useTokenIfSuccess = true, &$error = null) {
        $userToken = UserToken::findByToken($token);
        if($userToken) {
            if(!$userToken->isUsed() && !$userToken->isExpired() && !$userToken->isVoided()) {
                $tokenData = $userToken->getDecodedData();
                $user = $userToken->getUser();
                $this->userSession->setUserToken($token);
                $this->userSession->setUsername($user->username);
                $this->userSession->setRole($tokenData["role"]);
                $this->userSession->setAuthType(self::AUTH_TYPE_USER_TOKEN);
                $user = $this->getUser(true);
                if($user) {
                    if($useTokenIfSuccess) {
                        $userToken->use();
                    }
                    return $user;
                } else {
                    $error = "get user failed";
                }
            } else {
                if($userToken->isUsed()) {
                    $error = "token used";
                }

                if($userToken->isExpired()) {
                    $error = "token expired";
                }

                if($userToken->isVoided()) {
                    $error = "token voided";
                }
            }
        } else {
            $error = "token not found";
        }
        return null;
    }

    public function getRealRole() {
        if($this->isLoginAs()) {
            return $this->priorUserSession->getRole();
        } else {
            return $this->userSession->getRole();
        }
    }

    public function getRole() {
        return $this->userSession->getRole();
    }

    public function setRole($role) {
        $this->userSession->setRole($role);
    }

    /**
     * @return AuthSession
     */
    public function getUserSession()
    {
        return $this->userSession;
    }

    //original base codes

    public function setEncryptPasswordFunction($func) {
        $this->encryptPasswordFunction = $func;
    }
}