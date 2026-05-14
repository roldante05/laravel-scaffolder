#!/bin/bash
set -e

# Nos aseguramos de estar en la raíz del proyecto (un nivel arriba de scripts/)
cd "$(dirname "$0")/.."

GREEN='\033[0;32m'
BLUE='\033[0;34m'
RED='\033[0;31m'
NC='\033[0m'

echo -e "${BLUE}"
echo -e "    _____            ________      __    ___"
echo -e "   / ___/_________ _/ __/ __/___  / /___/ (_)___  ____ _"
echo -e "   \__ \/ ___/ __ \`/ /_/ /_/ __ \/ / __  / / __ \/ __ \`/"
echo -e "  ___/ / /__/ /_/ / __/ __/ /_/ / / /_/ / / / / / /_/ /"
echo -e " /____/\___/\__,_/_/ /_/  \____/_/\__,_/_/_/ /_/\__, /"
echo -e "                                                /____/  "
echo -e "${NC}"
echo -e "${BLUE}  Laravel Project Setup${NC}"
echo -e "${BLUE}  ────────────────────────────────────${NC}"
echo ""

# Helper para mostrar pasos
run_step() {
    local message=$1
    shift
    echo -ne "  ⏳ $message... "
    local LOG_FILE
    LOG_FILE=$(mktemp)

    if "$@" > "$LOG_FILE" 2>&1; then
        echo -e "${GREEN}✔${NC}"
        rm -f "$LOG_FILE"
    else
        echo -e "${RED}✘ ERROR${NC}"
        echo ""
        echo -e "${RED}  ── Últimas líneas del error ──${NC}"
        tail -n 15 "$LOG_FILE"
        echo -e "${RED}  ────────────────────────────${NC}"
        rm -f "$LOG_FILE"
        exit 1
    fi
}

# ── Verificaciones ───────────────────────────────────────────
if ! command -v docker &> /dev/null; then
    echo -e "  ${RED}✘ Docker no encontrado. Instalalo desde https://docs.docker.com/get-docker/${NC}"
    exit 1
fi

# ── Instalación ──────────────────────────────────────────────

# 1. Dependencias PHP (usa Docker para no requerir PHP/Composer local)
run_step "Instalando dependencias PHP" docker run --rm \
    -u "$(id -u):$(id -g)" \
    -v "$(pwd):/var/www/html" \
    -w /var/www/html \
    -e COMPOSER_HOME=/tmp/composer \
    laravelsail/php84-composer:latest \
    composer install --ignore-platform-reqs --no-interaction --no-scripts --quiet

# 2. Configurar el entorno
if [ ! -f .env ]; then
    run_step "Creando archivo .env" cp .env.example .env
fi

# 3. Levantar los contenedores
run_step "Iniciando contenedores (esto puede tardar la primera vez)" ./vendor/bin/sail up -d --build

# 4. Esperar a que los servicios de DB estén listos
echo -ne "  ⏳ Esperando que la base de datos esté lista... "
sleep 8
echo -e "${GREEN}✔${NC}"

# 5. Configuración de Laravel
run_step "Generando clave de aplicación" ./vendor/bin/sail artisan key:generate --quiet
run_step "Creando enlace de almacenamiento" ./vendor/bin/sail artisan storage:link --quiet
run_step "Ejecutando migraciones" ./vendor/bin/sail artisan migrate --force --quiet

# 6. Assets de frontend
run_step "Instalando dependencias de frontend" ./vendor/bin/sail npm install --legacy-peer-deps --silent
run_step "Compilando assets" ./vendor/bin/sail npm run build

# ── Listo ─────────────────────────────────────────────────────
echo ""
echo -e "${BLUE}  ────────────────────────────────────${NC}"
echo -e "${GREEN}  ✔ Proyecto listo${NC}"
echo ""
echo -e "  🌎 URL local:     ${BLUE}http://localhost${NC}"
echo -e "  💻 Para dev:      ${GREEN}./vendor/bin/sail npm run dev${NC}"
echo -e "  🐳 Para detener:  ${GREEN}./vendor/bin/sail down${NC}"
echo ""
