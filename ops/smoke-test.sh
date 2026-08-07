#!/usr/bin/env bash
#
# OP-06 (RC1 Operations Programme) — HTTP-layer smoke test.
#
# Checks the web server / routing / SSL layer specifically, deliberately
# separate from `php artisan slipguard:verify-deployment` (which checks the
# application/database layer). Run both after every deploy — if this one
# fails but the artisan command passes, the problem is Nginx/PHP-FPM/SSL,
# not the application; if it's the other way round, the reverse.
#
# Usage:
#   ops/smoke-test.sh https://slipguard.example.com
#   ops/smoke-test.sh http://127.0.0.1:8000   # local dev server

set -euo pipefail

BASE_URL="${1:?Usage: ops/smoke-test.sh https://your-domain}"
FAILURES=0

check() {
  local path="$1"
  local expected_status="$2"
  local url="${BASE_URL%/}${path}"

  local status
  status=$(curl -s -o /dev/null -w '%{http_code}' --max-time 10 "$url" || echo "000")

  if [ "$status" = "$expected_status" ]; then
    echo "  [OK] ${path} -> ${status}"
  else
    echo "  [FAIL] ${path} -> ${status} (expected ${expected_status})"
    FAILURES=$((FAILURES + 1))
  fi
}

echo "==> Smoke-testing ${BASE_URL}"
check "/up" 200
check "/" 200
check "/login" 200
check "/analyse" 200

if [ "$FAILURES" -gt 0 ]; then
  echo "==> ${FAILURES} check(s) failed."
  exit 1
fi

echo "==> All HTTP-layer checks passed."
