<?php
/**
 * CHM Sistema - Calendário/Agenda
 * @author ch-mestriner (https://ch-mestriner.com.br)
 * @date 23/12/2025
 */
?>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0"><i class="bi bi-calendar3 me-2"></i>Agenda</h4>
        <div>
            <a href="<?= APP_URL ?>bookings/create" class="btn btn-primary">
                <i class="bi bi-plus-lg me-1"></i>Novo Agendamento
            </a>
        </div>
    </div>

    <!-- Legenda -->
    <div class="card mb-3">
        <div class="card-body py-2">
            <div class="d-flex flex-wrap gap-3 justify-content-center">
                <span><span class="badge bg-warning">&nbsp;</span> Pendente</span>
                <span><span class="badge bg-info">&nbsp;</span> Confirmado</span>
                <span><span class="badge bg-primary">&nbsp;</span> Em Andamento</span>
                <span><span class="badge bg-success">&nbsp;</span> Concluído</span>
                <span><span class="badge bg-danger">&nbsp;</span> Cancelado</span>
            </div>
        </div>
    </div>

    <!-- Calendário -->
    <div class="card">
        <div class="card-body">
            <div id="calendar"></div>
        </div>
    </div>
</div>

<!-- Modal de Detalhes -->
<div class="modal fade" id="eventModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Detalhes do Agendamento</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="eventDetails">
                <!-- Conteúdo carregado via JS -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                <a href="#" id="eventEditBtn" class="btn btn-primary">Editar</a>
            </div>
        </div>
    </div>
</div>

<!-- FullCalendar -->
<link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/locales/pt-br.global.min.js"></script>

<style>
    #calendar {
        max-width: 100%;
    }
    .fc-event {
        cursor: pointer;
        padding: 2px 5px;
    }
    .fc-toolbar-title {
        font-size: 1.2rem !important;
    }
    @media (max-width: 768px) {
        .fc-toolbar {
            flex-direction: column;
            gap: 10px;
        }
        .fc-toolbar-chunk {
            display: flex;
            justify-content: center;
        }
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const calendarEl = document.getElementById('calendar');
    const modal = new bootstrap.Modal(document.getElementById('eventModal'));
    
    const calendar = new FullCalendar.Calendar(calendarEl, {
        locale: 'pt-br',
        initialView: window.innerWidth < 768 ? 'listWeek' : 'dayGridMonth',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay,listWeek'
        },
        buttonText: {
            today: 'Hoje',
            month: 'Mês',
            week: 'Semana',
            day: 'Dia',
            list: 'Lista'
        },
        navLinks: true,
        editable: false,
        dayMaxEvents: true,
        events: {
            url: APP_URL + 'api/calendar/events',
            method: 'GET',
            failure: function() {
                showToast('Erro ao carregar eventos', 'error');
            }
        },
        eventClick: function(info) {
            const event = info.event;
            const props = event.extendedProps;
            
            document.getElementById('eventDetails').innerHTML = `
                <div class="mb-3">
                    <strong>Código:</strong> ${props.code || '-'}<br>
                    <strong>Status:</strong> ${getStatusBadge(props.status)}
                </div>
                <div class="mb-3">
                    <strong>Cliente:</strong> ${props.client || '-'}<br>
                    <strong>Motorista:</strong> ${props.driver || '-'}
                </div>
                <div class="mb-3">
                    <strong>Origem:</strong> ${props.origin || '-'}<br>
                    <strong>Destino:</strong> ${props.destination || '-'}
                </div>
                <div>
                    <strong>Data:</strong> ${formatDate(event.start)}<br>
                    <strong>Hora:</strong> ${event.start.toLocaleTimeString('pt-BR', {hour: '2-digit', minute: '2-digit'})}
                </div>
            `;
            
            document.getElementById('eventEditBtn').href = APP_URL + 'bookings/' + event.id + '/edit';
            modal.show();
        },
        dateClick: function(info) {
            window.location.href = APP_URL + 'bookings/create?date=' + info.dateStr;
        }
    });
    
    calendar.render();

    function getStatusBadge(status) {
        const badges = {
            'pending': '<span class="badge bg-warning">Pendente</span>',
            'confirmed': '<span class="badge bg-info">Confirmado</span>',
            'in_progress': '<span class="badge bg-primary">Em Andamento</span>',
            'completed': '<span class="badge bg-success">Concluído</span>',
            'cancelled': '<span class="badge bg-danger">Cancelado</span>'
        };
        return badges[status] || status;
    }

    function formatDate(date) {
        return date.toLocaleDateString('pt-BR');
    }
});
</script>
