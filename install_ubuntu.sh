#!/usr/bin/env bash

# ==============================================================================
# SCRIPT DE INSTALACIÓN AUTOMÁTICA DE TUTORBOTPLUS 2.0 PARA UBUNTU SERVER
# ==============================================================================
# Instala y configura todo lo necesario para ejecutar TutorBotPlus 2.0 y su
# Juez Virtual (Judge0) en Ubuntu Server (20.04, 22.04 o 24.04 LTS x86_64).
#
# Uso:
#   sudo ./install_ubuntu.sh [opciones]
#
# Opciones:
#   --fresh-db     Reinicia la base de datos (migrate:fresh --seed). ¡BORRA DATOS!
#   --force-env    Sobrescribe el archivo .env actual (crea respaldo .env.bak)
#   --skip-judge0  Omite la instalación/inicio de Judge0 en Docker
#   -h, --help     Muestra esta ayuda
# ==============================================================================

set -Eeuo pipefail

# Colores para la salida en consola
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

log_info()    { echo -e "${BLUE}[INFO]${NC} $1"; }
log_success() { echo -e "${GREEN}[EXITO]${NC} $1"; }
log_warning() { echo -e "${YELLOW}[ADVERTENCIA]${NC} $1"; }
log_error()   { echo -e "${RED}[ERROR]${NC} $1"; exit 1; }

trap 'echo -e "${RED}[ERROR]${NC} El instalador falló en la línea ${LINENO} (comando: ${BASH_COMMAND})."' ERR

# 1. Procesar argumentos
FRESH_DB=false
FORCE_ENV=false
SKIP_JUDGE0=false

for arg in "$@"; do
    case "$arg" in
        --fresh-db)
            FRESH_DB=true
            ;;
        --force-env)
            FORCE_ENV=true
            ;;
        --skip-judge0)
            SKIP_JUDGE0=true
            ;;
        -h|--help)
            sed -n '3,19p' "$0"
            exit 0
            ;;
        *)
            log_error "Opción desconocida: $arg. Usa --help para ver las opciones disponibles."
            ;;
    esac
done

# 2. Verificar permisos de root y sistema operativo
if [ "${EUID}" -ne 0 ]; then
    log_error "Este script debe ejecutarse con permisos de superusuario (sudo ./install_ubuntu.sh)."
fi

if [ ! -f /etc/os-release ] || ! grep -qiE "ubuntu|debian" /etc/os-release; then
    log_warning "Sistema no reconocido como Ubuntu/Debian. Se intentará continuar de todas formas."
fi

# Variables de Configuración
APP_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PHP_VERSION="8.2"
DB_NAME="tutorbotplus"
DB_USER="tutorbot"
DB_PASS="tutorbot_pass_2026"
SERVER_IP=$(hostname -I 2>/dev/null | awk '{print $1}' || true)
if [ -z "${SERVER_IP}" ]; then
    SERVER_IP="127.0.0.1"
fi

export DEBIAN_FRONTEND=noninteractive
export COMPOSER_ALLOW_SUPERUSER=1
APT_OPTS=(-y -o Dpkg::Options::="--force-confdef" -o Dpkg::Options::="--force-confold")

echo -e "${GREEN}"
echo "===================================================================="
echo "    INSTALADOR AUTOMÁTICO DE TUTORBOTPLUS 2.0 + JUDGE0 JUEZ         "
echo "===================================================================="
echo -e "${NC}"
log_info "Directorio del proyecto : $APP_DIR"
log_info "IP detectada            : $SERVER_IP"
log_info "Modo reinicio BD        : $FRESH_DB"
log_info "Forzar nuevo .env       : $FORCE_ENV"
echo ""

# 3. Actualización del sistema e instalación de dependencias básicas
log_info "1/9. Actualizando repositorios e instalando herramientas básicas..."
apt-get update -y
apt-get upgrade "${APT_OPTS[@]}"
apt-get install "${APT_OPTS[@]}" \
    curl git unzip zip software-properties-common ca-certificates \
    gnupg lsb-release build-essential cron ufw

