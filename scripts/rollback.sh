#!/bin/bash
#
# CHM Sistema - Rollback de Deploy
# @author ch-mestriner (https://ch-mestriner.com.br)
# @date 24/12/2025
#

set -e

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_DIR="$(dirname "$SCRIPT_DIR")"
BACKUP_DIR="$PROJECT_DIR/backups"

echo "=== CHM Sistema - Rollback ==="
echo ""

# Listar backups disponíveis
echo "Backups disponíveis:"
ls -la "$BACKUP_DIR"/*.tar.gz 2>/dev/null || {
    echo "Nenhum backup encontrado em $BACKUP_DIR"
    exit 1
}

echo ""
echo "Para restaurar um backup:"
echo "  1. Extraia: tar -xzf <backup.tar.gz> -C $PROJECT_DIR"
echo "  2. Execute: ./deploy.sh"
echo ""

# Se passar argumento, restaurar automaticamente
if [ -n "$1" ]; then
    BACKUP_FILE="$1"
    if [ -f "$BACKUP_FILE" ]; then
        echo "Restaurando $BACKUP_FILE..."
        tar -xzf "$BACKUP_FILE" -C "$PROJECT_DIR"
        echo "✓ Backup restaurado localmente"
        echo "Execute ./deploy.sh para enviar ao servidor"
    else
        echo "Arquivo não encontrado: $BACKUP_FILE"
        exit 1
    fi
fi
