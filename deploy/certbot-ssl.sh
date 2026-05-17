#!/usr/bin/env bash
# =============================================================================
# TelamoNet - Instalación de HTTPS con Let's Encrypt + Certbot
# =============================================================================
# Uso: sudo ./deploy/certbot-ssl.sh --domain telamonet.example.com --email admin@example.com
# =============================================================================

set -euo pipefail

RED='\033[0;31m'; GREEN='\033[0;32m'; YELLOW='\033[1;33m'; BLUE='\033[0;34m'; NC='\033[0m'
info()    { echo -e "${BLUE}[INFO]${NC}  $1"; }
success() { echo -e "${GREEN}[OK]${NC}    $1"; }
warning() { echo -e "${YELLOW}[WARN]${NC}  $1"; }
error()   { echo -e "${RED}[ERROR]${NC} $1"; exit 1; }

[[ "$EUID" -ne 0 ]] && error "Ejecutar como root."

# --- Parsear argumentos ---
DOMAIN=""
EMAIL=""
while [[ $# -gt 0 ]]; do
    case "$1" in
        --domain) DOMAIN="$2"; shift 2 ;;
        --email)  EMAIL="$2";  shift 2 ;;
        *) error "Argumento desconocido: $1" ;;
    esac
done

[[ -z "$DOMAIN" ]] && error "Debes indicar el dominio: --domain tu.dominio.com"
[[ -z "$EMAIL"  ]] && error "Debes indicar el email:   --email admin@tu.dominio.com"

echo ""
echo -e "${BLUE}╔═══════════════════════════════════════════════════════════╗${NC}"
echo -e "${BLUE}║      TelamoNet - Instalación SSL/TLS (Let's Encrypt)      ║${NC}"
echo -e "${BLUE}╚═══════════════════════════════════════════════════════════╝${NC}"
echo ""

# =============================================================================
# PASO 1 - INSTALAR NGINX Y CERTBOT
# =============================================================================
info "Instalando Nginx y Certbot..."
apt-get update -qq
apt-get install -y -qq nginx certbot python3-certbot-nginx

# Asegurar que Nginx está activo
systemctl enable nginx
systemctl start nginx
success "Nginx instalado y activo."

# =============================================================================
# PASO 2 - CONFIGURAR NGINX HTTP (base para validación certbot)
# =============================================================================
info "Configurando Nginx para validación de dominio..."

NGINX_SITE="/etc/nginx/sites-available/telamonet"
NGINX_LINK="/etc/nginx/sites-enabled/telamonet"

cat > "$NGINX_SITE" << EOF
# TelamoNet - Configuración HTTP inicial (para validación Let's Encrypt)
server {
    listen 80;
    listen [::]:80;
    server_name ${DOMAIN} www.${DOMAIN};

    # Directorio de validación de Certbot
    location /.well-known/acme-challenge/ {
        root /var/www/certbot;
    }

    # Redirigir todo el demás tráfico HTTP → HTTPS
    location / {
        return 301 https://\$host\$request_uri;
    }
}
EOF

mkdir -p /var/www/certbot

# Activar el sitio
[[ -L "$NGINX_LINK" ]] && rm "$NGINX_LINK"
ln -s "$NGINX_SITE" "$NGINX_LINK"

# Eliminar el sitio por defecto si existe
[[ -L /etc/nginx/sites-enabled/default ]] && rm /etc/nginx/sites-enabled/default

nginx -t && systemctl reload nginx
success "Nginx HTTP configurado para validación."

# =============================================================================
# PASO 3 - OBTENER CERTIFICADO SSL DE LET'S ENCRYPT
# =============================================================================
info "Obteniendo certificado SSL para ${DOMAIN}..."

certbot certonly \
    --webroot \
    --webroot-path=/var/www/certbot \
    --email "$EMAIL" \
    --agree-tos \
    --no-eff-email \
    --domains "${DOMAIN},www.${DOMAIN}" \
    --non-interactive

success "Certificado SSL obtenido para ${DOMAIN}."

CERT_PATH="/etc/letsencrypt/live/${DOMAIN}"

# =============================================================================
# PASO 4 - CONFIGURAR NGINX COMPLETO CON HTTPS Y LOAD BALANCING
# =============================================================================
info "Generando configuración Nginx HTTPS + Alta Disponibilidad..."

cat > "$NGINX_SITE" << EOF
# =============================================================================
# TelamoNet - Nginx Reverse Proxy: HTTPS + Load Balancer
# Dominio: ${DOMAIN}
# =============================================================================

