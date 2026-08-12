<?php

namespace App\Controller\backend;

use KKsonFramework\CRUD\BaseCRUDController;
use KKsonFramework\CRUD\SearchFieldType\DropdownSearchField;

abstract class BackendCRUDControllerBase extends BaseCRUDController
{
    public function main($crud)
    {
        $this->addSearchableColField(new DropdownSearchField("active", [1 => "是", 0 => "否"], null, $this->crud));
    }
}
