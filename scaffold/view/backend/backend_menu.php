<?php

use KKsonFramework\Auth\Auth;
use KKsonFramework\RedBeanPHP\Model\Permission;
use KKsonFramework\Utils\UrlUtils;

?>
<li class="nav-item">
    <a href="<?= UrlUtils::burl("home") ?>" class="nav-link">
        <i class="nav-icon fa fa-home"></i>
        <p>首頁</p>
    </a>
</li>

<?php if (Auth::isPermitted(Permission::PERMISSION_USER_ADMIN_VIEW)) : ?>
<li class="nav-header">管理</li>
<li class="nav-item">
    <a href="<?= UrlUtils::burl("crud/user/list") ?>" class="nav-link crud">
        <i class="fa fa-user nav-icon"></i>
        <p>系統用戶</p>
    </a>
</li>
<?php endif; ?>

<li class="nav-header">選項</li>
<li class="nav-item">
    <a href="<?= UrlUtils::burl("profile") ?>" class="nav-link">
        <i class="nav-icon fa fa-id-card"></i>
        <p>用戶資訊</p>
    </a>
</li>
<li class="nav-item">
    <a href="<?= UrlUtils::burl("logout") ?>" class="nav-link">
        <i class="nav-icon fa fa-sign-out"></i>
        <p>登出</p>
    </a>
</li>
