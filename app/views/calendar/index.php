<?php
/**
 * CHM Sistema - Agenda Profissional (Estilo Google Calendar)
 * @author ch-mestriner (https://ch-mestriner.com.br)
 * @date 27/12/2025 01:56
 */
?>

<!-- FullCalendar CSS -->
<link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.css" rel="stylesheet">

<style>
:root {
    --gc-primary: #1a73e8;
    --gc-primary-hover: #1557b0;
    --gc-bg: #ffffff;
    --gc-sidebar-bg: #f8f9fa;
    --gc-border: #dadce0;
    --gc-text: #3c4043;
    --gc-text-secondary: #5f6368;
    --gc-pending: #f9ab00;
    --gc-confirmed: #039be5;
    --gc-in-progress: #7986cb;
    --gc-completed: #33b679;
    --gc-cancelled: #d93025;
}

.calendar-wrapper {
    display: flex;
    height: calc(100vh - 120px);
    min-height: 600px;
    background: var(--gc-bg);
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 1px 3px rgba(0,0,0,0.12), 0 1px 2px rgba(0,0,0,0.24);
}

/* Header da Agenda */
.calendar-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 12px 16px;
    background: var(--gc-bg);
    border-bottom: 1px solid var(--gc-border);
    gap: 16px;
    flex-wrap: wrap;
}

.calendar-header-left {
    display: flex;
    align-items: center;
    gap: 16px;
}

.calendar-logo {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 22px;
    font-weight: 400;
    color: var(--gc-text);
}

.calendar-logo i {
    color: var(--gc-primary);
    font-size: 28px;
}

.calendar-nav {
    display: flex;
    align-items: center;
    gap: 8px;
}

.btn-nav {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    border: none;
    background: transparent;
    color: var(--gc-text-secondary);
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: background 0.2s;
}

.btn-nav:hover {
    background: #f1f3f4;
}

.btn-today {
    padding: 8px 16px;
    border-radius: 4px;
    border: 1px solid var(--gc-border);
    background: var(--gc-bg);
    color: var(--gc-text);
    font-size: 14px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s;
}

.btn-today:hover {
    background: #f1f3f4;
}

.current-date {
    font-size: 22px;
    font-weight: 400;
    color: var(--gc-text);
    min-width: 200px;
}

/* Seletor de Visualização */
.view-selector {
    display: flex;
    background: #f1f3f4;
    border-radius: 4px;
    padding: 2px;
}

.view-btn {
    padding: 8px 12px;
    border: none;
    background: transparent;
    color: var(--gc-text-secondary);
    font-size: 13px;
    font-weight: 500;
    cursor: pointer;
    border-radius: 4px;
    transition: all 0.2s;
    white-space: nowrap;
}

.view-btn:hover {
    background: #e8eaed;
}

.view-btn.active {
    background: var(--gc-bg);
    color: var(--gc-primary);
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}

/* Botão Criar */
.btn-create {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 0 24px;
    height: 48px;
    border-radius: 24px;
    border: none;
    background: var(--gc-bg);
    color: var(--gc-text);
    font-size: 14px;
    font-weight: 500;
    cursor: pointer;
    box-shadow: 0 1px 3px rgba(0,0,0,0.12), 0 1px 2px rgba(0,0,0,0.24);
    transition: box-shadow 0.2s;
}

.btn-create:hover {
    box-shadow: 0 2px 6px rgba(0,0,0,0.15), 0 3px 6px rgba(0,0,0,0.2);
}

.btn-create i {
    font-size: 24px;
    color: var(--gc-primary);
}

/* Sidebar com Mini Calendário e Legenda */
.calendar-sidebar {
    width: 256px;
    padding: 16px;
    background: var(--gc-sidebar-bg);
    border-right: 1px solid var(--gc-border);
    overflow-y: auto;
    display: none;
}

@media (min-width: 992px) {
    .calendar-sidebar {
        display: block;
    }
}

/* Mini calendário */
.mini-calendar {
    margin: 12px 0 8px;
    padding: 12px;
    background: var(--gc-bg);
    border: 1px solid var(--gc-border);
    border-radius: 8px;
}

.mini-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 8px;
}

.mini-month {
    font-size: 13px;
    font-weight: 500;
    color: var(--gc-text);
    text-transform: capitalize;
}

.mini-nav {
    display: flex;
    gap: 4px;
}

.mini-nav-btn {
    width: 28px;
    height: 28px;
    border-radius: 50%;
    border: none;
    background: transparent;
    color: var(--gc-text-secondary);
    display: inline-flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: background 0.2s;
}

.mini-nav-btn:hover {
    background: #f1f3f4;
}

