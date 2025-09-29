<?php

namespace KKsonFramework\CRUD\FieldType;

class UrlTextField extends TextField
{
    private $customDisplayName = null;

    public function __construct($customDisplayName = null)
    {
        $this->customDisplayName = $customDisplayName;
    }

    public function renderCell($url, $bean)
    {
        if ($this->customDisplayName != null) {
            $displayName = $this->customDisplayName;
        } else {
            $displayName = $url;
        }
        if (!empty($url)) {
            $html = "";
            $html .= "<a href='{$url}' target='_blank'>{$displayName}</a>";
            return $html;
        }
        return "";
    }
}