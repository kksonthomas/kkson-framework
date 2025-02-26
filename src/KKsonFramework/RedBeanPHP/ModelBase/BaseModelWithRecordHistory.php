<?php

namespace KKsonFramework\RedBeanPHP\ModelBase;

use RedBeanPHP\R;

abstract class BaseModelWithRecordHistory extends BaseModel
{
    private $record = null;
    public function update()
    {
        parent::update();
        $oldData = null;
        if($this->id) {
            $oldData = R::getRow("SELECT * FROM `".static::_getTableName()."` WHERE id = ?", [$this->id]);
            $action = "update";
        } else {
            $action = "create";
        }
        $newData = $this->bean->export();

        $this->record = R::xdispense("record_history");
        $this->record->table_name = static::_getTableName();
        $this->record->action = $action;
        $this->record->old_data = $oldData === null ? null : json_encode($oldData);
        $this->record->new_data = $newData === null ? null : json_encode($newData);
    }

    public function after_update()
    {
        parent::after_update();

        $this->record->ref_id = $this->id;
        R::store($this->record);
        $this->record = null;
    }


    public function delete()
    {
        parent::delete();

        $data = $this->bean->export();
        $action = "delete";


        $this->record = R::xdispense("record_history");
        $this->record->table_name = static::_getTableName();
        $this->record->ref_id = $this->id;
        $this->record->action = $action;
        $this->record->old_data = json_encode($data);
        R::store($this->record);
        $this->record = null;
    }
}