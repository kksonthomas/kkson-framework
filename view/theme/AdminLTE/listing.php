<?php

use KKsonFramework\CRUD\SearchFieldType\SearchFieldBase;
use KKsonFramework\CRUD\KKsonCRUD;
use KKsonFramework\CRUD\Field;
use KKsonFramework\CRUD\Middleware\CSRFGuard;

/** @var Field[] $fields */
/** @var array $list */
/** @var KKsonCRUD $crud */
/** @var string $layoutName*/

$crud->addHeadExternalCss("/vendor/kksonthomas/kkson-framework/css/listing.css?v=2");
$crud->addBodyEndExternalJs("/vendor/kksonthomas/kkson-framework/js/listing.js?v=2");

$this->layout($layoutName, ["disableLayoutFooterFixed" => true]);

$isAjax = ($crud->isAjaxListView()) ? "true" : "false";
$jsonLink = $crud->getListViewJSONLink();
$enableSearch = $crud->isEnabledSearch() ? "true" : "false";
$enableSorting = $crud->isEnabledSorting() ? "true" : "false";

$crud->addJavaScriptCode(
    <<<JS
    let isAjax = $isAjax;
    let ajaxOptions = {
        "error": function(result, status, xhr) {
            AlertUtils.showError("系統發生錯誤，請稍後再試", "如果問題持續發生，請聯絡客服");
        }
    };
    let ajaxUrl = "$jsonLink";
    let enableSearch = $enableSearch;
    let enableSorting = $enableSorting;

    crud.initListView(isAjax?ajaxOptions:null, ajaxUrl, enableSearch, enableSorting, {

    });
    
    $(function () {
        $(".btnRefreshDatatable").click(function() {
            crud.getDataTable().ajax.reload(() => {
                ToastUtils.showSuccess("重新整理成功");
            });
        });
    });
JS
);

$tableDisplayName = ($crud->getTableDisplayName() != "" ? $crud->getTableDisplayName() : $crud->getData("tableDisplayName"));
?>

<?php $this->start('header'); ?>
<h2 class="mb-0 ml-2"><?= $tableDisplayName ?></h2>
<?php $this->stop(); ?>
<?= $crud->render($crud->getThemeName() . '::listing_search_panel', [""]); ?>

<div class="row">
    <div class="col-12">
        <div class="card card-outline card-primary">
            <div class="kkson-crud-table-header card-header bg-white sticky-top">
                <div class="row">
                    <div class="col-12">
                        <h3 class="card-title">
                            <?php 
                            echo $crud->getListViewHeaderButtonHtml();

                            //legacy support
                            if ($crud->getData("headerButtonHTML")) {
                                echo $crud->getData("headerButtonHTML");
                            }
                            ?>
                        </h3>
                        <div class="ext-dt-paging float-right">
                        </div>
                    </div>
                </div>

            </div>

            <div class="card-body">

                <table id="kkson-crud-table" class="table table-bordered table-hover dataTable display">
                    <thead>
                        <tr>
                            <!-- colspan="2"-->
                            <th data-dt-order="disable">動作</th>

                            <!-- Column Header -->
                            <?php foreach ($fields as $field) : ?>
                                <th data-dt-order="<?= $field->isSortable() ? "" : "disable" ?>"><?= $field->getDisplayName() ?></th>
                            <?php endforeach; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($list as $bean) : ?>
                            <tr id="row-<?= $bean->id ?>">

                                <!--       <td>
                                <label><input type="checkbox" value="<?/*=$bean->id */ ?>" /> </label>
                            </td>-->

                                <!-- Action TD -->
                                <td>
                                    <?php
                                    if ($crud->isEnabledEdit()) :
                                        $isAllowEdit = true;
                                        $isAllowEditClause = $crud->getData("isAllowEditClause");
                                        if ($isAllowEditClause) {
                                            $isAllowEdit = $isAllowEditClause($bean);
                                        }
                                        if ($isAllowEdit) :
                                    ?>
                                            <a href="<?= $crud->getEditLink($bean->id) ?>" class="btn btn-default <?= $crud->getCommonButtonClasses() ?>"><?= $crud->getEditName() ?></a>
                                        <?php
                                        else :
                                        ?>
                                            <a href="#" class="btn btn-default <?= $crud->getCommonButtonClasses() ?> disabled"><?= $crud->getEditName() ?></a>
                                    <?php
                                        endif;
                                    endif;
                                    ?>


                                    <?php
                                    if ($crud->isEnabledDelete()) :
                                        $isAllowDelete = true;
                                        $isAllowDeleteClause = $crud->getData("isAllowDeleteClause");
                                        if ($isAllowDeleteClause) {
                                            $isAllowDelete = $isAllowDeleteClause($bean);
                                        }
                                        if ($isAllowDelete) :
                                    ?>

                                            <a class="btn-delete btn btn-danger <?= $crud->getCommonButtonClasses() ?>" href="javascript:void(0)" data-id="<?= $bean->id ?>" data-url="<?= $this->e($crud->getDeleteLink($bean->id)) ?>"><?= $crud->getDeleteName() ?></a>
                                        <?php
                                        else :
                                        ?>
                                            <a href="#" class="btn btn-danger <?= $crud->getCommonButtonClasses() ?> disabled"><?= $crud->getDeleteName() ?></a>
                                    <?php
                                        endif;
                                    endif;
                                    ?>

                                    <!-- Action Closure -->
                                    <?php if ($crud->getRowAction() != null) : ?>
                                        <?php
                                        $c = $crud->getRowAction();
                                        echo $c($bean);
                                        ?>
                                    <?php endif; ?>

                                </td>

                                <!-- Cell -->
                                <?php foreach ($fields as $field) : ?>
                                    <td><?= $field->cellValue($bean); ?></td>
                                <?php endforeach; ?>
                            </tr>

                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

        </div>
    </div>
</div>