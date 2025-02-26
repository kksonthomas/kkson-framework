<?php

namespace KKsonFramework\CRUD\FieldType;


use KKsonFramework\CRUD\Exception\DirectoryPermissionException;
use KKsonFramework\Utils\UrlUtils;

class Image extends FileType
{
    public function getPreviewHTMLTemplate() {
        return '<a href="{fileURL}" class="d-flex justify-content-center border border-dark"><img src="{fileURL}" alt="" /></a>';
    }

    public function renderCell($value)
    {
        $imgURL = htmlspecialchars(UrlUtils::res($value));

        if ($value != null && $value != "") {
            return <<< HTML
<a target="_blank" href="$imgURL" class="d-block" style="width: 150px;"><img src="$imgURL" alt="" class="col-12 p-0"></a>
HTML;
        } else {
            return "";
        }


    }

}
