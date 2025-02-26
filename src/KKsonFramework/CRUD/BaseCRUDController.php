<?php


namespace KKsonFramework\CRUD;


use KKsonFramework\CRUD\SearchFieldType\SearchCriteriaClasses\SearchCriteria;
use KKsonFramework\CRUD\SearchFieldType\SearchFieldBase;
use RedBeanPHP\R;

abstract class BaseCRUDController
{

    protected $params = [];

    /**
     * @var SlimKKsonCRUD
     */
    protected $crud;

    protected $baseTableName;
    protected $baseTableAlias;

    private $joinClauses = [];
    private $havingClauses = [];
    private $whereClauses = [];
    private $sqlData = [];
    private $groupBy = null;
    /**
     * @var SearchFieldBase[]
     */
    private $searchableFieldMap = [];

    private $defaultOrderByField = 'id';
    private $defaultOrderByDirection = 'DESC';

    private $defaultOrderByFieldUseBaseFieldName = true;

    /**
     * @param SlimKKsonCRUD $crud
     */
    public abstract function main($crud);

    /**
     * @param SlimKKsonCRUD $crud
     */
    public abstract function listView($crud);

    /**
     * @param SlimKKsonCRUD $crud
     */
    public abstract function create($crud);

    /**
     * @param SlimKKsonCRUD $crud
     */
    public abstract function edit($crud);

    /**
     * @return SlimKKsonCRUD
     */
    public function getCRUD()
    {
        return $this->crud;
    }

    public function setCRUD(SlimKKsonCRUD $crud)
    {
        $this->crud = $crud;
    }

    public function setParam($i, $value) {
        $this->params[$i] = $value;
    }

    /**
     * @param SearchFieldBase $searchableField
     * @throws \Exception
     */
    public function addSearchableField($searchableField) {
        if(isset($this->searchableFieldMap[$searchableField->getName()])) {
            throw new \Exception("Duplicated Search field definition: {$searchableField->getName()}");
        }
        $this->searchableFieldMap[$searchableField->getName()] = $searchableField;

    }

    /**
     * @param $name
     * @return SearchFieldBase
     */
    public function getSearchableField($name) {
        if(isset($this->searchableFieldMap[$name])) {
            return $this->searchableFieldMap[$name];
        } else {
            return null;
        }
    }

    /**
     * @return SearchFieldBase[]
     */
    public function getSearchableFieldMap(): array
    {
        return $this->searchableFieldMap;
    }

    private function searchParamToSql($param, &$sqlData) {
        if(count($param) == 2) {
            //group
            $sqlList = [];
            $data = [];
            foreach ($param[1] as $p) {
                $sqlList[] = $this->searchParamToSql($p, $data);
            }
            if(count($sqlList)) {
                $sqlData = array_merge($sqlData, $data);
                return "(".implode(" $param[0] ", $sqlList).")";
            } else {
                return "";
            }
        } else if(count($param) == 3) {
            //sc
            $fieldName = $param[0];
            $cond = $param[1];
            $keyword = $param[2];

            $searchField = $this->getSearchableField($fieldName);

            $callback = $searchField->getProcessSearchToSqlCallback();

            if($callback) {
                $sql = $callback($searchField, $cond, $keyword, $sqlData);
            } else {
                $sql = "";
                $processedKeyword = $keyword;
                $operator = $cond;

                switch ($cond) {
                    case SearchCriteria::COND_START:
                        $processedKeyword = "$keyword%";
                        $operator = "LIKE";
                        break;
                    case SearchCriteria::COND_NOT_START:
                        $processedKeyword = "$keyword%";
                        $operator = "NOT LIKE";
                        break;
                    case SearchCriteria::COND_CONTAINS:
                        $processedKeyword = "%$keyword%";
                        $operator = "LIKE";
                        break;
                    case SearchCriteria::COND_NOT_CONTAINS:
                        $processedKeyword = "%$keyword%";
                        $operator = "NOT LIKE";
                        break;
                    case SearchCriteria::COND_ENDS:
                        $processedKeyword = "%$keyword";
                        $operator = "LIKE";
                        break;
                    case SearchCriteria::COND_NOT_ENDS:
                        $processedKeyword = "%$keyword";
                        $operator = "NOT LIKE";
                        break;
                    case SearchCriteria::COND_EQUAL:
                        $operator = "=";
                        break;
                    case SearchCriteria::COND_NOT_EQUAL:
                        $operator = "<>";
                        break;
                    case SearchCriteria::COND_EMPTY:
                        $sql = "({$searchField->getFieldSql(true,$this)} IS NULL OR {$searchField->getFieldSql(true,$this)} = '')";
                        break;
                    case SearchCriteria::COND_NOT_EMPTY:
                        $sql = "({$searchField->getFieldSql(true,$this)} IS NOT NULL OR {$searchField->getFieldSql(true,$this)} <> '')";
                        break;
                    case SearchCriteria::COND_LARGER_THAN_OR_EQUAL:
                    case SearchCriteria::COND_LARGER_THAN:
                    case SearchCriteria::COND_SMALLER_THAN_OR_EQUAL:
                    case SearchCriteria::COND_SMALLER_THAN:
                        break;
                    default:
                        return "";
                }
                if($sql == "") {
                    $sql = "(" .$searchField->getFieldSql(true,$this) . " $operator ?)";
                    $sqlData[] = $processedKeyword;
                }
            }

            return $sql;
        }
        return "";
    }

