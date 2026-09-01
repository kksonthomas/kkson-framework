<?php

use KKsonFramework\RedBeanPHP\Model\SystemLog;
use PHPUnit\Framework\TestCase;

class SystemLogGetClientIpTest extends TestCase
{
    private $savedServer;

    protected function setUp(): void
    {
        $this->savedServer = $_SERVER;
        $this->clearIpHeaders();
    }

    protected function tearDown(): void
    {
        $_SERVER = $this->savedServer;
    }

    public function testIpv6XForwardedForChainReturnsFirstAddress(): void
    {
        $_SERVER["HTTP_X_FORWARDED_FOR"] = "2a06:98c0:3600::103, 2a06:98c0:3600::103";
        $this->assertSame("2a06:98c0:3600::103", SystemLog::getClientIp());
    }

    public function testIpv4XForwardedForReturnsFirstAddress(): void
    {
        $_SERVER["HTTP_X_FORWARDED_FOR"] = "203.0.113.10, 198.51.100.2";
        $this->assertSame("203.0.113.10", SystemLog::getClientIp());
    }

    public function testSkipsInvalidTokenThenReturnsNextValidIp(): void
    {
        $_SERVER["HTTP_X_FORWARDED_FOR"] = "unknown, 192.0.2.1";
        $this->assertSame("192.0.2.1", SystemLog::getClientIp());
    }

    public function testFallsThroughToRemoteAddr(): void
    {
        $_SERVER["HTTP_X_FORWARDED_FOR"] = "not-an-ip";
        $_SERVER["REMOTE_ADDR"] = "198.51.100.20";
        $this->assertSame("198.51.100.20", SystemLog::getClientIp());
    }

    public function testReturnsNullWhenNoValidIp(): void
    {
        $_SERVER["HTTP_X_FORWARDED_FOR"] = "unknown, not-an-ip";
        $_SERVER["REMOTE_ADDR"] = "garbage";
        $this->assertNull(SystemLog::getClientIp());
    }

    private function clearIpHeaders(): void
    {
        foreach ([
            "HTTP_CLIENT_IP",
            "HTTP_X_FORWARDED_FOR",
            "HTTP_X_FORWARDED",
            "HTTP_X_CLUSTER_CLIENT_IP",
            "HTTP_FORWARDED_FOR",
            "HTTP_FORWARDED",
            "REMOTE_ADDR",
            "HTTP_VIA",
        ] as $key) {
            unset($_SERVER[$key]);
        }
    }
}
