<?php

namespace KKsonFramework\Auth;

use KKsonFramework\Classes\Session;

class AuthSession extends Session
{
    public function getUsername() {
        return $this->getSessionField("username");
    }
    public function getPassword() {
        return $this->getSessionField("password");
    }
    public function getRole() {
        return $this->getSessionField("role");
    }
    public function getAuthType() {
        return $this->getSessionField("auth_type");
    }
    public function getUserToken() {
        return $this->getSessionField("user_token");
    }

    public function setUsername($v) {
        $this->setSessionField("username", $v);
    }
    public function setPassword($v) {
        $this->setSessionField("password", $v);
    }
    public function setRole($v) {
        $this->setSessionField("role", $v);
    }
    public function setAuthType($v) {
        $this->setSessionField("auth_type", $v);
    }
    public function setUserToken($v) {
        $this->setSessionField("user_token", $v);
    }

    /**
     * @param AuthSession $authSession
     */
    public function import($authSession) {
        $this->setSessionField("username", $authSession->getUsername());
        $this->setSessionField("password", $authSession->getPassword());
        $this->setSessionField("role", $authSession->getRole());
        $this->setSessionField("auth_type", $authSession->getAuthType());
        $this->setSessionField("user_token", $authSession->getUserToken());
    }

    public function clear() {
        $this->unsetSessionField("username");
        $this->unsetSessionField("password");
        $this->unsetSessionField("role");
        $this->unsetSessionField("auth_type");
        $this->unsetSessionField("user_token");
    }
}