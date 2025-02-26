<?php
use KKsonFramework\CRUD\KKsonCRUD;
use KKsonFramework\CRUD\Field;
use KKsonFramework\CRUD\Middleware\CSRFGuard;

/** @var Field[] $fields */
/** @var array $list */
/** @var KKsonCRUD $crud */
/** @var string $layoutName*/

$crud->addBodyEndHTML(<<< HTML
<script>
    crud.setAjaxFormCallback(function (result) {
        if (result.class == "danger") {
            alertError(result.msg);
        } else {
            location.href = result.redirect_url;   
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
<i class="fa fa-plus"></i> <?=$crud->getCreateName()?> <?=$crud->getData("tableDisplayName");?>
<?php $this->stop();?>

<form id="kkson-form" action="<?=$crud->getCreateSubmitLink() ?>" data-method="post" class="ajax">
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
    <input type="submit" value="新增" class="btn btn-primary" form="kkson-form"/>
    <?php if ($crud->isEnabledListView()) : ?>
        <a href="<?=$crud->getListViewLink() ?>" class="btn btn-default">返回</a>
    <?php endif; ?>
    <span id="msg" class="ml-4 font-weight-bold"></span>
</div>
<?php $this->stop();?>

