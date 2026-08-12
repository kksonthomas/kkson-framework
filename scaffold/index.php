<?php
require_once "vendor/autoload.php";

use App\Auth\FrontendAuth;
use App\Controller\frontend\route\GeneralFrontendRouteController;
use App\FrontendApp;
use App\AppBeanHelper;
use KKsonFramework\Auth\Auth;
use KKsonFramework\App\DB;
use KKsonFramework\Classes\Slim\Middleware\PrettyExceptions;

$app = FrontendApp::init();
DB::init(new AppBeanHelper());
Auth::setAuthLogic(new FrontendAuth());
FrontendApp::checkIp();
$slim = $app->getSlim();
$slim->response->headers->remove("Content-Security-Policy");

$slim->group("", function () {
}, function () use ($slim) {
    $slim->get("/", [GeneralFrontendRouteController::class, "index"]);
    $slim->get("/loginAs/:id", [GeneralFrontendRouteController::class, "loginAs"]);
});

$slim->add(new PrettyExceptions());
$slim->run();
