<?php

namespace KKsonFramework\RedBeanPHP\ModelBase;

use KKsonFramework\Auth\Auth;
use KKsonFramework\RedBeanPHP\Exception\StaleDeletedModelException;
use KKsonFramework\Utils\Cache;
use RedBeanPHP\OODBBean;
use RedBeanPHP\R;
use RedBeanPHP\SimpleModel;

/**
 * @property ?int $modified_user_id
 * @property ?string $modified_date
 * @property ?int $creation_user_id
 * @property ?string $creation_date
 * @property int $id
 * @property int $_deleted
 */
abstract class BaseModelBase extends SimpleModel
{
    /**
     * 用作分別 Create 定 update
     * @var int
     */
    private $tempID = 0;

    /**
     * @var Cache
     */
    private $cache;

    /**
     * Allows undeleteSelf() / save(true) to set _deleted=0 when DB row is soft-deleted.
     */
    private bool $allowReviveDeleted = false;

    public function __construct()
    {
        $this->cache = new Cache();
    }

    public function hasCache($k) {
        return $this->cache->has($k);
    }

    public function putCache($k, $v) {
        $this->cache->put($k, $v);
    }

    public function getCache($k) {
        return $this->cache->get($k);
    }

    public function removeCache($k) {
        $this->cache->remove($k);
    }


    public abstract static function _getTableName();

    /**
     * @param $id
     * @param bool $findDeleted
     * @return static
     */
    public static function load($id, $findDeleted = false) {
        if(static::_enabledMimicDelete()) {
            $bean = R::findOne(static::_getTableName(), "id = ? ".($findDeleted ? "" : "AND _deleted = 0"), [$id]);
        } else {
            $bean = R::load(static::_getTableName(), $id);
        }

        if($bean && $bean->id != 0) {
            return $bean->box();
        } else {
            return null;
        }
    }


    /**
     * Get the ID after the object has been removed
     */
    protected function getTempID() {
        return $this->tempID;
    }

    public static function beansToType($rbs) {
        return array_map(function ($redBean) {
            return $redBean->box();
        }, $rbs);
    }

    public function open() {

    }

    public function save(bool $allowReviveDeleted = false) {
        $this->allowReviveDeleted = $allowReviveDeleted;
        try {
            return R::store($this);
        } finally {
            $this->allowReviveDeleted = false;
        }
    }

    public function update() {
        $this->assertMimicDeleteWritable();

        $this->tempID = $this->id;
        
        $user = Auth::getUser();
        
        if ($this->id == 0) {
            if ($user != null) {
                $this->creation_user_id = $user->id;
            }
            $this->creation_date = R::isoDateTime();
        }

        if ($user != null) {
            $this->modified_user_id = $user->id;
        }
        $this->modified_date = R::isoDateTime();
    }

    public function after_update() {

    }

    public function delete() {
        $this->tempID = $this->id;
    }

    public function after_delete() {

    }

    public function alias($aliasName) {
        return $this->bean->alias($aliasName);
    }

    public function fetchAs($type) {
        return $this->bean->fetchAs($type);
    }
    
    public function getBean() {
        return $this->bean;
    }
    
    /**
     * @param string $name
     * @return array
     */
    public function getOwnList($name) {
        $list = $this->bean->{"own" . ucfirst($name) . "List"};
        return self::beanListToObjectList($list);
    }
    
    /**
     * @param string $name
     * @return array
     */
    public function getSharedList($name) {
        $list = $this->bean->{"shared" . ucfirst($name) . "List"};
        return self::beanListToObjectList($list);
    }

    /**
     * @param OODBBean[] $list
     * @return static[]
     */
    public static function beanListToObjectList($list) {
        $result = [];
        foreach ($list as $bean) {
            $result[$bean->id] = $bean->box();
        }
        return $result;
    }

    public static function _enabledMimicDelete() {
        return false;
    }

    public function deleteSelf() {
        if(static::_enabledMimicDelete()) {
            $this->_deleted = 1;
            R::store($this);
        } else {
            R::trash($this);
        }
    }

    public function undeleteSelf() {
        if(static::_enabledMimicDelete()) {
            $this->_deleted = 0;
            $this->allowReviveDeleted = true;
            try {
                R::store($this);
            } finally {
                $this->allowReviveDeleted = false;
            }
        }
    }

