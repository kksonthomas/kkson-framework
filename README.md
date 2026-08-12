# KKson Framework

PHP admin/CRUD framework built on Slim 2, RedBeanPHP, Plates, and AdminLTE. Includes authentication, CRUD UI, search/export, permissions, and system logging.

## Installation

```bash
composer require kksonthomas/kkson-framework:^0.11
```

**New blank project:** copy the packaged scaffold, configure `conf/`, and import the greenfield SQL. Step-by-step: [docs/getting-started.md](docs/getting-started.md).

```bash
# After composer require in an empty project directory:
# Windows: Copy-Item -Recurse vendor\kksonthomas\kkson-framework\scaffold\* .
# bash:    cp -a vendor/kksonthomas/kkson-framework/scaffold/. .
mysql -u USER -p DATABASE < vendor/kksonthomas/kkson-framework/sql/0000_init_framework.sql
```

| Material | Path |
|----------|------|
| Getting started | [docs/getting-started.md](docs/getting-started.md) |
| Blank app scaffold | [scaffold/](scaffold/) |
| Greenfield DB init | [sql/0000_init_framework.sql](sql/0000_init_framework.sql) |

Add configuration under your application `conf/` directory (scaffold ships `.example` files):

- `app.config.ini` — environment and app settings (`env` selects the DB config file)
- `db.config.{env}.ini` — database connection

Default seed login after init SQL: `sysadmin` / `sysadmin` (change after first login). New databases use the init file only; existing DBs missing `system_log.client_ip` still use [sql/patch-v0.10.4.1-ip-ban.sql](sql/patch-v0.10.4.1-ip-ban.sql).

## Database transactions and Writer Cache (v0.11.0.0+)

Updating to v0.11.0.0+ enables transactional CRUD by default and adds `DB::begin()`, `DB::commit()`, `DB::rollback()`, and `DB::transaction()` with Writer Cache flush on rollback.

```bash
composer require kksonthomas/kkson-framework:^0.11.0.0
```

CRUD insert, update, and delete run inside a DB transaction **by default**. Opt out on a CRUD instance:

```php
$crud->setIsInsertUpdateUseTransaction(false);
```

Transactions require frozen RedBean (`DB::fixSchema()` / `R::freeze(true)` in normal app bootstrap).

### `DB` transaction API

Use `KKsonFramework\App\DB` for transaction control instead of importing `R` for `begin` / `commit` / `rollback`:

```php
use KKsonFramework\App\DB;

DB::begin();
try {
    // ...
    DB::commit();
} catch (\Throwable $e) {
    DB::rollback(); // rolls back + flushes RedBean Writer Cache
    throw $e;
}

// or
DB::transaction(function () {
    // ...
});
```

### Why rollback flushes cache

RedBean 5.7 Writer Cache (default ON) caches `R::find`, `R::findOne`, `R::load`, and related read queries. `R::store` and `R::exec` invalidate the cache on the next cached read; **`R::rollback()` does not**. After a rollback, cached reads can still return rows from the undone transaction.

`DB::rollback()` and a failed `DB::transaction()` call `R::getWriter()->flushCache()` as [RedBean recommends](https://www.redbeanphp.com/index.php?p=/database). Successful commits do not flush cache (committed data matches the cache). Direct SQL via `R::getCell` / `R::getAll` is not Writer-cached.

Custom code that still uses `R::begin()` / `R::rollback()` directly should switch to `DB::*` or call `R::getWriter()->flushCache()` after rollback.

## IP ban performance (v0.10.4.1+)

Updating the package improves IP ban behavior without any database change. Unauthenticated requests only check existing bans; failed-login counting runs after a failed login.

For faster queries on large `system_log` tables **on databases created before the greenfield init**, apply the optional SQL patch once:

```text
vendor/kksonthomas/kkson-framework/sql/patch-v0.10.4.1-ip-ban.sql
```

Example:

```bash
mysql -u USER -p DATABASE < vendor/kksonthomas/kkson-framework/sql/patch-v0.10.4.1-ip-ban.sql
```

The script adds `system_log.client_ip`, backfills simple JSON array rows, and creates indexes. Skip it if you do not need indexed IP lookups, or if the database was created with [sql/0000_init_framework.sql](sql/0000_init_framework.sql) (already includes `client_ip` and indexes). The app remains fully functional either way.

On re-run, ignore duplicate column or duplicate index errors. Requires `system_log` and `ban_ip_list` tables.

## Soft delete (mimic delete)

Models can soft-delete by setting `_deleted = 1` instead of removing rows. Enable on a `BaseModelBase` subclass:

```php
public static function _enabledMimicDelete() {
    return true;
}
```

The table must have an integer `_deleted` column (0 = active, 1 = deleted).

### Loading

- `Model::load($id)` — active rows only (`_deleted = 0`).
- `Model::load($id, true)` — includes soft-deleted rows.
- `Model::loadForUpdate($id)` — same filter as `load()`; use `loadForUpdate($id, true)` to lock a deleted row.
- `Model::isActiveInDb()` — re-reads `_deleted` from the database for long-running jobs.

### Saving and concurrency

`R::store()` runs the FUSE `update()` hook on boxed models. For mimic-delete tables, `BaseModelBase::update()` rejects a **stale revive**: the database row is soft-deleted (`_deleted = 1`) while the in-memory bean still has `_deleted = 0`. This prevents cross-request/cron races from undoing a delete after `deleteSelf()` or CRUD delete.

- Restore a row with `undeleteSelf()` or `save(allowReviveDeleted: true)`.
- Otherwise catch `KKsonFramework\RedBeanPHP\Exception\StaleDeletedModelException` and skip the write (typical for background jobs).

Subclasses that override `update()` **must** call `parent::update()` first so the guard runs.

### CRUD transactions

CRUD insert, update, and delete are transactional by default (see [Database transactions and Writer Cache (v0.11.0.0+)](#database-transactions-and-writer-cache-v011000)). This wraps **one HTTP request** only; it does not cover CLI, cron, or a second concurrent request. Long jobs should re-check `isActiveInDb()` before saving or handle `StaleDeletedModelException`.
