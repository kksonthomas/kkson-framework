<?php

namespace KKsonFramework\CRUD\FieldType;


use KKsonFramework\CRUD\Field;

abstract class FieldType
{

    /**
     * @var Field
     */
    protected $field;
    protected $attributeList = [];
    protected $classList = [];

    private $fieldRelation = Field::NORMAL;
    protected $beforeSaveBeanClosure = null;
    protected $afterSaveBeanClosure = null;

    public abstract function render($echo = false);

    /**
     * @param Field $field
     */
    public function setField($field)
    {
        $this->field = $field;
    }

    public function renderCell($value) {
       return htmlspecialchars($value);
    }

    /**
     * @return callable
     */
    public function getAfterSaveBeanClosure()
    {
        return $this->afterSaveBeanClosure;
    }

    /**
     * @param callable $afterSaveBeanClosure
     */
    public function setAfterSaveBeanClosure($afterSaveBeanClosure)
    {
        $this->afterSaveBeanClosure = $afterSaveBeanClosure;
    }

    protected function getReadOnlyString() {
        if ($this->field->isReadOnly()) {
            return "readonly";
        } else {
            return "";
        }
    }

    protected function getDisabledString() {
        if ($this->field->isDisabled()) {
            return "disabled";
        } else {
            return "";
        }
    }

    protected function getRequiredString()
    {
        if ($this->field->isRequired()) {
            return "required";
        } else {
            return "";
        }
    }

    protected function getRequiredStar()
    {
        if ($this->field->isRequired()) {
            return "<strong style='color: red;'>*</strong>";
        } else {
            return "";
        }
    }

    /**
     * @return array|string
     */
    public function getValue()
    {
        return $this->field->getRenderValue();
    }

    /**
     * @return int
     */
    public function getFieldRelation()
    {
        return $this->fieldRelation;
    }

    /**
     * @param int $fieldRelation
     */
    public function setFieldRelation($fieldRelation)
    {
        $this->fieldRelation = $fieldRelation;
    }

    public function addAttribute($key, $value) {
        $this->attributeList[$key] = $value;
    }

    public function removeAttribute($key) {
        unset($this->attributeList[$key]);
    }

    public function addClass($key, $value) {
        $this->classList[$key] = $value;
    }

    public function removeClass($key) {
        unset($this->classList[$key]);
    }

    public function beforeStoreValue($valueFromUser) {
        return $valueFromUser;
    }

    public function beforeRenderValue($valueFromDatabase) {
        return $valueFromDatabase;
    }

    /**
     * @return callable
     */
    public function getBeforeSaveBeanClosure()
    {
        return $this->beforeSaveBeanClosure;
    }

    /**
     * @param callable $beforeSaveBeanClosure
     */
    public function setBeforeSaveBeanClosure($beforeSaveBeanClosure)
    {
        $this->beforeSaveBeanClosure = $beforeSaveBeanClosure;
    }
  
}