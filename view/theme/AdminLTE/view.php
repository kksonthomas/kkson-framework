<?php
use KKsonFramework\CRUD\Field;
use KKsonFramework\CRUD\KKsonCRUD;
use KKsonFramework\CRUD\Middleware\CSRFGuard;

/** @var Field[] $fields */
/** @var array $list */
/** @var KKsonCRUD $crud */
/** @var string $layoutName */

$crud->addBodyEndHTML(<<< HTML
HTML
);

$this->layout($layoutName, [
    "crud" => $crud
]);
?>

<?php $this->start("header");?>
<i class="fa fa-eye"></i> 檢視 <?=$crud->getData("tableDisplayName");?>
<?php $this->stop();?>

<form>
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
    <?php if ($crud->isEnabledListView()) : ?>
        <a href="<?=$crud->getListViewLink() ?>" class="btn btn-default">返回</a>
    <?php endif; ?>
    <span id="msg" class="ml-4 font-weight-bold"></span>
</div>
<?php $this->stop();?>



