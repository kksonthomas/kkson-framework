<?php

namespace App\Controller\frontend\route;

use KKsonFramework\App\Controller\frontend\route\GeneralFrontendRouteController as BaseGeneralFrontendRouteController;
use KKsonFramework\Conf\AppConfig;

class GeneralFrontendRouteController extends BaseGeneralFrontendRouteController
{
    public function index()
    {
        $appName = htmlspecialchars(AppConfig::get()->appName(), ENT_QUOTES, 'UTF-8');
        echo <<<HTML
<!DOCTYPE html>
<html lang="zh-Hant">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{$appName}</title>
</head>
<body>
    <h1>{$appName}</h1>
    <p>Frontend placeholder.</p>
</body>
</html>
HTML;
    }
}
