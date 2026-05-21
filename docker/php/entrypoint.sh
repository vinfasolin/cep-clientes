#!/usr/bin/env sh
set -e

cd /var/www/html

if [ ! -f .env ] && [ -f .env.example ]; then
  cp .env.example .env
fi

if [ ! -d vendor ]; then
  composer install --no-interaction --prefer-dist
fi

if [ -f artisan ]; then
  if ! grep -q '^APP_KEY=base64:' .env 2>/dev/null; then
    php artisan key:generate --force
  fi
fi

if [ "${DB_CONNECTION:-mysql}" = "mysql" ]; then
  echo "Aguardando MySQL em ${DB_HOST:-mysql}:${DB_PORT:-3306}..."

  until php -r '
    $host = getenv("DB_HOST") ?: "mysql";
    $port = getenv("DB_PORT") ?: "3306";
    $database = getenv("DB_DATABASE") ?: "cep_clientes";
    $username = getenv("DB_USERNAME") ?: "cep_user";
    $password = getenv("DB_PASSWORD") ?: "cep_password";

    try {
        new PDO(
            "mysql:host={$host};port={$port};dbname={$database};charset=utf8mb4",
            $username,
            $password,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_TIMEOUT => 3,
            ]
        );

        exit(0);
    } catch (Throwable $exception) {
        fwrite(STDERR, $exception->getMessage() . PHP_EOL);
        exit(1);
    }
  '; do
    sleep 2
  done

  echo "MySQL disponivel."
fi

if [ -f artisan ]; then
  php artisan migrate --force
fi

exec "$@"