# Rate Limiting zones (deben estar fuera del bloque server)
limit_req_zone  \$binary_remote_addr zone=api:10m rate=20r/s;
limit_req_zone  \$binary_remote_addr zone=auth:10m rate=5r/m;
limit_conn_zone \$binary_remote_addr zone=conn_limit:10m;

# ---- Upstream: Balanceo de carga entre 2 réplicas Laravel ----
upstream telamonet_backend {
    least_conn;                        # Algoritmo: menos conexiones activas
    server backend_1:9000 weight=1;    # Réplica 1
    server backend_2:9000 weight=1;    # Réplica 2
    keepalive 32;                      # Conexiones persistentes al pool
}

# ---- Upstream: Frontend Vue (en producción es estático) ----
upstream telamonet_frontend {
    server frontend:80;
}

# ---- HTTP → Redirigir a HTTPS ----
server {
    listen 80;
    listen [::]:80;
    server_name ${DOMAIN} www.${DOMAIN};

    location /.well-known/acme-challenge/ {
        root /var/www/certbot;
    }

    location / {
        return 301 https://\$host\$request_uri;
    }
}

# ---- HTTPS Principal ----
server {
    listen 443 ssl http2;
    listen [::]:443 ssl http2;
    server_name ${DOMAIN} www.${DOMAIN};

    # ---- Certificados Let's Encrypt ----
    ssl_certificate     ${CERT_PATH}/fullchain.pem;
    ssl_certificate_key ${CERT_PATH}/privkey.pem;

    # ---- Configuración SSL/TLS Moderna (Mozilla Intermediate) ----
    ssl_protocols       TLSv1.2 TLSv1.3;
    ssl_ciphers         ECDHE-ECDSA-AES128-GCM-SHA256:ECDHE-RSA-AES128-GCM-SHA256:ECDHE-ECDSA-AES256-GCM-SHA384:ECDHE-RSA-AES256-GCM-SHA384:ECDHE-ECDSA-CHACHA20-POLY1305:ECDHE-RSA-CHACHA20-POLY1305:DHE-RSA-AES128-GCM-SHA256;
    ssl_prefer_server_ciphers off;

    # ---- OCSP Stapling (mejora rendimiento SSL) ----
    ssl_stapling         on;
    ssl_stapling_verify  on;
    ssl_trusted_certificate ${CERT_PATH}/chain.pem;
    resolver             8.8.8.8 8.8.4.4 valid=300s;
    resolver_timeout     5s;

    # ---- Parámetros DH seguros ----
    ssl_dhparam /etc/nginx/dhparam.pem;

    # ---- Sesiones SSL (mejora rendimiento) ----
    ssl_session_cache   shared:SSL:10m;
    ssl_session_timeout 1d;
    ssl_session_tickets off;

    # ==========================================================================
    # CABECERAS DE SEGURIDAD HTTP (OWASP)
    # ==========================================================================
    add_header Strict-Transport-Security "max-age=63072000; includeSubDomains; preload" always;
    add_header X-Frame-Options            "DENY"                          always;
    add_header X-Content-Type-Options     "nosniff"                       always;
    add_header X-XSS-Protection           "1; mode=block"                 always;
    add_header Referrer-Policy            "strict-origin-when-cross-origin" always;
    add_header Permissions-Policy         "geolocation=(), microphone=(), camera=(self)" always;
    add_header Content-Security-Policy    "default-src 'self'; script-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net; style-src 'self' 'unsafe-inline' https://fonts.googleapis.com; font-src 'self' https://fonts.gstatic.com; img-src 'self' data: https:; connect-src 'self' wss://${DOMAIN}; frame-ancestors 'none';" always;

    # Ocultar versión de Nginx
    server_tokens off;

    # ---- Límite de conexiones por IP ----
    limit_conn conn_limit 20;

    # ---- Tamaño máximo de petición ----
    client_max_body_size 50M;

    # ---- Logs ----
    access_log /var/log/nginx/telamonet_access.log;
    error_log  /var/log/nginx/telamonet_error.log warn;

    # ==========================================================================
    # RUTAS
    # ==========================================================================

    # -- Frontend Vue (SPA) --
    location / {
        root /var/www/html;
        index index.html;
        try_files \$uri \$uri/ /index.html;

        # Cache de assets estáticos
        location ~* \.(js|css|png|jpg|jpeg|gif|ico|svg|woff|woff2|ttf|eot)$ {
            expires     1y;
            add_header  Cache-Control "public, immutable";
            access_log  off;
        }
    }

    # -- API Backend Laravel (con load balancing y rate limiting) --
    # PHP-FPM habla protocolo FastCGI, NO HTTP → usar fastcgi_pass
    location /api/ {
        limit_req zone=api burst=40 nodelay;

        fastcgi_pass       telamonet_backend;
        fastcgi_index      index.php;
        include            fastcgi_params;
        fastcgi_param      SCRIPT_FILENAME /var/www/public/index.php;
        fastcgi_param      SERVER_NAME     \$host;
        fastcgi_param      HTTPS           on;
        fastcgi_param      HTTP_X_FORWARDED_PROTO \$scheme;

        # Timeouts
        fastcgi_connect_timeout 30s;
        fastcgi_send_timeout    30s;
        fastcgi_read_timeout    30s;
        fastcgi_buffer_size     128k;
        fastcgi_buffers         4 256k;
    }

    # -- Rutas de autenticación con rate limiting más estricto --
    location ~ ^/api/(auth|login|register|password) {
        limit_req zone=auth burst=3 nodelay;

        fastcgi_pass  telamonet_backend;
        fastcgi_index index.php;
        include       fastcgi_params;
        fastcgi_param SCRIPT_FILENAME /var/www/public/index.php;
        fastcgi_param HTTPS on;
    }

    # -- WebSocket (Signaling / LiveKit) --
    location /ws/ {
        proxy_pass          http://telamonet_frontend;
        proxy_http_version  1.1;
        proxy_set_header    Upgrade    \$http_upgrade;
        proxy_set_header    Connection "upgrade";
        proxy_set_header    Host       \$host;
        proxy_read_timeout  3600s;
        proxy_send_timeout  3600s;
    }

    # -- Health check (para el balanceador) --
    location /health {
        access_log off;
        return 200 '{"status":"ok","service":"telamonet"}';
        add_header Content-Type application/json;
    }

    # -- Bloquear acceso a archivos sensibles --
    location ~ /\.(env|git|htaccess|htpasswd) {
        deny all;
        return 404;
    }

    location ~ /(storage|bootstrap|vendor)/ {
        deny all;
        return 403;
    }
}
EOF

