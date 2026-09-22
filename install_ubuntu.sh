#!/usr/bin/env bash

# ==============================================================================
# SCRIPT DE INSTALACIÓN AUTOMÁTICA DE TUTORBOTPLUS 2.0 PARA UBUNTU SERVER
# ==============================================================================
# Este script instala y configura absolutamente todo lo necesario para ejecutar
# TutorBotPlus 2.0 y su Juez Virtual (Judge0) en una Máquina Virtual con Ubuntu Server.
#
# Requisitos:
# - Ubuntu Server 20.04, 22.04 o 24.04 LTS (x86_64)
# - Ejecutar con permisos de superusuario (sudo ./install_ubuntu.sh)
# ==============================================================================

set -e # Detener ejecución en caso de error

# Colores para la salida en consola
RED='\030[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # Sin color

log_info() { echo -e "${BLUE}[INFO]${NC} $1"; }
log_success() { echo -e "${GREEN}[EXITO]${NC} $1"; }
log_warning() { echo -e "${YELLOW}[ADVERTENCIA]${NC} $1"; }
log_error() { echo -e "${RED}[ERROR]${NC} $1"; exit 1; }

# 1. Verificar permisos de root
if [ "$EUID" -ne 0 ]; then
  log_error "Este script debe ejecutarse con permisos de superusuario (sudo)."
fi

# Variables de Configuración
APP_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
DB_NAME="tutorbotplus"
DB_USER="tutorbot"
DB_PASS="tutorbot_pass_2026"
SERVER_IP=$(hostname -I | awk '{print $1}')
if [ -z "$SERVER_IP" ]; then
    SERVER_IP="127.0.0.1"
fi

export DEBIAN_FRONTEND=noninteractive

echo -e "${GREEN}"
echo "===================================================================="
echo "    INSTALADOR AUTOMATICO DE TUTORBOTPLUS 2.0 + JUDGE0 JUEZ        "
echo "===================================================================="
echo -e "${NC}"
log_info "Directorio del proyecto: $APP_DIR"
log_info "IP del Servidor detectada: $SERVER_IP"
echo ""

# 2. Actualización del sistema e instalación de dependencias básicas
log_info "1/9. Actualizando repositorios e instalando herramientas básicas..."
apt-get update -y && apt-get upgrade -y
apt-get install -y curl git unzip zip software-properties-common ca-certificates gnupg lsb-release build-essential cron ufw

# 3. Instalación de Repositorios (PHP, Node.js, Docker)
log_info "2/9. Configurando repositorios de PHP 8.2, Node.js y Docker..."

# Repositorio PHP (Ondrej Surý)
LC_ALL=C.UTF-8 add-apt-repository ppa:ondrej/php -y
apt-get update -y

# Repositorio Node.js LTS (20.x)
if ! command -v node &> /dev/null; then
    curl -fsSL https://deb.nodesource.com/setup_20.x | bash -
fi

# Repositorio oficial de Docker
mkdir -p /etc/apt/keyrings
if [ ! -f /etc/apt/keyrings/docker.gpg ]; then
    curl -fsSL https://download.docker.com/linux/ubuntu/gpg | gpg --dearmor -o /etc/apt/keyrings/docker.gpg
    chmod a+r /etc/apt/keyrings/docker.gpg
fi
echo \
  "deb [arch=$(dpkg --print-architecture) signed-by=/etc/apt/keyrings/docker.gpg] https://download.docker.com/linux/ubuntu \
  $(lsb_release -cs) stable" | tee /etc/apt/sources.list.d/docker.list > /dev/null
apt-get update -y

# 4. Instalación de Software
log_info "3/9. Instalando PHP 8.2, Nginx, MySQL, Node.js y Docker..."
apt-get install -y php8.2 php8.2-fpm php8.2-cli php8.2-mysql php8.2-mbstring \
                   php8.2-xml php8.2-curl php8.2-zip php8.2-gd php8.2-intl \
                   php8.2-bcmath php8.2-sqlite3 php8.2-opcache \
                   nginx mysql-server nodejs \
                   docker-ce docker-ce-cli containerd.io docker-buildx-plugin docker-compose-plugin

# Instalación de Composer global
if ! command -v composer &> /dev/null; then
    log_info "Instalando Composer..."
    curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
fi

# 5. Configurar MySQL
log_info "4/9. Configurando la Base de Datos MySQL..."
systemctl start mysql
systemctl enable mysql

mysql -e "CREATE DATABASE IF NOT EXISTS \`${DB_NAME}\` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
mysql -e "CREATE USER IF NOT EXISTS '${DB_USER}'@'localhost' IDENTIFIED BY '${DB_PASS}';"
mysql -e "GRANT ALL PRIVILEGES ON \`${DB_NAME}\`.* TO '${DB_USER}'@'localhost';"
mysql -e "FLUSH PRIVILEGES;"
log_success "Base de datos '${DB_NAME}' configurada correctamente."

# 6. Desplegar Judge0 (Juez Virtual) mediante Docker Compose
log_info "5/9. Iniciando contenedor de Judge0 (Juez Virtual en puerto 2358)..."
systemctl start docker
systemctl enable docker

JUDGE0_DIR="$APP_DIR/judge0-v1.13.0"
if [ -d "$JUDGE0_DIR" ]; then
    cd "$JUDGE0_DIR"
    docker compose down 2>/dev/null || true
    docker compose up -d
    cd "$APP_DIR"
    log_success "Judge0 iniciado en Docker."