    /**
     * When mimic delete is enabled, re-reads _deleted from the database for this id.
     */
    public function isActiveInDb(): bool
    {
        if (!static::_enabledMimicDelete() || $this->id <= 0) {
            return true;
        }

        $dbDeleted = R::getCell(
            "SELECT `_deleted` FROM `" . static::_getTableName() . "` WHERE id = ?",
            [$this->id]
        );

        return $dbDeleted !== null && (int) $dbDeleted === 0;
    }

    /**
     * Rejects stale revive: DB row soft-deleted while in-memory bean still has _deleted=0.
     */
    protected function assertMimicDeleteWritable(): void
    {
        if (!static::_enabledMimicDelete() || $this->id <= 0) {
            return;
        }

        $table = static::_getTableName();
        $dbDeleted = R::getCell(
            "SELECT `_deleted` FROM `{$table}` WHERE id = ?",
            [$this->id]
        );

        if ($dbDeleted === null) {
            throw new StaleDeletedModelException($table, (int) $this->id, "Row no longer exists.");
        }

        if (static::isStaleMimicDeleteRevive((int) $dbDeleted, (int) ($this->_deleted ?? 0), $this->allowReviveDeleted)) {
            throw new StaleDeletedModelException($table, (int) $this->id);
        }
    }

    /**
     * True when DB row is soft-deleted but the in-memory bean still looks active.
     */
    protected static function isStaleMimicDeleteRevive(int $dbDeleted, int $beanDeleted, bool $allowReviveDeleted): bool
    {
        return $dbDeleted === 1 && $beanDeleted === 0 && !$allowReviveDeleted;
    }

    public function trash()
    {
        R::trash($this);
    }

    public static function find($sql, $data = []){
        return static::beanListToObjectList(R::find(static::_getTableName(), $sql, $data));
    }

    public static function findAll($sql = "", $data = [])
    {
        if($sql == "") {
            $sql = "1=1";
        }
        return self::find($sql, $data);
    }

    /**
     * @param $sql
     * @param array $data
     * @return static[]
     */
    public static function getAll($sql, $data = []) {
        return static::beanListToObjectList(R::convertToBeans(static::_getTableName(), R::getAll($sql, $data)));
    }

    /**
     * @return static
     */
    public static function dispenseModel(){
        //cant use dispense as function names as preserved name
        return R::xdispense(static::_getTableName())->box();
    }

    public function store() {
        return R::store($this);
    }

    /**
     * @param $sql
     * @param array $data
     * @return static
     */
    public static function findOne($sql, $data = []){
        $bean = R::findOne(static::_getTableName(), $sql, $data);
        if($bean) {
            return $bean->box();
        } else {
            return null;
        }
    }

    public static function findCollection($sql = "", $data = [])
    {
         return R::findCollection(static::_getTableName(), $sql, $data);
    }

    public function old($field) {
        return $this->getBean()->old($field);
    }

    public static function findById($id, $findDeleted = false) {
        return static::load($id, $findDeleted);
    }


    /**
     * @param int $id
     * @param bool $findDeleted
     * @return static|null
     */
    public static function loadForUpdate($id, $findDeleted = false) {
        $bean = R::loadForUpdate(static::_getTableName(), $id);

        if($bean && $bean->id != 0) {
            if (static::_enabledMimicDelete() && !$findDeleted && (int) $bean->_deleted === 1) {
                return null;
            }
            return $bean->box();
        } else {
            return null;
        }
    }

    /**
     * @param string $sql
     * @param array $data
     * @return static[]
     */
    public static function findForUpdate($sql, $data = []) {
        return self::beanListToObjectList(R::findForUpdate(static::_getTableName(), $sql, $data));
    }

    /**
     * @param string $sql
     * @param array $data
     * @return static|null
     */
    public static function findOneForUpdate($sql, $data = []) {
        $bean = R::findOneForUpdate(static::_getTableName(), $sql, $data);
        if($bean) {
            return $bean->box();
        } else {
            return null;
        }
    }

    public function export($meta = false, $parents = false, $onlyMe = false, $filters = []) {
        return $this->bean->export($meta, $parents, $onlyMe, $filters);
    }
}