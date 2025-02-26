<?php


namespace KKsonFramework\CRUD\FieldType;


class Email extends TextField
{
    /**
     * Email constructor.
     */
    public function __construct()
    {
        $this->type = "email";
    }

}