# TutorBot+ 2.0 (UCSC)

Plataforma de juez en línea (*Online Judge*), evaluación automatizada de código (C, C++, Python, Java, SQL) y tutoría inteligente asistida por Modelos de Lenguaje (LLM) para la **Universidad Católica de la Santísima Concepción (UCSC)**.

---

## Requisitos Previos

- **PHP 8.2+** (en Windows/XAMPP: `C:\xampp\php\php.exe`)
- **MySQL / MariaDB**
- **Composer** y **Node.js (v18+ / v20+)**
- **Docker + Docker Compose** (opcional en local si usas evaluación local/SQL, obligatorio en producción para **Judge0 v1.13.0**)

---

## Cómo Iniciar el Programa (Entorno Local / XAMPP en Windows)

### 1. Instalación de dependencias (primera vez)
```bash
composer install
npm install
```

### 2. Configurar variables de entorno y base de datos
1. Copia `.env.example` a `.env` (si aún no existe) y configura `DB_DATABASE=tutorbotplus`, `DB_USERNAME`, `DB_PASSWORD` y la clave de tu proveedor LLM si corresponde.
2. Crea la base de datos `tutorbotplus` en MySQL / phpMyAdmin.
3. Ejecuta las migraciones (y seeders si es una instalación nueva):
```bash
# Instalación nueva con datos iniciales:
C:\xampp\php\php.exe artisan migrate --seed
C:\xampp\php\php.exe artisan storage:link

# Si actualizas un servidor ya existente con datos:
C:\xampp\php\php.exe artisan migrate --force
C:\xampp\php\php.exe artisan optimize:clear
```

### 3. Iniciar el Servidor Web (Terminal 1)
```bash
C:\xampp\php\php.exe artisan serve --host=127.0.0.1 --port=8000
```
Abre en tu navegador: **http://127.0.0.1:8000**

### 4. Iniciar el Procesador de Evaluaciones (Terminal 2)
Necesario para procesar en segundo plano las colas de envíos con Judge0 y cerrar certámenes expirados:
```bash
C:\xampp\php\php.exe artisan schedule:work
```

---

## Cómo Iniciar en Servidor Linux (Ubuntu + Nginx + Docker Judge0)

El proyecto incluye el instalador automatizado `install_ubuntu.sh` (seguro e idempotente):
```bash
sudo chmod +x install_ubuntu.sh
sudo ./install_ubuntu.sh
```

Opciones útiles del instalador:
- `--fresh-db`: reinicia la base de datos desde cero (`migrate:fresh --seed`).
- `--force-env`: regenera el archivo `.env` (guardando respaldo `.env.bak.*`).
- `--skip-judge0`: omite la instalación/inicio de Judge0 en Docker.
- `--help`: muestra todas las opciones.

Comandos útiles en producción:
```bash
# Ver estado del evaluador en segundo plano (Scheduler)
sudo systemctl status tutorbot-scheduler.service

# Ver logs del evaluador en tiempo real
sudo journalctl -u tutorbot-scheduler.service -f

# Verificar contenedores de Judge0 y levantarlos manualmente si hace falta
docker ps
cd judge0-v1.13.0 && sudo docker compose up -d
```

---

## Cuentas de Prueba (Seeder por defecto)

| Rol | Correo | Contraseña |
| :--- | :--- | :--- |
| **Administrador** | `admin@tutorbot.com` | `admin` |
| **Profesor** | `profesor@tutorbot.com` | `profesor` |
| **Estudiante** | `estudiante@tutorbot.com` | `estudiante` |

---

## Pruebas Automatizadas

Para ejecutar la suite de pruebas PHPUnit:
```bash
vendor/bin/phpunit
```
