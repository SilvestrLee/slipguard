#!/usr/bin/env bash
#
# OP-01 (RC1 Operations Programme) — production server provisioning.
#
# Prepared, execution-ready code — NOT executed against a real server in
# this pass, since no cloud/hosting account exists in this environment
# (docs/08-operations/RC1_OPERATIONS_PROGRAMME.md explains why). Written
# to be run once, by hand, against a fresh Ubuntu 22.04/24.04 LTS VPS.
#
# Sized to docs/08-operations/PRODUCTION_OPERATIONS_BLUEPRINT.md §O-01's
# guiding principle: a single, well-specified VPS — no containers, no
# orchestration, matching SlipGuard's actual 1-1,000 user scale.
#
# Usage (as root, on a fresh server):
#   sudo bash ops/provision-server.sh

set -euo pipefail

DEPLOY_USER="${DEPLOY_USER:-slipguard}"
APP_DIR="/var/www/slipguard"
PHP_VERSION="8.3"

echo "==> Updating package index"
apt-get update -y

echo "==> Installing Nginx, PHP ${PHP_VERSION}-FPM + required extensions, Certbot, ufw, git, unzip"
apt-get install -y \
  nginx \
  "php${PHP_VERSION}-fpm" "php${PHP_VERSION}-cli" "php${PHP_VERSION}-mysql" \
  "php${PHP_VERSION}-mbstring" "php${PHP_VERSION}-xml" "php${PHP_VERSION}-curl" \
  "php${PHP_VERSION}-zip" "php${PHP_VERSION}-bcmath" "php${PHP_VERSION}-intl" \
  "php${PHP_VERSION}-gd" \
  mysql-client \
  certbot python3-certbot-nginx \
  ufw git unzip curl gzip

echo "==> Installing Composer"
if ! command -v composer &>/dev/null; then
  curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
fi

echo "==> Installing Node.js (for the frontend asset build) via NodeSource"
if ! command -v node &>/dev/null; then
  curl -fsSL https://deb.nodesource.com/setup_20.x | bash -
  apt-get install -y nodejs
fi

echo "==> Creating least-privilege deploy user '${DEPLOY_USER}' (no login shell password, key-based SSH only)"
if ! id "$DEPLOY_USER" &>/dev/null; then
  useradd --create-home --shell /bin/bash "$DEPLOY_USER"
  usermod -aG www-data "$DEPLOY_USER"
fi

echo "==> Setting production PHP memory_limit (closes PO-MVP-005 finding R-10)"
PHP_INI="/etc/php/${PHP_VERSION}/fpm/php.ini"
if [ -f "$PHP_INI" ]; then
  sed -i 's/^memory_limit = .*/memory_limit = 512M/' "$PHP_INI"
fi

echo "==> Creating the atomic-release directory structure (matches ops/deploy.sh's expectations)"
mkdir -p "${APP_DIR}"/{releases,shared/storage,shared/storage/app/public,shared/storage/app/backups}
chown -R "${DEPLOY_USER}:www-data" "$APP_DIR"

echo "==> Configuring the firewall — SSH, HTTP, HTTPS only"
ufw allow OpenSSH
ufw allow 'Nginx Full'
ufw --force enable

echo "==> Reloading PHP-FPM to pick up the memory_limit change"
systemctl reload "php${PHP_VERSION}-fpm" || systemctl restart "php${PHP_VERSION}-fpm"

cat <<'EOF'

==> Base provisioning complete. Remaining manual steps (deliberately not
    automated — each needs a real decision this script cannot make safely):

  1. Point DNS for your domain at this server's IP.
  2. Copy ops/nginx-slipguard.conf into /etc/nginx/sites-available/, edit
     the server_name and paths, symlink into sites-enabled, `nginx -t`,
     then `systemctl reload nginx`.
  3. Run: certbot --nginx -d your-domain.example.com
     (issues and auto-configures a real Let's Encrypt certificate — this
     resolves the SSL-expiry monitoring concern by construction, per the
     blueprint's own §O-04).
  4. Add the deploy user's SSH public key to
     /home/slipguard/.ssh/authorized_keys, then disable SSH password auth
     in /etc/ssh/sshd_config (PasswordAuthentication no) and restart sshd.
  5. Run ops/deploy.sh for the first real deploy.
  6. Set a real MySQL database + application user (least-privilege, no
     root use by the app — mirrors this repository's own local practice,
     I-01.3) and populate the production .env from
     .env.production.example.

EOF
