<?php

namespace App;

use KKsonFramework\App\FrontendApp as FrontendAppBase;
use KKsonFramework\Auth\Auth;

class FrontendApp extends FrontendAppBase
{
    public static function checkIp(): void
    {
        if (Auth::isLoggedIn()) {
            return;
        }

        $bannedIpList = self::isCurrentIpBanned() ?: self::updateIpLoginFailedBanStatus();
        if (!$bannedIpList) {
            return;
        }

        $crud = self::getCrud();
        if ($crud) {
            http_response_code(403);
            die($crud->render('ip_banned_page', ['banIpList' => $bannedIpList]));
        }

        $instance = self::getInstance();
        if (!$instance) {
            http_response_code(403);
            die('Access denied.');
        }

        if (basename((string) ($_SERVER['SCRIPT_FILENAME'] ?? ''), '.php') === 'api') {
            http_response_code(403);
            header('Content-Type: application/json; charset=UTF-8');
            die(json_encode([
                'ok' => false,
                'error' => '你的IP地址已被禁止訪問此系統',
                'ip' => $bannedIpList->ip,
                'reason' => $bannedIpList->reason_chi ?: $bannedIpList->reason,
                'unbanned_date' => $bannedIpList->unbanned_date,
            ], JSON_UNESCAPED_UNICODE));
        }

        http_response_code(403);
        die($instance->getTemplateEngine()->render('ip_banned_page', [
            'banIpList' => $bannedIpList,
        ]));
    }

    public function checkLogin()
    {
        if (!Auth::isLoggedIn()) {
            $requestUri = trim((string) ($_SERVER['REQUEST_URI'] ?? ""));
            if ($requestUri === "") {
                $requestUri = "/";
            }

            $authLogic = Auth::getAuthLogic();
            if ($authLogic) {
                $authLogic->getUserSession()->setSessionField("login_success_redirect_url", $requestUri);
            }
            $this->slim->redirect("/login");
        }
    }
}
