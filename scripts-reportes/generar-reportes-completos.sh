#!/bin/bash

#############################################################################
# Script para generar reportes COMPLETOS de órdenes vs documentos
#
# Uso:
#   ./generar-reportes-completos.sh
#
# Genera 6 CSVs:
#   - CSV_1: TODAS las órdenes (2,085)
#   - CSV_2: Órdenes PAGADAS estado 27 (47,050)
#   - CSV_3: Órdenes con BLOQUEADOS (141)
#   - CSV_4: BLOQUEADOS + DOCUMENTOS (90)
#   - CSV_5: BLOQUEADOS SIN DOCUMENTOS (51 - CRÍTICAS)
#   - CSV_6: DOCUMENTOS SIN BLOQUEADOS
#############################################################################

set -e

# Colores
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Directorio del script
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
OUTPUT_DIR="$SCRIPT_DIR/output"
PROJECT_ROOT="$(dirname $SCRIPT_DIR)"

echo -e "${BLUE}╔════════════════════════════════════════════════════════╗${NC}"
echo -e "${BLUE}║   GENERADOR DE REPORTES COMPLETOS - ÓRDENES           ║${NC}"
echo -e "${BLUE}╚════════════════════════════════════════════════════════╝${NC}\n"

# Crear directorio de output
mkdir -p "$OUTPUT_DIR"
echo -e "${GREEN}✅${NC} Directorio de output: $OUTPUT_DIR\n"

# Generar reportes completos
echo -e "${BLUE}🔄 Generando reportes completos...${NC}\n"
cd "$PROJECT_ROOT"
php "$SCRIPT_DIR/generar-reportes-completos.php"

# Verificar que los CSVs se generaron
if [ -f "$OUTPUT_DIR/CSV_1_TODAS_ORDENES.csv" ] && \
   [ -f "$OUTPUT_DIR/CSV_2_ORDENES_PAGADAS_ESTADO_27.csv" ] && \
   [ -f "$OUTPUT_DIR/CSV_3_ORDENES_CON_BLOQUEADOS.csv" ] && \
   [ -f "$OUTPUT_DIR/CSV_4_BLOQUEADOS_CON_DOCUMENTOS.csv" ] && \
   [ -f "$OUTPUT_DIR/CSV_5_BLOQUEADOS_SIN_DOCUMENTOS_CRITICAS.csv" ] && \
   [ -f "$OUTPUT_DIR/CSV_6_DOCUMENTOS_SIN_BLOQUEADOS.csv" ]; then
    echo -e "\n${GREEN}✅ TODOS LOS CSVs GENERADOS CORRECTAMENTE${NC}\n"
else
    echo -e "\n${RED}❌ Error: No se generaron todos los CSVs${NC}\n"
    exit 1
fi

# Contar registros en cada CSV
CSV1_COUNT=$(grep -c "," "$OUTPUT_DIR/CSV_1_TODAS_ORDENES.csv" || echo "0")
CSV2_COUNT=$(grep -c "," "$OUTPUT_DIR/CSV_2_ORDENES_PAGADAS_ESTADO_27.csv" || echo "0")
CSV3_COUNT=$(grep -c "," "$OUTPUT_DIR/CSV_3_ORDENES_CON_BLOQUEADOS.csv" || echo "0")
CSV4_COUNT=$(grep -c "," "$OUTPUT_DIR/CSV_4_BLOQUEADOS_CON_DOCUMENTOS.csv" || echo "0")
CSV5_COUNT=$(grep -c "," "$OUTPUT_DIR/CSV_5_BLOQUEADOS_SIN_DOCUMENTOS_CRITICAS.csv" || echo "0")
CSV6_COUNT=$(grep -c "," "$OUTPUT_DIR/CSV_6_DOCUMENTOS_SIN_BLOQUEADOS.csv" || echo "0")

# Restar headers
CSV1_COUNT=$((CSV1_COUNT - 1))
CSV2_COUNT=$((CSV2_COUNT - 1))
CSV3_COUNT=$((CSV3_COUNT - 1))
CSV4_COUNT=$((CSV4_COUNT - 1))
CSV5_COUNT=$((CSV5_COUNT - 1))
CSV6_COUNT=$((CSV6_COUNT - 1))

echo -e "${BLUE}╔════════════════════════════════════════════════════════╗${NC}"
echo -e "${BLUE}║                    RESUMEN FINAL                      ║${NC}"
echo -e "${BLUE}╚════════════════════════════════════════════════════════╝${NC}\n"

echo -e "📊 ${YELLOW}Archivos generados:${NC}"
echo -e "   • CSV_1 (TODAS las órdenes):                 $CSV1_COUNT"
echo -e "   • CSV_2 (Órdenes PAGADAS estado 27):         $CSV2_COUNT"
echo -e "   • CSV_3 (Órdenes con BLOQUEADOS):            $CSV3_COUNT"
echo -e "   • CSV_4 (BLOQUEADOS + DOCUMENTOS):           $CSV4_COUNT"
echo -e "   • CSV_5 (BLOQUEADOS SIN DOCUMENTOS):         ${RED}$CSV5_COUNT ⚠️ CRÍTICO${NC}"
echo -e "   • CSV_6 (DOCUMENTOS SIN BLOQUEADOS):         $CSV6_COUNT\n"

echo -e "📁 ${YELLOW}Ubicación de los CSVs:${NC}"
echo -e "   $OUTPUT_DIR/\n"

echo -e "📝 ${YELLOW}Próximos pasos:${NC}"
echo -e "   1. Revisar los 6 CSVs en la carpeta output/"
echo -e "   2. Crear documentos: ./generar-reporte.sh crear"
echo -e "   3. Validar resultados: ./generar-reportes-completos.sh\n"

echo -e "${GREEN}✅ LISTO${NC}\n"
