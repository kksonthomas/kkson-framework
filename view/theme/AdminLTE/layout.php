<?php

use KKsonFramework\App\App;
use KKsonFramework\Conf\AppConfig;
use KKsonFramework\Auth\Auth;
use KKsonFramework\CRUD\Middleware\CSRFGuard;
use KKsonFramework\Utils\UrlUtils;

/** @var SlimKKsonCRUD $crud */

$isLoginAs = Auth::isLoginAs();
?>
<!DOCTYPE html>
<html style="height: auto;">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">

    <title><?=$crud->getData("title") ?></title>

    <link rel="stylesheet"
          href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&amp;display=fallback">
    <link rel="stylesheet" href="/vendor/fortawesome/font-awesome/css/font-awesome.min.css">

    <link rel="stylesheet" href="/vendor/almasaeed2010/adminlte/plugins/daterangepicker/daterangepicker.css">
    <link rel="stylesheet" href="/vendor/kksonthomas/kkson-framework/lib/datatables/datatables.min.css">
    <link rel="stylesheet" href="/vendor/almasaeed2010/adminlte/plugins/select2/css/select2.min.css">
    <link rel="stylesheet" href="/vendor/almasaeed2010/adminlte/plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css">
    <link rel="stylesheet" href="/vendor/almasaeed2010/adminlte/plugins/sweetalert2/sweetalert2.min.css">
    <link rel="stylesheet" href="/vendor/almasaeed2010/adminlte/plugins/sweetalert2-theme-bootstrap-4/bootstrap-4.min.css">
    <link rel="stylesheet" href="/vendor/almasaeed2010/adminlte/plugins/toastr/toastr.min.css">
    <link rel="stylesheet" href="/vendor/almasaeed2010/adminlte/plugins/bootstrap-switch/css/bootstrap3/bootstrap-switch.min.css">

    <link rel="stylesheet" href="/vendor/almasaeed2010/adminlte/dist/css/adminlte.min.css">
    <link rel="stylesheet" href="/vendor/kksonthomas/kkson-framework/css/kkson-crud.css">

    <?=$crud->getHeadHTML(); ?>
</head>
<body class="sidebar-mini layout-navbar-fixed layout-footer-fixed" style="height: auto;">
<div class="wrapper">

    <nav class="main-header navbar navbar-expand navbar-white navbar-light ">

        <ul class="navbar-nav">
            <li class="nav-item">
                <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fa fa-bars"></i></a>
            </li>

            <li class="nav-item d-flex align-items-center">
                <?php if(App::isUAT()) :?>
                <span class="badge badge-danger font-weight-bold mr-1" style="font-size: 26px;">UAT</span>
                <?php endif; ?>
                <span class="font-weight-bold" style="font-size: 22px;"><?=$this->section('header')?></span>

            </li>
        </ul>

