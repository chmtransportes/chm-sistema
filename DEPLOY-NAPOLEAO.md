# CHM Sistema - Deploy Napoleão

**Autor:** ch-mestriner (https://ch-mestriner.com.br)  
**Data:** 2025-12-24  
**Servidor:** Napoleão (Apache + PHP + MySQL)

---

## Ambiente de Produção

| Item | Valor |
|------|-------|
| **URL** | https://chm-sistema.com.br |
| **Servidor** | 186.209.113.108 |
| **PHP** | 8.x |
| **MySQL** | 5.7+ |

---

## Estrutura de Deploy

```
CHM-SISTEMA/
├── app/                    # Código da aplicação
│   ├── api/
│   │   └── deploy_hook.php # Endpoint de deploy remoto
│   ├── config/
│   │   ├── config.php      # Configurações gerais
│   │   ├── env_loader.php  # Loader de ambiente
│   │   └── env.production.php # Credenciais produção
│   └── database/
│       ├── migrate.php     # Sistema de migrações
│       ├── seed.php        # Dados iniciais
│       └── migrations/     # Arquivos SQL
├── scripts/                # Scripts DevOps
│   ├── config.sh           # Credenciais FTP
│   ├── deploy.sh           # Upload FTP
│   ├── migrate.sh          # Executar migrações
│   ├── seed.sh             # Executar seed
│   ├── validate.sh         # Validar deploy
│   ├── rollback.sh         # Reverter deploy
│   └── run-all.sh          # Deploy completo
├── backups/                # Backups pré-deploy
└── logs/                   # Logs do sistema
```

---

## Como Fazer Deploy

### Deploy Completo (Recomendado)

```bash
cd /home/chm/Documentos/CHM-SISTEMA
./scripts/run-all.sh "Descrição do deploy"
```

**Fluxo executado:**
1. Git commit & push
2. Backup local
3. Upload FTP
4. Migrações
5. Seed
6. Validação

### Deploy Manual (Passo a Passo)

```bash
# 1. Commit das mudanças
./scripts/git-push.sh "Minha alteração"

# 2. Upload para servidor
./scripts/deploy.sh

# 3. Executar migrações
./scripts/migrate.sh

# 4. Executar seed (se necessário)
./scripts/seed.sh

# 5. Validar
./scripts/validate.sh
```

---

## Migrações

### Criar Nova Migração

1. Crie um arquivo SQL em `app/database/migrations/`
2. Nomeie com timestamp: `2025_12_24_001_create_table.sql`
3. Execute: `./scripts/migrate.sh`

### Exemplo de Migração

```sql
-- app/database/migrations/2025_12_24_001_add_column.sql
ALTER TABLE chm_clients ADD COLUMN new_field VARCHAR(100) NULL;
```

---

## Rollback

### Restaurar Backup

```bash
# Listar backups
ls -la backups/

# Restaurar
./scripts/rollback.sh backups/pre-deploy-20251224-120000.tar.gz

# Reenviar ao servidor
./scripts/deploy.sh
```

---

## Validação

### Testar Manualmente

```bash
# Testar site
curl -I https://chm-sistema.com.br/app/

# Health check
curl "https://chm-sistema.com.br/app/api/deploy_hook.php?action=health&secret=SEU_SECRET"
```

### Status do Deploy

```bash
curl "https://chm-sistema.com.br/app/api/deploy_hook.php?action=status&secret=SEU_SECRET"
```

---

## Credenciais

### Arquivos Sensíveis (NÃO COMMITAR)

- `app/config/env.production.php` - Credenciais de produção
- `scripts/config.sh` - Credenciais FTP

### Acesso ao Sistema

| Campo | Valor |
|-------|-------|
| URL | https://chm-sistema.com.br/app/ |
| E-mail | chm@chmtransportes.com.br |
| Senha | Ca258790$ |

---

## Troubleshooting

### Erro de Conexão FTP

```bash
# Testar conexão
lftp -u "chm-sistema@chm-sistema.com.br" 186.209.113.108
```

### Erro de Banco de Dados

```bash
# Testar conexão MySQL
mysql -h 186.209.113.108 -u chmtrans_chm-sistema -p chmtrans_chm-sistema
```

### Logs

```bash
# Ver logs de deploy
cat logs/deploy-$(date +%Y-%m-%d).log

# Ver logs da aplicação
cat logs/app-$(date +%Y-%m-%d).log
```

---

## Contato

- **Desenvolvedor:** ch-mestriner
- **Site:** https://ch-mestriner.com.br
