# Guía de Instalación en Ubuntu Server (Automática al 100%)

Para instalar **TutorBotPlus 2.0** junto con **Judge0 (Juez Virtual)** en una máquina virtual con **Ubuntu Server (20.04, 22.04 o 24.04 LTS)**, sigue estos sencillos pasos:

---

## 🚀 Pasos de Instalación

### 1. Copiar el proyecto a la Máquina Virtual
Sube o clona la carpeta del proyecto `TutorBotPlus-2.0` en tu servidor Ubuntu.

### 2. Acceder al directorio del proyecto
```bash
cd TutorBotPlus-2.0
```

### 3. Otorgar permisos de ejecución al script
```bash
chmod +x install_ubuntu.sh
```

### 4. Ejecutar el script instalador automatizado
```bash
sudo ./install_ubuntu.sh
```

---

## ⚙️ ¿Qué realiza el script automáticamente?

1. **Actualiza el sistema** e instala utilidades básicas (`curl`, `git`, `unzip`, `cron`, etc.).
2. **Instala PHP 8.2** con todas las extensiones requeridas (`fpm`, `cli`, `mysql`, `mbstring`, `xml`, `curl`, `zip`, `gd`, `intl`, `bcmath`).
3. **Instala Nginx**, **MySQL Server**, **Node.js (v20 LTS)** y **Composer**.
4. **Instala Docker y Docker Compose** y despliega automáticamente **Judge0 v1.13.0** en el puerto `2358`.
5. **Configura la Base de Datos MySQL** (`tutorbotplus`) y asigna los accesos.
6. **Configura Laravel**:
   - Crea el archivo `.env` para producción.
   - Instala dependencias con Composer y NPM.
   - Compila los componentes visuales (`npm run production`).
   - Ejecuta las migraciones y puebla la base de datos con los datos de prueba (`migrate:fresh --seed`).
   - Asigna los permisos correctos a `storage/` y `bootstrap/cache/`.
7. **Configura Nginx** como servidor web en el puerto `80`.
8. **Crea y activa un servicio Daemon (Systemd)** para `php artisan schedule:work`, garantizando que la evaluación automática de envíos de código por Judge0 funcione 24/7 en segundo plano.

---

## 🔑 Cuentas de Acceso de Prueba
Una vez finalizada la instalación, ingresa desde tu navegador a la IP de la máquina virtual (ejemplo: `http://192.168.1.50`):

- **Administrador**: `admin@tutorbot.com` / Clave: `admin`
- **Profesor**: `profesor@tutorbot.com` / Clave: `profesor`
- **Estudiante**: `estudiante@tutorbot.com` / Clave: `estudiante`

---

## 🛠️ Comandos Útiles

- **Ver estado del Evaluador en Segundo Plano (Scheduler)**:
  ```bash
  sudo systemctl status tutorbot-scheduler.service
  ```
- **Ver logs del Evaluador en Tiempo Real**:
  ```bash
  sudo journalctl -u tutorbot-scheduler.service -f
  ```
- **Verificar estado de los contenedores del Juez (Judge0)**:
  ```bash
  docker ps
  ```
