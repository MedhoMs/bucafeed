#!/usr/bin/env bash
# =============================================================================
# TelamoNet - Análisis de Vulnerabilidades (OWASP ZAP) + Parcheo
# =============================================================================
# Requisitos: Docker instalado en el servidor/máquina local
# Uso: chmod +x deploy/owasp-scan.sh && ./deploy/owasp-scan.sh
# =============================================================================

set -euo pipefail

RED='\033[0;31m'; GREEN='\033[0;32m'; YELLOW='\033[1;33m'; BLUE='\033[0;34m'; NC='\033[0m'
info()    { echo -e "${BLUE}[INFO]${NC}  $1"; }
success() { echo -e "${GREEN}[OK]${NC}    $1"; }
warning() { echo -e "${YELLOW}[WARN]${NC}  $1"; }
error()   { echo -e "${RED}[ERROR]${NC} $1"; }

TARGET_URL="${1:-http://localhost:8000}"
REPORT_DIR="./security-reports"
TIMESTAMP=$(date +"%Y%m%d_%H%M%S")
REPORT_HTML="${REPORT_DIR}/owasp_zap_report_${TIMESTAMP}.html"
REPORT_JSON="${REPORT_DIR}/owasp_zap_report_${TIMESTAMP}.json"

echo ""
echo -e "${RED}╔══════════════════════════════════════════════════════════╗${NC}"
echo -e "${RED}║    TelamoNet - Análisis de Vulnerabilidades OWASP ZAP   ║${NC}"
echo -e "${RED}╚══════════════════════════════════════════════════════════╝${NC}"
echo ""
info "Target: ${TARGET_URL}"
info "Reportes: ${REPORT_DIR}/"
echo ""

mkdir -p "$REPORT_DIR"

# =============================================================================
# ANÁLISIS CON OWASP ZAP (Docker)
# Modo: Baseline scan - escaneo pasivo sin modificar la aplicación
# =============================================================================
info "Ejecutando OWASP ZAP Baseline Scan..."
warning "Esto puede tardar varios minutos..."

# El código de salida 2 indica alertas encontradas (no error del script)
docker run --rm \
    -v "$(pwd)/${REPORT_DIR}:/zap/wrk:rw" \
    --add-host="host.docker.internal:host-gateway" \
    ghcr.io/zaproxy/zaproxy:stable \
    zap-baseline.py \
        -t "${TARGET_URL}" \
        -r "owasp_zap_report_${TIMESTAMP}.html" \
        -J "owasp_zap_report_${TIMESTAMP}.json" \
        -l WARN \
        --auto \
    || true   # No fallar aunque ZAP encuentre vulnerabilidades

echo ""
success "Análisis OWASP ZAP completado."
info "Reporte HTML: ${REPORT_HTML}"
info "Reporte JSON: ${REPORT_JSON}"

# =============================================================================
# PARSEAR RESULTADOS JSON Y MOSTRAR RESUMEN
# =============================================================================
if [[ -f "$REPORT_JSON" ]] && command -v python3 &>/dev/null; then
    echo ""
    echo -e "${YELLOW}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
    echo -e "${YELLOW}RESUMEN DE VULNERABILIDADES ENCONTRADAS${NC}"
    echo -e "${YELLOW}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"

    # Exportar la ruta como variable de entorno para que Python la lea
    export ZAP_REPORT_PATH="$REPORT_JSON"
    python3 << 'PYEOF'
import json, os

report_path = os.environ["ZAP_REPORT_PATH"]

with open(report_path) as f:
    data = json.load(f)

risk_counts = {"High": 0, "Medium": 0, "Low": 0, "Informational": 0}
risk_colors = {"High": "\033[0;31m", "Medium": "\033[1;33m", "Low": "\033[0;34m", "Informational": "\033[0;32m"}
NC = "\033[0m"

for site in data.get("site", []):
    for alert in site.get("alerts", []):
        risk = alert.get("riskdesc", "").split(" ")[0]
        count = int(alert.get("count", 1))
        if risk in risk_counts:
            risk_counts[risk] += count
            color = risk_colors.get(risk, NC)
            print(f"  {color}[{risk}]{NC} {alert.get('alert', 'Unknown')} ({count} instancias)")

print()
print(f"  Total - High: {risk_counts['High']} | Medium: {risk_counts['Medium']} | Low: {risk_counts['Low']} | Info: {risk_counts['Informational']}")
PYEOF
    true  # No fallar si el parseo falla
fi

echo ""
info "Abre el reporte HTML para el análisis completo:"
info "  ${REPORT_HTML}"
echo ""
