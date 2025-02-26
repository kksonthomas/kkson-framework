<?php

namespace KKsonFramework\CRUD\FieldType;


class Date extends TextField
{
    /**
     * Email constructor.
     */
    public function __construct()
    {
        $this->type = "date";
    }

}