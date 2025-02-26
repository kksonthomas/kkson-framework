<?php

namespace KKsonFramework\App\Controller\frontend\route;

use KKsonFramework\Auth\Auth;
use KKsonFramework\Utils\UrlUtils;
use Slim\Slim;

class AuthFrontendRouteController extends FrontendRouteControllerBase
{
    /**
     * @param Slim $slim
     */
    public static function bind($slim) {
        /**@uses AuthFrontendRouteController::login**/
        $slim->get("/auth/login", static::class .":login");

        /**@uses AuthFrontendRouteController::post_login**/
        $slim->post("/auth/login", static::class .":post_login");

        /**@uses AuthFrontendRouteController::logout**/
        $slim->get("/auth/logout", static::class .":logout");
    }

    function logout() {
        if(!Auth::isLoginAs()) {
            Auth::logout();
        } else {
            Auth::logout();
            Auth::logout();
        }
        $this->slim->redirect(UrlUtils::url("/", true));
    }
}