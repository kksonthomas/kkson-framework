<?php

namespace App\Model;

use KKsonFramework\RedBeanPHP\Model\User as BaseUser;

/**
 * @property string $username
 * @property string $password
 * @property string $role
 * @property int $active
 * @property string|null $creation_date
 * @property int|null $creation_user_id
 * @property string|null $modified_date
 * @property int|null $modified_user_id
 */
class User extends BaseUser
{
    public static function _enabledMimicDelete(): bool
    {
        return true;
    }

    public function update()
    {
        if ((int) $this->_deleted === 0) {
            $userTable = '`' . self::_getTableName() . '`';
            $list = self::getAll(
                "SELECT u.* FROM {$userTable} u
                    WHERE u.username = ? AND u._deleted = 0 AND u.id <> ?
                    FOR UPDATE",
                [$this->username, (int) $this->id],
            );
            $dup = empty($list) ? null : reset($list);
            if ($dup != null) {
                throw new \Exception("用戶名稱已存在");
            }
        }
        parent::update();
    }

    public static function getBackendRoleList()
    {
        return [
            self::ROLE_ADMIN => "管理員",
            self::ROLE_SYSTEM_ADMIN => "系統管理員",
        ];
    }
}