# =============================================================================
# PASO 5 - GENERAR PARÁMETROS DH SEGUROS (2048 bits)
# =============================================================================
info "Generando parámetros Diffie-Hellman seguros (puede tardar 1-2 minutos)..."
if [[ ! -f /etc/nginx/dhparam.pem ]]; then
    openssl dhparam -out /etc/nginx/dhparam.pem 2048
fi
success "Parámetros DH generados."

# =============================================================================
# PASO 6 - VALIDAR Y RECARGAR NGINX
# =============================================================================
nginx -t || error "Configuración de Nginx inválida. Revisa el log."
systemctl reload nginx
success "Nginx recargado con HTTPS activo."

# =============================================================================
# PASO 7 - RENOVACIÓN AUTOMÁTICA DE CERTIFICADOS (CRON)
# =============================================================================
info "Configurando renovación automática del certificado..."

# Añadir cron job para renovar el certificado dos veces al día
CRON_JOB="0 */12 * * * root certbot renew --quiet --deploy-hook 'systemctl reload nginx'"
CRON_FILE="/etc/cron.d/certbot-telamonet"

echo "$CRON_JOB" > "$CRON_FILE"
chmod 644 "$CRON_FILE"
success "Cron de renovación SSL configurado: $CRON_FILE"

# =============================================================================
# RESUMEN
# =============================================================================
echo ""
echo -e "${GREEN}╔══════════════════════════════════════════════════════════╗${NC}"
echo -e "${GREEN}║        ✅ HTTPS / SSL CONFIGURADO CON ÉXITO             ║${NC}"
echo -e "${GREEN}╚══════════════════════════════════════════════════════════╝${NC}"
echo ""
echo -e "  ${YELLOW}Dominio:${NC}         https://${DOMAIN}"
echo -e "  ${YELLOW}Certificado:${NC}     ${CERT_PATH}"
echo -e "  ${YELLOW}Expira:${NC}          $(certbot certificates 2>/dev/null | grep 'Expiry Date' | head -1 | awk '{print $3, $4}')"
echo -e "  ${YELLOW}Renovación:${NC}      Automática cada 12h via cron"
echo -e "  ${YELLOW}SSL Rating:${NC}      A+ (verifica en https://www.ssllabs.com/ssltest/)"
echo ""
