# CHM Sistema - Resumo do Projeto
**Autor:** ch-mestriner (https://ch-mestriner.com.br)
**Data:** 24/12/2025
**Versão:** 1.1.1

---

## 🔐 Credenciais de Acesso

| Campo | Valor |
|-------|-------|
| **URL** | http://localhost/chm-sistema/app/ |
| **E-mail** | chm@chmtransportes.com.br |
| **Senha** | Ca258790$ |

---

## 📁 Estrutura do Projeto

```
CHM-SISTEMA/
├── app/
│   ├── assets/          # CSS, JS, ícones
│   ├── auth/            # AuthController, UserModel
│   ├── bookings/        # Agendamentos
│   ├── calendar/        # Calendário
│   ├── clients/         # Clientes
│   ├── config/          # Configurações
│   ├── core/            # Classes base (Database, Session, Router, etc.)
│   ├── database/        # schema.sql
│   ├── drivers/         # Motoristas
│   ├── finance/         # Financeiro
│   ├── pwa/             # Manifest, Service Worker
│   ├── reports/         # Relatórios
│   ├── users/           # Dashboard
│   ├── vehicles/        # Veículos
│   ├── views/           # Templates HTML/PHP
│   ├── vouchers/        # Vouchers e Recibos
│   ├── whatsapp/        # Integração WhatsApp API
│   └── index.php        # Bootstrap da aplicação
├── backup/              # Backups automáticos
├── logs/                # Logs do sistema
├── install.php          # Script de instalação (DELETAR após uso)
└── cron-backup.php      # CRON para backup automático
```

---

## 🗄️ Banco de Dados

- **Nome:** chm_sistema
- **Usuário:** root
- **Senha:** (vazio)
- **Tabelas:** 14 tabelas criadas

### Tabelas Principais:
- chm_users - Usuários do sistema
- chm_clients - Clientes
- chm_drivers - Motoristas
- chm_vehicles - Veículos
- chm_bookings - Agendamentos
- chm_finance - Financeiro
- chm_whatsapp_messages - Mensagens WhatsApp
- chm_settings - Configurações
- chm_logs - Logs do sistema
- chm_backups - Registro de backups

---

## ⚙️ Configurações Importantes

**Arquivo:** `/app/config/config.php`

- Timezone: America/Sao_Paulo
- Comissão padrão: 11%
- Backup automático: a cada 10 minutos

---

## 🚀 Funcionalidades Implementadas

1. **Autenticação**
   - Login/Logout
   - Recuperação de senha
   - Perfis: Admin, Motorista, Cliente

2. **Clientes**
   - CRUD completo
   - Pessoa Física e Jurídica
   - Busca por CEP

3. **Motoristas**
   - CRUD completo
   - Controle de CNH
   - Comissões e fechamento mensal

4. **Veículos**
   - CRUD completo
   - Controle de manutenção e seguro

5. **Agendamentos**
   - CRUD completo
   - Status: Pendente, Confirmado, Em Andamento, Concluído, Cancelado
   - Cálculo automático de valores e comissões

6. **Calendário**
   - Visualização mensal, semanal e diária
   - Integração com FullCalendar

7. **Relatórios**
   - Faturamento por cliente/motorista/veículo
   - Comissões
   - Fechamento de motorista

8. **Vouchers/Recibos**
   - Geração de voucher para cliente
   - Geração de recibo após conclusão

9. **WhatsApp**
   - Envio de mensagens via API Business
   - Templates com tags dinâmicas

10. **PWA**
    - Instalável no celular
    - Funciona offline
    - Notificações push

11. **Backup**
    - Automático a cada 10 minutos
    - Manual via painel admin

---

## 📱 Acesso Mobile

O sistema é PWA e pode ser instalado no celular:
1. Acesse http://localhost/chm-sistema/app/ no celular
2. Clique em "Adicionar à tela inicial"

### ⚠️ Pendências Mobile (Etapa Futura)
- [ ] Mobile ainda usa layout desktop
- [ ] Menu hambúrguer NÃO implementado
- [ ] Responsividade mobile será feita em etapa futura
- [ ] Nenhuma correção mobile nesta fase

---

## 🔧 Comandos Úteis

### Iniciar serviços:
```bash
sudo systemctl start apache2 mysql
```

### Reinstalar banco de dados:
```bash
php /home/chm/Documentos/CHM-SISTEMA/install.php
```

### Backup manual:
```bash
mysqldump -u root chm_sistema > backup.sql
```

### CRON para backup automático:
```bash
*/10 * * * * php /home/chm/Documentos/CHM-SISTEMA/cron-backup.php
```

---

## 📝 Histórico de Alterações

| Data | Versão | Descrição |
|------|--------|-----------|
| 23/12/2025 | 1.0.0 | Criação inicial do sistema |
| 23/12/2025 | 1.1.0 | Sistema completo com todos os módulos |
| 24/12/2025 | 1.1.1 | Backup de memória, ajuste de datas padrão BR |

---

## ⚠️ Importante

1. **Delete o arquivo `install.php`** após a instalação por segurança
2. Configure as credenciais do **WhatsApp API** em `/app/config/config.php`
3. Configure o **CRON** para backup automático

---

*Documento atualizado em 24/12/2025 às 20:18*
