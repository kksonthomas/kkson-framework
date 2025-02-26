<?php

namespace KKsonFramework\CRUD\FieldType;


class CustomField extends FieldType
{

    /** @var  string */
    private $html;

    /**
     * Render Field for Create/Edit
     * @param bool|true $echo
     * @return string
     */
    public function render($echo = false)
    {

        if ($echo) {
            echo $this->html;
        }

        return $this->html;
    }

    /**
     * @param string $html
     */
    public function setHtml($html)
    {
        $this->html = $html;
    }

}