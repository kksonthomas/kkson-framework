<?php

namespace KKsonFramework\App\Controller\backend;


use KKsonFramework\CRUD\SlimKKsonCRUD;
use KKsonFramework\RedBeanPHP\Model\User;
use KKsonFramework\CRUD\BaseCRUDController;
use KKsonFramework\CRUD\KKsonCRUD;
use KKsonFramework\RedBeanPHP\Model\RecordHistory;
use Swaggest\JsonDiff\JsonDiff;
use Swaggest\JsonDiff\JsonPatch;

class RecordHistoryController extends BaseCRUDController
{

    /**
     * @param SlimKKsonCRUD $crud
     */
    public function main($crud)
    {
        $crud->checkRole([User::ROLE_SYSTEM_ADMIN]);

        $crud->setData("tableDisplayName","Record History");
        $crud->enableCreate(false);
        $crud->enableEdit(false);
        $crud->enableDelete(false);
        $crud->enableSearch(true);

        $tableName = $this->params[0];
        $refId = $this->params[1];

        $crud->find("table_name = ? AND ref_id = CAST(? as CHAR) order by id desc", [$tableName, $refId]);
        $initJsonData = function ($recordHistory) {
            /** @var RecordHistory $recordHistory */
            if(!$recordHistory->hasCache("oldData")) {
                $recordHistory->putCache("oldData", json_decode($recordHistory->old_data));
                $recordHistory->putCache("newData", json_decode($recordHistory->new_data));
            }
        };

        $crud->field("action")->setCellHTML(function ($v, $recordHistory) use ($initJsonData) {
            /** @var RecordHistory $recordHistory */
            $initJsonData($recordHistory);
            if($v == 'update') {
                $oldData = $recordHistory->getCache("oldData");
                $newData = $recordHistory->getCache("newData");
                if(@$oldData->_deleted === 0 && @$newData->_deleted === 1) {
                    return "delete (mimic)";
                }
            }
            return $v;
        });

        $crud->field("diff")->setCellHTML(function ($v, $recordHistory) use ($initJsonData) {
            /** @var RecordHistory $recordHistory */

            $initJsonData($recordHistory);
            $oldData = $recordHistory->getCache("oldData");
            $newData = $recordHistory->getCache("newData");
            $html = "";

            if($recordHistory->action == 'create') {
                foreach ($newData as $fieldName => $value) {
                    if(is_array($value)) {
                        $fieldName .= "_id";
                        $value = $value["id"];
                    }
                    $html .= "<div class='text-green'>+ $fieldName: $value</div>";
                }
            } else if($recordHistory->action == 'delete') {
                return "";
            } else {
                $diff = new JsonDiff(
                    $oldData,
                    $newData,
                    JsonDiff::REARRANGE_ARRAYS
                );

                $oldValues = [];

                foreach (JsonPatch::export($diff->getPatch()) as $d) {
                    $fieldName = substr($d->path, 1);
                    if(str_contains($fieldName, "/")) {
                        $html .= "<div>($d->path op: $d->op v: $d->value)</div>";
                        continue;
                    }

                    switch ($d->op) {
                        case "add":
                            $html .= "<div class='text-green'>+ $fieldName: $d->value</div>";
                            break;
                        case "test":
                            $oldValues[$d->path]= $d->value;
                            break;
                        case "replace":
                            if($fieldName !== "modified_date") {
                                $html .= "<div class='text-orange'>* $fieldName: {$oldValues[$d->path]} => $d->value</div>";
                            }
                            break;
                        case "remove":
                            $html .= "<div class='text-red'>- $fieldName</div>";
                            break;
                    }
                }
            }
            return $html;
        });
    }

    /**
     * @param KKsonCRUD $crud
     */
    public function listView($crud)
    {
        $crud->hideFields([
            "old_data",
            "new_data",
            "creation_date",
            "creation_user_id",
//            "modified_date",
//            "modified_user_id"
        ]);
    }

    /**
     * @param KKsonCRUD $crud
     */
    public function create($crud)
    {
        $crud->hideFields([
            "creation_date",
            "creation_user_id",
            "modified_date",
            "modified_user_id"
        ]);
    }

    /**
     * @param KKsonCRUD $crud
     */
    public function edit($crud)
    {

    }
}