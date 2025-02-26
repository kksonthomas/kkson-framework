<?php


namespace KKsonFramework\CRUD\SearchFieldType\SearchCriteriaClasses;


class SearchCriteriaGroup
{
    protected $groupCondition;
    protected $searchCriteriaList;

    /**
     * SearchCriteriaGroup constructor.
     * @param $groupCondition
     * @param SearchCriteria[] $searchCriteriaList
     */
    public function __construct($groupCondition, $searchCriteriaList)
    {
        $this->groupCondition = $groupCondition;
        $this->searchCriteriaList = $searchCriteriaList;
    }


    public function toDataObject() {
        $searchCriteriaDataList = [];
        foreach ($this->searchCriteriaList as $searchCriteria) {
            $searchCriteriaDataList[] = $searchCriteria->toDataObject();
        }
        return [$this->groupCondition, $searchCriteriaDataList];
    }

    public function toQueryString() {
        return base64_encode(json_encode($this->toDataObject()));
    }

    public static function createSimpleSearchCriteria($field, $cond, $keyword) {
        return new self("and", [new SearchCriteria($field, $cond, $keyword)]);
    }

}