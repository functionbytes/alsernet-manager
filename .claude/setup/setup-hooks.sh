#!/bin/bash

# Script para configurar hooks de git automáticamente
# Uso: bash manual/setup-hooks.sh

GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m'

echo -e "${YELLOW}🔧 Configurando git hooks para documentación automática...${NC}"

# Verificar si .git existe
if [ ! -d ".git" ]; then
    echo -e "${RED}❌ Este proyecto no es un repositorio Git${NC}"
    echo "Primero necesitas inicializar git:"
    echo "  git init"
    exit 1
fi

# Crear directorio de hooks si no existe
mkdir -p .git/hooks

# Copiar el pre-commit hook
if [ -f "manual/hooks/pre-commit" ]; then
    cp manual/hooks/pre-commit .git/hooks/pre-commit
    chmod +x .git/hooks/pre-commit
    echo -e "${GREEN}✅ Pre-commit hook instalado${NC}"
else
    echo -e "${RED}❌ No se encontró manual/hooks/pre-commit${NC}"
    exit 1
fi

echo ""
echo -e "${GREEN}✨ Configuración completada${NC}"
echo ""
echo "Los siguientes hooks están activados:"
echo "  • pre-commit: Genera documentación de comandos antes de cada commit"
echo ""
echo "Para desactivar los hooks en caso necesario, ejecuta:"
echo "  rm .git/hooks/pre-commit"
echo ""
