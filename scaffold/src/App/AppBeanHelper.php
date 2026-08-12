<?php

namespace App;

use KKsonFramework\RedBeanPHP\BeanHelper;

class AppBeanHelper extends BeanHelper
{
    public function __construct()
    {
        parent::__construct();
        $prefix = __namespace__ . '\\Model\\';
        $dir = __DIR__ . "/Model";
        self::addModelsFromDirectory($dir, $prefix);
    }
}
