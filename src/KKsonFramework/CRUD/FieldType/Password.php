<?php

namespace KKsonFramework\CRUD\FieldType;


class Password extends TextField
{

    /**
     * @var callback
     */
    private $encryptionClosure = null;

    public function __construct()
    {
        $this->type = "password";

        // Use MD5 by default
        $this->encryptionClosure = function ($v) {
            return password_hash($v, PASSWORD_DEFAULT);
        };
    }

    public function renderCell($value)
    {
        return "***";
    }

    public function beforeStoreValue($valueFromUser)
    {
        $c = $this->encryptionClosure;

        return $c($valueFromUser);
    }
}