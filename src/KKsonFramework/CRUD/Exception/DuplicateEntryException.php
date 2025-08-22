<?php

namespace KKsonFramework\CRUD\Exception;


class DuplicateEntryException extends \Exception
{
    
    /**
     * BeanNotNullException constructor.
     */
    public function __construct($fieldName, $fieldValue)
    {
        parent::__construct("Duplicate entry for field '$fieldName' with value '$fieldValue'.");
    }
}