<!--        <ul class="navbar-nav ml-auto">-->
<!--        </ul>-->
    </nav>


    <aside class="main-sidebar sidebar-dark-primary elevation-4">

        <a href="#" class="brand-link">
            <img src="/vendor/almasaeed2010/adminlte/dist/img/AdminLTELogo.png" alt="AdminLTE Logo" class="brand-image img-circle elevation-3"
                 style="opacity: .8">
            <span class="brand-text font-weight-light"><?= AppConfig::get()->appName()?></span>
            <?php if(App::isUAT()) : ?>
            <span class="right badge badge-danger">UAT</span>
            <?php endif; ?>
        </a>

        <div class="sidebar">

            <div class="user-panel mt-3 pb-3 mb-3 d-flex">
                <div class="image">
                    <img src="/vendor/kksonthomas/kkson-framework/img/user.png" class="img-circle elevation-2" alt="User Image">
                </div>
                <div class="info py-0">
                    <?php if($isLoginAs) :?>
                        <a href="#" class="d-block mt-n2 text-default">
                            <span class="small">
                                <?= Auth::getRealUser()->username ?>
                            </span>
                            <span>
                                <?= "<br><i class='fa fa-reply mr-1' style='transform: rotate(180deg)'></i> " . Auth::getUser()->username ?>
                            </span>

                        </a>
                    <?php else: ?>
                        <a href="#" class="d-block mt-n1"><?=Auth::getUser()->username ?></a>
                    <?php endif; ?>
                    <span class="d-block text-white-50 small mt-n1 <?=$isLoginAs ? "ml-4" : ""?>"><?=@Auth::getUser()->getRoleName()?></span>

                </div>
            </div>

            <nav class="mt-2">
                <ul class="nav nav-pills nav-sidebar nav-child-indent flex-column" data-widget="treeview" role="menu"
                    data-accordion="false">

                    <?=@$crud->enableMenu()?>
                    <?=@$crud->getData("menu") ?>
                </ul>
            </nav>

        </div>

    </aside>

    <div class="content-wrapper" style="min-height: 325px;">
        <div class="content-header <?=$this->section('content-header') ? "" : "p-1"?>">
            <div class="container-fluid">
                <?=$this->section('content-header')?>
            </div>
        </div>

        <div class="content">
            <div class="container-fluid">
                <?=$this->section('content')?>
            </div>
        </div>
    </div>


    <footer class="main-footer d-flex">
        <div class="d-flex align-items-start flex-column mr-auto">
            <?php
            if($this->section('footer')) {
                echo $this->section('footer');
            } else {
                echo AppConfig::get()->appName() . " 系統 (" . (App::isUAT()? "UAT" : "PROD") . " )";
            }
            ?>
        </div>
        <div class="d-none d-sm-flex align-items-end flex-column">
            <div class="mt-auto">
                <strong>Copyright © 2025 <a href="https://kotech.hk">Kotech Services Limited</a>.</strong> All rights reserved.
            </div>
        </div>
    </footer>
    <div id="sidebar-overlay"></div>
</div>

<script src="/vendor/almasaeed2010/adminlte/plugins/jquery/jquery.min.js"></script>
<script src="/vendor/almasaeed2010/adminlte/plugins/popper/umd/popper.min.js"></script>
<script src="/vendor/almasaeed2010/adminlte/plugins/popper/umd/popper-utils.min.js"></script>
<script src="/vendor/almasaeed2010/adminlte/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>

<script src="/vendor/almasaeed2010/adminlte/plugins/moment/moment-with-locales.min.js"></script>
<script src="/vendor/almasaeed2010/adminlte/plugins/select2/js/select2.full.min.js"></script>
<script src="/vendor/almasaeed2010/adminlte/plugins/select2/js/i18n/zh-TW.js"></script>
<script src="/vendor/almasaeed2010/adminlte/plugins/daterangepicker/daterangepicker.js"></script>
<script src="/vendor/almasaeed2010/adminlte/plugins/sweetalert2/sweetalert2.all.min.js"></script>
<script src="/vendor/almasaeed2010/adminlte/plugins/toastr/toastr.min.js"></script>
<script src="/vendor/kksonthomas/kkson-framework/lib/ckeditor5/ckeditor.js"></script>

<script src="/vendor/kksonthomas/kkson-framework/lib/datatables/datatables.min.js"></script>

<script src="/vendor/almasaeed2010/adminlte/plugins/jquery-validation/jquery.validate.min.js"></script>
<script src="/vendor/almasaeed2010/adminlte/plugins/jquery-validation/localization/messages_zh_TW.min.js"></script>
<script src="/vendor/almasaeed2010/adminlte/plugins/bootstrap-switch/js/bootstrap-switch.min.js"></script>

<script src="/vendor/almasaeed2010/adminlte/dist/js/adminlte.min.js?v=3.2.0"></script>
<script src="/vendor/kksonthomas/kkson-framework/js/KKsonCRUD.js"></script>
<script src="/vendor/kksonthomas/kkson-framework/js/backend.js"></script>

<script>
    var crud = new KKsonCRUD();
    var BASE_URL = "<?=(UrlUtils::isSSL() ? "https://" : "http://") . $_SERVER['SERVER_NAME'] ?>";
    var RES_URL = "<?=UrlUtils::res("") ?>";
    var csrfToken = <?=json_encode(CSRFGuard::$token) ?>;
</script>

<?=$crud->getBodyEndHTML(); ?>
</body>
</html>