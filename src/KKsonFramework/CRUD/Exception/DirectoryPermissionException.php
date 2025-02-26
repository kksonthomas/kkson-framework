<?php

namespace KKsonFramework\CRUD\Exception;


class DirectoryPermissionException extends \Exception
{
    
    /**
     * BeanNotNullException constructor.
     */
    public function __construct($dir)
    {
        parent::__construct("You have no permission to write file in the directory '$dir'.");
    }
}