# 4. Configuración de repositorios (PHP, Node.js, Docker)
log_info "2/9. Configurando repositorios de PHP ${PHP_VERSION}, Node.js y Docker..."

# Repositorio PHP (Ondrej Surý) - idempotente
if ! grep -rq "ondrej/php" /etc/apt/sources.list /etc/apt/sources.list.d/ 2>/dev/null; then
    LC_ALL=C.UTF-8 add-apt-repository ppa:ondrej/php -y
fi

# Repositorio Node.js LTS (20.x)
if ! command -v node &> /dev/null; then
    curl -fsSL https://deb.nodesource.com/setup_20.x | bash -
fi

# Repositorio oficial de Docker
if [ "$SKIP_JUDGE0" = false ]; then
    mkdir -p /etc/apt/keyrings
    if [ ! -f /etc/apt/keyrings/docker.gpg ]; then
        curl -fsSL https://download.docker.com/linux/ubuntu/gpg | gpg --dearmor -o /etc/apt/keyrings/docker.gpg
        chmod a+r /etc/apt/keyrings/docker.gpg
    fi
    UBUNTU_CODENAME=$(lsb_release -cs)
    echo \
      "deb [arch=$(dpkg --print-architecture) signed-by=/etc/apt/keyrings/docker.gpg] https://download.docker.com/linux/ubuntu ${UBUNTU_CODENAME} stable" \
      > /etc/apt/sources.list.d/docker.list
fi

apt-get update -y

# 5. Instalación de paquetes principales
log_info "3/9. Instalando PHP ${PHP_VERSION}, Nginx, MySQL, Node.js y Docker..."
PACKAGES=(
    "php${PHP_VERSION}" "php${PHP_VERSION}-fpm" "php${PHP_VERSION}-cli"
    "php${PHP_VERSION}-mysql" "php${PHP_VERSION}-mbstring" "php${PHP_VERSION}-xml"
    "php${PHP_VERSION}-curl" "php${PHP_VERSION}-zip" "php${PHP_VERSION}-gd"
    "php${PHP_VERSION}-intl" "php${PHP_VERSION}-bcmath" "php${PHP_VERSION}-sqlite3"
    "php${PHP_VERSION}-opcache"
    nginx mysql-server nodejs
)

if [ "$SKIP_JUDGE0" = false ]; then
    PACKAGES+=(docker-ce docker-ce-cli containerd.io docker-buildx-plugin docker-compose-plugin)
fi

apt-get install "${APT_OPTS[@]}" "${PACKAGES[@]}"

# Ajustar límites de PHP-FPM para cargas y peticiones largas (alineado con Nginx 64M)
PHP_CUSTOM_INI="/etc/php/${PHP_VERSION}/fpm/conf.d/99-tutorbot.ini"
cat <<EOF > "$PHP_CUSTOM_INI"
upload_max_filesize = 64M
post_max_size = 64M
max_execution_time = 180
memory_limit = 512M
EOF
systemctl enable "php${PHP_VERSION}-fpm"
systemctl restart "php${PHP_VERSION}-fpm"

# Instalación de Composer global
if ! command -v composer &> /dev/null; then
    log_info "Instalando Composer..."
    curl -fsSL https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
else
    composer self-update --no-interaction >/dev/null 2>&1 || true
fi

# 6. Configurar archivo .env (respetando uno existente salvo --force-env)
log_info "4/9. Configurando variables de entorno (.env)..."
cd "$APP_DIR"

