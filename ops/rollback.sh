#!/usr/bin/env bash
#
# OP-02 (RC1 Operations Programme) — rollback.
#
# Prepared, execution-ready code — not run against a real server in this
# pass (none exists). Points `current` back at the previous release
# directory. Relies on releases being named by timestamp
# (ops/deploy.sh's own convention), so "previous" is simply the
# second-most-recent directory under releases/.

set -euo pipefail

APP_DIR="/var/www/slipguard"
RELEASES_DIR="${APP_DIR}/releases"

PREVIOUS_RELEASE="$(ls -1t "$RELEASES_DIR" | sed -n '2p')"

if [ -z "$PREVIOUS_RELEASE" ]; then
  echo "No previous release found under ${RELEASES_DIR} — nothing to roll back to." >&2
  exit 1
fi

echo "==> Rolling back to release ${PREVIOUS_RELEASE}"
ln -sfn "${RELEASES_DIR}/${PREVIOUS_RELEASE}" "${APP_DIR}/current.tmp"
mv -Tf "${APP_DIR}/current.tmp" "${APP_DIR}/current"

sudo systemctl reload php8.3-fpm
php artisan queue:restart

echo "==> Rolled back to ${PREVIOUS_RELEASE}. Record this in docs/08-operations/INCIDENT_AND_LEARNING_LOG.md."
