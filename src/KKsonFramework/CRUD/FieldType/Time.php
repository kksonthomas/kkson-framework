<?php

namespace KKsonFramework\CRUD\FieldType;


class Time extends TextField
{
    /**
     * Email constructor.
     */
    public function __construct()
    {
        $this->type = "time";
    }

}