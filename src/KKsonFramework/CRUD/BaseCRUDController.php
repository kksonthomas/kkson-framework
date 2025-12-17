<?php


namespace KKsonFramework\CRUD;

use KKsonFramework\Conf\AppConfig;
use KKsonFramework\CRUD\SearchFieldType\SearchCriteriaClasses\SearchCriteria;
use KKsonFramework\CRUD\SearchFieldType\SearchFieldBase;
use KKsonFramework\CRUD\SearchFieldType\TextSearchField;
use RedBeanPHP\R;

abstract class BaseCRUDController
{

    protected $params = [];

    /**
     * @var SlimKKsonCRUD
     */
    protected $crud;

    protected $baseTableName;
    protected $shouldEscapeBaseTableName = false;
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
    /**
     * @var SearchFieldBase[]
     */
    private $searchableColFieldMap = [];

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
     * @param SearchFieldBase $searchableField
     * @throws \Exception
     */
    public function addSearchableColField($searchableColField) {
        if(isset($this->searchableColFieldMap[$searchableColField->getName()])) {
            throw new \Exception("Duplicated Searchable Col field definition: {$searchableColField->getName()}");
        }
        $this->searchableColFieldMap[$searchableColField->getName()] = $searchableColField;

    }

    /**
     * @param $name
     * @return SearchFieldBase
     */
    public function getSearchableColField($name) {
        if(isset($this->searchableColFieldMap[$name])) {
            return $this->searchableColFieldMap[$name];
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

    /**
     * @return SearchFieldBase[]
     */
    public function getSearchableColFieldMap(): array
    {
        return $this->searchableColFieldMap;
    }

    private function searchParamToSql($param, &$sqlData, $allowNotDefinedSearchableField = false, $getSearchableFieldCallback = null) {
        if(!$getSearchableFieldCallback) {
            $getSearchableFieldCallback = [$this, "getSearchableField"];
        }
        if(count($param) == 2) {
            //group
            $sqlList = [];
            $data = [];
            foreach ($param[1] as $p) {
                $sqlList[] = $this->searchParamToSql($p, $data, $allowNotDefinedSearchableField, $getSearchableFieldCallback);
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
            $keyword = $param[2] instanceof \stdClass ? $param[2]->id : $param[2]; // {id, text} object for select2 ajax

            $searchField = $getSearchableFieldCallback($fieldName);
            if(!$searchField) {
                if($allowNotDefinedSearchableField) {
                    $searchField = new TextSearchField($fieldName);
                } else {
                    throw new \Exception("Searchable field not found: $fieldName");
                }
            }

            $callback = $searchField->getProcessSearchToSqlCallback();

            if($callback) {
                $sql = $callback($searchField, $cond, $keyword, $sqlData);
            } else {
                $sql = "";
                $keyword = $keyword;
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
        $this->crud->setData("searchableColFieldMap", $this->getSearchableColFieldMap());
        // handle search
        $q = $this->crud->getSlim()->request->params("q");
        if($q !== null) {
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

        $columns = $this->crud->getSlim()->request->params("columns", []);
        $colSearchParams = [];
        $fields = [null, ...$this->crud->getShowFields()];
        if(count($columns)) {
            //decode from columns parameter
            foreach($columns as $column) {
                $fixeds = @$column["search"]["fixed"];
                if($fixeds) {
                    foreach($fixeds as $fixed) {    
                        if($fixed["name"] == "kkson-crud-col-search") {
                            $details = json_decode($fixed["term"]);
                            if(isset($column["data"])) {
                                $field = $fields[$column["data"]];
                                if($field && isset($details->term)) {
                                    $colSearchParams[] = [
                                        $field->getName(),
                                        $details->logic,
                                        $details->term
                                    ];
                                }
                            }
                        }
                    }
                }
            }
        } else {
            //decode from hash parameter
            $hash = $this->crud->getSlim()->request->params("hash", "");
            if($hash) {
                $hashParts = explode("&", $hash);
                foreach($hashParts as $hashPart) {
                    $key = explode("=", $hashPart)[0];
                    $value = explode("=", $hashPart)[1];
                    $field = $fields[$key];
                    if($field) {
                        $colSearchParams[] = [
                            $field->getName(),
                            "contains",
                            $value
                        ];
                    }
                }
            }
        }
        if(count($colSearchParams)) {
            $this->whereClauses[] = $this->searchParamToSql(["AND", $colSearchParams], $this->sqlData, true, [$this, "getSearchableColField"]);
        }
    }

    public static function getSearchParam($q) {
        $decodedQ = base64_decode(rawurldecode($q));
        $json = urldecode($decodedQ);
        $searchParam = json_decode($json);
        return $searchParam;
    }

    public static function encodeSearchParam($searchParam) {
        $json = json_encode($searchParam);
        $encodedQ = base64_encode(rawurlencode($json));
        $q = http_build_query(["q" => $encodedQ]);
        return $q;
    }

    public function getWhereClauseSql() {
        return empty($this->whereClauses) ? "1=1" : implode(" AND ", $this->whereClauses) ;
    }

    /**
     * @return mixed
     */
    public function getBaseTableName()
    {
        return $this->shouldEscapeBaseTableName ? "`$this->baseTableName`" : $this->baseTableName;
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
    public function setBaseTableName($tableName, $alias, $shouldEscapeTableName = false): void
    {
        $this->baseTableName = $tableName;
        $this->baseTableAlias = $alias;
        $this->shouldEscapeBaseTableName = $shouldEscapeTableName;
    }

    public function addWhereClause($whereClause, $data = []) {
        $this->whereClauses[] = $whereClause;
        $this->sqlData = array_merge($this->sqlData, $data);
    }

    public function baseFieldName($name) {
        return "$this->baseTableAlias.`$name`";
    }

    public function setTableDisplayName($name, $alsoSetTitle = true) {
        $this->crud->setData("tableDisplayName",$name);
        if($alsoSetTitle) {
            $this->crud->setData("title", AppConfig::get()->appName() . " - " . $name);
        }
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
            if($showField->getSql()) {
                $fieldSQLs[] = $showField->getSql() . " AS " . $showField->getName();
            } else if($searchableField && $searchableField->getFieldSql(false)) {
                $fieldSQLs[] = $searchableField->getFieldSql() . " AS " . $showField->getName();
            }
        }
        $fieldSql = implode(", ", $fieldSQLs);
        return $fieldSql;
    }

    private function getSqlBody($withGroupBy = true) {

        $groupBySql = $withGroupBy ? $this->getGroupBySql() . " ". $this->getHavingClauseSql() : "";
        $sql = "
            FROM {$this->getBaseTableName()} $this->baseTableAlias
            {$this->getJoinClausesSql()}
            WHERE
                {$this->getWhereClauseSql()}
            {$groupBySql}";

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
            // R::fancyDebug(1);
            $data = R::getAll($sql, $this->sqlData);
            return R::convertToBeans($this->baseTableName, $data);
        });
        $this->crud->setCountListViewDataClosure(function($keyword) {
            $sql = "SELECT 
            COUNT(distinct {$this->baseFieldName("id")})
                {$this->getSqlBody(false)}";
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
