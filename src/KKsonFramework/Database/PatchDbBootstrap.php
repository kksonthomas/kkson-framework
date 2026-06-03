<?php

namespace KKsonFramework\Database;

use KKsonFramework\Conf\AppConfig;
use KKsonFramework\Conf\DbConfig;
use LogicException;

/**
 * Bootstrap for CLI DB patches when kkson-framework is installed via Composer.
 */
class PatchDbBootstrap
{
    /**
     * Locate vendor/autoload.php from this package's bin directory.
     */
    public static function findAutoloadPath(string $binDir): ?string
    {
        $candidates = [
            realpath($binDir . "/../../autoload.php"),
            realpath($binDir . "/../vendor/autoload.php"),
        ];
        foreach ($candidates as $path) {
            if ($path && is_file($path)) {
                return $path;
            }
        }
        return null;
    }

    /**
     * Application project root = parent of vendor/ (Composer install layout).
     */
    public static function projectRootFromAutoload(string $autoloadPath): string
    {
        $vendorDir = dirname(realpath($autoloadPath));
        return dirname($vendorDir);
    }

    /**
     * @param string[] $argv
     */
    public static function parseConfDirArg(array $argv): string
    {
        foreach (array_slice($argv, 1) as $arg) {
            if (strpos($arg, "--conf-dir=") === 0) {
                return substr($arg, 11);
            }
        }
        return "conf";
    }

    /**
     * @param string[] $argv
     * @return string Project root directory (after chdir)
     */
    public static function init(string $binDir, array $argv): string
    {
        $autoload = self::findAutoloadPath($binDir);
        if (!$autoload) {
            throw new LogicException(
                "Composer autoload not found. Install kkson-framework with Composer and run: vendor/bin/patch-v0.10.4.1-ip-ban-db or php vendor/kksonthomas/kkson-framework/bin/patch-v0.10.4.1-ip-ban-db.php"
            );
        }

        require_once $autoload;

        $projectRoot = self::projectRootFromAutoload($autoload);
        if (!is_dir($projectRoot)) {
            throw new LogicException("Invalid project root: {$projectRoot}");
        }

        chdir($projectRoot);

        $confDir = self::parseConfDirArg($argv);
        $confPath = self::resolveConfPath($projectRoot, $confDir);

        if (!file_exists($confPath . "app.config.ini")) {
            throw new LogicException(
                "Missing app.config.ini in {$confPath}. Expected your application's conf/ next to vendor/."
            );
        }

        AppConfig::set(new AppConfig($confPath));
        $env = AppConfig::get()->env();
        $dbConfigFiles = glob($confPath . "db.config.*.ini");
        if (!$dbConfigFiles) {
            throw new LogicException(
                "No db.config.{env}.ini found in {$confPath} (env={$env})."
            );
        }

        DbConfig::set(new DbConfig($confPath));

        return $projectRoot;
    }

    public static function resolveConfPath(string $projectRoot, string $confDir): string
    {
        if (preg_match('#^([a-zA-Z]:)?[/\\\\]#', $confDir)) {
            $path = rtrim($confDir, "/\\") . "/";
        } else {
            $path = rtrim($projectRoot, "/\\") . "/" . trim($confDir, "/\\") . "/";
        }

        if (!is_dir($path)) {
            throw new LogicException("Config directory not found: {$path}");
        }

        return $path;
    }
}
