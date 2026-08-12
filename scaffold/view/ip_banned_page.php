<?php

/**@var BanIpList $banIpList **/

use KKsonFramework\RedBeanPHP\Model\BanIpList; ?>

<?php

use KKsonFramework\Conf\AppConfig;
use KKsonFramework\Utils\DateUtils;

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Log in</title>
    <!-- Tell the browser to be responsive to screen width -->
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
    <!-- Bootstrap 3.3.4 -->
    <link href="/vendor/almasaeed2010/adminlte/plugins/bootstrap/js/bootstrap.bundle.min.js" rel="stylesheet"
          type="text/css"/>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&amp;display=fallback">
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="/vendor/fortawesome/font-awesome/css/font-awesome.min.css">


    <link rel="stylesheet" href="/vendor/almasaeed2010/adminlte/plugins/icheck-bootstrap/icheck-bootstrap.min.css">
    <!-- Theme style -->
    <link rel="stylesheet" href="/vendor/almasaeed2010/adminlte/dist/css/adminlte.min.css">

</head>
<body class="login-page" style="min-height: 466px;">
<div class="login-box">
    <div class="card card-outline card-primary">
        <div class="card-header text-center">
            <a href="#" class="h1"><b><?= AppConfig::get()->appName()?></b> Backend Portal</a>
        </div>
        <div class="card-body bg-danger text-center">
            <h1 class="font-weight-bolder"><i class="icon fa fa-ban"></i> 訪問被禁止</h1>
            你的IP地址 <h3 class="font-weight-bolder"><?=$banIpList->ip?></h3> 被禁止訪問此系統.<br>
            原因: <?=$banIpList->reason_chi ? $banIpList->reason_chi : ""?><br>
            <?=$banIpList->unbanned_date ? "解禁日期: ". DateUtils::toChineseFormatDate($banIpList->unbanned_date, true) : ""?><br>
            <br>
            <h1 class="font-weight-bolder"><i class="icon fa fa-ban"></i> Access Denied</h1>
            Your IP address <h3 class="font-weight-bolder"><?=$banIpList->ip?></h3>  is banned from access this system.<br>
            <br>
            Reason: <?=$banIpList->reason ? $banIpList->reason : ""?><br>
            <?=$banIpList->unbanned_date ? "UnBan Date: $banIpList->unbanned_date" : ""?><br>
        </div>
        <div class="card-footer bg-info text-center">
            如需協助, 請聯絡系統管理員<br>
            Please contact system administrator if you need assistance.
        </div>
    </div>


</div>

<script src="/vendor/almasaeed2010/adminlte/plugins/jquery/jquery.min.js"></script>
<script src="/vendor/almasaeed2010/adminlte/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="/vendor/almasaeed2010/adminlte/dist/js/adminlte.min.js?v=3.2.0"></script>
</body>
</html>