.mini-weekdays {
    display: grid;
    grid-template-columns: repeat(7, 1fr);
    gap: 2px;
    font-size: 10px;
    color: var(--gc-text-secondary);
    text-transform: uppercase;
    margin-bottom: 6px;
}

.mini-weekdays span {
    text-align: center;
}

.mini-grid {
    display: grid;
    grid-template-columns: repeat(7, 1fr);
    gap: 2px;
}

.mini-day {
    height: 28px;
    border-radius: 50%;
    border: none;
    background: transparent;
    font-size: 12px;
    color: var(--gc-text);
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    transition: background 0.2s, color 0.2s;
}

.mini-day:hover {
    background: #e8eaed;
}

.mini-day.is-today {
    border: 1px solid var(--gc-primary);
    color: var(--gc-primary);
    font-weight: 600;
}

.mini-day.is-selected {
    background: var(--gc-primary);
    color: #ffffff;
}

.mini-day--empty {
    cursor: default;
    pointer-events: none;
}

/* Minhas agendas */
.calendar-agendas {
    margin-top: 16px;
}

.legend-title {
    font-size: 11px;
    font-weight: 500;
    color: var(--gc-text-secondary);
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 12px;
}

.legend-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 8px 0;
    font-size: 13px;
    color: var(--gc-text);
}

.agenda-item {
    cursor: pointer;
}

.agenda-item input {
    margin: 0;
}

.agenda-item input:not(:checked) + .legend-dot {
    opacity: 0.4;
}

.agenda-item input:not(:checked) ~ .agenda-name {
    color: var(--gc-text-secondary);
}

.legend-dot {
    width: 12px;
    height: 12px;
    border-radius: 50%;
}

.legend-dot.pending { background: var(--gc-pending); }
.legend-dot.confirmed { background: var(--gc-confirmed); }
.legend-dot.in-progress { background: var(--gc-in-progress); }
.legend-dot.completed { background: var(--gc-completed); }
.legend-dot.cancelled { background: var(--gc-cancelled); }

.agenda-add {
    width: 100%;
    margin-top: 8px;
    padding: 8px 10px;
    border-radius: 6px;
    border: 1px dashed var(--gc-border);
    background: transparent;
    color: var(--gc-text-secondary);
    font-size: 12px;
    font-weight: 500;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    cursor: pointer;
    transition: all 0.2s;
}

.agenda-add:hover {
    border-color: var(--gc-primary);
    color: var(--gc-primary);
    background: rgba(26, 115, 232, 0.05);
}

/* Feriados */
.event-holiday {
    font-style: italic;
}

.fc-day-holiday {
    background-color: #e8f5e9 !important;
}

.holiday-indicator {
    position: absolute;
    top: 2px;
    right: 2px;
    font-size: 10px;
    color: #4caf50;
}

/* Área Principal do Calendário */
.calendar-main {
    flex: 1;
    display: flex;
    flex-direction: column;
    overflow: hidden;
}

#calendar {
    flex: 1;
    padding: 0;
}

/* Customização do FullCalendar - Estilo Google */
.fc {
    font-family: 'Google Sans', 'Roboto', -apple-system, BlinkMacSystemFont, sans-serif;
}

.fc .fc-toolbar {
    display: none !important;
}

.fc .fc-view-harness {
    background: var(--gc-bg);
}

.fc .fc-scrollgrid {
    border: none !important;
}

.fc .fc-scrollgrid-section > td {
    border: none;
}

.fc th {
    border: none !important;
    padding: 12px 0 !important;
    font-size: 11px !important;
    font-weight: 500 !important;
    color: var(--gc-text-secondary) !important;
    text-transform: uppercase !important;
}

.fc td {
    border-color: var(--gc-border) !important;
}

.fc .fc-daygrid-day {
    min-height: 100px;
}

.fc .fc-daygrid-day-number {
    padding: 8px !important;
    font-size: 14px;
    color: var(--gc-text);
}

.fc .fc-day-today {
    background: #e8f0fe !important;
}

.fc .fc-day-today .fc-daygrid-day-number {
    background: var(--gc-primary);
    color: white;
    width: 28px;
    height: 28px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
}

/* Eventos */
.fc-event {
    border: none !important;
    border-radius: 4px !important;
    padding: 2px 6px !important;
    font-size: 12px !important;
    font-weight: 500 !important;
    cursor: pointer !important;
    margin: 1px 2px !important;
}

.fc-event:hover {
    filter: brightness(0.95);
}

.fc-event-main {
    padding: 2px 4px;
}

/* Lista (Programação) - Estilo Google */
.fc-list {
    border: none !important;
}

.fc-list-day-cushion {
    background: #f8f9fa !important;
    padding: 8px 16px !important;
}

.fc-list-day-text, .fc-list-day-side-text {
    font-size: 14px !important;
    font-weight: 500 !important;
    color: var(--gc-text) !important;
}

