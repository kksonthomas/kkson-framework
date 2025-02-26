<?php

namespace KKsonFramework\CRUD\FieldType;


use KKsonFramework\CRUD\Exception\DirectoryPermissionException;
use KKsonFramework\Utils\UrlUtils;

class Camera extends Image
{
    protected $additionalAttr = "accept=\"image/*;capture=camera\"";
}