if [ -f "$APP_DIR/.env" ] && [ "$FORCE_ENV" = false ]; then
    log_warning "Se detectó un archivo .env existente; se conservará para no perder claves ni credenciales."
    # Leer credenciales de BD existentes si están definidas
    ENV_DB_NAME=$(grep -E '^DB_DATABASE=' "$APP_DIR/.env" | cut -d '=' -f2- | tr -d '"' | tr -d "'" || true)
    ENV_DB_USER=$(grep -E '^DB_USERNAME=' "$APP_DIR/.env" | cut -d '=' -f2- | tr -d '"' | tr -d "'" || true)
    ENV_DB_PASS=$(grep -E '^DB_PASSWORD=' "$APP_DIR/.env" | cut -d '=' -f2- | tr -d '"' | tr -d "'" || true)
    [ -n "$ENV_DB_NAME" ] && DB_NAME="$ENV_DB_NAME"
    [ -n "$ENV_DB_USER" ] && DB_USER="$ENV_DB_USER"
    [ -n "$ENV_DB_PASS" ] && DB_PASS="$ENV_DB_PASS"
else
    if [ -f "$APP_DIR/.env" ]; then
        cp "$APP_DIR/.env" "$APP_DIR/.env.bak.$(date +%Y%m%d%H%M%S)"
        log_info "Respaldo de .env anterior guardado como .env.bak.*"
    fi
    cat <<EOF > "$APP_DIR/.env"
APP_NAME="TutorBotPlus 2.0"
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_URL=http://${SERVER_IP}

LOG_CHANNEL=stack
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=warning

JUDGE0_API_KEY_RAPID_API=
JUDGE0_API_KEY_PROD=local_judge0_token
JUDGE0_AUTHORIZE_KEY_PROD=local_judge0_auth
JUDGE0_AUTHENTICATION_KEY_PROD=X-Auth-Token
JUDGE0_URL_PROD=http://127.0.0.1:2358

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=${DB_NAME}
DB_USERNAME=${DB_USER}
DB_PASSWORD=${DB_PASS}

BROADCAST_DRIVER=log
CACHE_DRIVER=file
FILESYSTEM_DISK=local
QUEUE_CONNECTION=sync
SESSION_DRIVER=file
SESSION_LIFETIME=120

MEMCACHED_HOST=127.0.0.1

REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

MAIL_MAILER=smtp
MAIL_HOST=mailpit
MAIL_PORT=1025
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS="hello@tutorbot.com"
MAIL_FROM_NAME="\${APP_NAME}"

OPENAI_API_KEY=
OPENAI_ORGANIZATION=
EOF
    log_success "Archivo .env generado."
fi

# 7. Configurar MySQL
log_info "5/9. Configurando Base de Datos MySQL (${DB_NAME})..."
systemctl start mysql
systemctl enable mysql

mysql -e "CREATE DATABASE IF NOT EXISTS \`${DB_NAME}\` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
if [ "$DB_USER" != "root" ]; then
    mysql -e "CREATE USER IF NOT EXISTS '${DB_USER}'@'localhost' IDENTIFIED BY '${DB_PASS}';"
    mysql -e "CREATE USER IF NOT EXISTS '${DB_USER}'@'127.0.0.1' IDENTIFIED BY '${DB_PASS}';"
    mysql -e "ALTER USER '${DB_USER}'@'localhost' IDENTIFIED BY '${DB_PASS}';"
    mysql -e "ALTER USER '${DB_USER}'@'127.0.0.1' IDENTIFIED BY '${DB_PASS}';"
    mysql -e "GRANT ALL PRIVILEGES ON \`${DB_NAME}\`.* TO '${DB_USER}'@'localhost';"
    mysql -e "GRANT ALL PRIVILEGES ON \`${DB_NAME}\`.* TO '${DB_USER}'@'127.0.0.1';"
    mysql -e "FLUSH PRIVILEGES;"
fi
log_success "Base de datos '${DB_NAME}' configurada correctamente."