.fc-list-event {
    cursor: pointer;
}

.fc-list-event:hover td {
    background: #f1f3f4 !important;
}

.fc-list-event-time {
    font-size: 13px !important;
    color: var(--gc-text-secondary) !important;
    padding: 12px 16px !important;
}

.fc-list-event-title {
    font-size: 14px !important;
    color: var(--gc-text) !important;
    padding: 12px 16px !important;
}

.fc-list-event-dot {
    border-radius: 50% !important;
}

/* TimeGrid (Dia/Semana) */
.fc-timegrid-slot {
    height: 48px !important;
}

.fc-timegrid-slot-label {
    font-size: 11px !important;
    color: var(--gc-text-secondary) !important;
}

.fc-timegrid-event {
    border-radius: 4px !important;
    border-left: 4px solid !important;
}

/* Mobile Responsivo */
@media (max-width: 768px) {
    .calendar-wrapper {
        height: calc(100vh - 80px);
        flex-direction: column;
    }
    
    .calendar-header {
        padding: 8px 12px;
    }
    
    .calendar-logo {
        font-size: 18px;
    }
    
    .calendar-logo i {
        font-size: 24px;
    }
    
    .current-date {
        font-size: 16px;
        min-width: auto;
    }
    
    .view-selector {
        overflow-x: auto;
        max-width: 100%;
    }
    
    .view-btn {
        padding: 6px 10px;
        font-size: 12px;
    }
    
    .btn-create {
        width: 48px;
        height: 48px;
        padding: 0;
        border-radius: 50%;
        position: fixed;
        bottom: 80px;
        right: 16px;
        z-index: 1000;
        background: var(--gc-primary);
    }
    
    .btn-create span {
        display: none;
    }
    
    .btn-create i {
        color: white;
        font-size: 28px;
    }
    
    .fc .fc-daygrid-day {
        min-height: 60px;
    }
}

/* Modal Estilo Google */
.modal-google .modal-content {
    border: none;
    border-radius: 8px;
    box-shadow: 0 24px 38px 3px rgba(0,0,0,0.14), 0 9px 46px 8px rgba(0,0,0,0.12), 0 11px 15px -7px rgba(0,0,0,0.2);
}

.modal-google .modal-header {
    border: none;
    padding: 24px 24px 16px;
}

.modal-google .modal-title {
    font-size: 22px;
    font-weight: 400;
    color: var(--gc-text);
}

.modal-google .modal-body {
    padding: 0 24px 24px;
}

.modal-google .modal-footer {
    border: none;
    padding: 16px 24px;
}

.event-detail-row {
    display: flex;
    align-items: flex-start;
    gap: 16px;
    padding: 12px 0;
}

.event-detail-icon {
    width: 24px;
    color: var(--gc-text-secondary);
    text-align: center;
}

.event-detail-content {
    flex: 1;
}

.event-detail-label {
    font-size: 12px;
    color: var(--gc-text-secondary);
    margin-bottom: 2px;
}

.event-detail-value {
    font-size: 14px;
    color: var(--gc-text);
}

.status-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 4px 12px;
    border-radius: 16px;
    font-size: 12px;
    font-weight: 500;
}

