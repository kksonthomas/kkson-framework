<?php

namespace App;

use KKsonFramework\CRUD\SlimKKsonCRUD;
use RedBeanPHP\RedException;

class BackendCrud extends SlimKKsonCRUD
{
    /**
     * @throws RedException
     */
    public function __construct()
    {
        $this->setLoginViewName("backend/login");
        $this->setMenuViewName("backend/backend_menu");
        $this->setInsufficientPermissionViewName("backend/no_permission");
        parent::__construct("crud", "ajax", null);
    }
}
