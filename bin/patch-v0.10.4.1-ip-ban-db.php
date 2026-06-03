#!/usr/bin/env php
<?php

/**
 * v0.10.4.1 — optional IP ban performance DB patch (idempotent).
 *
 * Installed via Composer (run from any directory):
 *   vendor/bin/patch-v0.10.4.1-ip-ban-db
 *
 * If you see "Permission denied", use PHP explicitly:
 *   php vendor/kksonthomas/kkson-framework/bin/patch-v0.10.4.1-ip-ban-db.php
 *
 * Optional: custom conf directory relative to project root or absolute path:
 *   vendor/bin/patch-v0.10.4.1-ip-ban-db --conf-dir=conf
 */

use KKsonFramework\App\MySQLiHelper;
use KKsonFramework\Database\PatchDbBootstrap;
use KKsonFramework\Database\PatchV010041IpBanDb;

try {
    $projectRoot = PatchDbBootstrap::init(__DIR__, $argv ?? []);
} catch (Throwable $e) {
    fwrite(STDERR, $e->getMessage() . "\n");
    exit(1);
}

echo "KKson Framework DB patch " . PatchV010041IpBanDb::VERSION . " (IP ban performance)\n";
echo "Project root: " . $projectRoot . "\n";

try {
    $patch = new PatchV010041IpBanDb(new MySQLiHelper());
    $patch->run();
    echo "Done.\n";
    exit(0);
} catch (Throwable $e) {
    fwrite(STDERR, "Patch failed: " . $e->getMessage() . "\n");
    exit(1);
}
