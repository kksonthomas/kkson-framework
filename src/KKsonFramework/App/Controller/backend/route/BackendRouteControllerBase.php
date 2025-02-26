<?php

namespace KKsonFramework\App\Controller\backend\route;

use KKsonFramework\App\App;

use KKsonFramework\CRUD\SlimKKsonCRUD;
use Slim\Slim;

class BackendRouteControllerBase
{
    /**
     * @var SlimKKsonCRUD
     */
    protected $crud;
    /**
     * @var Slim
     */
    protected $slim;

    public function __construct()
    {
        $this->crud = App::getCrud();
        $this->slim = $this->crud->getSlim();
    }
}