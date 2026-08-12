# Blank KKson app scaffold

Minimal consumer project for [kksonthomas/kkson-framework](https://github.com/kksonthomas/kkson-framework) **0.11+**: backend admin (User CRUD), blank frontend, empty `/api` group.

## Use this folder

Copy the contents of this directory into a **new project root** (not inside `vendor/` permanently).

### From a Composer install of the framework

```bash
mkdir my-app && cd my-app
composer require kksonthomas/kkson-framework:^0.11
# Windows PowerShell
Copy-Item -Recurse vendor\kksonthomas\kkson-framework\scaffold\* .
# or bash
# cp -a vendor/kksonthomas/kkson-framework/scaffold/. .
composer install
```

### From a clone of kkson-framework

```bash
mkdir my-app && cd my-app
cp -a /path/to/kkson-framework/scaffold/. .
# Point composer at a path repo while developing the framework, or require the published package:
composer install
```

If you copied after `composer require`, you already have `vendor/`; keep the scaffold `composer.json` and run `composer update` so the app autoload (`App\`) is registered.

## Configure

```bash
copy conf\app.config.ini.example conf\app.config.ini
copy conf\db.config.uat.ini.example conf\db.config.uat.ini
```

Edit hostnames, `app_name`, and database credentials.

## Database

```bash
mysql -u USER -p DATABASE < vendor/kksonthomas/kkson-framework/sql/0000_init_framework.sql
```

See [sql/README.md](sql/README.md).

## Run

Point the Apache (or equivalent) document root at this project. Routes:

| Path | Entry |
|------|--------|
| `/` | `index.php` (blank frontend) |
| `/api` | `api.php` (empty API group) |
| `/backend` | `backend.php` (admin portal) |

Default login after SQL seed: **sysadmin** / **sysadmin** — change the password after first login.

## Next steps

1. Add RedBean models under `src/App/Model/`.
2. Add CRUD controllers under `src/App/Controller/backend/` and register them in `backend.php`.
3. Add frontend or API routes in `index.php` / `api.php`.

Full walkthrough: [../docs/getting-started.md](../docs/getting-started.md).
