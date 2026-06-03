# KKson Framework

PHP admin/CRUD framework for internal web applications. It builds on **Slim 2**, **RedBeanPHP**, **Plates**, and **AdminLTE**, and provides authentication, CRUD screens, search/export, permissions, and system logging.

Install via Composer:

```bash
composer require kksonthomas/kkson-framework
```

Configure database and app settings under your project `conf/` directory (`db.config.{env}.ini`, `app.config.ini`).

---

## v0.10.4.1 — IP ban performance update

This release improves IP ban performance while staying backward-compatible.

### What changed

- **Request path:** Unauthenticated requests only check whether the IP is already banned (`ban_ip_list`). Failed-login counting and auto-ban no longer run on every anonymous page load.
- **After failed login:** Auto-ban evaluation runs when a login attempt fails (same threshold: 8 failures).
- **Optional `client_ip` column:** New logs can store a normalized client IP when the column exists. Queries prefer `client_ip` and fall back to `header_ip_data LIKE` for older rows.

### Running without the DB patch

You can update the Composer package and run the application **without** applying the database patch. Behavior remains correct; the largest gain (no per-request failed-login scans) applies immediately. Indexed `client_ip` queries require the patch below.

### Recommended DB patch (optional, idempotent)

From your **application project root** (where `conf/` lives), after `composer update`:

```bash
vendor/bin/patch-v0.10.4.1-ip-ban-db
```

Or from this package repo during development:

```bash
composer patch-ip-ban-db
```

The script:

1. Adds nullable `system_log.client_ip` if missing  
2. Backfills `client_ip` from existing `header_ip_data` JSON (batched)  
3. Creates indexes if missing:
   - `system_log(type, client_ip, creation_date)`
   - `ban_ip_list(ip, unbanned_date)`
   - `ban_ip_list(ip, is_auto_unban, creation_date)`

Safe to run multiple times.

### Requirements for the patch script

- MySQL/MariaDB with `system_log` and `ban_ip_list` tables  
- Valid `conf/db.config.{env}.ini` (env from `app.config.ini`)  
- PHP `ext-mysqli`

If the patch cannot connect or required tables are missing, it exits with an error and does not change runtime behavior of the framework code.
