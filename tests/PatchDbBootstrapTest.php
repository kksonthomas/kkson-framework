<?php

use KKsonFramework\Database\PatchDbBootstrap;
use PHPUnit\Framework\TestCase;

class PatchDbBootstrapTest extends TestCase
{
    public function testFindAutoloadFromPackageBinDir(): void
    {
        $binDir = dirname(__DIR__) . "/bin";
        $autoload = PatchDbBootstrap::findAutoloadPath($binDir);
        $this->assertNotNull($autoload);
        $this->assertStringEndsWith("autoload.php", $autoload);
    }

    public function testProjectRootFromAutoload(): void
    {
        $autoload = realpath(dirname(__DIR__) . "/vendor/autoload.php");
        $this->assertNotFalse($autoload);
        $root = PatchDbBootstrap::projectRootFromAutoload($autoload);
        $this->assertSame(realpath(dirname(__DIR__)), $root);
    }

    public function testResolveConfPathRelative(): void
    {
        $root = sys_get_temp_dir();
        $confName = "kksonfw_conf_test_" . uniqid();
        $confFull = $root . DIRECTORY_SEPARATOR . $confName;
        mkdir($confFull);
        try {
            $path = PatchDbBootstrap::resolveConfPath($root, $confName);
            $this->assertTrue(is_dir($path));
            $this->assertStringContainsString($confName, $path);
        } finally {
            @rmdir($confFull);
        }
    }
}
