<?php

namespace KKsonFramework\RedBeanPHP\Model;

use KKsonFramework\RedBeanPHP\ModelBase\BaseModel;
use RedBeanPHP\R;

/**
 * @property mixed old_data
 * @property mixed new_data
 * @property mixed action
 */
class RecordHistory extends BaseModel
{
    public static function _getTableName()
    {
        return "record_history";
    }

    /*
     * Helper functions
     */
}