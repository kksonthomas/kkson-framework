<?php

namespace KKsonFramework\CRUD\FieldType;


use KKsonFramework\Utils\UrlUtils;

class OptionalImageFileType extends FileType
{

    protected $width = 150;

    public function setWidth($width)
    {
        $this->width = $width;
    }

    public function __construct($uploadPath = "upload/", $width = "150px", $viewButtonText = "Open", $removeButtonText = "Remove", $chooseFileText = "Choose File") {
        parent::__construct($uploadPath, $viewButtonText, $removeButtonText, $chooseFileText);
        $this->width = $width;
    }

    public function getPreviewHTMLTemplate($fileURL) {
        $fullURL = UrlUtils::fullRes($fileURL);
        if($this->isImage($fullURL)) {
            return '<a href="{fileURL}" class="d-flex justify-content-center border border-dark"><img src="{fileURL}" alt="" /></a>';
        } else {
            return '<a href="{fileURL}" target="_blank" class="btn btn-primary">' . $this->viewButtonText . '</a>';
        }
    }

    protected function isImage($fileURL) {
        $pathInfo = pathinfo($fileURL);
        $ext = $pathInfo["extension"];
        return $ext == "jpg" || $ext == "jpeg" || $ext == "png" || $ext == "gif";
    }

    public function renderCell($value, $bean)
    {
        $imgURL = htmlspecialchars(UrlUtils::res($value));

        if ($value != null && $value != "") {
            if($this->isImage($value)) {    
                return <<< HTML
<a target="_blank" href="$imgURL" class="d-block" style="width: {$this->width};"><img src="$imgURL" alt="" class="col-12 p-0"></a>
HTML;
            } else {
                return <<< HTML
<a href="$imgURL" target="_blank" class="btn btn-primary btn-sm">$this->viewButtonText</a>
HTML;
            }
        } else {
            return "";
        }


    }

}
