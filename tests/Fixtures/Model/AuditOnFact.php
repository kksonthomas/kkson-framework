<?php

namespace KKsonFramework\Tests\Fixtures\Model;

use KKsonFramework\RedBeanPHP\ModelBase\BaseModelBase;

class AuditOnFact extends BaseModelBase
{
    public static function _getTableName()
    {
        return "audit_on_fact";
    }
}
