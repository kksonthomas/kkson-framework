<?php

namespace KKsonFramework\CRUD\FieldType;

use KKsonFramework\CRUD\KKsonCRUD;

class DropDownSelect2 extends Dropdown
{
    /**
     * ManyToOne constructor.
     * @param $dataOptions
     * @param bool $nullOption
     * @internal param callable $nameClosure
     */
    public function __construct($dataOptions, $nullOption = true) {
        $options = [];
        if ($nullOption) {
            $options[KKsonCRUD::NULL] = "--";
        }
        parent::__construct($options + $dataOptions);

        $this->enableSelect2(true);
    }

    public function render($echo = false) {
        $html = "<div class='form-group'>".parent::render(false)."</div>";
        if($echo) {
            echo $html;
        }

        return $html;
    }

}