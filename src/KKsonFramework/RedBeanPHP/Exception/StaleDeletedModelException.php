<?php

namespace KKsonFramework\RedBeanPHP\Exception;

class StaleDeletedModelException extends \Exception
{
    public function __construct(string $table, int $id, ?string $detail = null)
    {
        $message = "Cannot save {$table} id={$id}: row is soft-deleted in the database"
            . " while the in-memory bean is still active (_deleted=0).";
        if ($detail !== null) {
            $message .= " " . $detail;
        }
        parent::__construct($message);
    }
}
