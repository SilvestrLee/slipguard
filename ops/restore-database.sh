#!/usr/bin/env bash
#
# OP-05 (RC1 Operations Programme) — restore-drill script.
#
# Restores a backup produced by `php artisan slipguard:backup-database`
# into a genuinely disposable database, never the real one, mirroring the
# method this repository already established and verified in
# docs/engineering/I-01.2-MYSQL-MIGRATION-SAFETY-VERIFICATION.md.
#
# Requires MySQL credentials with CREATE DATABASE / DROP DATABASE
# privilege (i.e. an admin/root-level account) — the application's own
# `slipguard` database user deliberately does NOT have this privilege
# (confirmed directly: `GRANT ALL PRIVILEGES ON slipguard.*` only, no
# global CREATE), so this script cannot be run with the app's normal
# credentials by design. Run it with a real admin account when verifying
# a real production backup.
#
# Usage:
#   ops/restore-database.sh /path/to/backup.sql.gz
#
# Environment (override as needed):
#   MYSQL_BINARY      default: mysql
#   MYSQL_HOST        default: 127.0.0.1
#   MYSQL_PORT        default: 3306
#   MYSQL_ADMIN_USER  default: root
#   RESTORE_DB_NAME   default: slipguard_restore_verify

set -euo pipefail

BACKUP_FILE="${1:?Usage: ops/restore-database.sh /path/to/backup.sql.gz}"
MYSQL_BINARY="${MYSQL_BINARY:-mysql}"
MYSQL_HOST="${MYSQL_HOST:-127.0.0.1}"
MYSQL_PORT="${MYSQL_PORT:-3306}"
MYSQL_ADMIN_USER="${MYSQL_ADMIN_USER:-root}"
RESTORE_DB_NAME="${RESTORE_DB_NAME:-slipguard_restore_verify}"

if [ ! -f "$BACKUP_FILE" ]; then
  echo "Backup file not found: $BACKUP_FILE" >&2
  exit 1
fi

echo "==> Creating disposable database '$RESTORE_DB_NAME' (dropped first if it already exists from a prior failed drill)"
"$MYSQL_BINARY" -h"$MYSQL_HOST" -P"$MYSQL_PORT" -u"$MYSQL_ADMIN_USER" -p \
  -e "DROP DATABASE IF EXISTS \`$RESTORE_DB_NAME\`; CREATE DATABASE \`$RESTORE_DB_NAME\`;"

echo "==> Restoring $BACKUP_FILE into $RESTORE_DB_NAME"
if [[ "$BACKUP_FILE" == *.gz ]]; then
  gunzip -c "$BACKUP_FILE" | "$MYSQL_BINARY" -h"$MYSQL_HOST" -P"$MYSQL_PORT" -u"$MYSQL_ADMIN_USER" -p "$RESTORE_DB_NAME"
else
  "$MYSQL_BINARY" -h"$MYSQL_HOST" -P"$MYSQL_PORT" -u"$MYSQL_ADMIN_USER" -p "$RESTORE_DB_NAME" < "$BACKUP_FILE"
fi

echo "==> Verifying: table count and a spot-check row count"
"$MYSQL_BINARY" -h"$MYSQL_HOST" -P"$MYSQL_PORT" -u"$MYSQL_ADMIN_USER" -p "$RESTORE_DB_NAME" -e "
  SELECT COUNT(*) AS table_count FROM information_schema.tables WHERE table_schema = '$RESTORE_DB_NAME';
  SELECT COUNT(*) AS users_rows FROM users;
"

echo "==> Dropping the disposable database"
"$MYSQL_BINARY" -h"$MYSQL_HOST" -P"$MYSQL_PORT" -u"$MYSQL_ADMIN_USER" -p \
  -e "DROP DATABASE \`$RESTORE_DB_NAME\`;"

echo "==> Restore drill complete. Record the result in docs/08-operations/INCIDENT_AND_LEARNING_LOG.md or the next Operations Readiness review."
