<?php

use KKsonFramework\CRUD\SearchFieldType\SearchFieldBase;
use KKsonFramework\CRUD\KKsonCRUD;
use KKsonFramework\CRUD\Field;
use KKsonFramework\CRUD\Middleware\CSRFGuard;

/** @var Field[] $fields */
/** @var array $list */
/** @var KKsonCRUD $crud */
/** @var string $layoutName*/

$crud->addHeadExternalCss("/vendor/kksonthomas/kkson-framework/css/listing.css");
$crud->addBodyEndExternalJs("/vendor/kksonthomas/kkson-framework/js/listing.js");

$this->layout($layoutName);

$searchableFieldMap = $crud->getData("searchableFieldMap");
if(!$searchableFieldMap) {
    $searchableFieldMap = [];
}
$searchableFieldJsObj = [];
foreach ($searchableFieldMap as $searchableField) {
    /** @var SearchFieldBase $searchableField */
    $searchableFieldJsObj[$searchableField->getName()] = [
        "render" => $searchableField->render(),
        "conditions" => $searchableField->getConditionList(),
        "displayName" => $searchableField->getDisplayName()
    ];
}
$isAjax = ($crud->isAjaxListView()) ? "true" : "false";
$jsonLink = $crud->getListViewJSONLink();
$enableSearch = $crud->isEnabledSearch() ? "true" : "false";
$enableSorting = $crud->isEnabledSorting() ? "true" : "false";

$searchableFieldJson = json_encode($searchableFieldJsObj);
$conditionConfig = json_encode(SearchFieldBase::getConditionConfig());

