<?php

namespace KKsonFramework\Tests\Fixtures\Model;

use KKsonFramework\RedBeanPHP\ModelBase\BaseModelBase;

class AuditOffFact extends BaseModelBase
{
    public static function _getTableName()
    {
        return "audit_off_fact";
    }

    public static function _enabledAuditFields(): bool
    {
        return false;
    }
}
