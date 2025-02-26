<?php


namespace KKsonFramework\CRUD\SearchFieldType;

use KKsonFramework\App\App;
use KKsonFramework\CRUD\SlimKKsonCRUD;
use KKsonFramework\CRUD\BaseCRUDController;
use KKsonFramework\CRUD\KKsonCRUD;
use KKsonFramework\CRUD\SearchFieldType\SearchCriteriaClasses\SearchCriteria;

abstract class SearchFieldBase
{
    public static function getConditionConfig() {
        return [
            //cond => [displayName, isShowsInput]
            SearchCriteria::COND_EQUAL => ["等於 = ", true],
            SearchCriteria::COND_NOT_EQUAL => ["不等於 !=", true],
            SearchCriteria::COND_START => ["開始於", true],
            SearchCriteria::COND_NOT_START => ["非開始於", true],
            SearchCriteria::COND_CONTAINS => ["包含", true],
            SearchCriteria::COND_NOT_CONTAINS => ["不包含", true],
            SearchCriteria::COND_ENDS => ["結束於", true],
            SearchCriteria::COND_NOT_ENDS => ["非結束於", true],

            SearchCriteria::COND_EMPTY => ["為空", false],
            SearchCriteria::COND_NOT_EMPTY => ["不為空", false],

            SearchCriteria::COND_LARGER_THAN_OR_EQUAL => ["大於等於 >=", true],
            SearchCriteria::COND_LARGER_THAN => ["大於 >", true],
            SearchCriteria::COND_SMALLER_THAN_OR_EQUAL => ["小於等於 <=", true],
            SearchCriteria::COND_SMALLER_THAN => ["小於 <", true],
        ];
    }

    private $name;
    private $sqlFieldName;
    private $displayName;
    private $processSearchToSqlCallback;
    /**
     * @var SlimKKsonCRUD
     */
    private $crud;

    /**
     * SearchFieldBase constructor.
     * @param $name
     * @param SlimKKsonCRUD|string $displayName
     * @param null $fieldSql
     * @param callable|null $processSearchToSqlCallback
     */
    public function __construct($name, $displayName = null, $fieldSql = null, callable $processSearchToSqlCallback = null)
    {
        $this->name = $name;
        if($displayName instanceof KKsonCRUD) {
            $this->crud = $displayName;
        } else {
            $this->displayName = $displayName;
        }

        $this->sqlFieldName = $fieldSql;

        $this->setProcessSearchToSqlCallback($processSearchToSqlCallback);
    }

    public function getName() {
        return $this->name;
    }

    public abstract function render();

    public function getConditionList() {
        return [
            SearchCriteria::COND_CONTAINS,
            SearchCriteria::COND_NOT_CONTAINS,
            SearchCriteria::COND_EQUAL,
            SearchCriteria::COND_NOT_EQUAL,
            SearchCriteria::COND_START,
            SearchCriteria::COND_NOT_START,
            SearchCriteria::COND_ENDS,
            SearchCriteria::COND_NOT_ENDS,
            SearchCriteria::COND_EMPTY,
            SearchCriteria::COND_NOT_EMPTY
        ];
    }

    /**
     * @param bool $tryGetFromField
     * @param BaseCRUDController $controller
     * @return string
     */
    public function getFieldSql($tryGetFromField = true, $controller = null)
    {
        if($this->sqlFieldName) {
            return $this->sqlFieldName;
        } else if($tryGetFromField){
            $crud = App::getCrud();
            $field = $crud->getField($this->getName());
            if($field && $field->getSql()) {
                return $field->getSql();
            } else {
                if($controller) {
                    return $controller->baseFieldName($this->getName());
                }
                return $this->getName();
            }
        }

    }

    /**
     * @return string
     */
    public function getDisplayName()
    {
        if($this->displayName == null) {
            /** @var SlimKKsonCRUD $crud */
            $crud = App::getCrud();
            if($crud->getField($this->getName())) {
                return $crud->getField($this->getName())->getDisplayName();
            } else {
                return ucwords(str_replace('_', ' ', $this->name));
            }
        } else {
            return $this->displayName;
        }
    }

    /**
     * @return callable|\Closure|null
     */
    public function getProcessSearchToSqlCallback()
    {
        return $this->processSearchToSqlCallback;
    }

    /**
     * @param callable|\Closure $processSearchToSqlCallback
     */
    public function setProcessSearchToSqlCallback($processSearchToSqlCallback): void
    {
        $this->processSearchToSqlCallback = $processSearchToSqlCallback;
    }

}