$crud->addJavaScriptCode(<<<JS
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
        "scrollX": true,
        "initComplete": function() {
            $('.dt-paging').first().appendTo('.ext-dt-paging');
        },
        "layout": {
            "bottomEnd": {
                "paging": {
                    "previousNext": false
                }
            },
            "topEnd": {
                "paging": {
                    "previousNext": false
                }
            },
        }
    });
    
    $(function () {
        let searchableFields = $searchableFieldJson;
        let conditionConfig = $conditionConfig;
        let config = {
            searchableFields: searchableFields,
            conditionConfig: conditionConfig,
            maxIndent: 3
        };
        window.searchingPane = new KKsonCRUDSearchingPane($("#formSearchCriteria"), config);
        
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
<h2 class="mb-0 ml-2"><?=$tableDisplayName?></h2>
<?php $this->stop(); ?>

<?php if(!empty($crud->getData("searchableFieldMap"))) : ?>
<div class="row">
    <div class="col-12">
        <div class="card card-outline card-default ">
            <div class="card-header with-border">
                <h3 class="card-title"><i class="fa fa-search"></i> 搜尋</h3>
            </div>
            <!-- template of search form -->
            <div class="searchCriteria tmpl mb-1 row">
                <div class="col-3">
                    <select class="form-control selFieldName">
                        <option value="" class="placeholder" disabled selected="" hidden="">欄位</option>
                        <?php
                        foreach ($searchableFieldMap as $searchableFieldName => $searchableField) {
                            /** @var SearchFieldBase $searchableField */
                            echo "<option value='$searchableFieldName'>{$searchableField->getDisplayName()}</option>";
                        }
                        ?>
                    </select>
                </div>
                <div class="col-3">
                    <select class="form-control selCond" autocomplete="hacking">
                        <option class="placeholder" value="" disabled="disabled" selected="" hidden="">條件</option>
                    </select>
                </div>
                <div class="col">
                    <div class="keywordContainer"></div>
                </div>
                <div class="col-2">
                    <div class="btn-group float-right">
                        <button type="button" class="btn btn-warning btnUnIndentSc"><i class="fa fa-chevron-left"></i></button>
                        <button type="button" class="btn btn-warning btnIndentSc"><i class="fa fa-chevron-right"></i></button>
                        <button type="button" class="btn btn-danger btnDelSc">x</button>
                    </div>
                </div>
            </div>
            <div class="scGroup row mb-1 tmpl">
                <div class="groupBtnCol ml-2" style="display: none;">
                    <div class="btn-group-vertical h-100">
                        <button type="button" class="btn btn-default btn-block btnScGroupCondition h-100" data-value=""></button>
                        <button type="button" class="btn btn-danger btn-block btnDelScGroup">x</button>
                    </div>

                </div>
                <div class="col">
                    <div class="row">
                        <div class="groupSc col-12">

                        </div>
                        <div class="col-12">
                            <button type="button" class="btn btn-default btnAddSc"><i class="fa fa-plus"></i> 增加搜尋條件</button>
                        </div>
                    </div>
                </div>
            </div>
            <!-- form start -->
            <form class="form-horizontal" action="" method="get" id="formSearchCriteria">
                <div class="card-body row">
                    <div class="formScBody col-xl-9 col-12">
                    </div>
                </div>
                <!-- /.card-body -->
                <div class="card-footer">
                    <button type="submit" class="btn btn-info">搜尋</button>
                    <button type="button" class="btn btn-default btnResetSearch">重設</button>
                </div>
                <!-- /.card-footer -->
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<div class="row">
    <div class="col-12">
        <div class="card card-outline card-primary">
            <div class="card-header bg-white sticky-top">
                <h3 class="card-title">
                    <?= $crud->getCreateButtonHtml() ?>
                    <!-- Export Button -->
                    <?= $crud->getExportButtonHtml() ?>

                    <button type="button" class="btn btn-default btnRefreshDatatable"><i class="fa fa-sync"></i> 重新整理</button>

                    <?php if ($crud->getData("headerButtonHTML")) {
                        echo $crud->getData("headerButtonHTML");
                    } ?>
                </h3>
                <div class="ext-dt-paging float-right">
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
                            <th data-dt-order="<?=$field->isSortable()?"":"disable"?>"><?=$field->getDisplayName() ?></th>
                        <?php endforeach; ?>
                    </tr>
                    </thead>
                    <tfoot>
                        <tr>
                            <th></th>
                            <?php foreach ($fields as $field) : ?>
                                <th></th>
                            <?php endforeach; ?>
                        </tr>
                    </tfoot>
                    <tbody>
                    <?php foreach ($list as $bean) : ?>
                        <tr id="row-<?=$bean->id ?>">

                     <!--       <td>
                                <label><input type="checkbox" value="<?/*=$bean->id */?>" /> </label>
                            </td>-->

                            <!-- Action TD -->
                            <td>
                                <?php
                                    if ($crud->isEnabledEdit()) :
                                        $isAllowEdit = true;
                                        $isAllowEditClause = $crud->getData("isAllowEditClause");
                                        if($isAllowEditClause) {
                                            $isAllowEdit = $isAllowEditClause($bean);
                                        }
                                        if($isAllowEdit) :
                                    ?>
                                    <a href="<?=$crud->getEditLink($bean->id) ?>" class="btn btn-default"><?=$crud->getEditName() ?></a>
                                <?php
                                        else :
                                ?>
                                    <a href="#" class="btn btn-default disabled"><?=$crud->getEditName() ?></a>
                                <?php
                                        endif;
                                    endif;
                                ?>


                                <?php
                                    if ($crud->isEnabledDelete()) :
                                        $isAllowDelete = true;
                                        $isAllowDeleteClause = $crud->getData("isAllowDeleteClause");
                                        if($isAllowDeleteClause) {
                                            $isAllowDelete = $isAllowDeleteClause($bean);
                                        }
                                        if($isAllowDelete) :
                                ?>

                                        <a class="btn-delete btn btn-danger" href="javascript:void(0)" data-id="<?=$bean->id ?>" data-url="<?=$this->e($crud->getDeleteLink($bean->id)) ?>"><?=$crud->getDeleteName() ?></a>
                                <?php
                                        else :
                                ?>
                                        <a href="#" class="btn btn-danger disabled"><?=$crud->getDeleteName() ?></a>
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
                                <td><?=$field->cellValue($bean); ?></td>
                            <?php endforeach; ?>
                        </tr>

                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

        </div>
    </div>
</div>



