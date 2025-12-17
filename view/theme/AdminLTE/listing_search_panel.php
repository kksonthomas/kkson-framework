<?php

use KKsonFramework\CRUD\SearchFieldType\SearchFieldBase;
use KKsonFramework\CRUD\KKsonCRUD;

/** @var KKsonCRUD $crud */

$searchableFieldMap = $crud->getData("searchableFieldMap");
if (!$searchableFieldMap) {
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


$searchableFieldJson = json_encode($searchableFieldJsObj);
$conditionConfig = json_encode(SearchFieldBase::getConditionConfig());

$crud->addJavaScriptCode(<<<JS
    $(function () {        
        let searchableFields = $searchableFieldJson;
        let conditionConfig = $conditionConfig;
        let config = {
            searchableFields: searchableFields,
            conditionConfig: conditionConfig,
            maxIndent: 3
        };
        window.searchingPane = new KKsonCRUDSearchingPane($("#formSearchCriteria"), config);
        
        // Toggle chevron icon on collapse/expand
        $('#searchPanelCollapse').on('show.bs.collapse', function () {
            $(this).closest('.card').find('.card-tools .fa-chevron-down').removeClass('fa-chevron-down').addClass('fa-chevron-up');
        }).on('hide.bs.collapse', function () {
            $(this).closest('.card').find('.card-tools .fa-chevron-up').removeClass('fa-chevron-up').addClass('fa-chevron-down');
        });

        // Show panel if there's existing search data
        if(searchingPane.toDataObject() != null) {
            $('#searchPanelCollapse').collapse('show');
        }
    });
JS
);

if (!empty($crud->getData("searchableFieldMap"))) : ?>
    <div class="row">
        <div class="col-12">
            <div class="card card-outline card-info ">
                <div class="card-header with-border" data-toggle="collapse" data-target="#searchPanelCollapse" aria-expanded="false" aria-controls="searchPanelCollapse" style="cursor: pointer;">
                    <h3 class="card-title"><i class="fa fa-search"></i> 進階搜尋</h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" data-toggle="collapse" data-target="#searchPanelCollapse" aria-expanded="false" aria-controls="searchPanelCollapse" onclick="event.stopPropagation();">
                            <i class="fa fa-chevron-down"></i>
                        </button>
                    </div>
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
                    <div class="col-2">
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
                            <button type="button" class="btn btn-info btnCopySc"><i class="fa fa-copy"></i></button>
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
                <div id="searchPanelCollapse" class="collapse">
                    <form class="form-horizontal" action="" method="get" id="formSearchCriteria">
                        <div class="card-body row">
                            <div class="formScBody col-12">
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
    </div>
<?php endif; ?>