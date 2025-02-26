<?php

namespace KKsonFramework\App\Controller\frontend\route;

use KKsonFramework\App\FrontendApp;
use Slim\Slim;

class FrontendRouteControllerBase
{
    /**
     * @var FrontendApp
     */
    protected $app;
    /**
     * @var Slim
     */
    protected $slim;

    public function __construct()
    {
        $this->app = FrontendApp::getInstance();
        $this->slim = $this->app->getSlim();
    }

    public function enableJSONResponse()
    {
        $this->slim->response->header('Content-Type', 'application/json');
    }
}