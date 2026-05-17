# 🛡️ TelamoNet — Seguridad e Infraestructura de Alta Disponibilidad

> **Reto: "Fortaleza Digital: Blindaje y Resiliencia"**
> Documentación completa de todas las medidas implementadas.

---

## 📋 Índice

1. [Arquitectura General](#arquitectura-general)
2. [Hardenización del Servidor](#hardenización-del-servidor)
3. [HTTPS con Let's Encrypt](#https-con-lets-encrypt)
4. [Alta Disponibilidad y Balanceo de Carga](#alta-disponibilidad-y-balanceo-de-carga)
5. [Análisis de Vulnerabilidades (OWASP ZAP)](#análisis-de-vulnerabilidades-owasp-zap)
6. [Cabeceras de Seguridad HTTP](#cabeceras-de-seguridad-http)
7. [Sistema de Backups Automatizados](#sistema-de-backups-automatizados)
8. [Guía de Despliegue en Producción](#guía-de-despliegue-en-producción)
9. [Checklist de Seguridad](#checklist-de-seguridad)

---

## 🏗️ Arquitectura General

```
Internet (HTTPS :443)
        │
        ▼
┌───────────────────┐     UFW Firewall
│   Nginx (LB)      │◄──  Solo puertos 443, 80, 2222
│   (Contenedor)    │     bloqueados por iptables
└────────┬──────────┘
         │   least_conn upstream
    ┌────┴────┐
    ▼         ▼
┌──────────┐ ┌──────────┐
│Backend_1 │ │Backend_2 │   Laravel (PHP-FPM)
│(Laravel) │ │(Laravel) │   2 réplicas idénticas
└────┬─────┘ └─────┬────┘
     └──────┬───────┘
            ▼
    ┌───────────────┐
    │     Redis     │   Sesiones y caché compartidas
    └───────────────┘
            │
            ▼
    ┌───────────────┐
    │   MySQL 8.0   │   Solo accesible en 127.0.0.1
    └───────────────┘
```

### Puertos expuestos al exterior

| Puerto | Servicio              | Acceso        |
|--------|-----------------------|---------------|
| `443`  | HTTPS (Nginx)         | 🌐 Público    |
| `80`   | HTTP → redirige HTTPS | 🌐 Público    |
| `2222` | SSH (puerto custom)   | 🔒 Solo admin |
| `3307` | MySQL                 | ❌ Bloqueado  |
| `8080` | phpMyAdmin            | ❌ Solo localhost |
| `3000` | Signaling WS          | ❌ Solo interno |

---

## 🔐 Hardenización del Servidor

### Script: `deploy/harden.sh`

Ejecutar **una sola vez** al provisionar el servidor:

```bash
# Dar permisos de ejecución
chmod +x deploy/harden.sh

# Ejecutar como root (pasando el puerto SSH deseado)
SSH_PORT=2222 sudo -E ./deploy/harden.sh
```

### Lo que hace el script

#### 1. Endurecimiento SSH (`/etc/ssh/sshd_config`)

| Parámetro                  | Valor    | Motivo                                              |
|----------------------------|----------|-----------------------------------------------------|
| `Port`                     | `2222`   | Evita bots que escanean el puerto 22 por defecto    |
| `PermitRootLogin`          | `no`     | El root no puede conectarse directamente            |
| `PasswordAuthentication`   | `no`     | Solo autenticación por clave pública (SSH Keys)     |
| `MaxAuthTries`             | `3`      | Máximo 3 intentos antes de cortar la conexión       |
| `LoginGraceTime`           | `20`     | 20 segundos para autenticarse o desconexión         |
| `AllowTcpForwarding`       | `no`     | Evita uso de SSH como proxy/túnel no autorizado     |
| `X11Forwarding`            | `no`     | Sin reenvío de pantallas gráficas                   |
| `Protocol`                 | `2`      | Solo protocolo SSH v2 (v1 es vulnerable)            |
| `ClientAliveInterval`      | `300`    | Desconecta sesiones inactivas tras 5 min            |
| `AllowGroups`              | `sshusers sudo` | Solo usuarios en estos grupos pueden conectar |

> **Nota de seguridad:** El script detecta automáticamente al usuario que ejecuta `sudo` (vía `$SUDO_USER`) y lo añade al grupo `sshusers` antes de reiniciar SSH, para evitar que te quedes fuera del servidor.

#### 2. Cortafuegos UFW

```
Política por defecto:  DENY incoming / ALLOW outgoing
Reglas activas:
  ✅ ALLOW  2222/tcp   (SSH)
  ✅ ALLOW  80/tcp     (HTTP)
  ✅ ALLOW  443/tcp    (HTTPS)
  ✅ LIMIT  2222/tcp   (Rate limit anti-bruteforce)
  ❌ DENY   3307/tcp   (MySQL)
  ❌ DENY   8080/tcp   (phpMyAdmin)
```

#### 3. Fail2Ban — Protección anti-fuerza bruta

```ini
[sshd]        maxretry=3    bantime=24h
[nginx-auth]  maxretry=5    bantime=1h
[nginx-req]   maxretry=10   bantime=1h
```

#### 4. Servicios deshabilitados

- `cups` — Impresión
- `avahi-daemon` — mDNS local
- `rpcbind` — NFS legacy
- `bluetooth`, `ModemManager` — Hardware innecesario

#### 5. Actualizaciones de seguridad automáticas

`unattended-upgrades` configurado para aplicar parches de seguridad cada día automáticamente.

---

## 🔒 HTTPS con Let's Encrypt

### Script: `deploy/certbot-ssl.sh`

```bash
chmod +x deploy/certbot-ssl.sh
sudo ./deploy/certbot-ssl.sh \
  --domain telamonet.example.com \
  --email admin@example.com
```

### Lo que configura

- **Certbot** (cliente Let's Encrypt) para obtener certificados TLS gratuitos
- **Validación webroot** mediante el directorio `/var/www/certbot`
- **Renovación automática** vía cron cada 12 horas (`certbot renew`)
- **Parámetros DH** de 2048 bits (`/etc/nginx/dhparam.pem`)

### Configuración TLS aplicada

```nginx
ssl_protocols       TLSv1.2 TLSv1.3;  # Sin SSL 3.0, TLS 1.0 ni 1.1
ssl_prefer_server_ciphers off;          # Dejar elegir al cliente (más seguro)
ssl_session_cache   shared:SSL:10m;
ssl_session_tickets off;                # Anti-BEAST / Forward Secrecy
ssl_stapling        on;                 # OCSP Stapling (verifica revocación)
```

> 🎯 **Objetivo:** Rating A+ en [SSL Labs Test](https://www.ssllabs.com/ssltest/)

---

## ⚖️ Alta Disponibilidad y Balanceo de Carga

### Archivo: `docker-compose.ha.yml`

```bash
# Levantar el stack de Alta Disponibilidad completo
docker compose -f docker-compose.ha.yml up --build -d
```

### Componentes del stack HA

| Servicio       | Réplicas | Función                                    |
|----------------|----------|--------------------------------------------|
| `nginx`        | 1        | Balanceador de carga + terminación SSL     |
| `backend_1`    | 1        | Réplica 1 de Laravel (PHP-FPM)             |
| `backend_2`    | 1        | Réplica 2 de Laravel (PHP-FPM)             |
| `redis`        | 1        | Sesiones y caché compartidas (HA)          |
| `db`           | 1        | MySQL 8.0 (solo localhost)                 |
| `signaling`    | 1        | Servidor WebSocket (videollamadas)         |
| `phpmyadmin`   | —        | Solo con `--profile dev`                   |

### Algoritmo de balanceo: `least_conn`

```nginx
upstream telamonet_php {
    least_conn;           # Menor número de conexiones activas
    server backend_1:9000 weight=1 max_fails=3 fail_timeout=30s;
    server backend_2:9000 weight=1 max_fails=3 fail_timeout=30s;
    keepalive 32;
}
```

El algoritmo **least_conn** dirige cada nueva petición al backend con menos conexiones activas en ese momento, garantizando distribución equitativa incluso con peticiones de distinta duración.

### Sesiones compartidas con Redis

Sin Redis, las sesiones de usuario solo existirían en la réplica que las creó, provocando que el usuario tuviera que volver a loguearse si la siguiente petición iba a la otra réplica. Con Redis, **ambas réplicas comparten el mismo store de sesiones**.

```env
SESSION_DRIVER=redis
CACHE_DRIVER=redis
```

### Health Checks

Cada servicio tiene un health check configurado en Docker Compose. Nginx solo enviará tráfico a backends que pasen el check:

```bash
# Verificar salud del stack
curl https://tu-dominio.com/health
# → {"status":"healthy","backends":["backend_1","backend_2"]}
```

> **Nota técnica:** El healthcheck de los backends usa `php-fpm -t` (validación de configuración PHP-FPM) en lugar de `php artisan health`, que requiere el paquete `spatie/laravel-health` no incluido por defecto en Laravel.

---

## 🔍 Análisis de Vulnerabilidades OWASP ZAP

### Script: `deploy/owasp-scan.sh`

```bash
chmod +x deploy/owasp-scan.sh

# Escanear la aplicación en local (levantar primero con docker compose)
./deploy/owasp-scan.sh http://localhost:8000

# Escanear en producción
./deploy/owasp-scan.sh https://tu-dominio.com
```

### Cómo funciona

El script ejecuta **OWASP ZAP Baseline Scan** en modo Docker:
- **Pasivo**: analiza el tráfico sin atacar activamente la aplicación
- Detecta las vulnerabilidades del **OWASP Top 10**
- Genera reportes en **HTML** y **JSON** en `security-reports/`

### Vulnerabilidades detectadas y parcheadas

| OWASP Top 10 | Vulnerabilidad            | Parche implementado                              |
|-------------|---------------------------|--------------------------------------------------|
| A05         | Falta cabecera HSTS       | `Strict-Transport-Security` en Nginx + middleware |
| A05         | Clickjacking              | `X-Frame-Options: DENY`                          |
| A05         | MIME Sniffing             | `X-Content-Type-Options: nosniff`                |
| A05         | XSS reflejado             | `Content-Security-Policy` estricta               |
| A05         | Información del servidor  | `server_tokens off` + eliminar `X-Powered-By`    |
| A07         | Sin HTTPS                 | HSTS + redirección HTTP→HTTPS                    |
| A04         | Sin rate limiting         | `limit_req_zone` en Nginx por ruta               |
| A03         | Inyección vía parámetros  | Validación Laravel + CSP                         |

---

## 🔑 Cabeceras de Seguridad HTTP

### Middleware: `app/Http/Middleware/SecurityHeadersMiddleware.php`

Registrado globalmente en `bootstrap/app.php`. Aplica a **todas** las respuestas:

```
Strict-Transport-Security: max-age=63072000; includeSubDomains; preload
X-Frame-Options: DENY
X-Content-Type-Options: nosniff
X-XSS-Protection: 1; mode=block
Referrer-Policy: strict-origin-when-cross-origin
Permissions-Policy: geolocation=(), microphone=(), camera=(self)
Content-Security-Policy: default-src 'self'; ...
Cross-Origin-Resource-Policy: same-origin
Cross-Origin-Opener-Policy: same-origin
```

> Verifica las cabeceras en: [securityheaders.com](https://securityheaders.com)

---

## 💾 Sistema de Backups Automatizados

### Script: `deploy/backup.sh`

```bash
chmod +x deploy/backup.sh

# Variables requeridas (cargar desde .env o exportar)
export DB_ROOT_PASSWORD="tu_password"
export DB_DATABASE="telamonet"
export BACKUP_ENCRYPTION_KEY="clave_aes_256_segura"
export BACKUP_REMOTE_HOST="backup.tu-servidor.com"
export BACKUP_REMOTE_USER="backup"
export BACKUP_REMOTE_PATH="/backups/telamonet"

# Ejecutar backup manual
./deploy/backup.sh
```

### Qué incluye cada backup

```
telamonet_backup_20260517_030000/
├── db_dump_20260517_030000.sql.gz     # Volcado completo MySQL
├── storage_20260517_030000.tar.gz     # Archivos subidos (imágenes, docs)
└── configs_20260517_030000.tar.gz     # .env, nginx.conf, docker-compose
```

### Seguridad del backup

1. **Cifrado AES-256** antes de la transferencia
2. **Transferencia SSH** con `rsync` (canal seguro)
3. **Retención** configurable (por defecto: 7 días)
4. **Log** en `/var/log/telamonet-backup.log`

### Cron automático

Instalado en `/etc/cron.d/telamonet`:

```cron
0 3 * * * root /opt/telamonet/deploy/backup.sh
```

Backup diario a las **3:00 AM** del servidor.

---

## 🚀 Guía de Despliegue en Producción

Orden de ejecución recomendado en un servidor Ubuntu 22.04 limpio:

```bash
# 1. Clonar el repositorio en el servidor
git clone https://github.com/tu-org/telamonet.git /opt/telamonet
cd /opt/telamonet

# 2. Configurar variables de entorno
cp .env.ha.example .env
nano .env   # Rellenar TODOS los valores marcados con <CAMBIAR>

# 3. Hardenizar el servidor (SSH, UFW, Fail2Ban)
SSH_PORT=2222 sudo -E ./deploy/harden.sh
# ⚠️ Abrir nueva terminal con el puerto 2222 para verificar antes de continuar

# 4. Instalar Docker y Docker Compose
curl -fsSL https://get.docker.com | sudo sh
sudo usermod -aG docker $USER

# 5. Construir e iniciar el stack de Alta Disponibilidad
docker compose -f docker-compose.ha.yml up --build -d

# 6. Obtener certificado SSL y configurar HTTPS
sudo ./deploy/certbot-ssl.sh \
  --domain tu-dominio.com \
  --email admin@tu-dominio.com

# 7. Instalar los cron jobs de producción
sudo cp deploy/crontab.production /etc/cron.d/telamonet
sudo chmod 644 /etc/cron.d/telamonet

# 8. Ejecutar primer backup manual para verificar
./deploy/backup.sh

# 9. Ejecutar análisis OWASP ZAP
./deploy/owasp-scan.sh https://tu-dominio.com
```

---

## ✅ Checklist de Seguridad

### Hardenización del Servidor
- [ ] Script `harden.sh` ejecutado en el servidor de producción
- [ ] SSH accesible solo por el nuevo puerto (`2222`)
- [ ] Root login deshabilitado por SSH
- [ ] Autenticación por contraseña SSH deshabilitada (solo SSH Keys)
- [ ] UFW activo con política `deny incoming` por defecto
- [ ] Fail2Ban activo y monitorizando SSH + Nginx
- [ ] Actualizaciones de seguridad automáticas activas

### HTTPS / SSL
- [ ] Certificado Let's Encrypt obtenido y activo
- [ ] HTTP redirige automáticamente a HTTPS (301)
- [ ] Rating SSL A+ verificado en SSL Labs
- [ ] HSTS habilitado con `preload`
- [ ] Renovación automática de certificados configurada

### Alta Disponibilidad
- [ ] 2 réplicas de backend corriendo (`docker ps`)
- [ ] Sesiones funcionando correctamente con Redis
- [ ] Health check `/health` devuelve 200
- [ ] MySQL solo accesible en `127.0.0.1` (no en `0.0.0.0`)
- [ ] phpMyAdmin solo accesible via `localhost` (no internet)

### Cabeceras de Seguridad (OWASP)
- [ ] `Strict-Transport-Security` presente en respuestas
- [ ] `X-Frame-Options: DENY` presente
- [ ] `Content-Security-Policy` configurada
- [ ] `X-Powered-By` eliminado de respuestas
- [ ] Verificación en securityheaders.com con grado A o superior

### Backups
- [ ] Backup manual ejecutado y verificado
- [ ] Backup cifrado (AES-256) confirmado
- [ ] Transferencia al servidor remoto confirmada
- [ ] Cron de backup instalado y activo
- [ ] Proceso de restauración documentado y probado

### Análisis de Vulnerabilidades
- [ ] Escaneo OWASP ZAP ejecutado
- [ ] Vulnerabilidades High/Medium parcheadas
- [ ] Reporte HTML generado y guardado en `security-reports/`

---

## 📁 Estructura de Archivos de Seguridad

```
telamonet/
├── deploy/
│   ├── harden.sh              # Hardenización del servidor
│   ├── certbot-ssl.sh         # Instalación HTTPS/SSL Let's Encrypt
│   ├── nginx.ha.conf          # Nginx: Load Balancer + SSL
│   ├── backup.sh              # Backups automáticos y remotos
│   ├── owasp-scan.sh          # Análisis de vulnerabilidades OWASP ZAP
│   ├── crontab.production     # Cron jobs de producción
│   └── mysql/
│       └── my.cnf             # Hardening MySQL
├── docker-compose.ha.yml      # Stack de Alta Disponibilidad
├── .env.ha.example            # Variables de entorno para producción HA
├── backend/
│   ├── app/Http/Middleware/
│   │   └── SecurityHeadersMiddleware.php  # Cabeceras OWASP
│   └── bootstrap/
│       └── app.php            # Registro del middleware de seguridad
├── security-reports/          # Reportes OWASP ZAP (generados, en .gitignore)
└── docs/
    └── SECURITY.md            # Este documento
```

---

*Documentación generada para el Reto "Fortaleza Digital: Blindaje y Resiliencia" — TelamoNet 2026*

---

## 🔧 Bugs Detectados y Corregidos

Durante la revisión de calidad se identificaron y corrigieron 5 problemas críticos:

| #  | Archivo                    | Bug                                                                                     | Corrección                                                                                |
|----|----------------------------|-----------------------------------------------------------------------------------------|-------------------------------------------------------------------------------------------|
| 1  | `deploy/owasp-scan.sh`     | Python heredoc usaba `sys.argv[1]`, pero los heredocs no reciben argumentos del shell   | Usa `os.environ["ZAP_REPORT_PATH"]` inyectado como variable de entorno exportada         |
| 2  | `deploy/certbot-ssl.sh`    | Usaba `proxy_pass` HTTP para el backend, pero PHP-FPM habla protocolo FastCGI           | Cambiado a `fastcgi_pass` con los `fastcgi_param` correctos                               |
| 3  | `docker-compose.ha.yml`    | Healthcheck `php artisan health` no existe sin `spatie/laravel-health`                  | Cambiado a `php-fpm -t` (valida config FPM sin dependencias externas)                    |
| 4  | `deploy/harden.sh`         | Creaba grupo `sshusers` pero no añadía al usuario actual → bloqueo total de SSH         | Detecta `$SUDO_USER` y lo añade automáticamente al grupo antes de reiniciar SSH          |
| 5  | `bootstrap/app.php`        | El middleware `SecurityHeadersMiddleware` debe registrarse globalmente                  | Registrado con `$middleware->append()` en `withMiddleware()` de Laravel 11               |
