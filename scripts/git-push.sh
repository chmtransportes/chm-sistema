#!/bin/bash
#
# CHM Sistema - Git Commit e Push
# @author ch-mestriner (https://ch-mestriner.com.br)
# @date 24/12/2025
#

set -e

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_DIR="$(dirname "$SCRIPT_DIR")"

cd "$PROJECT_DIR"

echo "=== CHM Sistema - Git Push ==="
echo ""

# Verificar se há mudanças
if git diff --quiet && git diff --staged --quiet; then
    echo "Nenhuma mudança para commitar"
    exit 0
fi

# Adicionar todas as mudanças
git add -A

# Commit com mensagem
MSG="${1:-Deploy automático $(date '+%Y-%m-%d %H:%M')}"
git commit -m "$MSG"

# Push
if git remote | grep -q origin; then
    echo "Enviando para origin..."
    git push origin main 2>/dev/null || git push origin master 2>/dev/null || echo "Push falhou ou sem remote configurado"
fi

echo ""
echo "✓ Commit realizado: $MSG"
