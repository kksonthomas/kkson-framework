<?php

namespace KKsonFramework\RedBeanPHP\Model;

use KKsonFramework\RedBeanPHP\ModelBase\BaseModelBase;
use RedBeanPHP\R;

/**
 * @property mixed old_data
 * @property mixed new_data
 * @property mixed action
 */
class RecordHistory extends BaseModelBase
{
    public static function _getTableName()
    {
        return "record_history";
    }

    /*
     * Helper functions
     */
}