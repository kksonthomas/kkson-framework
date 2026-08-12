<?php

namespace App;

use KKsonFramework\App\App as BackendAppBase;

class BackendApp extends BackendAppBase
{
    public static function checkIp(): void
    {
        if (\KKsonFramework\Auth\Auth::isLoggedIn()) {
            return;
        }

        $bannedIpList = self::isCurrentIpBanned() ?: self::updateIpLoginFailedBanStatus();
        $crud = self::getCrud();
        if ($crud && $bannedIpList) {
            http_response_code(403);
            die($crud->render('ip_banned_page', ['banIpList' => $bannedIpList]));
        }
    }
}
