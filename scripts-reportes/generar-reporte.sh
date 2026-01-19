#!/bin/bash

#############################################################################
# Script para generar CSVs de Órdenes Pagadas vs Documentos
#
# Uso:
#   ./generar-reporte.sh              # Generar todos los CSVs
#   ./generar-reporte.sh crear        # Generar CSVs + crear documentos
#   ./generar-reporte.sh limpiar      # Generar + limpiar documentos orfandos
#
# Genera 4 CSVs:
#   - CSV_1: Órdenes con bloqueados (35)
#   - CSV_2: Órdenes con documentos (641)
#   - CSV_3: Órdenes bloqueadas sin documentos (4) ⚠️ CRÍTICO
#   - CSV_4: Documentos orfandos (1,444)
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
PROJECT_ROOT="$(dirname $(dirname $SCRIPT_DIR))"

echo -e "${BLUE}╔════════════════════════════════════════════════════════╗${NC}"
echo -e "${BLUE}║   GENERADOR DE REPORTES - ÓRDENES VS DOCUMENTOS       ║${NC}"
echo -e "${BLUE}╚════════════════════════════════════════════════════════╝${NC}\n"

# Crear directorio de output
mkdir -p "$OUTPUT_DIR"
echo -e "${GREEN}✅${NC} Directorio de output: $OUTPUT_DIR\n"

# Generar CSVs
echo -e "${BLUE}🔄 Generando CSVs...${NC}\n"
cd "$PROJECT_ROOT"
php "$SCRIPT_DIR/generar-csvs-ordenes.php"

# Verificar que los CSVs se generaron
if [ -f "$OUTPUT_DIR/CSV_1_ORDENES_PAGADAS_CON_BLOQUEADOS.csv" ] && \
   [ -f "$OUTPUT_DIR/CSV_2_ORDENES_PAGADAS_CON_DOCUMENTOS.csv" ] && \
   [ -f "$OUTPUT_DIR/CSV_3_ORDENES_PAGADAS_SIN_DOCUMENTOS_CON_BLOQUEADOS.csv" ] && \
   [ -f "$OUTPUT_DIR/CSV_4_DOCUMENTOS_ORFANDOS.csv" ]; then
    echo -e "\n${GREEN}✅ TODOS LOS CSVs GENERADOS CORRECTAMENTE${NC}\n"
else
    echo -e "\n${RED}❌ Error: No se generaron todos los CSVs${NC}\n"
    exit 1
fi

# Mostrar opción de acción
ACTION="${1:-}"

case "$ACTION" in
    crear)
        echo -e "${YELLOW}⚠️ OPCIÓN: Crear documentos para órdenes con bloqueados${NC}\n"

        # Contar órdenes en CSV_3
        ORDENES_CRITICAS=$(grep -c "," "$OUTPUT_DIR/CSV_3_ORDENES_PAGADAS_SIN_DOCUMENTOS_CON_BLOQUEADOS.csv" || echo "0")
        ORDENES_CRITICAS=$((ORDENES_CRITICAS - 1)) # Restar el header

        if [ "$ORDENES_CRITICAS" -gt 0 ]; then
            echo -e "${YELLOW}Órdenes que necesitan documentos: $ORDENES_CRITICAS${NC}\n"
            echo -e "Ejecutando: ${BLUE}php artisan app:create-blocked-product-documents --force --limit=$ORDENES_CRITICAS${NC}\n"
            php artisan app:create-blocked-product-documents --force --limit=$ORDENES_CRITICAS
            echo -e "\n${GREEN}✅ Documentos creados${NC}\n"
        else
            echo -e "${GREEN}ℹ️ No hay órdenes que necesiten documentos${NC}\n"
        fi
        ;;

    limpiar)
        echo -e "${YELLOW}⚠️ OPCIÓN: Limpiar documentos orfandos${NC}\n"

        # Contar documentos orfandos
        ORFANDOS=$(grep -c "," "$OUTPUT_DIR/CSV_4_DOCUMENTOS_ORFANDOS.csv" || echo "0")
        ORFANDOS=$((ORFANDOS - 1)) # Restar el header

        echo -e "${YELLOW}Documentos orfandos encontrados: $ORFANDOS${NC}\n"
        echo -e "${RED}⚠️ CUIDADO: Esta operación es destructiva${NC}"
        echo -e "Los documentos serán ELIMINADOS (excepto los con status=5)\n"

        read -p "¿Estás seguro? (s/n): " -n 1 -r
        echo
        if [[ $REPLY =~ ^[Ss]$ ]]; then
            echo -e "\nEjecutando: ${BLUE}php artisan app:validate-and-cleanup-documents --force${NC}\n"
            php artisan app:validate-and-cleanup-documents --force
            echo -e "\n${GREEN}✅ Limpieza completada${NC}\n"
        else
            echo -e "${YELLOW}Operación cancelada${NC}\n"
        fi
        ;;

    *)
        # Solo generar CSVs (opción por defecto)
        echo -e "${GREEN}✅ CSVs generados correctamente${NC}\n"
        echo -e "${BLUE}Opciones adicionales:${NC}"
        echo -e "  ./generar-reporte.sh crear    - Crear 4 documentos faltantes"
        echo -e "  ./generar-reporte.sh limpiar  - Limpiar documentos orfandos\n"
        ;;
esac

# Mostrar resumen final
echo -e "${BLUE}╔════════════════════════════════════════════════════════╗${NC}"
echo -e "${BLUE}║                    RESUMEN FINAL                      ║${NC}"
echo -e "${BLUE}╚════════════════════════════════════════════════════════╝${NC}\n"

CSV1_COUNT=$(grep -c "," "$OUTPUT_DIR/CSV_1_ORDENES_PAGADAS_CON_BLOQUEADOS.csv" || echo "0")
CSV2_COUNT=$(grep -c "," "$OUTPUT_DIR/CSV_2_ORDENES_PAGADAS_CON_DOCUMENTOS.csv" || echo "0")
CSV3_COUNT=$(grep -c "," "$OUTPUT_DIR/CSV_3_ORDENES_PAGADAS_SIN_DOCUMENTOS_CON_BLOQUEADOS.csv" || echo "0")
CSV4_COUNT=$(grep -c "," "$OUTPUT_DIR/CSV_4_DOCUMENTOS_ORFANDOS.csv" || echo "0")

# Restar headers
CSV1_COUNT=$((CSV1_COUNT - 1))
CSV2_COUNT=$((CSV2_COUNT - 1))
CSV3_COUNT=$((CSV3_COUNT - 1))
CSV4_COUNT=$((CSV4_COUNT - 1))

echo -e "📊 ${YELLOW}Archivos generados:${NC}"
echo -e "   • CSV_1 (Órdenes con bloqueados):              $CSV1_COUNT"
echo -e "   • CSV_2 (Órdenes con documentos):              $CSV2_COUNT"
echo -e "   • CSV_3 (Órdenes bloqueadas SIN docs):         ${RED}$CSV3_COUNT ⚠️ CRÍTICO${NC}"
echo -e "   • CSV_4 (Documentos orfandos):                 $CSV4_COUNT\n"

echo -e "📁 ${YELLOW}Ubicación de los CSVs:${NC}"
echo -e "   $OUTPUT_DIR/\n"

echo -e "📝 ${YELLOW}Próximos pasos:${NC}"
echo -e "   1. Revisar los CSVs en la carpeta output/"
echo -e "   2. Crear documentos: ./generar-reporte.sh crear"
echo -e "   3. Limpiar orfandos: ./generar-reporte.sh limpiar\n"

echo -e "${GREEN}✅ LISTO${NC}\n"
