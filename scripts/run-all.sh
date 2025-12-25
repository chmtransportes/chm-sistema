#!/bin/bash
#
# CHM Sistema - Deploy Completo
# @author ch-mestriner (https://ch-mestriner.com.br)
# @date 24/12/2025
#
# Fluxo: COMMIT -> PUSH -> BACKUP -> FTP DEPLOY -> MIGRATE -> SEED -> VALIDATE
#

set -e

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"

RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m'

echo -e "${GREEN}"
echo "╔══════════════════════════════════════════╗"
echo "║     CHM Sistema - Deploy Completo        ║"
echo "╚══════════════════════════════════════════╝"
echo -e "${NC}"

# 1. Git Commit e Push
echo -e "${YELLOW}[1/5] Git Commit & Push...${NC}"
"$SCRIPT_DIR/git-push.sh" "${1:-Deploy $(date '+%Y-%m-%d %H:%M')}" || true
echo ""

# 2. Deploy FTP (inclui backup)
echo -e "${YELLOW}[2/5] Deploy FTP...${NC}"
"$SCRIPT_DIR/deploy.sh"
echo ""

# 3. Migrações
echo -e "${YELLOW}[3/5] Migrações...${NC}"
"$SCRIPT_DIR/migrate.sh"
echo ""

# 4. Seed
echo -e "${YELLOW}[4/5] Seed...${NC}"
"$SCRIPT_DIR/seed.sh"
echo ""

# 5. Validação
echo -e "${YELLOW}[5/5] Validação...${NC}"
"$SCRIPT_DIR/validate.sh"
echo ""

echo -e "${GREEN}"
echo "╔══════════════════════════════════════════╗"
echo "║       Deploy Completo com Sucesso!       ║"
echo "╚══════════════════════════════════════════╝"
echo -e "${NC}"
echo "URL: https://chm-sistema.com.br"
echo ""
