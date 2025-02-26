<?php

namespace KKsonFramework\CRUD;


class FieldGroup
{

    private $groupName;

    /**
     * @var Field[]
     */
    private $fieldList = [];

    private $widthList = [];

    /**
     * @return mixed
     */
    public function getGroupName()
    {
        return $this->groupName;
    }

    /**
     * @param mixed $groupName
     */
    public function setGroupName($groupName)
    {
        $this->groupName = $groupName;
    }

    public function addField(Field $field, $width)
    {
        $this->fieldList[$field->getName()] = $field;
        $this->widthList[$field->getName()] = $width;
    }

    /**
     * @return Field[]
     */
    public function getFieldList() {
        return $this->fieldList;
    }

    public function getWidth($fieldName) {

        if (isset($this->widthList[$fieldName])) {
            return $this->widthList[$fieldName];
        } else {
            return 6;
        }

    }

}