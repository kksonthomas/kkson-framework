<?php

use KKsonFramework\Conf\AppConfig;

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>404 Page Not Found</title>
    <!-- Tell the browser to be responsive to screen width -->
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="/vendor/almasaeed2010/adminlte/plugins/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&amp;display=fallback">
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="/vendor/fortawesome/font-awesome/css/font-awesome.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">


    <link rel="stylesheet" href="/vendor/almasaeed2010/adminlte/plugins/icheck-bootstrap/icheck-bootstrap.min.css">
    <!-- Theme style -->
    <link rel="stylesheet" href="/vendor/almasaeed2010/adminlte/dist/css/adminlte.min.css">
    
    <style>
        .error-page {
            max-width: 600px;
            margin: 20px auto;
            text-align: center;
        }
        .error-page .headline {
            font-size: 100px;
            font-weight: 300;
        }
        .error-page .error-content {
            padding: 20px;
        }
        .search-form {
            margin: 20px 0;
        }
        @media (max-width: 767px) {
            .error-page .headline {
                font-size: 80px;
            }
        }
    </style>

</head>
<body class="hold-transition sidebar-mini">
<div class="wrapper">
    <!-- Content Wrapper -->
    <div class="content-wrapper" style="margin-left: 0;">
        <!-- Content Header (Page header) -->
        <!-- Main content -->
        <section class="content">
            <div class="error-page">
                <h2 class="headline text-warning"> 404</h2>

                <div class="error-content">
                    <h3><i class="fas fa-exclamation-triangle text-warning"></i> 哎呀！頁面未找到。</h3>

                    <p>
                        我們找不到您要尋找的頁面。
                    </p>

                    <div class="mt-3">
                        <a href="javascript:history.back()" class="btn btn-secondary ml-2">
                            <i class="fas fa-arrow-left"></i> 返回上一頁
                        </a>
                    </div>
                </div>
            </div>
        </section>
    </div>
</div>

<script src="/vendor/almasaeed2010/adminlte/plugins/jquery/jquery.min.js"></script>
<script src="/vendor/almasaeed2010/adminlte/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="/vendor/almasaeed2010/adminlte/dist/js/adminlte.min.js?v=3.2.0"></script>
</body>
</html>
