# KKson Framework

PHP admin/CRUD framework built on Slim 2, RedBeanPHP, Plates, and AdminLTE. Includes authentication, CRUD UI, search/export, permissions, and system logging.

## Installation

```bash
composer require kksonthomas/kkson-framework
```

Add configuration under your application `conf/` directory:

- `app.config.ini` — environment and app settings (`env` selects the DB config file)
- `db.config.{env}.ini` — database connection

## IP ban database patch (v0.10.4.1+)

From v0.10.4.1, IP ban checks are faster in code alone. An optional database patch adds `system_log.client_ip`, backfills existing rows, and creates indexes for large `system_log` tables.

The application runs correctly **without** the patch. Apply it when you want indexed IP lookups on login logs.

**Run** (in your app project, where `vendor/` and `conf/` live):

```bash
vendor/bin/patch-v0.10.4.1-ip-ban-db
```

**If you get Permission denied:**

```bash
php vendor/kksonthomas/kkson-framework/bin/patch-v0.10.4.1-ip-ban-db.php
```

**Custom config path:**

```bash
vendor/bin/patch-v0.10.4.1-ip-ban-db --conf-dir=conf
```

The script finds the project root via `vendor/autoload.php`, loads `conf/app.config.ini` and `conf/db.config.{env}.ini`, and is safe to run repeatedly.

**Requires:** MySQL/MariaDB, `system_log` and `ban_ip_list` tables, `ext-mysqli`.

**When hacking this repo directly:**

```bash
composer patch-ip-ban-db
```
