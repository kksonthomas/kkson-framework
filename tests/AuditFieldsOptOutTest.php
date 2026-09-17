<?php

require_once __DIR__ . "/Fixtures/Model/AuditOffFact.php";
require_once __DIR__ . "/Fixtures/Model/AuditOnFact.php";

use KKsonFramework\CRUD\KKsonCRUD;
use KKsonFramework\RedBeanPHP\BeanHelper;
use KKsonFramework\Tests\Fixtures\Model\AuditOffFact;
use KKsonFramework\Tests\Fixtures\Model\AuditOnFact;
use PHPUnit\Framework\TestCase;
use RedBeanPHP\R;

class AuditFieldsOptOutTest extends TestCase
{
    private const AUDIT_COLUMNS = [
        "creation_date",
        "creation_user_id",
        "modified_date",
        "modified_user_id",
    ];

    public static function setUpBeforeClass(): void
    {
        new BeanHelper();
        BeanHelper::addModelsFromDirectory(
            __DIR__ . "/Fixtures/Model",
            "KKsonFramework\\Tests\\Fixtures\\Model\\"
        );

        if (!extension_loaded("pdo_sqlite")) {
            return;
        }

        R::setup("sqlite::memory:");
        R::ext("xdispense", function ($type) {
            return R::getRedBean()->dispense($type);
        });
        R::getRedBean()->setBeanHelper(new BeanHelper());
    }

    protected function setUp(): void
    {
        if (!extension_loaded("pdo_sqlite") || !R::testConnection()) {
            return;
        }

        R::freeze(false);
        R::exec("DROP TABLE IF EXISTS audit_off_fact");
        R::exec("DROP TABLE IF EXISTS audit_on_fact");
        R::getWriter()->flushCache();
    }

    protected function tearDown(): void
    {
        if (extension_loaded("pdo_sqlite") && R::testConnection()) {
            R::freeze(false);
        }
    }

    public static function tearDownAfterClass(): void
    {
        if (extension_loaded("pdo_sqlite") && R::testConnection()) {
            R::freeze(false);
            R::close();
            R::removeToolBoxByKey("default");
        }
    }

    public function testHelperFollowsModelFlagAndDefaultsTrueWhenUnmapped(): void
    {
        $mappedOff = $this->createStub(KKsonCRUD::class);
        $mappedOff->method("getTableName")->willReturn(AuditOffFact::_getTableName());
        $this->assertFalse(BeanHelper::isCurrentTableEnabledAuditFields($mappedOff));

        $mappedOn = $this->createStub(KKsonCRUD::class);
        $mappedOn->method("getTableName")->willReturn(AuditOnFact::_getTableName());
        $this->assertTrue(BeanHelper::isCurrentTableEnabledAuditFields($mappedOn));

        $unmapped = $this->createStub(KKsonCRUD::class);
        $unmapped->method("getTableName")->willReturn("no_such_table_xyz");
        $this->assertTrue(BeanHelper::isCurrentTableEnabledAuditFields($unmapped));
    }

    public function testFrozenStoreWithoutAuditColumnsOnInsertAndUpdate(): void
    {
        $this->requireSqlite();
        R::exec("CREATE TABLE audit_off_fact (id INTEGER PRIMARY KEY AUTOINCREMENT, name TEXT)");
        R::freeze(true);

        $model = AuditOffFact::dispenseModel();
        $model->name = "first";
        $id = $model->save();

        $this->assertGreaterThan(0, $id);
        $this->assertBeanHasNoAuditKeys($model->export());

        $loaded = AuditOffFact::load($id);
        $this->assertNotNull($loaded);
        $loaded->name = "second";
        $loaded->save();

        $this->assertSame("second", AuditOffFact::load($id)->name);
        $this->assertBeanHasNoAuditKeys($loaded->export());
        $this->assertArrayNotHasKey("_deleted", $loaded->export());
    }

    public function testUnfrozenStoreDoesNotRecreateAuditColumns(): void
    {
        $this->requireSqlite();
        R::exec("CREATE TABLE audit_off_fact (id INTEGER PRIMARY KEY AUTOINCREMENT, name TEXT)");
        R::freeze(false);

        $model = AuditOffFact::dispenseModel();
        $model->name = "fluid";
        $model->save();

        $columns = R::inspect(AuditOffFact::_getTableName());
        foreach (self::AUDIT_COLUMNS as $column) {
            $this->assertArrayNotHasKey($column, $columns);
        }
        $this->assertArrayNotHasKey("_deleted", $columns);
        $this->assertBeanHasNoAuditKeys($model->export());
    }

    public function testDefaultTrueStillWritesAuditDates(): void
    {
        $this->requireSqlite();
        R::exec(
            "CREATE TABLE audit_on_fact (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name TEXT,
                creation_date TEXT,
                creation_user_id INTEGER,
                modified_date TEXT,
                modified_user_id INTEGER
            )"
        );
        R::freeze(true);

        $model = AuditOnFact::dispenseModel();
        $model->name = "audited";
        $id = $model->save();

        $this->assertGreaterThan(0, $id);
        $this->assertNotEmpty($model->creation_date);
        $this->assertNotEmpty($model->modified_date);
        $this->assertNull($model->creation_user_id);
        $this->assertNull($model->modified_user_id);

        $creationDate = $model->creation_date;
        $loaded = AuditOnFact::load($id);
        $loaded->name = "audited-updated";
        $loaded->save();

        $this->assertSame($creationDate, $loaded->creation_date);
        $this->assertNotEmpty($loaded->modified_date);
        $this->assertSame("audited-updated", AuditOnFact::load($id)->name);
    }

    private function requireSqlite(): void
    {
        if (!extension_loaded("pdo_sqlite")) {
            $this->markTestSkipped("pdo_sqlite is required");
        }
    }

    private function assertBeanHasNoAuditKeys(array $export): void
    {
        foreach (self::AUDIT_COLUMNS as $column) {
            $this->assertArrayNotHasKey($column, $export);
        }
    }
}
