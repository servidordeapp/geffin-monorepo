#!/usr/bin/env bash

if [ -n "$MYSQL_USER" ]; then
mysql --user=root --password="$MYSQL_ROOT_PASSWORD" <<-EOSQL
    GRANT ALL PRIVILEGES ON \`tenant\_%\`.* TO '$MYSQL_USER'@'%';
EOSQL
fi
