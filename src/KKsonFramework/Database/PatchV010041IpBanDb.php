<?php

namespace KKsonFramework\Database;

use KKsonFramework\App\MySQLiHelper;
use KKsonFramework\RedBeanPHP\Model\SystemLog;

/**
 * Idempotent DB patch for v0.10.4.1 IP ban performance (client_ip column + indexes).
 */
class PatchV010041IpBanDb
{
    public const VERSION = "v0.10.4.1";

    private const BATCH_SIZE = 500;

    private MySQLiHelper $helper;

    public function __construct(MySQLiHelper $helper)
    {
        $this->helper = $helper;
    }

    public function run(): void
    {
        $this->ensureTableExists("system_log");
        $this->ensureTableExists("ban_ip_list");

        $this->ensureClientIpColumn();
        $this->backfillClientIp();
        $this->ensureIndex(
            "system_log",
            "idx_system_log_type_client_ip_creation_date",
            ["type", "client_ip", "creation_date"]
        );
        $this->ensureIndex(
            "ban_ip_list",
            "idx_ban_ip_list_ip_unbanned_date",
            ["ip", "unbanned_date"]
        );
        $this->ensureIndex(
            "ban_ip_list",
            "idx_ban_ip_list_ip_auto_unban_creation",
            ["ip", "is_auto_unban", "creation_date"]
        );

        SystemLog::resetClientIpColumnCache();
    }

    private function ensureTableExists(string $table): void
    {
        $rows = $this->helper->query("SHOW TABLES LIKE ?", [$table]);
        if (!is_array($rows) || count($rows) === 0) {
            throw new \RuntimeException("Required table `$table` does not exist.");
        }
    }

    private function ensureClientIpColumn(): void
    {
        $rows = $this->helper->query(
            "SHOW COLUMNS FROM `system_log` LIKE 'client_ip'"
        );
        if (is_array($rows) && count($rows) > 0) {
            echo "Column system_log.client_ip already exists.\n";
            return;
        }

        $this->helper->exec(
            "ALTER TABLE `system_log` ADD COLUMN `client_ip` VARCHAR(45) NULL DEFAULT NULL AFTER `header_ip_data`"
        );
        echo "Added column system_log.client_ip.\n";
    }

    private function backfillClientIp(): void
    {
        $total = 0;
        while (true) {
            $limit = (int) self::BATCH_SIZE;
            $rows = $this->helper->query(
                "SELECT `id`, `header_ip_data` FROM `system_log` WHERE `client_ip` IS NULL AND `header_ip_data` IS NOT NULL AND `header_ip_data` != '' LIMIT {$limit}"
            );
            if (!is_array($rows) || count($rows) === 0) {
                break;
            }

            foreach ($rows as $row) {
                $id = $row[0] ?? ($row["id"] ?? null);
                $headerIpData = $row[1] ?? ($row["header_ip_data"] ?? null);
                if ($id === null) {
                    continue;
                }
                $clientIp = self::extractClientIpFromHeaderIpData($headerIpData);
                if ($clientIp === null) {
                    continue;
                }
                $this->helper->exec(
                    "UPDATE `system_log` SET `client_ip` = ? WHERE `id` = ? AND `client_ip` IS NULL",
                    [$clientIp, $id]
                );
                $total++;
            }

            if (count($rows) < self::BATCH_SIZE) {
                break;
            }
        }

        echo "Backfilled client_ip on {$total} system_log row(s).\n";
    }

    /**
     * @param string|array|null $headerIpData
     */
    public static function extractClientIpFromHeaderIpData($headerIpData): ?string
    {
        if ($headerIpData === null || $headerIpData === "") {
            return null;
        }
        if (is_array($headerIpData)) {
            $filtered = array_filter($headerIpData);
            $ip = $filtered ? reset($filtered) : null;
            return is_string($ip) && $ip !== "" ? $ip : null;
        }
        $decoded = json_decode((string) $headerIpData, true);
        if (!is_array($decoded)) {
            return null;
        }
        $filtered = array_filter($decoded);
        $ip = $filtered ? reset($filtered) : null;
        return is_string($ip) && $ip !== "" ? $ip : null;
    }

    /**
     * @param string[] $columns
     */
    private function ensureIndex(string $table, string $indexName, array $columns): void
    {
        if ($this->indexExists($table, $indexName)) {
            echo "Index {$table}.{$indexName} already exists.\n";
            return;
        }
        if ($this->hasEquivalentIndex($table, $columns)) {
            echo "Skipping {$indexName}; equivalent index already exists on {$table}.\n";
            return;
        }

        $columnList = implode(", ", array_map(function ($c) {
            return "`{$c}`";
        }, $columns));
        $this->helper->exec("CREATE INDEX `{$indexName}` ON `{$table}` ({$columnList})");
        echo "Created index {$table}.{$indexName}.\n";
    }

    private function indexExists(string $table, string $indexName): bool
    {
        $rows = $this->helper->query("SHOW INDEX FROM `{$table}` WHERE Key_name = ?", [$indexName]);
        return is_array($rows) && count($rows) > 0;
    }

    /**
     * @param string[] $columns
     */
    private function hasEquivalentIndex(string $table, array $columns): bool
    {
        $rows = $this->helper->query("SHOW INDEX FROM `{$table}`");
        if (!is_array($rows)) {
            return false;
        }

        $indexes = [];
        foreach ($rows as $row) {
            $keyName = $row["Key_name"] ?? ($row[2] ?? null);
            $columnName = $row["Column_name"] ?? ($row[4] ?? null);
            $seq = (int) ($row["Seq_in_index"] ?? ($row[3] ?? 0));
            if ($keyName === null || $columnName === null || $keyName === "PRIMARY") {
                continue;
            }
            if (!isset($indexes[$keyName])) {
                $indexes[$keyName] = [];
            }
            $indexes[$keyName][$seq] = $columnName;
        }

        foreach ($indexes as $colsBySeq) {
            ksort($colsBySeq);
            if (array_values($colsBySeq) === $columns) {
                return true;
            }
        }

        return false;
    }
}
