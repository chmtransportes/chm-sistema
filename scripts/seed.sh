#!/bin/bash
#
# CHM Sistema - Executar Seed Remoto
# @author ch-mestriner (https://ch-mestriner.com.br)
# @date 24/12/2025
#

set -e

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
source "$SCRIPT_DIR/config.sh"

echo "=== CHM Sistema - Seed ==="
echo ""

RESPONSE=$(curl -s -X POST "${APP_URL}/app/api/deploy_hook.php?action=seed&secret=${DEPLOY_SECRET}")

echo "$RESPONSE" | python3 -m json.tool 2>/dev/null || echo "$RESPONSE"

if echo "$RESPONSE" | grep -q '"success":true'; then
    echo ""
    echo "✓ Seed executado com sucesso"
    exit 0
else
    echo ""
    echo "✗ Erro no seed"
    exit 1
fi
