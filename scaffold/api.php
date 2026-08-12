<?php

require_once "vendor/autoload.php";

use App\Auth\FrontendAuth;
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

// API routes — add controllers here as needed.
$slim->group("/api", function () {
}, function () use ($slim) {
});

$slim->add(new PrettyExceptions());
$slim->run();
