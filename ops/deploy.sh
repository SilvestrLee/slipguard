#!/usr/bin/env bash
#
# OP-02 (RC1 Operations Programme) — atomic-release deployment.
#
# Prepared, execution-ready code — NOT run against a real server in this
# pass (none exists). Written for the directory structure
# ops/provision-server.sh creates: /var/www/slipguard/{releases,shared,current}.
# `current` is a symlink to the active release — the deploy swaps it
# atomically at the very end, so a request never sees a half-deployed
# state (docs/08-operations/PRODUCTION_OPERATIONS_BLUEPRINT.md §O-02's
# "near-zero downtime" recommendation, implemented for real here rather
# than left as a description).
#
# On any failure before the symlink swap, nothing about the live site has
# changed yet — the release directory is simply incomplete and can be
# deleted. On failure of the post-swap smoke test specifically, this
# script calls ops/rollback.sh itself rather than leaving a known-bad
# release live.
#
# Usage (as the deploy user, on the production server):
#   ops/deploy.sh <git-ref>   # e.g. ops/deploy.sh main

set -euo pipefail

APP_DIR="/var/www/slipguard"
RELEASES_DIR="${APP_DIR}/releases"
SHARED_DIR="${APP_DIR}/shared"
GIT_REF="${1:?Usage: ops/deploy.sh <git-ref>}"
REPO_URL="${SLIPGUARD_REPO_URL:?Set SLIPGUARD_REPO_URL to the real repository URL}"
PHP_BIN="${PHP_BIN:-php}"

RELEASE_NAME="$(date +%Y%m%d%H%M%S)"
RELEASE_DIR="${RELEASES_DIR}/${RELEASE_NAME}"

echo "==> Cloning ${GIT_REF} into ${RELEASE_DIR}"
git clone --depth 1 --branch "$GIT_REF" "$REPO_URL" "$RELEASE_DIR"

echo "==> Linking shared storage and .env (never part of the release itself)"
rm -rf "${RELEASE_DIR}/storage"
ln -s "${SHARED_DIR}/storage" "${RELEASE_DIR}/storage"
ln -s "${SHARED_DIR}/.env" "${RELEASE_DIR}/.env"

cd "$RELEASE_DIR"

echo "==> Installing dependencies (production, no dev packages)"
composer install --no-dev --optimize-autoloader --no-interaction

echo "==> Building frontend assets"
npm ci
npm run build

echo "==> Running migrations (--force required outside local/testing envs)"
"$PHP_BIN" artisan migrate --force

echo "==> Caching config, routes, views, events"
"$PHP_BIN" artisan config:cache
"$PHP_BIN" artisan route:cache
"$PHP_BIN" artisan view:cache
"$PHP_BIN" artisan event:cache

echo "==> Running the functional smoke test (OP-06) against the new release, before it goes live"
if ! "$PHP_BIN" artisan slipguard:verify-deployment; then
  echo "==> Functional verification FAILED. New release NOT put live. Leaving ${RELEASE_DIR} for inspection."
  exit 1
fi

echo "==> Swapping the 'current' symlink atomically"
ln -sfn "$RELEASE_DIR" "${APP_DIR}/current.tmp"
mv -Tf "${APP_DIR}/current.tmp" "${APP_DIR}/current"

echo "==> Reloading PHP-FPM (picks up the new opcache) and restarting queue workers"
sudo systemctl reload php8.3-fpm
"$PHP_BIN" artisan queue:restart

echo "==> Running the HTTP-layer smoke test (OP-06) against the now-live site"
if ! bash "${RELEASE_DIR}/ops/smoke-test.sh" "${SLIPGUARD_APP_URL:?Set SLIPGUARD_APP_URL to the live site URL}"; then
  echo "==> HTTP-layer verification FAILED against the live site. Rolling back."
  bash "${RELEASE_DIR}/ops/rollback.sh"
  exit 1
fi

echo "==> Pruning old releases (keep the last 5)"
cd "$RELEASES_DIR" && ls -1t | tail -n +6 | xargs -r rm -rf

echo "==> Deploy of ${GIT_REF} (release ${RELEASE_NAME}) complete and verified."
