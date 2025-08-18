<?php

namespace KKsonFramework\CRUD\Exception;


class EditReadOnlyRecordException extends \Exception
{
    
    /**
     * EditReadOnlyRecordException constructor.
     */
    public function __construct()
    {
        parent::__construct("This record is read only.");
    }
}