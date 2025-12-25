#!/bin/bash
#
# CHM Sistema - Validar Deploy
# @author ch-mestriner (https://ch-mestriner.com.br)
# @date 24/12/2025
#

set -e

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
source "$SCRIPT_DIR/config.sh"

echo "=== CHM Sistema - Validação ==="
echo ""

# Testar página principal
echo "Testando ${APP_URL}..."
HTTP_CODE=$(curl -s -o /dev/null -w "%{http_code}" "${APP_URL}/app/")

if [ "$HTTP_CODE" -eq 200 ] || [ "$HTTP_CODE" -eq 302 ]; then
    echo "✓ Site respondendo (HTTP $HTTP_CODE)"
else
    echo "✗ Site não respondeu corretamente (HTTP $HTTP_CODE)"
    exit 1
fi

# Testar health check
echo ""
echo "Verificando health check..."
HEALTH=$(curl -s "${APP_URL}/app/api/deploy_hook.php?action=health&secret=${DEPLOY_SECRET}")

if echo "$HEALTH" | grep -q '"database":"OK"'; then
    echo "✓ Banco de dados OK"
else
    echo "✗ Problema no banco de dados"
    echo "$HEALTH"
    exit 1
fi

# Testar login page
echo ""
echo "Verificando página de login..."
LOGIN_CODE=$(curl -s -o /dev/null -w "%{http_code}" "${APP_URL}/app/login")

if [ "$LOGIN_CODE" -eq 200 ]; then
    echo "✓ Página de login OK"
else
    echo "✗ Página de login com problema (HTTP $LOGIN_CODE)"
    exit 1
fi

echo ""
echo "=== Validação Concluída ===" 
echo "✓ Todos os testes passaram"
