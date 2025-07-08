<?php

namespace KKsonFramework\CRUD\FieldType;

use Stringy\Stringy;

class OverflowHiddenTextField extends TextField
{
    protected $maxWidth = 100;

    public function __construct($maxWidth = 100) {
        $this->maxWidth = $maxWidth;
    }

    public function renderCell($value, $bean)
    {
        if (!empty($value)) {
            $shortValue = Stringy::create($value)->truncate($this->maxWidth, "...");
            $html = "";
            $html .= "<a href='javascript:void(0)' onclick='KKsonUtils.copyToClipboard(\"" . htmlspecialchars($value) . "\")' title='" . htmlspecialchars($value) . "'>" . htmlspecialchars($shortValue) . "</a>";
            return $html;
        }
        return "";
    }
}