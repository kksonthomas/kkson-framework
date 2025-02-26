<?php
namespace KKsonFramework\App\Controller\frontend\route;

use KKsonFramework\Auth\FrontendAuth;
use KKsonFramework\Auth\Auth;
use KKsonFramework\RedBeanPHP\Model\UserToken;

class GeneralFrontendRouteController extends FrontendRouteControllerBase
{
    public function index()
    {
        echo $this->app->render("home");
    }

    function loginAs($userId)
    {
        $token = $this->slim->request->get("token");
        $tokenObj = UserToken::findByToken($token);
        if ($tokenObj) {
            if ($tokenObj->isUsed()) {
                die("User token is used.");
            }
            if ($tokenObj->isVoided()) {
                die("User token is voided.");
            }
            if ($tokenObj->isExpired()) {
                die("User token is expired.");
            }
            if ($tokenObj->type != UserToken::TYPE_FRONTEND_LOGIN_AS) {
                die("User token type invalid.");
            }
            /** @var FrontendAuth $auth */
            $auth = Auth::getAuthLogic();
            $auth->logout();
            $error = null;
            if ($auth->tokenLogin($token, true, $error)) {
                Auth::setRole($tokenObj->getDecodedData()["role"]);
                Auth::loginAs($userId);
                $this->slim->redirect("/");
            } else {
                die("User token login failed: $error");
            }
        } else {
            die("User token not found.");
        }
    }
}