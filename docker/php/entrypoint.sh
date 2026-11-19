#!/usr/bin/env bash
# Prepara o projeto na primeira subida: dependências, .env, banco e link do storage.
set -e
cd /var/www/html

como_www() { su -s /bin/bash www-data -c "$*"; }

if [ ! -f .env ]; then
    echo "» Criando .env a partir de .env.docker"
    cp .env.docker .env
    chown www-data:www-data .env
fi

if [ ! -f vendor/autoload.php ] || [ composer.json -nt vendor/autoload.php ]; then
    echo "» Instalando dependências do Composer (na primeira vez demora um pouco)"
    como_www "COMPOSER_HOME=/tmp/composer composer install --no-interaction --prefer-dist"
fi

if ! grep -q '^APP_KEY=base64:' .env; then
    como_www "php artisan key:generate --force"
fi

echo "» Esperando o MySQL"
for i in $(seq 1 60); do
    if php -r 'try { new PDO("mysql:host=".getenv("DB_HOST").";port=3306", getenv("DB_USERNAME"), getenv("DB_PASSWORD")); exit(0); } catch (Throwable $e) { exit(1); }'; then
        break
    fi
    sleep 2
done

como_www "php artisan migrate --force"

# Categorias e a 16ª edição. O seeder não duplica nada, então roda em toda subida.
como_www "php artisan db:seed --force"

[ -L public/storage ] || como_www "php artisan storage:link"
mkdir -p storage/framework/{cache,sessions,views} storage/logs bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache

echo "» Pronto: loja em ${APP_URL:-http://localhost:8080} · painel em ${APP_URL:-http://localhost:8080}/painel"
exec "$@"
