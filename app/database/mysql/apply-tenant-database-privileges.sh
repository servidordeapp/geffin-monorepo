#!/usr/bin/env bash
#
# Grants the sail MySQL user privileges on tenant databases (tenant_%)
# in an already-running Sail environment.
#
# The init script (grant-tenant-database-privileges.sh) only runs when the
# MySQL volume is first created. Use this script to apply the same grant
# to an existing volume without recreating it.
#
# Usage (from the app/ directory):
#   ./database/mysql/apply-tenant-database-privileges.sh

set -euo pipefail

APP_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)"
cd "$APP_DIR"

if [ ! -f .env ]; then
    echo "Error: .env not found in $APP_DIR" >&2
    exit 1
fi

DB_USERNAME="$(grep '^DB_USERNAME=' .env | cut -d= -f2-)"
DB_PASSWORD="$(grep '^DB_PASSWORD=' .env | cut -d= -f2-)"

vendor/bin/sail exec mysql mysql --user=root --password="$DB_PASSWORD" <<-EOSQL
    GRANT ALL PRIVILEGES ON \`tenant\_%\`.* TO '$DB_USERNAME'@'%';
    FLUSH PRIVILEGES;
    SHOW GRANTS FOR '$DB_USERNAME'@'%';
EOSQL

echo "Tenant database privileges granted to '$DB_USERNAME'."
