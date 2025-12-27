# 🗺️ Mapa Mental - Sistema CHM

```mermaid
mindmap
  root((CHM Sistema<br/>Gestão de Transportes))
    Core
      Autenticação
        Login/Logout
        Recuperação de Senha
        Perfis: Admin, Motorista, Cliente
        Middlewares auth e admin
      Router
        Rotas Públicas
        Rotas Autenticadas
        Rotas Admin
      Database
        PDO MySQL
        14 Tabelas
        Migrations
    Gestão de Pessoas
      Clientes
        CRUD Completo
        Pessoa Física
        Pessoa Jurídica
        Busca CEP
        Vinculação com Usuário
      Motoristas
        CRUD Completo
        Controle CNH
        Comissões
        Fechamento Mensal
        Dados Bancários
        Tipo: Próprio/Terceirizado
      Usuários
        Perfis
        Dashboard
        Alterar Senha
    Operação
      Agendamentos
        CRUD Completo
        Código Único
        6 Tipos de Serviço
        Cálculo Automático
        Status: 5 Estados
        Voucher/Recibo
      Calendário
        Visão Mensal
        Visão Semanal
        Visão Diária
        FullCalendar
        Exportar/Importar
      Veículos
        CRUD Completo
        Controle Seguro
        Controle Manutenção
        Disponibilidade
        5 Categorias
    Financeiro
      Contas a Pagar
        Despesas Gerais
        Comissões Motoristas
        Recorrentes
        5 Status
      Contas a Receber
        Receitas
        Vinculação Agendamento
        5 Status
      Relatórios
        Faturamento Cliente
        Faturamento Motorista
        Faturamento Veículo
        Comissões
        Fechamento Motorista
        Fluxo de Caixa
        DRE
    Integrações
      WhatsApp Business
        Envio de Mensagens
        Templates
        Tags Dinâmicas
        Histórico
        Webhook
        Status de Entrega
      PWA
        Instalável
        Offline
        Service Worker
        Manifest
        Notificações Push
    Sistema
      Backup
        Automático a cada 10min
        Manual
        Retenção 30 dias
        CRON Job
      Logs
        Auditoria
        Ações de Usuários
        IP e User Agent
      Configurações
        Ambiente Dev/Prod
        WhatsApp API
        Comissões
        SMTP
      Vouchers
        Geração Voucher
        Geração Recibo
        Envio Email/WhatsApp
        Acesso Público
```

---

## 📊 Visão de Camadas

```mermaid
graph TB
    subgraph "Frontend - Views"
        V1[Login/Auth]
        V2[Dashboard]
        V3[Clientes]
        V4[Motoristas]
        V5[Veículos]
        V6[Agendamentos]
        V7[Calendário]
        V8[Financeiro]
        V9[Relatórios]
        V10[WhatsApp]
        V11[Vouchers]
    end

    subgraph "Application Layer - Controllers"
        C1[AuthController]
        C2[DashboardController]
        C3[ClientController]
        C4[DriverController]
        C5[VehicleController]
        C6[BookingController]
        C7[CalendarController]
        C8[FinanceController]
        C9[ReportController]
        C10[WhatsAppController]
        C11[VoucherController]
    end

    subgraph "Business Layer - Models & Services"
        M1[UserModel]
        M2[ClientModel]
        M3[DriverModel]
        M4[VehicleModel]
        M5[BookingModel]
        M6[FinanceModel]
        S1[WhatsAppService]
        S2[BackupService]
        S3[NotificationService]
    end

    subgraph "Core Layer"
        CORE1[Router]
        CORE2[Database]
        CORE3[Session]
        CORE4[Validator]
        CORE5[Helpers]
    end

    subgraph "Data Layer"
        DB[(MySQL<br/>14 Tabelas)]
    end

    V1 --> C1
    V2 --> C2
    V3 --> C3
    V4 --> C4
    V5 --> C5
    V6 --> C6
    V7 --> C7
    V8 --> C8
    V9 --> C9
    V10 --> C10
    V11 --> C11

    C1 --> M1
    C3 --> M2
    C4 --> M3
    C5 --> M4
    C6 --> M5
    C8 --> M6
    C10 --> S1

    M1 --> CORE2
    M2 --> CORE2
    M3 --> CORE2
    M4 --> CORE2
    M5 --> CORE2
    M6 --> CORE2

    CORE2 --> DB
    S1 -.->|API Externa| WA[WhatsApp Business API]
    S2 --> DB
    S3 --> DB

    CORE1 -.->|Middlewares| C1
    CORE3 -.->|Auth| C2
    CORE4 -.->|Validação| C6
```

