# CHM Sistema - Fluxo CI/CD Windsurf (Produção)

**Versão:** 2.3.4  
**@author:** ch-mestriner (https://ch-mestriner.com.br)  
**@date:** 27/12/2025 14:30

## Visão Geral

O GitHub é a **única fonte da verdade**. Um push em `main` dispara automaticamente:
1. Validação de código PHP
2. Deploy via FTP para produção
3. Limpeza de cache
4. Checklist pós-deploy

**PROIBIDO:** FTP manual, cPanel, alterações diretas no servidor.

## Secrets Necessários (GitHub)

Configure em: `Settings → Secrets and variables → Actions`

| Secret | Descrição |
|--------|-----------|
| `FTP_HOST` | Host FTP (ex: 186.209.113.108) |
| `FTP_USER` | Usuário FTP (ex: chm-sistema@chm-sistema.com.br) |
| `FTP_PASS` | Senha FTP |
| `DB_HOST` | Host MySQL (ex: 186.209.113.108) |
| `DB_NAME` | Nome do banco (ex: chmtrans_chm-sistema) |
| `DB_USER` | Usuário MySQL |
| `DB_PASS` | Senha MySQL |
| `DEPLOY_SECRET` | Token para endpoints de deploy (ex: chm-deploy-2025) |

## Workflow Automático

```
GitHub Push → Validate → Deploy FTP → Clear Cache → Post-Deploy Check
```

### Jobs do Pipeline

1. **validate** - Valida sintaxe PHP e arquivos críticos
2. **deploy** - Upload via FTP/SFTP para produção
3. **restore-database** - (Manual) Restaura backup do banco
4. **post-deploy-validation** - Testes automáticos pós-deploy

## Endpoints de Deploy

| Endpoint | Função |
|----------|--------|
| `/api/health` | Health check (sem auth) |
| `/api/deploy/clear-cache?secret=X` | Limpa OPcache e cache de arquivos |
| `/api/deploy/restore-db?secret=X&date=YYYY-MM-DD` | Restaura backup do banco |

## Correções v2.3.4 (HTTP 500)

**Causa raiz:** `app/index.php` usava constantes (`APP_PATH`, `LOGS_PATH`) ANTES de carregar `config.php`.

**Correções aplicadas:**
- `index.php` raiz: entry point mínimo sem `declare(strict_types)`
- `app/index.php`: carrega `config.php` PRIMEIRO
- `.htaccess`: sem `php_value`/`php_flag` (compatível com shared hosting)
- Error handlers robustos via código PHP

## Checklist Pós-Deploy

O workflow valida automaticamente:
- ✓ Bootstrap (index.php → app/index.php)
- ✓ Rota `/login` retorna 200
- ✓ Conexão com banco de dados
- ✓ Ausência de HTTP 500 em páginas críticas

## Resultado Esperado

Um push no GitHub executa:
```
validate → deploy → clear-cache → post-deploy-check → ✓ PRODUÇÃO ONLINE
```

Sem intervenção manual. Sem FTP. Sem cPanel.
