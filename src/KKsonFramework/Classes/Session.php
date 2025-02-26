<?php


namespace KKsonFramework\Classes;


class Session
{
    protected $prefix;
    private $prefix2;

    public function __construct($prefix)
    {
        $this->prefix = $prefix;
        $this->prefix2 = $prefix ? $prefix."_" : "";
    }

    public function getSessionField($field) {
        $field = $this->prefix2 . $field;
        return isset($_SESSION[$field]) ? $_SESSION[$field] : null;
    }
    public function setSessionField($field, $v) {
        $field = $this->prefix2 . $field;
        $_SESSION[$field] = $v;
    }
    public function issetSessionField($field) {
        $field = $this->prefix2 . $field;
        return isset($_SESSION[$field]);
    }
    public function unsetSessionField($field) {
        $field = $this->prefix2 . $field;
        if(isset($_SESSION[$field])) {
            unset($_SESSION[$field]);
        }
    }

    public function clearAll() {
        $keyList = [];
        foreach ($_SESSION as $key => $value) {
            if(strpos($key, $this->prefix2) === 0) {
                $keyList[] = $key;
            }
        }

        foreach ($keyList as $key) {
            unset($_SESSION[$key]);
        }
    }
}