else
    log_warning "No se encontró el directorio judge0-v1.13.0. Se omitió el inicio de Judge0."
fi

# 7. Configuración del Proyecto Laravel TutorBotPlus
log_info "6/9. Configurando variables de entorno (.env) y dependencias PHP/JS..."
cd "$APP_DIR"

# Crear carpetas requeridas si no existen
mkdir -p storage/app/public/archivos_adicionales
mkdir -p storage/framework/cache/data
mkdir -p storage/framework/sessions
mkdir -p storage/framework/views
mkdir -p storage/logs

# Generar archivo .env
cat <<EOF > "$APP_DIR/.env"
APP_NAME="TutorBotPlus 2.0"
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_URL=http://${SERVER_IP}

LOG_CHANNEL=stack
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=debug

JUDGE0_API_KEY_RAPID_API=
JUDGE0_API_KEY_PROD=local_key
JUDGE0_AUTHORIZE_KEY_PROD=local_auth
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

# Instalación de Composer (dependencias PHP)
log_info "Instalando paquetes de Composer..."
composer install --no-dev --optimize-autoloader --no-interaction

# Generar clave de aplicación Laravel
log_info "Generando clave de la aplicación Laravel..."
php artisan key:generate --force

# Instalación y Compilación de activos Frontend (Node/NPM)
log_info "Instalando paquetes NPM y compilando assets frontend..."
npm install --no-audit --no-fund
npm run production || npx mix --production

# Migraciones y Seeders de la Base de Datos
log_info "Ejecutando migraciones de Base de Datos y Seeders iniciales..."
php artisan migrate:fresh --seed --force

# Enlace simbólico de storage
php artisan storage:link --force || true

# Optimizar cachés de Laravel
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Ajustar Permisos de Archivos
log_info "Ajustando permisos de archivos para www-data..."
chown -R www-data:www-data "$APP_DIR"
chmod -R 775 "$APP_DIR/storage" "$APP_DIR/bootstrap/cache"

# 8. Configuración del Servidor Web Nginx
log_info "7/9. Configurando Nginx para servir TutorBotPlus..."

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

    location / {
        try_files \$uri \$uri/ /index.php?\$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME \$realpath_root\$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
EOF

# Activar sitio en Nginx y remover el sitio por defecto
ln -sf "$NGINX_CONF" /etc/nginx/sites-enabled/tutorbot
rm -f /etc/nginx/sites-enabled/default

# Verificar sintaxis Nginx y reiniciar
nginx -t
systemctl restart nginx

# 9. Configurar Servicio de Procesador de Evaluaciones (Laravel Scheduler)
log_info "8/9. Creando servicio Systemd para el Procesador de Evaluaciones (schedule:work)..."

SYSTEMD_SERVICE="/etc/systemd/system/tutorbot-scheduler.service"
cat <<EOF > "$SYSTEMD_SERVICE"
[Unit]
Description=TutorBotPlus Evaluaciones Scheduler Daemon
After=network.target mysql.service docker.service

[Service]
User=www-data
Group=www-data
WorkingDirectory=${APP_DIR}
ExecStart=/usr/bin/php ${APP_DIR}/artisan schedule:work
Restart=always
RestartSec=5

[Install]
WantedBy=multi-user.target
EOF

systemctl daemon-reload
systemctl enable tutorbot-scheduler.service
systemctl restart tutorbot-scheduler.service

# Configuración de Firewall (UFW)
log_info "9/9. Configurando reglas de firewall (UFW)..."
ufw allow 80/tcp || true
ufw allow 443/tcp || true
ufw allow 22/tcp || true

echo ""
echo -e "${GREEN}====================================================================${NC}"
echo -e "${GREEN}    ¡INSTALACIÓN COMPLETADA CON ÉXITO! TUTORBOTPLUS ESTÁ LISTO     ${NC}"
echo -e "${GREEN}====================================================================${NC}"
echo ""
echo -e "Acceso a la Plataforma Web:"
echo -e "   URL: ${YELLOW}http://${SERVER_IP}${NC} (o http://localhost si estás dentro de la VM)"
echo ""
echo -e "Cuentas de Prueba creadas:"
echo -e "   - Administrador : ${BLUE}admin@tutorbot.com${NC}     / Clave: ${YELLOW}admin${NC}"
echo -e "   - Profesor      : ${BLUE}profesor@tutorbot.com${NC}  / Clave: ${YELLOW}profesor${NC}"
echo -e "   - Estudiante    : ${BLUE}estudiante@tutorbot.com${NC}/ Clave: ${YELLOW}estudiante${NC}"
echo ""
echo -e "Servicios Activos en la Máquina Virtual:"
echo -e "   - Nginx (Web Server)         : $(systemctl is-active nginx)"
echo -e "   - PHP-FPM 8.2                : $(systemctl is-active php8.2-fpm)"
echo -e "   - MySQL Base de Datos        : $(systemctl is-active mysql)"
echo -e "   - Judge0 (Docker Juez Virtual): $(docker ps --format '{{.Names}}' | grep -q server && echo 'activo' || echo 'inactivo')"
echo -e "   - Evaluador de Envío (Daemon): $(systemctl is-active tutorbot-scheduler.service)"
echo ""
echo -e "Para revisar logs del evaluador de envíos:"
echo -e "   sudo journalctl -u tutorbot-scheduler.service -f"
echo -e "${GREEN}====================================================================${NC}"
