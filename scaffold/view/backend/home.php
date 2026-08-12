<?php

use KKsonFramework\Auth\Auth;
use KKsonFramework\Conf\AppConfig;
use KKsonFramework\CRUD\KKsonCRUD;
use KKsonFramework\Utils\UrlUtils;

/** @var KKsonCRUD $crud */
/** @var string $layoutName */

$this->layout($layoutName, [
    "crud" => $crud,
]);

$user = Auth::getUser();
$permissionList = $user->getPermissionList();

$this->push("header");
?>
<span class="content-header">首頁</span>
<?php
$this->end();
?>
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card border-0 shadow-lg mt-5">
            <div class="card-body p-5 bg-light position-relative rounded">
                <div class="mb-4">
                    <h2 class="card-title font-weight-bold" style="letter-spacing:2px; color:#003366;">
                        歡迎使用 <?= AppConfig::get()->appName() ?> 後台管理系統
                    </h2>
                </div>
                <p class="lead card-text mb-4" style="color:#555;">
                    請使用 <span class="badge badge-primary mx-1 px-2" style="font-size: 1rem;"><i class="fa fa-bars"></i> 左側選單</span> 瀏覽各項功能
                </p>
            </div>
        </div>
    </div>
</div>

<div class="row justify-content-center mt-4">
    <div class="col-lg-8">
        <div class="card border-0 shadow">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="fa fa-user"></i> 用戶資訊</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr>
                                <td class="font-weight-bold" style="width: 120px;">用戶名稱:</td>
                                <td><?= htmlspecialchars($user->username) ?></td>
                            </tr>
                            <tr>
                                <td class="font-weight-bold">身份:</td>
                                <td>
                                    <span class="badge badge-info"><?= htmlspecialchars($user->getRoleName()) ?></span>
                                </td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <div class="text-right">
                            <a href="<?= UrlUtils::burl("profile") ?>" class="btn btn-outline-primary">
                                <i class="fa fa-id-card"></i> 查看完整資料
                            </a>
                        </div>
                    </div>
                </div>
                <?php if (!empty($permissionList)): ?>
                <hr>
                <div>
                    <h6 class="font-weight-bold mb-3"><i class="fa fa-key"></i> 擁有權限 (<?= count($permissionList) ?>)</h6>
                    <div class="d-flex flex-wrap">
                        <?php foreach ($permissionList as $permissionName => $displayName): ?>
                            <span class="badge badge-secondary mr-2 mb-2"><?= htmlspecialchars($displayName) ?></span>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
