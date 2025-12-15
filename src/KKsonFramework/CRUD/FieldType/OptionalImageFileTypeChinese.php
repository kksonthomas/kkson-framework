<?php

namespace KKsonFramework\CRUD\FieldType;


use KKsonFramework\Utils\UrlUtils;

class OptionalImageFileTypeChinese extends OptionalImageFileType
{

    public function __construct($uploadPath = "upload/", $width = "150px") {
        parent::__construct($uploadPath, $width, "查看", "移除", "選擇文件");
    }
}
