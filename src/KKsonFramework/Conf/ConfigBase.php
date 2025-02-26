<?php

namespace KKsonFramework\Conf;

use LogicException;

abstract class ConfigBase
{
    protected $config;

    public function __construct($configFilename, $configDir = "conf/")
    {
        $filename = $configDir . $configFilename;
        if(file_exists($filename)) {
            $this->config = parse_ini_file($filename, true);
        } else {
            throw new LogicException("$filename not found");
        }
    }

    public function getConfigSection($name) {
        return @($this->config[$name]);
    }
}