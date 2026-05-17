#!/usr/bin/env bash
# =============================================================================
# TelamoNet - Server Hardening Script
# "Fortaleza Digital: Blindaje y Resiliencia"
# =============================================================================
# Ejecutar como root en el servidor de producción (Ubuntu 22.04 LTS):
#   chmod +x deploy/harden.sh && sudo ./deploy/harden.sh
# =============================================================================

set -euo pipefail

# --- Colores ---
RED='\033[0;31m'; GREEN='\033[0;32m'; YELLOW='\033[1;33m'; BLUE='\033[0;34m'; NC='\033[0m'
info()    { echo -e "${BLUE}[INFO]${NC}  $1"; }
success() { echo -e "${GREEN}[OK]${NC}    $1"; }
warning() { echo -e "${YELLOW}[WARN]${NC}  $1"; }
error()   { echo -e "${RED}[ERROR]${NC} $1"; exit 1; }

# --- Verificar ejecución como root ---
[[ "$EUID" -ne 0 ]] && error "Este script debe ejecutarse como root (sudo)."

echo ""
echo -e "${BLUE}╔══════════════════════════════════════════════════════════╗${NC}"
echo -e "${BLUE}║       TelamoNet - Hardenización del Servidor             ║${NC}"
echo -e "${BLUE}╚══════════════════════════════════════════════════════════╝${NC}"
echo ""

# =============================================================================
# PASO 1 - ACTUALIZAR EL SISTEMA
# =============================================================================
info "Paso 1/6: Actualizando el sistema operativo..."
apt-get update -qq && apt-get upgrade -y -qq
apt-get install -y -qq ufw fail2ban curl unattended-upgrades
success "Sistema actualizado."

# =============================================================================
# PASO 2 - ASEGURAR SSH
# =============================================================================
info "Paso 2/6: Endureciendo la configuración de SSH..."

SSH_CONFIG="/etc/ssh/sshd_config"
SSH_PORT="${SSH_PORT:-2222}"

# Crear copia de seguridad del archivo original
cp "$SSH_CONFIG" "${SSH_CONFIG}.bak.$(date +%Y%m%d%H%M%S)"
info "Backup de sshd_config creado."

# Función para cambiar o añadir una directiva SSH
set_ssh_param() {
    local key="$1"
    local value="$2"
    if grep -qE "^#?${key}" "$SSH_CONFIG"; then
        sed -i "s|^#\?${key}.*|${key} ${value}|" "$SSH_CONFIG"
    else
        echo "${key} ${value}" >> "$SSH_CONFIG"
    fi
}

set_ssh_param "Port"                      "$SSH_PORT"
set_ssh_param "PermitRootLogin"           "no"
set_ssh_param "PasswordAuthentication"    "no"
set_ssh_param "ChallengeResponseAuthentication" "no"
set_ssh_param "MaxAuthTries"              "3"
set_ssh_param "LoginGraceTime"            "20"
set_ssh_param "AllowAgentForwarding"      "no"
set_ssh_param "AllowTcpForwarding"        "no"
set_ssh_param "X11Forwarding"             "no"
set_ssh_param "PrintLastLog"              "yes"
set_ssh_param "Protocol"                  "2"
set_ssh_param "ClientAliveInterval"       "300"
set_ssh_param "ClientAliveCountMax"       "2"
# Limitar grupos SSH (crear grupo 'sshusers' y añadir tus usuarios)
set_ssh_param "AllowGroups"               "sshusers sudo"

# Crear grupo sshusers si no existe
groupadd -f sshusers

# ¡CRÍTICO! Añadir el usuario actual al grupo sshusers ANTES de reiniciar SSH
# Si ejecutas con sudo, $SUDO_USER contiene tu usuario real
CURRENT_USER="${SUDO_USER:-$(whoami)}"
if [[ "$CURRENT_USER" != "root" ]]; then
    usermod -aG sshusers "$CURRENT_USER"
    info "Usuario '${CURRENT_USER}' añadido al grupo 'sshusers'."
else
    warning "No se detectó el usuario real (ejecutaste como root directo)."
    warning "Añade tu usuario manualmente:  usermod -aG sshusers TU_USUARIO"
    warning "Si no lo haces, NO podrás reconectarte por SSH tras reiniciar."
    read -p "¿Continuar igualmente? (y/N): " confirm
    [[ "$confirm" != "y" && "$confirm" != "Y" ]] && error "Abortado. Añade tu usuario y vuelve a ejecutar."
fi

systemctl restart ssh
success "SSH endurecido. Nuevo puerto: ${SSH_PORT}."
success "Usuario '${CURRENT_USER}' tiene acceso SSH via grupo 'sshusers'."
warning "IMPORTANTE: Abre una segunda sesión en el puerto ${SSH_PORT} antes de cerrar esta."

# =============================================================================
# PASO 3 - CONFIGURAR CORTAFUEGOS UFW
# =============================================================================
info "Paso 3/6: Configurando el cortafuegos UFW..."

# Resetear reglas existentes
ufw --force reset

# Políticas por defecto: bloquear todo lo entrante, permitir lo saliente
ufw default deny incoming
ufw default allow outgoing
ufw default deny forward

# Permitir el nuevo puerto SSH (¡CRÍTICO - antes de activar!)
ufw allow "$SSH_PORT"/tcp comment "SSH endurecido"

# Tráfico web estándar
ufw allow 80/tcp  comment "HTTP (redirige a HTTPS)"
ufw allow 443/tcp comment "HTTPS (SSL/TLS)"

