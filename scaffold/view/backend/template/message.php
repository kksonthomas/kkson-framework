<?php
use KKsonFramework\CRUD\KKsonCRUD;
use KKsonFramework\CRUD\Field;


/** @var Field[] $fields */
/** @var array $list */
/** @var KKsonCRUD $crud */
/** @var string $layoutName*/

/** @var string $type*/
/** @var string $title*/
/** @var string $content*/
/** @var string $backUrl*/

$this->layout($layoutName, [
    "crud" => $crud
]);

//$type
//#title

$script = /** @lang HTML */ <<<'JS'

JS;
$crud->addBodyEndHTML($script);
?>
<div class="callout callout-<?=$type?>">
    <h4><?=$title?></h4>
    <p><?=$content?></p>
</div>

<?php if(isset($backUrl)) { ?>
    <a href="<?=$backUrl?>" class="btn btn-default" >返回</a>
<?php } ?>
<div class="clearfix"></div>
