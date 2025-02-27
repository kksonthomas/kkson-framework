<?php
use KKsonFramework\CRUD\Field;
use KKsonFramework\CRUD\KKsonCRUD;
use KKsonFramework\CRUD\Middleware\CSRFGuard;

/** @var Field[] $fields */
/** @var array $list */
/** @var KKsonCRUD $crud */
/** @var string $layoutName */

$crud->addBodyEndHTML(<<< HTML
<script>
    let msg = $("#msg");
    crud.setAjaxFormCallback(function (result) {
        msg.html("");
        if(msg.data("addedClasses")) {
            msg.removeClass(msg.data("addedClasses"));
        }
        if (result.class === "danger") {
                AlertUtils.showError(result.msg);
        } else {
            if(result.msg === "Saved.") {
                result.msg = "已儲存修改。";
            }
            let timeoutKey = msg.data("fadeOutTimeout");
            if(timeoutKey) {
                clearTimeout(timeoutKey);
            }
            let addedClasses = `text-\${result.class}`;
            msg.show().html(result.msg).data("addedClasses", addedClasses).addClass(addedClasses).data("fadeOutTimeout", setTimeout(function() {
                $("#msg").fadeOut(1000).data("fadeOutTimeout", null);
            }, 4000));
            ToastUtils.show(result.msg, result.class);
        }
    });
</script>
HTML
);

$this->layout($layoutName, [
    "crud" => $crud
]);
?>

<?php $this->start("header");?>
<i class="fa fa-edit"></i> 編輯 <?=$crud->getData("tableDisplayName");?>
<?php $this->stop();?>

<form id="kkson-form" action="<?= $crud->getEditSubmitLink($crud->getBean()->id) ?>" data-method="<?=$crud->getEditSubmitMethod() ?>" class="ajax">
    <?=CSRFGuard::inputTag()?>
    <div class="row mb-4">
        <!-- left column -->
        <div class="col-12 col-lg-10">
            <div class="card card-outline card-primary">
                <div class="card-body">
                    <?php foreach ($fields as $field) : ?>
                        <?= $field->render(false) ?>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</form>

<?php $this->start("footer");?>
<div>
    <input type="submit" value="儲存" class="btn btn-primary" form="kkson-form"/>
    <?php if ($crud->isEnabledListView()) : ?>
        <a href="<?=$crud->getListViewLink() ?>" class="btn btn-default">返回</a>
    <?php endif; ?>
    <span id="msg" class="ml-4 font-weight-bold"></span>
</div>
<?php $this->stop();?>



