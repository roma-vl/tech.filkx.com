#!/bin/bash
set -euo pipefail

COLOR="${1:-}"
APP_DIR="/var/www/live.filkx.com"

TARGET_DIR="$APP_DIR/$COLOR/current"
TARGET_COMPOSE="$TARGET_DIR/docker-compose-production.yml"

LARAVEL_CLI="tech-api-php-cli"
LARAVEL_FPM="tech-api-php-fpm"
WORKDIR_IN_CONTAINER="/app"

# -----------------------------
# Валідація
# -----------------------------
if [[ "$COLOR" != "blue" && "$COLOR" != "green" ]]; then
  echo "❌ Usage: deploy.sh [blue|green]"
  exit 1
fi

if [[ ! -f "$TARGET_COMPOSE" ]]; then
  echo "❌ docker-compose не знайдено: $TARGET_COMPOSE"
  exit 1
fi

# Переходимо в директорію проекту, щоб відносні шляхи в docker-compose.yml резолвилися правильно
cd "$TARGET_DIR"

# Надаємо права на читання для папки frontend/dist, щоб Nginx всередині контейнера не повертав 403
chmod -R 755 frontend/dist || true

# -----------------------------
# Протилежне середовище
# -----------------------------
if [[ "$COLOR" == "blue" ]]; then
  OPPOSITE="green"
else
  OPPOSITE="blue"
fi

OPPOSITE_DIR="$APP_DIR/$OPPOSITE/current"
OPPOSITE_COMPOSE="$OPPOSITE_DIR/docker-compose-production.yml"


echo "🚀 Деплой у $COLOR середовище (live.filkx.com)"
echo "🔁 Opposite → $OPPOSITE"

# -----------------------------
# Стопаємо протилежне середовище (якщо є)
# -----------------------------
if [[ -f "$OPPOSITE_COMPOSE" ]]; then
  echo "🛑 Stopping $OPPOSITE"
#  docker-compose -f "$OPPOSITE_COMPOSE" down --remove-orphans || true
  docker-compose -f "$OPPOSITE_COMPOSE" down || true
else
  echo "ℹ️ $OPPOSITE ще не ініціалізоване — пропускаємо"
fi

# -----------------------------
# Піднімаємо target
# -----------------------------
echo "🟢 Starting $COLOR"
docker-compose -f "$TARGET_COMPOSE" up -d --build

# -----------------------------
# Atomic switch
# -----------------------------
ln -sfn "$TARGET_DIR" "$APP_DIR/current"

echo "⏳ Waiting for $LARAVEL_CLI container and /app..."
until docker-compose -f "$TARGET_COMPOSE" run --rm "$LARAVEL_CLI" test -d /app >/dev/null 2>&1; do
  sleep 1
done
echo "✅ $LARAVEL_CLI is ready"

# -----------------------------
# Laravel filesystem & version info
# -----------------------------
VERSION=$(git -C "$TARGET_DIR" describe --tags --always || echo "production")
COMMIT_SHA=$(git -C "$TARGET_DIR" rev-parse --short HEAD || echo "unknown")
TIMESTAMP=$(date +%s)

echo "ℹ️ Writing version.json → $VERSION / $COMMIT_SHA"

docker-compose -f "$TARGET_COMPOSE" run --rm -u root "$LARAVEL_CLI" sh -c "
mkdir -p /app/storage/logs /app/storage/framework/cache /app/storage/framework/sessions /app/storage/framework/views /app/bootstrap/cache /app/storage/app/public/var
cat <<EOF > /app/storage/app/public/var/version.json
{
  \"version\": \"$VERSION\",
  \"commit\": \"$COMMIT_SHA\",
  \"timestamp\": $TIMESTAMP
}
EOF
chmod -R 775 /app/storage/app /app/storage/framework /app/storage/logs /app/storage/media-library /app/bootstrap/cache
chown -R 1000:1000 /app/storage /app/bootstrap/cache
chown 1000:1000 /app/storage/oauth-*.key 2>/dev/null || true
"


# -----------------------------
# Artisan
# -----------------------------
for cmd in \
  "migrate --force" \
  "config:clear" \
  "config:cache" \
  "route:cache" \
  "storage:link"
do
  docker-compose -f "$TARGET_COMPOSE" run --rm -w "$WORKDIR_IN_CONTAINER" "$LARAVEL_CLI" php artisan $cmd
done

echo "🎉 Deploy complete → active = $COLOR"
