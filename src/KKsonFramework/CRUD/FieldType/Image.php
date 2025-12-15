<?php

namespace KKsonFramework\CRUD\FieldType;


use KKsonFramework\Utils\UrlUtils;

class Image extends FileType
{

    protected $width = 150;

    public function setWidth($width)
    {
        $this->width = $width;
    }

    public function __construct($uploadPath = "upload/", $width = "150px") {
        parent::__construct($uploadPath);
        $this->width = $width;
    }

    public function getPreviewHTMLTemplate($fileURL) {
        return '<a href="{fileURL}" class="d-flex justify-content-center border border-dark"><img src="{fileURL}" alt="" /></a>';
    }

    public function renderCell($value, $bean)
    {
        $imgURL = htmlspecialchars(UrlUtils::res($value));

        if ($value != null && $value != "") {
            return <<< HTML
<a target="_blank" href="$imgURL" class="d-block" style="width: {$this->width};"><img src="$imgURL" alt="" class="col-12 p-0"></a>
HTML;
        } else {
            return "";
        }


    }

}