# Rate limiting para SSH (máx. 6 intentos/30s por IP → bloqueo automático)
ufw limit "$SSH_PORT"/tcp comment "SSH rate limit anti-bruteforce"

# Bloquear puertos Docker que no deben ser públicos
# (DB, phpMyAdmin solo accesibles vía localhost)
ufw deny 3307/tcp  comment "MySQL - solo localhost via Docker"
ufw deny 8080/tcp  comment "phpMyAdmin - solo localhost via Docker"
ufw deny 5173/tcp  comment "Vite Dev - solo localhost"
ufw deny 3000/tcp  comment "Signaling - solo localhost"

# Activar UFW sin confirmación interactiva
ufw --force enable

success "UFW configurado y activado."
ufw status verbose

# =============================================================================
# PASO 4 - DESHABILITAR SERVICIOS INNECESARIOS
# =============================================================================
info "Paso 4/6: Deshabilitando servicios innecesarios..."

SERVICES_TO_DISABLE=(
    "cups"            # Impresoras - innecesario en servidor
    "cups-browsed"    # Exploración de impresoras en red
    "avahi-daemon"    # mDNS/descubrimiento local - innecesario en nube
    "rpcbind"         # NFS RPC - innecesario
    "nfs-server"      # Servidor NFS
    "rpc-statd"       # Monitorización NFS
    "bluetooth"       # Bluetooth
    "ModemManager"    # Gestión de módems
    "whoopsie"        # Reporte de fallos Ubuntu
    "apport"          # Reporte de crashes
)

for svc in "${SERVICES_TO_DISABLE[@]}"; do
    if systemctl list-unit-files --quiet "$svc.service" &>/dev/null; then
        systemctl stop    "$svc" 2>/dev/null || true
        systemctl disable "$svc" 2>/dev/null || true
        systemctl mask    "$svc" 2>/dev/null || true
        info "Servicio deshabilitado: $svc"
    fi
done

success "Servicios innecesarios deshabilitados."

# =============================================================================
# PASO 5 - CONFIGURAR FAIL2BAN (Protección contra fuerza bruta)
# =============================================================================
info "Paso 5/6: Configurando Fail2Ban..."

cat > /etc/fail2ban/jail.d/telamonet.conf << EOF
[DEFAULT]
bantime  = 1h
findtime = 10m
maxretry = 5
backend  = systemd

# Protección SSH
[sshd]
enabled  = true
port     = ${SSH_PORT}
logpath  = /var/log/auth.log
maxretry = 3
bantime  = 24h

# Protección Nginx - demasiadas peticiones
[nginx-http-auth]
enabled  = true
port     = http,https
logpath  = /var/log/nginx/error.log

[nginx-limit-req]
enabled  = true
port     = http,https
logpath  = /var/log/nginx/error.log
maxretry = 10

# Protección contra escaneo de puertos (portscan)
[nginx-botsearch]
enabled  = true
port     = http,https
logpath  = /var/log/nginx/access.log
maxretry = 2
EOF

systemctl enable fail2ban
systemctl restart fail2ban
success "Fail2Ban configurado y activo."

# =============================================================================
# PASO 6 - ACTUALIZACIONES DE SEGURIDAD AUTOMÁTICAS
# =============================================================================
info "Paso 6/6: Habilitando actualizaciones de seguridad automáticas..."

cat > /etc/apt/apt.conf.d/50unattended-upgrades << 'EOF'
Unattended-Upgrade::Allowed-Origins {
    "${distro_id}:${distro_codename}-security";
    "${distro_id}ESMApps:${distro_codename}-apps-security";
    "${distro_id}ESM:${distro_codename}-infra-security";
};
Unattended-Upgrade::AutoFixInterruptedDpkg "true";
Unattended-Upgrade::MinimalSteps "true";
Unattended-Upgrade::Remove-Unused-Kernel-Packages "true";
Unattended-Upgrade::Remove-Unused-Dependencies "true";
Unattended-Upgrade::Automatic-Reboot "false";
Unattended-Upgrade::Automatic-Reboot-Time "04:00";
EOF

cat > /etc/apt/apt.conf.d/20auto-upgrades << 'EOF'
APT::Periodic::Update-Package-Lists "1";
APT::Periodic::Download-Upgradeable-Packages "1";
APT::Periodic::AutocleanInterval "7";
APT::Periodic::Unattended-Upgrade "1";
EOF

systemctl enable unattended-upgrades
success "Actualizaciones de seguridad automáticas habilitadas."

# =============================================================================
# RESUMEN FINAL
# =============================================================================
echo ""
echo -e "${GREEN}╔══════════════════════════════════════════════════════════╗${NC}"
echo -e "${GREEN}║          ✅ HARDENIZACIÓN COMPLETADA CON ÉXITO           ║${NC}"
echo -e "${GREEN}╚══════════════════════════════════════════════════════════╝${NC}"
echo ""
echo -e "  ${YELLOW}Puerto SSH:${NC}     $SSH_PORT"
echo -e "  ${YELLOW}UFW Estado:${NC}     $(ufw status | head -1)"
echo -e "  ${YELLOW}Fail2Ban:${NC}       $(systemctl is-active fail2ban)"
echo -e "  ${YELLOW}Auto-updates:${NC}   $(systemctl is-active unattended-upgrades)"
echo ""
warning "PRÓXIMO PASO: Ejecuta deploy/certbot-ssl.sh para configurar HTTPS."
echo ""
