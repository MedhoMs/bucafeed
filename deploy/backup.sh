#!/usr/bin/env bash
# =============================================================================
# TelamoNet - Sistema de Backups Automatizados y Remotos
# =============================================================================
# Ejecutar directamente o via cron:
#   0 3 * * * /ruta/al/proyecto/deploy/backup.sh >> /var/log/telamonet-backup.log 2>&1
#
# Variables de entorno requeridas (en /etc/environment o .env del servidor):
#   BACKUP_REMOTE_HOST   — IP o hostname del servidor remoto (ej. backup.example.com)
#   BACKUP_REMOTE_USER   — Usuario SSH en el servidor remoto
#   BACKUP_REMOTE_PATH   — Ruta en el servidor remoto (ej. /backups/telamonet)
#   BACKUP_ENCRYPTION_KEY — Clave para cifrar el backup (openssl)
#   DB_ROOT_PASSWORD     — Contraseña root de MySQL
#   DB_DATABASE          — Nombre de la base de datos
# =============================================================================

set -euo pipefail

# --- Configuración ---
PROJECT_NAME="telamonet"
TIMESTAMP=$(date +"%Y%m%d_%H%M%S")
BACKUP_DIR="/tmp/${PROJECT_NAME}_backups"
BACKUP_FILE="${PROJECT_NAME}_backup_${TIMESTAMP}"
RETENTION_DAYS="${BACKUP_RETENTION_DAYS:-7}"      # Mantener backups N días
LOG_FILE="/var/log/${PROJECT_NAME}-backup.log"

# Contenedores Docker
DB_CONTAINER="${DB_CONTAINER:-telamonet_db}"
STORAGE_PATH="${STORAGE_PATH:-./backend/storage/app}"

# Colores
RED='\033[0;31m'; GREEN='\033[0;32m'; YELLOW='\033[1;33m'; BLUE='\033[0;34m'; NC='\033[0m'
ts()      { date +"[%Y-%m-%d %H:%M:%S]"; }
info()    { echo -e "$(ts) ${BLUE}[INFO]${NC}  $1" | tee -a "$LOG_FILE"; }
success() { echo -e "$(ts) ${GREEN}[OK]${NC}    $1" | tee -a "$LOG_FILE"; }
warning() { echo -e "$(ts) ${YELLOW}[WARN]${NC}  $1" | tee -a "$LOG_FILE"; }
error()   { echo -e "$(ts) ${RED}[ERROR]${NC} $1" | tee -a "$LOG_FILE"; exit 1; }

echo "" | tee -a "$LOG_FILE"
echo "$(ts) ========================================================" | tee -a "$LOG_FILE"
echo "$(ts)   TelamoNet Backup - ${TIMESTAMP}" | tee -a "$LOG_FILE"
echo "$(ts) ========================================================" | tee -a "$LOG_FILE"

# =============================================================================
# PASO 1 - PREPARAR DIRECTORIO DE BACKUP
# =============================================================================
info "Preparando directorio de backup temporal..."
mkdir -p "${BACKUP_DIR}/${BACKUP_FILE}"
cd "${BACKUP_DIR}/${BACKUP_FILE}"

# =============================================================================
# PASO 2 - DUMP DE BASE DE DATOS MYSQL
# =============================================================================
info "Volcando base de datos MySQL..."

# Verificar que el contenedor DB está corriendo
if ! docker ps --format '{{.Names}}' | grep -q "^${DB_CONTAINER}$"; then
    error "El contenedor ${DB_CONTAINER} no está corriendo."
fi

docker exec "${DB_CONTAINER}" \
    mysqldump \
        --user=root \
        --password="${DB_ROOT_PASSWORD:-root}" \
        --single-transaction \
        --routines \
        --triggers \
        --events \
        --hex-blob \
        "${DB_DATABASE:-telamonet}" \
    > "db_dump_${TIMESTAMP}.sql"

# Comprimir el dump
gzip "db_dump_${TIMESTAMP}.sql"
success "Dump MySQL creado: db_dump_${TIMESTAMP}.sql.gz ($(du -sh "db_dump_${TIMESTAMP}.sql.gz" | cut -f1))"

# =============================================================================
# PASO 3 - BACKUP DE STORAGE (imágenes, archivos subidos)
# =============================================================================
info "Respaldando storage de Laravel..."

if [[ -d "${STORAGE_PATH}" ]]; then
    tar -czf "storage_${TIMESTAMP}.tar.gz" -C "$(dirname "${STORAGE_PATH}")" "$(basename "${STORAGE_PATH}")"
    success "Storage respaldado: storage_${TIMESTAMP}.tar.gz ($(du -sh "storage_${TIMESTAMP}.tar.gz" | cut -f1))"
else
    warning "Directorio de storage no encontrado: ${STORAGE_PATH}"
fi

# =============================================================================
# PASO 4 - BACKUP DE CONFIGURACIÓN (.env files, nginx.conf, etc.)
# =============================================================================
info "Respaldando archivos de configuración..."

# Crear directorio temporal para configs
CONFIG_DIR="config_backup"
mkdir -p "$CONFIG_DIR"

# Copiar archivos de configuración (SIN credenciales sensibles al repo)
CONFIGS=(
    "../../.env"
    "../../backend/.env"
    "../../docker-compose.yml"
    "../../docker-compose.ha.yml"
    "../../nginx.conf"
    "../../nginx.prod.conf"
    "../../deploy/nginx.ha.conf"
)