.status-badge.pending { background: #fef7e0; color: #b06000; }
.status-badge.confirmed { background: #e3f2fd; color: #0277bd; }
.status-badge.in-progress { background: #e8eaf6; color: #3949ab; }
.status-badge.completed { background: #e8f5e9; color: #2e7d32; }
.status-badge.cancelled { background: #ffebee; color: #c62828; }
</style>

<div class="calendar-wrapper">
    <!-- Sidebar -->
    <div class="calendar-sidebar">
        <button class="btn-create" onclick="window.location.href='<?= APP_URL ?>bookings/create'">
            <i class="bi bi-plus-lg"></i>
            <span>Criar</span>
        </button>
        
        <div class="mini-calendar">
            <div class="mini-header">
                <div class="mini-month" id="miniMonthLabel"></div>
                <div class="mini-nav">
                    <button class="mini-nav-btn" id="miniPrev" type="button" aria-label="Mês anterior">
                        <i class="bi bi-chevron-left"></i>
                    </button>
                    <button class="mini-nav-btn" id="miniNext" type="button" aria-label="Próximo mês">
                        <i class="bi bi-chevron-right"></i>
                    </button>
                </div>
            </div>
            <div class="mini-weekdays">
                <span>D</span>
                <span>S</span>
                <span>T</span>
                <span>Q</span>
                <span>Q</span>
                <span>S</span>
                <span>S</span>
            </div>
            <div class="mini-grid" id="miniCalendarGrid"></div>
        </div>

        <div class="calendar-agendas">
            <div class="legend-title">Minhas agendas</div>
            <label class="legend-item agenda-item">
                <input type="checkbox" class="agenda-toggle" data-status="pending" checked>
                <span class="legend-dot pending"></span>
                <span class="agenda-name">Pendente</span>
            </label>
            <label class="legend-item agenda-item">
                <input type="checkbox" class="agenda-toggle" data-status="confirmed" checked>
                <span class="legend-dot confirmed"></span>
                <span class="agenda-name">Confirmado</span>
            </label>
            <label class="legend-item agenda-item">
                <input type="checkbox" class="agenda-toggle" data-status="in_progress" checked>
                <span class="legend-dot in-progress"></span>
                <span class="agenda-name">Em Andamento</span>
            </label>
            <label class="legend-item agenda-item">
                <input type="checkbox" class="agenda-toggle" data-status="completed" checked>
                <span class="legend-dot completed"></span>
                <span class="agenda-name">Concluído</span>
            </label>
            <label class="legend-item agenda-item">
                <input type="checkbox" class="agenda-toggle" data-status="cancelled" checked>
                <span class="legend-dot cancelled"></span>
                <span class="agenda-name">Cancelado</span>
            </label>
            <button class="agenda-add" type="button" id="btnAddAgenda" data-bs-toggle="modal" data-bs-target="#createEventModal">
                <i class="bi bi-plus-lg"></i>
                Adicionar agenda
            </button>
        </div>
        
        <!-- Importar/Exportar - Google Calendar -->
        <div class="calendar-actions mt-4">
            <div class="legend-title">Google Calendar</div>
            <a href="<?= APP_URL ?>calendar/import" class="btn btn-outline-primary btn-sm w-100 mb-2">
                <i class="bi bi-cloud-download me-2"></i>Importar
            </a>
            <a href="<?= APP_URL ?>calendar/export" class="btn btn-outline-secondary btn-sm w-100">
                <i class="bi bi-cloud-upload me-2"></i>Exportar
            </a>
        </div>
    </div>
    
    <!-- Main -->
    <div class="calendar-main">
        <!-- Header -->
        <div class="calendar-header">
            <div class="calendar-header-left">
                <div class="calendar-logo">
                    <i class="bi bi-calendar3"></i>
                    <span class="d-none d-md-inline">Agenda</span>
                </div>
                
                <div class="calendar-nav">
                    <button class="btn-today" id="btnToday">Hoje</button>
                    <button class="btn-nav" id="btnPrev"><i class="bi bi-chevron-left"></i></button>
                    <button class="btn-nav" id="btnNext"><i class="bi bi-chevron-right"></i></button>
                </div>
                
                <div class="current-date" id="currentDate"></div>
            </div>
            
            <div class="view-selector">
                <button class="view-btn" data-view="listMonth">Programação</button>
                <button class="view-btn" data-view="timeGridDay">Dia</button>
                <button class="view-btn" data-view="timeGridWeek">Semana</button>
                <button class="view-btn" data-view="dayGridMonth">Mês</button>
                <button class="view-btn" data-view="multiMonthYear">Ano</button>
                <button class="view-btn" data-view="listWeek">7 dias</button>
            </div>
        </div>
        
        <!-- Calendário -->
        <div id="calendar"></div>
    </div>
</div>

<!-- Botão Criar Mobile (FAB) -->
<button class="btn-create d-lg-none" onclick="window.location.href='<?= APP_URL ?>bookings/create'" style="display:none" id="fabCreate">
    <i class="bi bi-plus-lg"></i>
    <span>Criar</span>
</button>

<!-- Modal de Detalhes -->
<div class="modal fade modal-google" id="eventModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="eventTitle">Detalhes do Agendamento</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="eventDetails"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-danger me-auto" id="btnDeleteEvent" style="display:none">
                    <i class="bi bi-trash me-1"></i>Excluir
                </button>
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Fechar</button>
                <a href="#" id="eventEditBtn" class="btn btn-primary">
                    <i class="bi bi-pencil me-1"></i>Editar
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Criar Evento -->
<div class="modal fade modal-google" id="createEventModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-calendar-plus me-2 text-primary"></i>
                    Novo Evento na Agenda
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="createEventForm">
                <input type="hidden" name="csrf_token" value="<?= $csrfToken ?? '' ?>">
                <div class="modal-body">
                    <div class="row g-3">
                        <!-- Título -->
                        <div class="col-12">
                            <label class="form-label">Título do Evento <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="title" id="eventTitleInput" required 
                                   placeholder="Ex: Reunião com cliente, Transfer VCP-GRU...">
                        </div>
                        
                        <!-- Data e Hora -->
                        <div class="col-md-6">
                            <label class="form-label">Data de Início <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" name="start_date" id="eventStartDate" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Hora de Início <span class="text-danger">*</span></label>
                            <input type="time" class="form-control" name="start_time" id="eventStartTime" required>
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label">Data de Término</label>
                            <input type="date" class="form-control" name="end_date" id="eventEndDate">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Hora de Término</label>
                            <input type="time" class="form-control" name="end_time" id="eventEndTime">
                        </div>
                        
                        <!-- Dia inteiro -->
                        <div class="col-12">
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" name="all_day" id="eventAllDay">
                                <label class="form-check-label" for="eventAllDay">Dia inteiro</label>
                            </div>
                        </div>
                        
                        <!-- Local -->
                        <div class="col-12">
                            <label class="form-label">Local</label>
                            <input type="text" class="form-control" name="location" id="eventLocation" 
                                   placeholder="Ex: Aeroporto de Guarulhos, Escritório...">
                        </div>
                        
                        <!-- Descrição -->
                        <div class="col-12">
                            <label class="form-label">Descrição</label>
                            <textarea class="form-control" name="description" id="eventDescription" rows="2" 
                                      placeholder="Observações adicionais..."></textarea>
                        </div>
                        
                        <!-- Cor -->
                        <div class="col-md-6">
                            <label class="form-label">Cor do Evento</label>
                            <select class="form-select" name="color" id="eventColor">
                                <option value="#1a73e8">🔵 Azul (Padrão)</option>
                                <option value="#33b679">🟢 Verde</option>
                                <option value="#f9ab00">🟡 Amarelo</option>
                                <option value="#d93025">🔴 Vermelho</option>
                                <option value="#7986cb">🟣 Roxo</option>
                                <option value="#039be5">🔷 Ciano</option>
                                <option value="#616161">⚫ Cinza</option>
                            </select>
                        </div>
                        
                        <!-- Tipo -->
                        <div class="col-md-6">
                            <label class="form-label">Tipo de Evento</label>
                            <select class="form-select" name="event_type" id="eventType">
                                <option value="personal">Pessoal</option>
                                <option value="meeting">Reunião</option>
                                <option value="reminder">Lembrete</option>
                                <option value="task">Tarefa</option>
                            </select>
                        </div>
                        
                        <!-- Associações -->
                        <div class="col-md-6">
                            <label class="form-label">Cliente (opcional)</label>
                            <select class="form-select" name="client_id" id="eventClientId">
                                <option value="">Nenhum</option>
                                <?php if (!empty($clients)): ?>
                                    <?php foreach ($clients as $c): ?>
                                        <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['name']) ?></option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Motorista (opcional)</label>
                            <select class="form-select" name="driver_id" id="eventDriverId">
                                <option value="">Nenhum</option>
                                <?php if (!empty($drivers)): ?>
                                    <?php foreach ($drivers as $d): ?>
                                        <option value="<?= $d['id'] ?>"><?= htmlspecialchars($d['name']) ?></option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>
                        
                        <!-- Notificações -->
                        <div class="col-12">
                            <label class="form-label">Notificações por E-mail</label>
                            <div class="d-flex flex-wrap gap-3">
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" name="notify_email" id="notifyEmail">
                                    <label class="form-check-label" for="notifyEmail">Enviar e-mail</label>
                                </div>
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" name="notify_client" id="notifyClient">
                                    <label class="form-check-label" for="notifyClient">Notificar cliente</label>
                                </div>
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" name="notify_driver" id="notifyDriver">
                                    <label class="form-check-label" for="notifyDriver">Notificar motorista</label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary" id="btnSaveEvent">
                        <i class="bi bi-check-lg me-1"></i>Salvar Evento
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- FullCalendar JS -->
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/locales/pt-br.global.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const calendarEl = document.getElementById('calendar');
    const modal = new bootstrap.Modal(document.getElementById('eventModal'));
    const currentDateEl = document.getElementById('currentDate');
    const viewButtons = document.querySelectorAll('.view-btn');
    const miniMonthLabel = document.getElementById('miniMonthLabel');
    const miniCalendarGrid = document.getElementById('miniCalendarGrid');
    const miniPrevBtn = document.getElementById('miniPrev');
    const miniNextBtn = document.getElementById('miniNext');
    const agendaToggles = Array.from(document.querySelectorAll('.agenda-toggle'));

    let selectedDate = new Date();
    let miniCurrent = new Date();
    let activeStatuses = new Set();
    
    // Cores por status
    const statusColors = {
        'pending': '#f9ab00',
        'confirmed': '#039be5',
        'in_progress': '#7986cb',
        'completed': '#33b679',
        'cancelled': '#d93025'
    };
    
    const statusLabels = {
        'pending': 'Pendente',
        'confirmed': 'Confirmado',
        'in_progress': 'Em Andamento',
        'completed': 'Concluído',
        'cancelled': 'Cancelado'
    };

    syncActiveStatuses();

    // Inicializa FullCalendar
    const calendar = new FullCalendar.Calendar(calendarEl, {
        locale: 'pt-br',
        initialView: 'listMonth',
        height: '100%',
        headerToolbar: false,
        navLinks: true,
        editable: false,
        dayMaxEvents: 3,
        moreLinkText: 'mais',
        noEventsText: 'Nenhum evento para exibir',
        nowIndicator: true,
        slotMinTime: '06:00:00',
        slotMaxTime: '22:00:00',
        allDaySlot: false,
        
        events: {
            url: APP_URL + 'api/calendar/events',
            method: 'GET',
            failure: function() {
                showToast('Erro ao carregar eventos', 'error');
            }
        },
        
        eventDidMount: function(info) {
            const status = info.event.extendedProps.status;
            info.el.style.backgroundColor = statusColors[status] || '#6c757d';
            info.el.style.borderColor = statusColors[status] || '#6c757d';
            if (status && !activeStatuses.has(status)) {
                info.el.style.display = 'none';
            }
        },
        
        eventClick: function(info) {
            const event = info.event;
            const props = event.extendedProps;
            const startDate = event.start;
            
            document.getElementById('eventTitle').textContent = props.client || 'Agendamento';
            
            document.getElementById('eventDetails').innerHTML = `
                <div class="event-detail-row">
                    <div class="event-detail-icon"><i class="bi bi-bookmark"></i></div>
                    <div class="event-detail-content">
                        <div class="event-detail-label">Código</div>
                        <div class="event-detail-value">${props.code || '-'}</div>
                    </div>
                </div>
                <div class="event-detail-row">
                    <div class="event-detail-icon"><i class="bi bi-flag"></i></div>
                    <div class="event-detail-content">
                        <div class="event-detail-label">Status</div>
                        <div class="event-detail-value">
                            <span class="status-badge ${props.status}">${statusLabels[props.status] || props.status}</span>
                        </div>
                    </div>
                </div>
                <div class="event-detail-row">
                    <div class="event-detail-icon"><i class="bi bi-clock"></i></div>
                    <div class="event-detail-content">
                        <div class="event-detail-label">Data e Hora</div>
                        <div class="event-detail-value">
                            ${startDate.toLocaleDateString('pt-BR', {weekday: 'long', year: 'numeric', month: 'long', day: 'numeric'})}
                            <br>${startDate.toLocaleTimeString('pt-BR', {hour: '2-digit', minute: '2-digit'})}
                        </div>
                    </div>
                </div>
                <div class="event-detail-row">
                    <div class="event-detail-icon"><i class="bi bi-person"></i></div>
                    <div class="event-detail-content">
                        <div class="event-detail-label">Cliente</div>
                        <div class="event-detail-value">${props.client || '-'}</div>
                    </div>
                </div>
                <div class="event-detail-row">
                    <div class="event-detail-icon"><i class="bi bi-car-front"></i></div>
                    <div class="event-detail-content">
                        <div class="event-detail-label">Motorista</div>
                        <div class="event-detail-value">${props.driver || '-'}</div>
                    </div>
                </div>
                <div class="event-detail-row">
                    <div class="event-detail-icon"><i class="bi bi-geo-alt"></i></div>
                    <div class="event-detail-content">
                        <div class="event-detail-label">Origem → Destino</div>
                        <div class="event-detail-value">${props.origin || '-'} → ${props.destination || '-'}</div>
                    </div>
                </div>
            `;
            
            document.getElementById('eventEditBtn').href = APP_URL + 'bookings/' + event.id + '/edit';
            modal.show();
        },
        
        dateClick: function(info) {
            window.location.href = APP_URL + 'bookings/create?date=' + info.dateStr;
        },
        
        datesSet: function(info) {
            updateCurrentDate();
            syncMiniWithCalendar();
        }
    });
    
    calendar.render();
    updateCurrentDate();
    syncMiniWithCalendar();
    setActiveView('listMonth');
    
    function syncActiveStatuses() {
        activeStatuses = new Set(
            agendaToggles.filter(toggle => toggle.checked).map(toggle => toggle.dataset.status)
        );
    }

    function formatDateLocal(date) {
        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const day = String(date.getDate()).padStart(2, '0');
        return `${year}-${month}-${day}`;
    }

    function parseDateLocal(dateStr) {
        const [year, month, day] = dateStr.split('-').map(Number);
        return new Date(year, month - 1, day);
    }

    function isSameDay(a, b) {
        return a.getFullYear() === b.getFullYear() &&
            a.getMonth() === b.getMonth() &&
            a.getDate() === b.getDate();
    }

    function renderMiniCalendar() {
        if (!miniMonthLabel || !miniCalendarGrid) {
            return;
        }

        const year = miniCurrent.getFullYear();
        const month = miniCurrent.getMonth();
        const firstDay = new Date(year, month, 1);
        const startWeekday = firstDay.getDay();
        const daysInMonth = new Date(year, month + 1, 0).getDate();
        const today = new Date();

        let monthLabel = firstDay.toLocaleDateString('pt-BR', { month: 'long', year: 'numeric' });
        monthLabel = monthLabel.charAt(0).toUpperCase() + monthLabel.slice(1);
        miniMonthLabel.textContent = monthLabel;

        miniCalendarGrid.innerHTML = '';
        const fragment = document.createDocumentFragment();

        for (let i = 0; i < startWeekday; i++) {
            const empty = document.createElement('span');
            empty.className = 'mini-day mini-day--empty';
            fragment.appendChild(empty);
        }

        for (let day = 1; day <= daysInMonth; day++) {
            const date = new Date(year, month, day);
            const button = document.createElement('button');
            button.type = 'button';
            button.className = 'mini-day';
            button.textContent = day;
            button.dataset.date = formatDateLocal(date);
            button.setAttribute('aria-label', date.toLocaleDateString('pt-BR', {
                weekday: 'long',
                day: 'numeric',
                month: 'long',
                year: 'numeric'
            }));

            if (isSameDay(date, today)) {
                button.classList.add('is-today');
                button.setAttribute('aria-current', 'date');
            }

            if (isSameDay(date, selectedDate)) {
                button.classList.add('is-selected');
            }

            fragment.appendChild(button);
        }

        miniCalendarGrid.appendChild(fragment);
    }

    function syncMiniWithCalendar() {
        if (!miniMonthLabel || !miniCalendarGrid) {
            return;
        }

        selectedDate = calendar.getDate();
        miniCurrent = new Date(selectedDate.getFullYear(), selectedDate.getMonth(), 1);
        renderMiniCalendar();
    }

    // Atualiza título da data
    function updateCurrentDate() {
        const view = calendar.view;
        const start = view.currentStart;
        
        let dateStr = '';
        if (view.type.includes('Month') || view.type === 'dayGridMonth') {
            dateStr = start.toLocaleDateString('pt-BR', { month: 'long', year: 'numeric' });
        } else if (view.type === 'timeGridWeek' || view.type === 'listWeek') {
            const end = new Date(start);
            end.setDate(end.getDate() + 6);
            dateStr = `${start.toLocaleDateString('pt-BR', { day: 'numeric', month: 'short' })} - ${end.toLocaleDateString('pt-BR', { day: 'numeric', month: 'short', year: 'numeric' })}`;
        } else if (view.type === 'timeGridDay') {
            dateStr = start.toLocaleDateString('pt-BR', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' });
        } else if (view.type === 'multiMonthYear') {
            dateStr = start.getFullYear().toString();
        } else {
            dateStr = start.toLocaleDateString('pt-BR', { month: 'long', year: 'numeric' });
        }
        
        currentDateEl.textContent = dateStr.charAt(0).toUpperCase() + dateStr.slice(1);
    }
    
    // Define visualização ativa
    function setActiveView(viewName) {
        viewButtons.forEach(btn => {
            btn.classList.toggle('active', btn.dataset.view === viewName);
        });
    }
    
    // Eventos dos botões
    document.getElementById('btnToday').addEventListener('click', () => {
        calendar.today();
    });
    
    document.getElementById('btnPrev').addEventListener('click', () => {
        calendar.prev();
    });
    
    document.getElementById('btnNext').addEventListener('click', () => {
        calendar.next();
    });
    
    viewButtons.forEach(btn => {
        btn.addEventListener('click', () => {
            const view = btn.dataset.view;
            calendar.changeView(view);
            setActiveView(view);
        });
    });

    // Mini calendário
    if (miniCalendarGrid) {
        miniCalendarGrid.addEventListener('click', (event) => {
            const target = event.target.closest('.mini-day[data-date]');
            if (!target) {
                return;
            }
            const dateStr = target.dataset.date;
            selectedDate = parseDateLocal(dateStr);
            calendar.gotoDate(dateStr);
            renderMiniCalendar();
        });
    }

    if (miniPrevBtn) {
        miniPrevBtn.addEventListener('click', () => {
            miniCurrent = new Date(miniCurrent.getFullYear(), miniCurrent.getMonth() - 1, 1);
            renderMiniCalendar();
        });
    }

    if (miniNextBtn) {
        miniNextBtn.addEventListener('click', () => {
            miniCurrent = new Date(miniCurrent.getFullYear(), miniCurrent.getMonth() + 1, 1);
            renderMiniCalendar();
        });
    }

    // Filtros de agenda
    agendaToggles.forEach(toggle => {
        toggle.addEventListener('change', () => {
            syncActiveStatuses();
            calendar.rerenderEvents();
        });
    });
    
    // FAB mobile
    if (window.innerWidth < 992) {
        document.getElementById('fabCreate').style.display = 'flex';
    }
    
    window.addEventListener('resize', () => {
        if (window.innerWidth < 992) {
            document.getElementById('fabCreate').style.display = 'flex';
        } else {
            document.getElementById('fabCreate').style.display = 'none';
        }
    });
    
    // ========================================
    // CRIAR EVENTO - Formulário
    // ========================================
    const createEventModal = document.getElementById('createEventModal');
    const createEventForm = document.getElementById('createEventForm');
    
    // Preenche data/hora atual ao abrir modal
    createEventModal.addEventListener('show.bs.modal', function() {
        const now = new Date();
        const dateStr = formatDateLocal(now);
        const timeStr = now.toTimeString().slice(0, 5);
        
        document.getElementById('eventStartDate').value = dateStr;
        document.getElementById('eventStartTime').value = timeStr;
        document.getElementById('eventEndDate').value = dateStr;
        
        // Hora de término = +1 hora
        const endTime = new Date(now.getTime() + 60 * 60 * 1000);
        document.getElementById('eventEndTime').value = endTime.toTimeString().slice(0, 5);
    });
    
    // Toggle dia inteiro
    document.getElementById('eventAllDay').addEventListener('change', function() {
        const timeInputs = document.querySelectorAll('#eventStartTime, #eventEndTime');
        timeInputs.forEach(input => {
            input.disabled = this.checked;
            if (this.checked) {
                input.value = '';
            }
        });
    });
    
    // Submit do formulário de criação
    createEventForm.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        const submitBtn = document.getElementById('btnSaveEvent');
        const originalText = submitBtn.innerHTML;
        
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Salvando...';
        
        try {
            const response = await fetch(APP_URL + 'api/calendar/events', {
                method: 'POST',
                body: formData
            });
            
            const result = await response.json();
            
            if (result.success) {
                // Fecha modal e atualiza calendário
                bootstrap.Modal.getInstance(createEventModal).hide();
                calendar.refetchEvents();
                createEventForm.reset();
                showToast('Evento criado com sucesso!', 'success');
            } else {
                showToast(result.message || 'Erro ao criar evento', 'error');
            }
        } catch (error) {
            console.error('Erro:', error);
            showToast('Erro de conexão', 'error');
        } finally {
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
        }
    });
    
    // ========================================
    // EXCLUIR EVENTO
    // ========================================
    let currentEventId = null;
    let currentEventType = null;
    
    document.getElementById('btnDeleteEvent').addEventListener('click', async function() {
        if (!currentEventId || currentEventType === 'booking') return;
        
        if (!confirm('Tem certeza que deseja cancelar este evento?')) return;
        
        try {
            const formData = new FormData();
            formData.append('id', currentEventId);
            formData.append('csrf_token', document.querySelector('[name="csrf_token"]')?.value || '');
            
            const response = await fetch(APP_URL + 'api/calendar/events/delete', {
                method: 'POST',
                body: formData
            });
            
            const result = await response.json();
            
            if (result.success) {
                modal.hide();
                calendar.refetchEvents();
                showToast('Evento cancelado', 'success');
            } else {
                showToast(result.message || 'Erro ao cancelar', 'error');
            }
        } catch (error) {
            showToast('Erro de conexão', 'error');
        }
    });
    
    // ========================================
    // TOAST NOTIFICATIONS
    // ========================================
    function showToast(message, type = 'info') {
        // Verifica se já existe container de toasts
        let container = document.querySelector('.toast-container');
        if (!container) {
            container = document.createElement('div');
            container.className = 'toast-container position-fixed bottom-0 end-0 p-3';
            container.style.zIndex = '9999';
            document.body.appendChild(container);
        }
        
        const toastId = 'toast-' + Date.now();
        const bgClass = type === 'success' ? 'bg-success' : type === 'error' ? 'bg-danger' : 'bg-primary';
        const icon = type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-circle' : 'info-circle';
        
        const toastHtml = `
            <div id="${toastId}" class="toast align-items-center text-white ${bgClass} border-0" role="alert">
                <div class="d-flex">
                    <div class="toast-body">
                        <i class="bi bi-${icon} me-2"></i>${message}
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                </div>
            </div>
        `;
        
        container.insertAdjacentHTML('beforeend', toastHtml);
        const toastEl = document.getElementById(toastId);
        const toast = new bootstrap.Toast(toastEl, { delay: 4000 });
        toast.show();
        
        toastEl.addEventListener('hidden.bs.toast', () => toastEl.remove());
    }
});
</script>
