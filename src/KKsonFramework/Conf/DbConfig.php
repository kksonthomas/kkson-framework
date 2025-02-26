<?php

namespace KKsonFramework\Conf;

use Stringy\Stringy;

class DbConfig extends ConfigBase
{
    const CONFIG_FILENAME = "db.config.{env}.ini";
    static protected $instance;

    public static function get() : self {
        if(!static::$instance) {
            static::$instance = new static();
        }
        return static::$instance;
    }

    public static function set(self $instance) : void
    {
        static::$instance = $instance;
    }

    public function __construct($configDir = "conf/")
    {
        $filename = (new Stringy(static::CONFIG_FILENAME))->replace("{env}", AppConfig::get()->env());
        parent::__construct($filename, $configDir);
    }

    public function dbHost() {
        return @($this->getConfigSection("database")["host"]);
    }
    public function dbDatabase() {
        return @($this->getConfigSection("database")["database"]);
    }
    public function dbUsername() {
        return @($this->getConfigSection("database")["username"]);
    }
    public function dbPassword() {
        return @($this->getConfigSection("database")["password"]);
    }
}