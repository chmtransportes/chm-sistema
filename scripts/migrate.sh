#!/bin/bash
#
# CHM Sistema - Executar Migrações Remotas
# @author ch-mestriner (https://ch-mestriner.com.br)
# @date 24/12/2025
#

set -e

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
source "$SCRIPT_DIR/config.sh"

echo "=== CHM Sistema - Migrações ==="
echo ""

RESPONSE=$(curl -s -X POST "${APP_URL}/app/api/deploy_hook.php?action=migrate&secret=${DEPLOY_SECRET}")

echo "$RESPONSE" | python3 -m json.tool 2>/dev/null || echo "$RESPONSE"

# Verificar sucesso
if echo "$RESPONSE" | grep -q '"success":true'; then
    echo ""
    echo "✓ Migrações executadas com sucesso"
    exit 0
else
    echo ""
    echo "✗ Erro nas migrações"
    exit 1
fi
