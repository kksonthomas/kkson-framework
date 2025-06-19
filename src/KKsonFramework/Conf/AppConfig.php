<?php

namespace KKsonFramework\Conf;

class AppConfig extends ConfigBase
{
    const APP_CONFIG_FILENAME = "app.config.ini";
    static protected $instances = [];

    final public static function get() : static
    {
        $calledClass = get_called_class();

        if (!isset(static::$instances[$calledClass]))
        {
            static::$instances[$calledClass] = new $calledClass();
        }

        return static::$instances[$calledClass];
    }

    public function __construct($configDir = "conf/")
    {   
        parent::__construct(static::APP_CONFIG_FILENAME, $configDir);
    }

    public function env() {
        return @($this->getConfigSection("app")["env"]) ?? "uat";
    }

    public function backend() {
        return @($this->getConfigSection("app")["backend"]);
    }

    public function frontend() {
        return @($this->getConfigSection("app")["frontend"]);
    }

    public function backendBaseUrl() {
        return @($this->getConfigSection("app")["backend_base_url"]);
    }

    public function frontendBaseUrl() {
        return @($this->getConfigSection("app")["frontend_base_url"]);
    }

    public function appName() {
        return @($this->getConfigSection("display")["app_name"]);
    }

    public function forceSsl() {
        return @($this->getConfigSection("app")["force_ssl"]) ? true : false;
    }
}