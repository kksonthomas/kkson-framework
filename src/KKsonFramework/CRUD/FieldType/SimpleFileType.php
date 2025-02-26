<?php

namespace KKsonFramework\CRUD\FieldType;


use KKsonFramework\CRUD\Exception\DirectoryPermissionException;
use KKsonFramework\CRUD\FieldType\FieldType;
use KKsonFramework\Utils\UrlUtils;
use Stringy\Stringy;

class SimpleFileType extends FieldType
{

    protected $inputType = "file";
    protected $additionalAttr = "";
    private $allowMultiple;

    public function __construct($allowMultiple = false)
    {
        $this->allowMultiple = $allowMultiple;
        if($allowMultiple) {
            $this->additionalAttr .= " multiple";
        }
    }

    public function render($echo = false)
    {
        $name = $this->field->getName();
        $display = $this->field->getDisplayName();
        $value = $this->getValue();
        $readOnly = $this->getReadOnlyString();
        $required = $this->getRequiredString();
        $crud = $this->field->getCRUD();
        $inputType = $this->inputType;
        $additionalAttr = $this->additionalAttr;

        $inputName = $name . ($this->allowMultiple ? "[]" : "");
        $html = <<< HTML
<div class="form-group">
    <label for="upload-$name">$display</label>
    <input id="upload-$name" class="form-control" type="$inputType" name="$inputName" $readOnly $required data-required="$required" $additionalAttr />
HTML;

        if ($echo)
            echo $html;

        return $html;
    }

    public function renderCell($value)
    {
        $imgURL = UrlUtils::res($value);

        if ($value != null && $value != "") {
            return <<< HTML
<a target="_blank" href="$imgURL">Open File</a>
HTML;
        } else {
            return "";
        }
    }
}