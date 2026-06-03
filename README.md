# KKson Framework

PHP admin/CRUD framework built on Slim 2, RedBeanPHP, Plates, and AdminLTE. Includes authentication, CRUD UI, search/export, permissions, and system logging.

## Installation

```bash
composer require kksonthomas/kkson-framework
```

Add configuration under your application `conf/` directory:

- `app.config.ini` — environment and app settings (`env` selects the DB config file)
- `db.config.{env}.ini` — database connection

## IP ban performance (v0.10.4.1+)

Updating the package improves IP ban behavior without any database change. Unauthenticated requests only check existing bans; failed-login counting runs after a failed login.

For faster queries on large `system_log` tables, apply the optional SQL patch once:

```text
vendor/kksonthomas/kkson-framework/sql/patch-v0.10.4.1-ip-ban.sql
```

Example:

```bash
mysql -u USER -p DATABASE < vendor/kksonthomas/kkson-framework/sql/patch-v0.10.4.1-ip-ban.sql
```

The script adds `system_log.client_ip`, backfills simple JSON array rows, and creates indexes. Skip it if you do not need indexed IP lookups; the app remains fully functional.

On re-run, ignore duplicate column or duplicate index errors. Requires `system_log` and `ban_ip_list` tables.