# 8. Desplegar Judge0 (Juez Virtual) mediante Docker Compose
if [ "$SKIP_JUDGE0" = false ]; then
    log_info "6/9. Iniciando Judge0 (Juez Virtual en puerto 2358)..."
    systemctl start docker
    systemctl enable docker

    JUDGE0_DIR="$APP_DIR/judge0-v1.13.0"
    if [ ! -d "$JUDGE0_DIR" ] && [ -f "$APP_DIR/judge0-v1.13.0.zip" ]; then
        log_info "Descomprimiendo judge0-v1.13.0.zip..."
        unzip -q "$APP_DIR/judge0-v1.13.0.zip" -d "$APP_DIR"
    fi

    if [ -d "$JUDGE0_DIR" ]; then
        cd "$JUDGE0_DIR"
        docker compose up -d db redis
        sleep 8
        docker compose up -d
        cd "$APP_DIR"

        log_info "Esperando a que Judge0 responda en http://127.0.0.1:2358/about..."
        JUDGE0_READY=false
        for _ in $(seq 1 15); do
            if curl -fsS http://127.0.0.1:2358/about >/dev/null 2>&1; then
                JUDGE0_READY=true
                break
            fi
            sleep 2
        done

        if [ "$JUDGE0_READY" = true ]; then
            log_success "Judge0 iniciado y respondiendo correctamente."
        else
            log_warning "Judge0 fue iniciado en Docker, pero aún está terminando de arrancar. Verifica con: docker compose -f $JUDGE0_DIR/docker-compose.yml logs -f"
        fi
    else
        log_warning "No se encontró judge0-v1.13.0 ni judge0-v1.13.0.zip. Se omitió el inicio de Judge0."
    fi
else
    log_info "6/9. Omitiendo despliegue de Judge0 (--skip-judge0)."
fi

# 9. Dependencias Laravel, Frontend, Migraciones y Permisos
log_info "7/9. Instalando dependencias PHP/JS, compilando assets y preparando Laravel..."
cd "$APP_DIR"

mkdir -p storage/app/public/archivos_adicionales \
         storage/framework/cache/data \
         storage/framework/sessions \
         storage/framework/views \
         storage/logs \
         bootstrap/cache

# Composer
composer install --no-dev --optimize-autoloader --no-interaction

# Generar APP_KEY solo si está vacía
CURRENT_APP_KEY=$(grep -E '^APP_KEY=' "$APP_DIR/.env" | cut -d '=' -f2- || true)
if [ -z "$CURRENT_APP_KEY" ]; then
    log_info "Generando APP_KEY de Laravel..."
    php artisan key:generate --force
fi

# Dependencias Frontend y compilación
if [ -f "$APP_DIR/package-lock.json" ]; then
    npm ci --no-audit --no-fund || npm install --no-audit --no-fund
else
    npm install --no-audit --no-fund
fi
npm run production

# Migraciones seguras (no destruye datos existentes a menos que se pase --fresh-db)
if [ "$FRESH_DB" = true ]; then
    log_warning "Ejecutando migrate:fresh --seed (--fresh-db activo)..."
    php artisan migrate:fresh --seed --force
else
    log_info "Ejecutando migraciones pendientes..."
    php artisan migrate --force
    USER_COUNT=$(mysql -N -s -e "SELECT COUNT(*) FROM \`${DB_NAME}\`.users;" 2>/dev/null || echo "0")
    if [ "${USER_COUNT:-0}" -eq 0 ]; then
        log_info "Tabla users vacía. Ejecutando Seeders iniciales..."
        php artisan db:seed --force
    else
        log_info "La base de datos ya contiene ${USER_COUNT} usuarios; se omiten los seeders para no duplicar registros."
    fi
fi

# Enlace simbólico de storage y optimización de caché
php artisan storage:link --force || true
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Ajustar permisos
chown -R www-data:www-data "$APP_DIR"
chmod -R 775 "$APP_DIR/storage" "$APP_DIR/bootstrap/cache"

# 10. Configurar Nginx y Servicio Systemd del Scheduler
log_info "8/9. Configurando Nginx y el servicio Systemd de evaluaciones..."

