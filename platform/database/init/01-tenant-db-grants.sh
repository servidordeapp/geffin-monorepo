#!/bin/sh
# Grant the application DB user the privileges needed to create, migrate and
# drop per-tenant databases (stancl/tenancy, database-per-tenant strategy).
#
# The official MySQL image only grants the app user ALL PRIVILEGES on its own
# MYSQL_DATABASE. Tenant databases are named "tenant<uuid>" (config tenancy.php
# -> database.prefix), so the user also needs privileges on the `tenant%`
# pattern, which includes CREATE/DROP DATABASE for matching names.
#
# Runs once on a fresh data volume, after the entrypoint has created MYSQL_USER.
#
# Also provisions the test database (geffin_test, used by phpunit) and grants
# the app user privileges on it plus the `tenant%` databases that tests spin up.
set -e

mysql -uroot -p"${MYSQL_ROOT_PASSWORD}" <<SQL
CREATE DATABASE IF NOT EXISTS \`geffin_test\` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
GRANT ALL PRIVILEGES ON \`geffin_test\`.* TO '${MYSQL_USER}'@'%';
GRANT ALL PRIVILEGES ON \`tenant%\`.* TO '${MYSQL_USER}'@'%';
FLUSH PRIVILEGES;
SQL
