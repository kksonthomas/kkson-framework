<?php

require_once "vendor/autoload.php";

use App\BackendApp;
use App\AppBeanHelper;
use App\BackendCrud;
use App\Auth\BackendAuth;
use App\Controller\backend\UserController;
use App\Controller\backend\route\GeneralBackendRouteController;
use App\Model\SystemLog;
use KKsonFramework\App\DB;
use KKsonFramework\Auth\Auth;
use KKsonFramework\Classes\Slim\Middleware\PrettyExceptions;
use KKsonFramework\Utils\UrlUtils;

BackendApp::init();
DB::init(new AppBeanHelper());

$crud = new BackendCrud();
BackendApp::setCrud($crud);
Auth::setAuthLogic(new BackendAuth());
BackendApp::checkIp();
$crud->setInsufficientPermissionViewName("backend/no_permission");
$slim = $crud->getSlim();
$slim->response->headers->remove("Content-Security-Policy");
$slim->notFound(function () use ($crud) {
    echo $crud->render("404_not_found");
});

$basePath = UrlUtils::baseUrl();

$slim->group($basePath, function () use ($crud) {
}, function () use ($crud, $slim) {
});

$slim->group($basePath, function () use ($crud) {
    $crud->checkLogin();
}, function () use ($crud, $slim) {
    $slim->get("/", [GeneralBackendRouteController::class, "index"]);
    $slim->get("/home", [GeneralBackendRouteController::class, "home"]);
    $slim->get("/login_as/:id", [GeneralBackendRouteController::class, "login_as"]);
    $slim->get("/frontend_login_as/:id", [GeneralBackendRouteController::class, "frontend_login_as"]);
    $slim->get("/profile", [GeneralBackendRouteController::class, "profile"]);
    $slim->post("/profile", [GeneralBackendRouteController::class, "post_profile"]);
    $slim->get("/logout", [GeneralBackendRouteController::class, "logout"]);
});

$slim->group("", function () use ($crud) {
    $crud->checkLogin();
}, function () use ($crud, $slim) {
    $crud->add("user", new UserController());
});

$crud->config(function () use ($crud) {
    $crud->hideFields(["id"]);
    $crud->enableSearch(false);
    $crud->enableColSearch(true);

    $crud->setIsInsertUpdateUseTransaction(true);
    $crud->useActionDropdown();

    if (AppBeanHelper::isCurrentTableEnabledMimicDelete($crud)) {
        $crud->find("_deleted = 0");
    }

    $onInsertUpdateError = function ($internalErrorMsg, $ex) {
        SystemLog::createException($ex);
        return $internalErrorMsg;
    };
    $crud->setOnInsertError($onInsertUpdateError);
    $crud->setOnUpdateError($onInsertUpdateError);

    $crud->setCommonButtonClasses("btn-sm");
    $crud->setHeaderButtonClasses("btn-sm");
    $crud->setHeaderButtonGroupClasses("btn-group-sm");

    $crud->setListViewHeaderButtonHtml(function () use ($crud) {
        $headerButtonHTML = "<div class='btn-group'>";
        $headerButtonHTML .= $crud->getCreateButtonHtml();
        $headerButtonHTML .= $crud->getExportButtonHtml();
        $headerButtonHTML .= $crud->getDatatableColFilterButtonHtml();
        $headerButtonHTML .= $crud->getListViewRefreshButtonHtml();
        $headerButtonHTML .= "</div>";
        return $headerButtonHTML;
    });

    $crud->field("creation_date")->setSearchable(false);
    $crud->field("creation_user_id")->setSearchable(false);
    $crud->field("modified_date")->setSearchable(false);
    $crud->field("modified_user_id")->setSearchable(false);
});

$slim->add(new PrettyExceptions());
$slim->run();
