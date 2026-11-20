#!/usr/bin/env bash
# Termina a atualização depois que a Hostinger traz código novo do GitHub.
#
# A integração Git da Hostinger só faz o "git pull". Rode este script no
# terminal (SSH), na pasta do projeto, quando a atualização precisar de:
#   1. composer install (só roda se o composer.lock mudou)
#   2. tabelas novas (migrate)
#   3. caches de configuração, rotas e telas refeitos
#
# Uso:  bash deploy/pos-deploy.sh --forcar
#       (sem --forcar, só age se o commit mudou desde a última vez)
# Variáveis opcionais: PHP_BIN (ex.: /opt/alt/php83/usr/bin/php), COMPOSER_BIN
set -euo pipefail
cd "$(dirname "$0")/.."

PHP="${PHP_BIN:-php}"
COMPOSER="${COMPOSER_BIN:-composer}"
export COMPOSER_HOME="${COMPOSER_HOME:-${HOME:-/tmp}/.composer}"
ESTADO=storage/app/deploy
mkdir -p "$ESTADO"

# evita duas execuções ao mesmo tempo
if command -v flock >/dev/null 2>&1; then
    exec 9>"$ESTADO/trava"
    flock -n 9 || exit 0
fi

atual=$(git rev-parse HEAD 2>/dev/null || true)
anterior=$(cat "$ESTADO/commit" 2>/dev/null || true)

if [ -z "$atual" ]; then
    echo "[$(date '+%F %T')] Não achei o repositório Git nesta pasta." >&2
    exit 1
fi

if [ "$atual" = "$anterior" ] && [ "${1:-}" != "--forcar" ]; then
    exit 0
fi

echo "[$(date '+%F %T')] Atualizando: ${anterior:0:7} -> ${atual:0:7}"

versao=$("$PHP" -r 'echo PHP_VERSION_ID;')
if [ "$versao" -lt 80300 ]; then
    echo "O PHP do terminal é $("$PHP" -r 'echo PHP_VERSION;'), precisa ser 8.3+. Defina PHP_BIN no Cron." >&2
    exit 1
fi

lock=$(sha1sum composer.lock | cut -d' ' -f1)
if [ ! -f vendor/autoload.php ] || [ "$lock" != "$(cat "$ESTADO/composer-lock" 2>/dev/null || true)" ]; then
    echo "composer.lock mudou: instalando dependências"
    "$COMPOSER" install --no-dev --optimize-autoloader --no-interaction
    echo "$lock" > "$ESTADO/composer-lock"
fi

"$PHP" artisan migrate --force
"$PHP" artisan optimize
"$PHP" artisan filament:optimize

echo "$atual" > "$ESTADO/commit"
echo "[$(date '+%F %T')] Pronto."
