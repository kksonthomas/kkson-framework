<?php

namespace App\Controller\backend\route;

use App\BackendApp;
use KKsonFramework\App\Controller\backend\route\BackendRouteControllerBase;
use KKsonFramework\Auth\Auth;
use KKsonFramework\Auth\AuthBasic;
use KKsonFramework\Conf\AppConfig;
use KKsonFramework\CRUD\Middleware\CSRFGuard;
use KKsonFramework\RedBeanPHP\Model\UserToken;
use KKsonFramework\Utils\UrlUtils;

class GeneralBackendRouteController extends BackendRouteControllerBase
{
    public function index()
    {
        $this->slim->redirect("/backend/home");
    }

    public function home()
    {
        echo $this->crud->render("backend/home", []);
    }

    public function login_as($userId)
    {
        /** @var AuthBasic $auth */
        Auth::loginAs($userId);

        if (BackendApp::isBackend()) {
            BackendApp::getCrud()->getSlim()->redirect("/backend");
        } else {
            BackendApp::getCrud()->getSlim()->redirect("/");
        }
    }

    public function frontend_login_as($userId)
    {
        $token = UserToken::genToken(UserToken::TYPE_FRONTEND_LOGIN_AS, ["role" => Auth::getRole()]);
        $frontendBaseUrl = AppConfig::get()->frontendBaseUrl();
        if ($frontendBaseUrl) {
            $scheme = UrlUtils::isSSL() ? "https://" : "http://";
            $url = $scheme . $frontendBaseUrl . "/loginAs/$userId";
        } else {
            $backendBase = rtrim(UrlUtils::baseUrl(true), "/");
            $frontendBase = preg_replace("#/backend$#", "", $backendBase);
            $url = ($frontendBase === "" ? "" : $frontendBase) . "/loginAs/$userId";
        }

        $csrfTag = CSRFGuard::inputTag();
        echo "<html>
<body>
    <form id='formRedirect' style='display:none' action='$url' method='get'>
        $csrfTag
        <input type='hidden' name='token' value='$token' />
    </form>
    <script>
        window.formRedirect.submit();
    </script>
</body>
</html>";
    }

    public function profile()
    {
        $this->crud->setData("title", "帳戶資料");
        $this->crud->setData("pageTitle", "帳戶資料");

        echo $this->crud->render("backend/template/profile");
    }

    public function post_profile()
    {
        if ($_POST['password'] != $_POST['confirm_password']) {
            $_SESSION['error'] = '確認密碼不符合';
        } else if (strlen($_POST['password']) < 8) {
            $_SESSION['error'] = '密碼需至少8位';
        } else {
            $user = Auth::getUser(true);
            $user->changePassword($_POST['password']);
            Auth::logout();
            Auth::login($user->username, $_POST['password']);
            $_SESSION['success'] = "更改密碼成功";
        }

        $this->slim->redirect(UrlUtils::burl("/profile"));
    }

    public function logout()
    {
        Auth::logout();
        $this->slim->redirect(UrlUtils::burl("/"));
    }
}
