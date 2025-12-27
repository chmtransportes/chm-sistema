# CHM Sistema - Fluxo CI/CD Windsurf (Produção)

## Visão geral
1. O GitHub é a única fonte da verdade. Qualquer push em `main` ou `production` dispara de imediato o workflow no Windsurf.
2. O Windsurf executa um restore completo do banco, limpa cache, aplica o código e realiza validações automáticas antes de liberar o site em produção.
3. Não há FTP, cPanel ou ações manuais no servidor; tudo acontece dentro do CI/CD.

## Sequência automatizada
1. **Restore do banco**
   - O pipeline descompacta `backup-db-27-12-2025_02-00-01.sql.gz` e faz `mysql` usando as credenciais do Windurf, garantindo o estado do banco de 27/12 entre 00:01 e 02:00.
2. **Limpeza de cache**
   - Executa `php -r "define('CHM_SISTEMA', true); require 'app/config/config.php'; require 'app/core/Helpers.php'; CHM\\Core\\Helpers::clearCache();"` para remover caches de aplicação e garantir que as views recompiladas usem os dados restaurados.
3. **Deploy do código**
   - O entry point `index.php` permanece leve e apenas inclui `app/index.php`.
   - `app/index.php` inicializa sessão, autoload, configurações, middlewares e todas as rotas, disparando `Router::dispatch()` sem `declare(strict_types)` nem `php_flag`, compatível com hospedagem compartilhada.
4. **Validação pós-deploy**
   - Scripts verificam:
     * Bootstrap (index → app)
     * Inicialização do Router
     * `/login` e `/dashboard`
     * `/calendar` e `/api/calendar/events`
     * Logs (`logs/php-errors.log`) sem `Fatal error`

## Pré-requisitos no repositório
- `app/index.php` restaurado para o front controller principal (sem stub). 
- `.htaccess` replicando o conteúdo seguro do `.htaccess.bak` (rewrite `/`, bloqueios e charset UTF-8) versionado no repo.
- Scripts e diretórios de logs/upload/backup criados automaticamente no front controller.
- Workflow YAML `CHM Sistema – Pós Deploy Validation` presente no GitHub Actions/Windsurf.

## Responsabilidades do Windsurf/CI
- Executar o restore do backup 27/12 antes de qualquer outra etapa.
- Limpar cache com a chamada ao helper após o restore.
- Validar o sistema como descrito acima e falhar o build caso alguma etapa indique erro.
- Garantir que o deploy finalize somente se todas as validações passarem.

## Resultado esperado
Um push ativo no GitHub produz o seguinte ciclo: restore → limpeza de cache → deploy → checklist → site online sem HTTP 500. Esse fluxo evita intervenções manuais e mantém a hospedagem Apache/PHP saudável.
