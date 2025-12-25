#!/bin/bash
# ============================================================
# CHM-SISTEMA - Backup Automático Completo
# @author ch-mestriner (https://ch-mestriner.com.br)
# @date 24/12/2025
# ============================================================

# Configurações
PROJECT_DIR="/home/chm/Documentos/CHM-SISTEMA"
BACKUP_DIR="${PROJECT_DIR}/backups"
DATE_BR=$(date +"%d-%m-%Y")
TIME_BR=$(date +"%H-%M-%S")
TIMESTAMP="${DATE_BR}_${TIME_BR}"

# Banco de dados
DB_HOST="186.209.113.108"
DB_NAME="chmtrans_chm-sistema"
DB_USER="chmtrans_chm-sistema"
DB_PASS="Ca258790%Ca258790%"

# Arquivos de backup
BACKUP_FILES="${BACKUP_DIR}/files/backup-files-${TIMESTAMP}.zip"
BACKUP_DB="${BACKUP_DIR}/db/backup-db-${TIMESTAMP}.sql"
BACKUP_LOG="${BACKUP_DIR}/logs/backup-${DATE_BR}.log"

# Função de log
log() {
    echo "[$(date +"%d/%m/%Y %H:%M:%S")] $1" >> "$BACKUP_LOG"
    echo "[$(date +"%d/%m/%Y %H:%M:%S")] $1"
}

# Início do backup
log "=========================================="
log "INÍCIO DO BACKUP AUTOMÁTICO"
log "=========================================="

# 1) Backup dos arquivos do sistema
log "Iniciando backup dos arquivos..."
cd "$PROJECT_DIR"
zip -r "$BACKUP_FILES" app/ -x "*/backups/*" -x "*.log" -x "*/cache/*" >> "$BACKUP_LOG" 2>&1

if [ -f "$BACKUP_FILES" ]; then
    SIZE_FILES=$(du -h "$BACKUP_FILES" | cut -f1)
    log "✓ Arquivos: $BACKUP_FILES ($SIZE_FILES)"
else
    log "✗ ERRO: Falha ao criar backup de arquivos"
fi

# 2) Backup do banco de dados MySQL
log "Iniciando backup do banco de dados..."
mysqldump -h "$DB_HOST" -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" > "$BACKUP_DB" 2>> "$BACKUP_LOG"

if [ -f "$BACKUP_DB" ] && [ -s "$BACKUP_DB" ]; then
    SIZE_DB=$(du -h "$BACKUP_DB" | cut -f1)
    log "✓ Banco de dados: $BACKUP_DB ($SIZE_DB)"
    
    # Compactar o SQL
    gzip "$BACKUP_DB"
    log "✓ SQL compactado: ${BACKUP_DB}.gz"
else
    log "✗ ERRO: Falha ao criar backup do banco de dados"
fi

# 3) Limpeza de backups antigos (manter últimos 30 dias)
log "Verificando backups antigos..."
find "${BACKUP_DIR}/files" -name "*.zip" -mtime +30 -delete 2>/dev/null
find "${BACKUP_DIR}/db" -name "*.sql.gz" -mtime +30 -delete 2>/dev/null
find "${BACKUP_DIR}/logs" -name "*.log" -mtime +60 -delete 2>/dev/null
log "✓ Limpeza de backups antigos concluída"

# Resumo
log "=========================================="
log "BACKUP CONCLUÍDO"
log "=========================================="
log "Data: $DATE_BR"
log "Hora: $(date +"%H:%M:%S")"
log "Arquivos: $(ls -1 ${BACKUP_DIR}/files/*.zip 2>/dev/null | wc -l) backups"
log "Banco: $(ls -1 ${BACKUP_DIR}/db/*.sql.gz 2>/dev/null | wc -l) backups"
log "=========================================="

exit 0