    public function initSearchFunction() {
        $this->crud->setData("searchableFieldMap", $this->getSearchableFieldMap());
        // handle search
        $q = $this->crud->getSlim()->request->params("q");
        
        $decodedQ = base64_decode(rawurldecode($q));
        $json = urldecode($decodedQ);
        
        $searchParam = json_decode($json);
        if($searchParam) {
            $this->whereClauses[] = $this->searchParamToSql($searchParam, $this->sqlData);
        }

        //temp fix for export excel
        if(!empty($q)) {
            $paramString = http_build_query(["q" => $q]);
            $this->crud->setExportLink($this->crud->getExportLink() . "?" .  $paramString);
        }
    }

    public function getWhereClauseSql() {
        return empty($this->whereClauses) ? "1=1" : implode(" AND ", $this->whereClauses) ;
    }

    /**
     * @return mixed
     */
    public function getBaseTableName()
    {
        return $this->baseTableName;
    }

    /**
     * @return mixed
     */
    public function getBaseTableAlias()
    {
        return $this->baseTableAlias;
    }

    /**
     * @param $tableName
     * @param $alias
     */
    public function setBaseTableName($tableName, $alias): void
    {
        $this->baseTableName = $tableName;
        $this->baseTableAlias = $alias;
    }

    public function addWhereClause($whereClause, $data = []) {
        $this->whereClauses[] = $whereClause;
        $this->sqlData = array_merge($this->sqlData, $data);
    }

    public function baseFieldName($name) {
        return "$this->baseTableAlias.`$name`";
    }

    public function setTableDisplayName($name) {
        $this->crud->setData("tableDisplayName",$name);
    }

    public function addJoinClause($tableName, $alias, $joinClause, $joinType = "") {
        $this->joinClauses[] = "$joinType JOIN $tableName $alias ON $joinClause";
    }

    public function addLeftJoinClause($tableName, $alias, $joinClause) {
        $this->addJoinClause($tableName, $alias, $joinClause, "LEFT");
    }

    public function addHavingClause($havingClause, $data = []) {
        $this->havingClauses[] = $havingClause;
        $this->sqlData = array_merge($this->sqlData, $data);
    }

    private function getJoinClausesSql() {
        return implode(" ", $this->joinClauses);
    }

    private function getSelectFieldsSql() {
        $fieldSQLs = ["$this->baseTableAlias.*"];
        foreach ($this->crud->getFields() as $showField) {
            $searchableField = $this->getSearchableField($showField->getName());
            if($searchableField && $searchableField->getFieldSql(false)) {
                $fieldSQLs[] = $searchableField->getFieldSql() . " AS " . $showField->getName();
            } else if($showField->getSql()) {
                $fieldSQLs[] = $showField->getSql() . " AS " . $showField->getName();
            }
        }
        $fieldSql = implode(", ", $fieldSQLs);
        return $fieldSql;
    }

    private function getSqlBody() {
        $sql = "
            FROM $this->baseTableName $this->baseTableAlias
            {$this->getJoinClausesSql()}
            WHERE
                {$this->getWhereClauseSql()}
            {$this->getGroupBySql()}";

        return $sql;
    }

    public function getHavingClauseSql() {
        return empty($this->havingClauses) ? "" : ("HAVING ".implode(" AND ", $this->havingClauses)) ;
    }

    public function setupListViewDataClosures() {
        $this->crud->setListViewDataClosure(function($start, $rowPerPage, $keyword, $sortField, $sortOrder) {
            //the keyword is not used as it is fuzzy search function, which may have performance issues
            $sortFieldObj = $this->crud->getField($sortField);
            if($sortFieldObj && $sortFieldObj->isSortable()) {
                $searchableField = $this->getSearchableField($sortField);
                if($searchableField && $searchableField->getFieldSql(true,$this)) {
                    $sortField = $searchableField->getFieldSql(true,$this);
                }  else {
                    $sortField = $sortFieldObj->getSql($this->baseFieldName($sortField));
                }
            } else {
                $sortField = $this->getOrderByField();
                $sortOrder = $this->getOrderByDir();
            }

            $pageLimit = $start !== null && $rowPerPage !== null ? "LIMIT $start, $rowPerPage" : "" ;

            $sql = "SELECT {$this->getSelectFieldsSql()} {$this->getSqlBody()} ORDER BY $sortField $sortOrder $pageLimit";
            return R::convertToBeans($this->baseTableName, R::getAll($sql, $this->sqlData));
        });
        $this->crud->setCountListViewDataClosure(function($keyword) {
            $sql = "SELECT 
            COUNT({$this->baseFieldName("id")})
                {$this->getSqlBody()}";
            return R::getCell($sql, $this->sqlData);
        });
    }

    /**
     * @param null $groupBy
     */
    public function setGroupBy($groupBy): void
    {
        $this->groupBy = $groupBy;
    }

    public function getGroupBySql() {
        return $this->groupBy ? "GROUP BY $this->groupBy" :  "";
    }

    /**
     * Get the default order by field
     * @return string
     */
    public function getOrderByField(): string
    {
        return $this->defaultOrderByFieldUseBaseFieldName ? $this->baseFieldName($this->defaultOrderByField) : $this->defaultOrderByField;
    }

    /**
     * Get the default order by direction
     * @return string
     */
    public function getOrderByDir(): string
    {
        return $this->defaultOrderByDirection;
    }

    /**
     * Set the default order by field and direction
     * @param string $field
     * @param string $direction
     */
    public function setOrderBy(string $field, string $direction = 'DESC'): void
    {
        $this->defaultOrderByFieldUseBaseFieldName = false;
        $this->defaultOrderByField = $field;
        $this->defaultOrderByDirection = strtoupper($direction);
        if (!in_array($this->defaultOrderByDirection, ['ASC', 'DESC'])) {
            $this->defaultOrderByDirection = 'DESC';
        }
    }
}
