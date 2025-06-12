<?php

namespace KKsonFramework\CRUD\FieldType;

class UrlButton extends TextField
{
    public function renderCell($url)
    {
        if (!empty($url)) {
            $html = "<div class='btn-group'>";
            $html .= "<a href='{$url}' target='_blank' class='btn btn-primary btn-sm' title='{$url}'>查看 <i class='fa fa-external-link'></i></a>";
            $html .= "<button class='btn btn-secondary btn-sm' onclick='KKsonUtils.copyToClipboard(\"{$url}\")'><i class='fa fa-copy'></i></button>";
            $html .= "</div>";
            return $html;
        }
        return "";
    }
}