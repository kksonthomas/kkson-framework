# Database init

Import the framework greenfield schema from the package (do not maintain a second copy here):

```bash
# After composer install (consumer project)
mysql -u USER -p DATABASE < vendor/kksonthomas/kkson-framework/sql/0000_init_framework.sql

# Or from a clone of kkson-framework
mysql -u USER -p DATABASE < ../sql/0000_init_framework.sql
```

New databases only need that file. Existing databases that predate `system_log.client_ip` may instead need `vendor/kksonthomas/kkson-framework/sql/patch-v0.10.4.1-ip-ban.sql`.