NGINX_CONF="/etc/nginx/sites-available/tutorbot"
cat <<EOF > "$NGINX_CONF"
server {
    listen 80;
    server_name _;
    root ${APP_DIR}/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php index.html;
    charset utf-8;
    client_max_body_size 64M;

    gzip on;
    gzip_types text/plain text/css application/json application/javascript text/xml application/xml application/xml+rss text/javascript;

    location / {
        try_files \$uri \$uri/ /index.php?\$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php${PHP_VERSION}-fpm.sock;
        fastcgi_param SCRIPT_FILENAME \$realpath_root\$fastcgi_script_name;
        fastcgi_read_timeout 180;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
EOF

ln -sf "$NGINX_CONF" /etc/nginx/sites-enabled/tutorbot
rm -f /etc/nginx/sites-enabled/default
nginx -t
systemctl enable nginx
systemctl restart nginx

PHP_BIN=$(command -v php)
SYSTEMD_SERVICE="/etc/systemd/system/tutorbot-scheduler.service"
cat <<EOF > "$SYSTEMD_SERVICE"
[Unit]
Description=TutorBotPlus Evaluaciones Scheduler Daemon
After=network.target mysql.service docker.service

[Service]
User=www-data
Group=www-data
WorkingDirectory=${APP_DIR}
ExecStart=${PHP_BIN} ${APP_DIR}/artisan schedule:work
Restart=always
RestartSec=5
StandardOutput=journal
StandardError=journal
SyslogIdentifier=tutorbot-scheduler

[Install]
WantedBy=multi-user.target
EOF

systemctl daemon-reload
systemctl enable tutorbot-scheduler.service
systemctl restart tutorbot-scheduler.service

# 11. Configuración de Firewall (UFW)
log_info "9/9. Configurando reglas de firewall (UFW)..."
ufw allow 80/tcp >/dev/null 2>&1 || true
ufw allow 443/tcp >/dev/null 2>&1 || true
ufw allow 22/tcp >/dev/null 2>&1 || true

JUDGE0_STATUS="omitido"
if [ "$SKIP_JUDGE0" = false ]; then
    if curl -fsS http://127.0.0.1:2358/about >/dev/null 2>&1; then
        JUDGE0_STATUS="activo (http://127.0.0.1:2358)"
    else
        JUDGE0_STATUS="iniciando o inactivo (revisar docker ps)"
    fi
fi

echo ""
echo -e "${GREEN}====================================================================${NC}"
echo -e "${GREEN}    ¡INSTALACIÓN COMPLETADA CON ÉXITO! TUTORBOTPLUS ESTÁ LISTO     ${NC}"
echo -e "${GREEN}====================================================================${NC}"
echo ""
echo -e "Acceso a la Plataforma Web:"
echo -e "   URL: ${YELLOW}http://${SERVER_IP}${NC} (o http://localhost dentro del servidor)"
echo ""
echo -e "Cuentas de Prueba iniciales (si se ejecutó el seeder):"
echo -e "   - Administrador : ${BLUE}admin@tutorbot.com${NC}      / Clave: ${YELLOW}admin${NC}"
echo -e "   - Profesor      : ${BLUE}profesor@tutorbot.com${NC}   / Clave: ${YELLOW}profesor${NC}"
echo -e "   - Estudiante    : ${BLUE}estudiante@tutorbot.com${NC} / Clave: ${YELLOW}estudiante${NC}"
echo ""
echo -e "Estado de Servicios:"
echo -e "   - Nginx (Web Server)          : $(systemctl is-active nginx)"
echo -e "   - PHP-FPM ${PHP_VERSION}                 : $(systemctl is-active php${PHP_VERSION}-fpm)"
echo -e "   - MySQL Base de Datos         : $(systemctl is-active mysql)"
echo -e "   - Judge0 (Docker Juez Virtual): ${JUDGE0_STATUS}"
echo -e "   - Evaluador de Envío (Daemon) : $(systemctl is-active tutorbot-scheduler.service)"
echo ""
echo -e "Comandos útiles:"
echo -e "   - Logs del evaluador : ${BLUE}sudo journalctl -u tutorbot-scheduler.service -f${NC}"
echo -e "   - Configurar OpenAI  : Edita ${BLUE}${APP_DIR}/.env${NC} (OPENAI_API_KEY) y ejecuta ${BLUE}php artisan config:cache${NC}"
echo -e "${GREEN}====================================================================${NC}"
