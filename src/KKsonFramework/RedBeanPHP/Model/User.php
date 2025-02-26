<?php

namespace KKsonFramework\RedBeanPHP\Model;

use KKsonFramework\RedBeanPHP\ModelBase\BaseModelWithRecordHistory;
use RedBeanPHP\R;

/**
 * @property mixed active
 * @property mixed advisor_code
 * @property mixed agent_code
 * @property mixed client_code
 * @property mixed role
 * @property mixed password
 * @property mixed username
 */
class User extends BaseModelWithRecordHistory
{
    const ROLE_SYSTEM_ADMIN = "SYSADMIN";
    const ROLE_ADMIN = "ADMIN";
    const ROLE_GENERAL_USER = "GENERAL USER";

    public static function _getTableName()
    {
        return "user";
    }

    public static function _enabledMimicDelete()
    {
        return true;
    }

    /**
     * @return User
     */
    public static function findByUsername(string $username) {
        return self::findOne("username = ?",  [$username]);
    }

    /**
     * @return array
     */
    public function getPermissionList() {
        if(!$this->hasCache("permission_list")) {
            $this->putCache("permission_list", Permission::getPermissionListByUserId($this->id));
        }
        return $this->getCache("permission_list");
    }

    public static function getRoleList() {
        return [
            self::ROLE_GENERAL_USER => "一般用戶",
            self::ROLE_ADMIN => "管理員", 
            self::ROLE_SYSTEM_ADMIN => "系統管理員"
        ];
    }

    public function getRoleName() {
        return @self::getRoleList()[$this->role] ?? $this->role;
    }

    public function isRole($roles) {
        foreach ($roles as $role) {
            if($this->role == $role) {
                return true;
            }
        }
        return false;
    }

    public function isAdmin() {
        return $this->isRole([self::ROLE_ADMIN]);
    }

    public function isSystemAdmin() {
        return $this->isRole([self::ROLE_SYSTEM_ADMIN]);
    }

    public function isPermitted($role, $permissionName) {
        if($role == User::ROLE_SYSTEM_ADMIN) {
            return true;
        } else if($role == User::ROLE_ADMIN) {
            return isset($this->getPermissionList()[$permissionName]);
        } else {
            return false;
        }
    }

    public function changePassword($newPassword) {
        R::exec("UPDATE `user` SET password = ? WHERE id = ?", [
            password_hash($newPassword, PASSWORD_DEFAULT),
            $this->id
        ]);
    }

    /**
     * @param int $offset
     * @return SystemLog
     */
    public function getLastLoginSystemLog($offset = 1) {
        //$offset default is 1 because the first record should be the current user who just logged in
        return SystemLog::findOne("type IN (? , ?) AND request_uri = ? AND log->>\"$.username\" = ? ORDER BY id DESC LIMIT 1 OFFSET ?", ["LOGIN", "LOGIN_FAILED", "/auth/login", $this->username, $offset]);
    }

    public function isPasswordSet()
    {
        return $this->password !== "";
    }
}