---

## 🔄 Fluxo de Dados - Agendamento

```mermaid
sequenceDiagram
    participant U as Usuário Admin
    participant R as Router
    participant BC as BookingController
    participant BM as BookingModel
    participant WS as WhatsAppService
    participant DB as Database
    participant Cliente

    U->>R: POST /bookings (dados)
    R->>BC: create()
    BC->>BC: Valida dados
    BC->>BM: create(dados)
    BM->>BM: Calcula total e comissão
    BM->>DB: INSERT INTO chm_bookings
    DB-->>BM: ID do agendamento
    BM-->>BC: Booking criado
    BC->>WS: Enviar voucher
    WS->>DB: INSERT INTO chm_whatsapp_messages
    WS->>Cliente: Envia mensagem WhatsApp
    BC-->>U: Sucesso + Redirect
```

---

## 📦 Módulos por Prioridade de Uso

### Alta Prioridade (Uso Diário)
1. **Agendamentos** - Núcleo do sistema
2. **Calendário** - Visualização operacional
3. **Clientes** - Base cadastral
4. **Motoristas** - Recursos operacionais
5. **Veículos** - Recursos operacionais

### Média Prioridade (Uso Semanal/Mensal)
6. **Financeiro** - Controle de contas
7. **Relatórios** - Análise gerencial
8. **Vouchers** - Documentação

### Baixa Prioridade (Uso Eventual)
9. **WhatsApp** - Notificações automatizadas
10. **Backup** - Manutenção do sistema

---

## 🎯 Dependências entre Módulos

```mermaid
graph LR
    CLIENTES[Clientes] --> AGENDAMENTOS[Agendamentos]
    MOTORISTAS[Motoristas] --> AGENDAMENTOS
    VEICULOS[Veículos] --> AGENDAMENTOS
    AGENDAMENTOS --> CALENDARIO[Calendário]
    AGENDAMENTOS --> VOUCHERS[Vouchers]
    AGENDAMENTOS --> FINANCEIRO[Financeiro]
    AGENDAMENTOS --> WHATSAPP[WhatsApp]
    FINANCEIRO --> RELATORIOS[Relatórios]
    MOTORISTAS --> RELATORIOS
    CLIENTES --> RELATORIOS
    VEICULOS --> RELATORIOS
    
    style AGENDAMENTOS fill:#ff6b6b,stroke:#c92a2a,color:#fff
    style FINANCEIRO fill:#4ecdc4,stroke:#0a8080,color:#fff
    style RELATORIOS fill:#45b7d1,stroke:#1e8ba8,color:#fff
```

**Legenda:**
- **Vermelho (Agendamentos):** Módulo central - tudo depende dele
- **Verde-água (Financeiro):** Alimentado por agendamentos
- **Azul (Relatórios):** Consolidação de dados

---

## ⚙️ Tecnologias por Camada

| Camada | Tecnologias |
|--------|-------------|
| **Frontend** | HTML5, CSS3, JavaScript Vanilla, FullCalendar.js |
| **Backend** | PHP 7.4+, Arquitetura MVC, PSR-4 Autoloader |
| **Database** | MySQL 8.0 / MariaDB, PDO, Prepared Statements |
| **Security** | bcrypt, CSRF Protection, SQL Injection Prevention |
| **PWA** | Service Worker, Web Manifest, Offline Support |
| **Integration** | WhatsApp Business API (Graph API v18.0) |
| **Server** | Apache 2.4, .htaccess, mod_rewrite |
| **DevOps** | CRON Jobs, Automated Backups, Git |

---

## 📌 Status de Implementação

| Módulo | Status | Observações |
|--------|--------|-------------|
| ✅ Autenticação | 100% | Login, recuperação de senha, perfis |
| ✅ Clientes | 100% | CRUD completo PF/PJ |
| ✅ Motoristas | 100% | CRUD completo + comissões |
| ✅ Veículos | 100% | CRUD completo + manutenção |
| ✅ Agendamentos | 100% | CRUD + status + cálculos |
| ✅ Calendário | 100% | Múltiplas visualizações |
| ✅ Financeiro | 100% | Contas a pagar/receber |
| ✅ Relatórios | 100% | 9 tipos de relatórios |
| ✅ Vouchers | 100% | Geração + envio |
| ⏳ WhatsApp | 90% | Estrutura pronta, pendente config API |
| ⏳ PWA | 80% | Instalável, mas não responsivo |
| ✅ Backup | 100% | Automático + manual |

---

*Mapa mental criado em 26/12/2025 - Análise do Sistema CHM*
