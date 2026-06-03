<?php

use KKsonFramework\Database\PatchV010041IpBanDb;
use PHPUnit\Framework\TestCase;

class IpBanPerformanceTest extends TestCase
{
    public function testExtractClientIpFromJsonArray(): void
    {
        $json = json_encode(["203.0.113.1", null, "198.51.100.2"]);
        $this->assertSame("203.0.113.1", PatchV010041IpBanDb::extractClientIpFromHeaderIpData($json));
    }

    public function testExtractClientIpFromPhpArray(): void
    {
        $this->assertSame("10.0.0.1", PatchV010041IpBanDb::extractClientIpFromHeaderIpData(["10.0.0.1", "10.0.0.2"]));
    }

    public function testExtractClientIpReturnsNullForEmpty(): void
    {
        $this->assertNull(PatchV010041IpBanDb::extractClientIpFromHeaderIpData(null));
        $this->assertNull(PatchV010041IpBanDb::extractClientIpFromHeaderIpData(""));
        $this->assertNull(PatchV010041IpBanDb::extractClientIpFromHeaderIpData("not-json"));
    }
}
