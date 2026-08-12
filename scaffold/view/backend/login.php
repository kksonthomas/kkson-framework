<?php

use KKsonFramework\Conf\AppConfig;

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
        <div class="card-body">
            <?php if (isset($_SESSION['msg'])) : ?>
                <p class="login-box-msg text-danger"><?= $_SESSION['msg'] ?></p>
                <?php unset($_SESSION['msg']); ?>
            <?php else: ?>
                <p class="login-box-msg">Sign in to start your session</p>
            <?php endif; ?>
            <form action="" method="post">
                <div class="input-group mb-3">
                    <input type="text" class="form-control" name="username" placeholder="username">
                    <div class="input-group-append">
                        <div class="input-group-text">
                            <span class="fa fa-envelope"></span>
                        </div>
                    </div>
                </div>
                <div class="input-group mb-3">
                    <input type="password" class="form-control" name="password" placeholder="Password">
                    <div class="input-group-append">
                        <div class="input-group-text">
                            <span class="fa fa-lock"></span>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-8 d-none">
                        <div class="icheck-primary">
                            <input type="checkbox" id="remember">
                            <label for="remember">
                                Remember Me
                            </label>
                        </div>
                    </div>

                    <div class="col-4 offset-8">
                        <button type="submit" class="btn btn-primary btn-block">Sign In</button>
                    </div>

                </div>
            </form>
            <div class="social-auth-links text-center mt-2 mb-3 d-none">
                <a href="#" class="btn btn-block btn-primary">
                    <i class="fab fa-facebook mr-2"></i> Sign in using Facebook
                </a>
                <a href="#" class="btn btn-block btn-danger">
                    <i class="fab fa-google-plus mr-2"></i> Sign in using Google+
                </a>
            </div>

            <p class="mb-1  d-none">
                <a href="forgot-password.html">I forgot my password</a>
            </p>
            <p class="mb-0 d-none">
                <a href="register.html" class="text-center">Register a new membership</a>
            </p>
        </div>

    </div>

</div>

<script src="/vendor/almasaeed2010/adminlte/plugins/jquery/jquery.min.js"></script>
<script src="/vendor/almasaeed2010/adminlte/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="/vendor/almasaeed2010/adminlte/dist/js/adminlte.min.js?v=3.2.0"></script>
</body>
</html>
