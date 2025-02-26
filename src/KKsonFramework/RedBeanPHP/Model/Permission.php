<?php

namespace KKsonFramework\RedBeanPHP\Model;


use KKsonFramework\RedBeanPHP\ModelBase\BaseModelBase;
use RedBeanPHP\OODBBean;
use RedBeanPHP\R;

/**
 * @property mixed display_name
 * @property mixed name
 * @property mixed group
 */
class Permission extends BaseModelBase
{
    const PERMISSION_USER_ADMIN_VIEW = "USER_ADMIN_VIEW";
    const PERMISSION_USER_ADMIN_MODIFY = "USER_ADMIN_MODIFY";

    const PERMISSION_BACKEND_LOGIN_AS = "BACKEND_LOGIN_AS";
    const PERMISSION_FRONTEND_LOGIN_AS = "FRONTEND_LOGIN_AS";

    const PERMISSION_EMAIL_HISTORY_VIEW = "EMAIL_HISTORY_VIEW";

    public static function _getTableName()
    {
        return "permission";
    }

    public static function getPermissionListByUserId($userId){
        return R::getAssoc("SELECT p.`name`, p.display_name FROM ".self::_getTableName()." p 
                                    JOIN permission_user up ON up.permission_id = p.id
                                WHERE up.user_id = ?", [$userId]);
    }
}