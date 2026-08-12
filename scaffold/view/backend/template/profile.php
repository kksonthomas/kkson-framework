<?php

use KKsonFramework\Auth\Auth;
use KKsonFramework\CRUD\KKsonCRUD;
use KKsonFramework\CRUD\Middleware\CSRFGuard;

/** @var KKsonCRUD $crud */
/** @var string $layoutName*/

$crud->addBodyEndHTML(<<< HTML
<script>

</script>
HTML
);

$this->layout($layoutName, [
    "crud" => $crud
]);

$user = Auth::getUser();

$this->push("header");?>帳戶資料
<?php
$this->end();
?>


<div class="row">
    <div class="col-md-10">
        <div class="card card-primary">
            <div class="card-body">
                <table class="table">
                    <tr>
                        <td style="border-top:0;">用戶名稱:</td>
                        <td style="border-top:0;"><?= $user->username; ?></td>
                    </tr>
                    <tr>
                        <td>權限:</td>
                        <td><?= $user->getRoleName() ?></td>
                    </tr>
                    <tr>
                        <td>擁有權限:</td>
                        <td><?= implode("<br>", $user->getPermissionList()) ?></td>
                    </tr>
                </table>

                <br>

                <form action="" method="post">
                    <?=CSRFGuard::inputTag()?>
                    <h4>更改密碼</h4>
                    <hr>

                    <div class="form-group">
                        <label for="field-password"> 新密碼</label>
                        <input id="field-password" class="form-control" type="password" name="password" minlength="8"
                               required>
                    </div>

                    <div class="form-group">
                        <label for="field-confirm_password"> 確認新密碼</label>
                        <input id="field-confirm_password" class="form-control" type="password" name="confirm_password"
                               required>
                    </div>

                    <p>* 密碼需至少8位</p>

                    <br>

                    <input type="submit" class="btn btn-primary" value="確認">
                    <div style="margin-top: 10px;">
                        <?php if (isset($_SESSION["error"])) { ?>
                            <div class="callout callout-danger">
                                <h4><?= $_SESSION["error"] ?></h4>
                            </div>
                            <?php
                            unset($_SESSION["error"]);
                        }
                        if (isset($_SESSION["success"])) {
                            ?>
                            <div class="callout callout-success">
                                <h4><?= $_SESSION["success"] ?></h4>
                            </div>
                            <?php
                            unset($_SESSION["success"]);
                        }
                        ?>
                    </div>

                </form>
            </div>
            <div class="card-footer">

            </div>
        </div>

        <div id="msg">

        </div>

    </div>
</div>