for cfg in "${CONFIGS[@]}"; do
    if [[ -f "$cfg" ]]; then
        cp "$cfg" "$CONFIG_DIR/"
        info "Config respaldado: $cfg"
    fi
done

tar -czf "configs_${TIMESTAMP}.tar.gz" "$CONFIG_DIR/"
rm -rf "$CONFIG_DIR"
success "Configuraciones respaldadas."

# =============================================================================
# PASO 5 - EMPAQUETAR TODO EN UN ÚNICO ARCHIVO
# =============================================================================
info "Empaquetando todo el backup..."
cd "${BACKUP_DIR}"
tar -czf "${BACKUP_FILE}.tar.gz" "${BACKUP_FILE}/"
rm -rf "${BACKUP_FILE}/"

BACKUP_SIZE=$(du -sh "${BACKUP_DIR}/${BACKUP_FILE}.tar.gz" | cut -f1)
info "Backup empaquetado: ${BACKUP_FILE}.tar.gz (${BACKUP_SIZE})"

# =============================================================================
# PASO 6 - CIFRAR EL BACKUP (AES-256)
# =============================================================================
if [[ -n "${BACKUP_ENCRYPTION_KEY:-}" ]]; then
    info "Cifrando backup con AES-256..."
    openssl enc -aes-256-cbc -pbkdf2 -iter 100000 \
        -in  "${BACKUP_DIR}/${BACKUP_FILE}.tar.gz" \
        -out "${BACKUP_DIR}/${BACKUP_FILE}.tar.gz.enc" \
        -pass "pass:${BACKUP_ENCRYPTION_KEY}"
    rm "${BACKUP_DIR}/${BACKUP_FILE}.tar.gz"
    FINAL_FILE="${BACKUP_FILE}.tar.gz.enc"
    success "Backup cifrado: ${FINAL_FILE}"
else
    warning "BACKUP_ENCRYPTION_KEY no definida. El backup NO está cifrado."
    FINAL_FILE="${BACKUP_FILE}.tar.gz"
fi

# =============================================================================
# PASO 7 - TRANSFERIR A SERVIDOR REMOTO (SCP/RSYNC)
# =============================================================================
if [[ -n "${BACKUP_REMOTE_HOST:-}" ]]; then
    info "Transfiriendo backup al servidor remoto ${BACKUP_REMOTE_HOST}..."

    # Asegurar que el directorio remoto existe
    ssh -o StrictHostKeyChecking=no \
        "${BACKUP_REMOTE_USER:-backup}@${BACKUP_REMOTE_HOST}" \
        "mkdir -p ${BACKUP_REMOTE_PATH:-/backups/${PROJECT_NAME}}"

    # Transferir con rsync (reintentos automáticos, compresión)
    rsync -avz --progress \
        --rsh="ssh -o StrictHostKeyChecking=no" \
        "${BACKUP_DIR}/${FINAL_FILE}" \
        "${BACKUP_REMOTE_USER:-backup}@${BACKUP_REMOTE_HOST}:${BACKUP_REMOTE_PATH:-/backups/${PROJECT_NAME}}/"

    success "Backup transferido al servidor remoto."

    # Eliminar backups remotos más antiguos de N días
    info "Limpiando backups remotos de más de ${RETENTION_DAYS} días..."
    ssh -o StrictHostKeyChecking=no \
        "${BACKUP_REMOTE_USER:-backup}@${BACKUP_REMOTE_HOST}" \
        "find ${BACKUP_REMOTE_PATH:-/backups/${PROJECT_NAME}}/ -name '${PROJECT_NAME}_backup_*' -mtime +${RETENTION_DAYS} -delete"
    success "Limpieza remota completada."
else
    warning "BACKUP_REMOTE_HOST no definida. El backup solo se ha guardado localmente."
fi

# =============================================================================
# PASO 8 - VERIFICAR INTEGRIDAD DEL BACKUP LOCAL
# =============================================================================
info "Verificando integridad del backup..."
if [[ "${FINAL_FILE}" == *.enc ]]; then
    # Verificar que el archivo cifrado no está vacío
    [[ -s "${BACKUP_DIR}/${FINAL_FILE}" ]] && success "Backup cifrado verificado ($(du -sh "${BACKUP_DIR}/${FINAL_FILE}" | cut -f1))."
else
    tar -tzf "${BACKUP_DIR}/${FINAL_FILE}" > /dev/null && success "Integridad del backup verificada."
fi

# =============================================================================
# PASO 9 - LIMPIEZA LOCAL (eliminar backups locales de más de N días)
# =============================================================================
info "Limpiando backups locales de más de ${RETENTION_DAYS} días..."
find "${BACKUP_DIR}/" -name "${PROJECT_NAME}_backup_*" -mtime "+${RETENTION_DAYS}" -delete
success "Limpieza local completada."

# =============================================================================
# RESUMEN
# =============================================================================
echo "" | tee -a "$LOG_FILE"
echo "$(ts) ✅ BACKUP COMPLETADO CON ÉXITO" | tee -a "$LOG_FILE"
echo "$(ts)    Archivo:  ${BACKUP_DIR}/${FINAL_FILE}" | tee -a "$LOG_FILE"
echo "$(ts)    Tamaño:   $(du -sh "${BACKUP_DIR}/${FINAL_FILE}" | cut -f1)" | tee -a "$LOG_FILE"
echo "$(ts)    Remoto:   ${BACKUP_REMOTE_HOST:-No configurado}" | tee -a "$LOG_FILE"
echo "$(ts)    Log:      ${LOG_FILE}" | tee -a "$LOG_FILE"
echo "" | tee -a "$LOG_FILE"
