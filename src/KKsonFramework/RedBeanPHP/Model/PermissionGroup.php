<?php

namespace KKsonFramework\RedBeanPHP\Model;

use KKsonFramework\RedBeanPHP\ModelBase\BaseModel;

/**
 * @property int id
 * @property string name
 * @property string display_name
 * @property int display_weight
 * @property string creation_date
 * @property int|null creation_user_id
 * @property string modified_date
 * @property int|null modified_user_id
 */
class PermissionGroup extends BaseModel
{
    public static function _getTableName()
    {
        return "permission_group";
    }

    public static function findByName($name) {
        return self::findOne('name = ?', [$name]);
    }

    public static function findByDisplayName($displayName) {
        return self::findOne('display_name = ?', [$displayName]);
    }
}

