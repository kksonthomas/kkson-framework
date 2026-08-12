# Getting started with KKson Framework

Build a new blank application using only this package (no other project trees required).

**Target version:** `kksonthomas/kkson-framework` **^0.11** (PHP **8.1+**, MariaDB / MySQL 5.7+, Apache with `mod_rewrite` or equivalent).

## Recommended path: copy the scaffold

The repo ships a ready consumer app under [`scaffold/`](../scaffold/). After installing the package, copy it into your new project root and configure it.

```bash
mkdir my-app && cd my-app
composer require kksonthomas/kkson-framework:^0.11

# Windows PowerShell
Copy-Item -Recurse vendor\kksonthomas\kkson-framework\scaffold\* .

# bash
# cp -a vendor/kksonthomas/kkson-framework/scaffold/. .

composer dump-autoload
```

If `composer.json` was overwritten by the scaffold copy, run `composer install` again so dependencies match the scaffold file (it requires `^0.11`).

Details: [`scaffold/README.md`](../scaffold/README.md).

## Configuration

Copy examples and edit:

```bash
copy conf\app.config.ini.example conf\app.config.ini
copy conf\db.config.uat.ini.example conf\db.config.uat.ini
```

| File | Role |
|------|------|
| `conf/app.config.ini` | `env` selects the DB file; backend/frontend on/off; base URLs; `app_name` |
| `conf/db.config.{env}.ini` | Database host, name, user, password |

`conf/` is denied from HTTP via `conf/.htaccess`. Real `*.ini` files are gitignored by the scaffold.

## Database init (greenfield)

Create an empty database, then:

```bash
mysql -u USER -p DATABASE < vendor/kksonthomas/kkson-framework/sql/0000_init_framework.sql
```

This creates framework tables (`user`, `permission`, `permission_user`, `session`, `system_log`, `ban_ip_list`, `record_history`, `user_token`), seeds core permissions, and creates **sysadmin** / **sysadmin** (`SYSADMIN`).

The init script already includes `system_log.client_ip` and the IP-ban indexes. **Do not** apply `sql/patch-v0.10.4.1-ip-ban.sql` on a database created from the init file.

### Existing databases only

If you are upgrading an older app that already has `system_log` / `ban_ip_list` but lacks `client_ip` or the related indexes, apply:

```bash
mysql -u USER -p DATABASE < vendor/kksonthomas/kkson-framework/sql/patch-v0.10.4.1-ip-ban.sql
```

Ignore duplicate column/index errors on re-run.

## Web server

Point the document root at the project directory (where `index.php`, `backend.php`, and `api.php` live). The scaffold `.htaccess` routes:

| URL prefix | Script |
|------------|--------|
| `/backend` | `backend.php` |
| `/api` | `api.php` |
| everything else | `index.php` |

Open `/backend` and sign in as **sysadmin** / **sysadmin**. Change the password immediately.

## What the scaffold includes

- **Backend:** AdminLTE login, home, profile, User CRUD, permissions, login-as hooks
- **Frontend:** Placeholder homepage (`index.php`)
- **API:** Empty `/api` Slim group (`api.php`) for JSON routes
- **Bootstrap pattern:** `App\BackendApp` / `App\FrontendApp`, `BackendAuth` / `FrontendAuth`, `AppBeanHelper`, transactional CRUD defaults

## Adding your first feature

1. Create a RedBean model in `src/App/Model/` (extend framework models or `BaseModelBase` as needed). Ensure the table exists (your own migration SQL under the app’s `sql/`).
2. Add a backend CRUD controller under `src/App/Controller/backend/` (extend `BackendCRUDControllerBase` or the framework CRUD controller base).
3. Register it in `backend.php` with `$crud->add("table_name", new YourController())`.
4. Add a menu item in `view/backend/backend_menu.php`.
5. For JSON APIs, add routes inside the `/api` group in `api.php`.

## Manual wiring (without copying scaffold)

If you already have an app layout:

1. `composer require kksonthomas/kkson-framework:^0.11`
2. Add `conf/app.config.ini` and `conf/db.config.{env}.ini` as above
3. Import [`sql/0000_init_framework.sql`](../sql/0000_init_framework.sql)
4. Bootstrap Slim entry points the same way as [`scaffold/backend.php`](../scaffold/backend.php) / [`scaffold/index.php`](../scaffold/index.php): `App::init` / subclass → `DB::init(AppBeanHelper)` → `Auth::setAuthLogic` → `checkIp` → routes → `PrettyExceptions` → `run()`

Use the scaffold files as the reference implementation.

## Related docs in README

- [Database transactions and Writer Cache (v0.11+)](../README.md#database-transactions-and-writer-cache-v011000)
- [IP ban performance (v0.10.4.1+)](../README.md#ip-ban-performance-v01041)
- [Soft delete (mimic delete)](../README.md#soft-delete-mimic-delete)
