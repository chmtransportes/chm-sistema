#!/bin/bash
#
# CHM Sistema - Script de Deploy via FTP
# @author ch-mestriner (https://ch-mestriner.com.br)
# @date 24/12/2025
#

set -e

# Cores
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m'

# Diretórios
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_DIR="$(dirname "$SCRIPT_DIR")"
APP_DIR="$PROJECT_DIR/app"

echo -e "${GREEN}=== CHM Sistema - Deploy ===${NC}"
echo "Projeto: $PROJECT_DIR"
echo ""

# Carregar configurações
source "$SCRIPT_DIR/config.sh" 2>/dev/null || {
    echo -e "${RED}ERRO: Arquivo config.sh não encontrado${NC}"
    echo "Crie o arquivo scripts/config.sh com as credenciais FTP"
    exit 1
}

# Verificar lftp
if ! command -v lftp &> /dev/null; then
    echo -e "${YELLOW}Instalando lftp...${NC}"
    sudo apt-get update && sudo apt-get install -y lftp
fi

# Criar backup local antes do deploy
BACKUP_FILE="$PROJECT_DIR/backups/pre-deploy-$(date +%Y%m%d-%H%M%S).tar.gz"
mkdir -p "$PROJECT_DIR/backups"
echo -e "${YELLOW}Criando backup local...${NC}"
tar -czf "$BACKUP_FILE" -C "$PROJECT_DIR" app --exclude="*.log"
echo "Backup: $BACKUP_FILE"

# Deploy via FTP
echo -e "${YELLOW}Iniciando upload FTP...${NC}"
lftp -c "
set ssl:verify-certificate no
set ftp:ssl-allow no
open -u $FTP_USER,$FTP_PASS $FTP_HOST
mirror -R --verbose --delete --exclude .git/ --exclude .gitignore --exclude backups/ --exclude logs/ $APP_DIR $FTP_ROOT
bye
"

echo -e "${GREEN}Deploy concluído!${NC}"
echo ""

# Executar migrações remotas
echo -e "${YELLOW}Executando migrações...${NC}"
"$SCRIPT_DIR/migrate.sh"

echo -e "${GREEN}=== Deploy Finalizado ===${NC}"
