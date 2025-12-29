<?php
/**
 * CHM Sistema - Controller de Calendário/Agenda (Google Calendar Style)
 * @author ch-mestriner (https://ch-mestriner.com.br)
 * @date 29/12/2025 17:55
 * @version 2.5.0
 */

namespace CHM\Calendar;

use CHM\Core\Controller;
use CHM\Core\Session;
use CHM\Core\Database;
use CHM\Core\Validator;
use CHM\Core\Helpers;
use CHM\Bookings\BookingModel;
use CHM\Clients\ClientModel;
use CHM\Drivers\DriverModel;

class CalendarController extends Controller
{
    private BookingModel $bookingModel;
    private CalendarModel $calendarModel;

    public function __construct()
    {
        parent::__construct();
        $this->bookingModel = new BookingModel();
        $this->calendarModel = new CalendarModel();
    }

    public function index(): void
    {
        $this->requireAuth();
        
        // Carrega dados para os selects do modal
        $clientModel = new ClientModel();
        $driverModel = new DriverModel();
        
        $this->setData('clients', $clientModel->getForSelect());
        $this->setData('drivers', $driverModel->getForSelect());
        
        $this->setTitle('Agenda');
        $this->view('calendar.index');
    }
    
    // API: Retorna eventos para o calendário (bookings + eventos customizados + feriados)
    public function apiEvents(): void
    {
        $this->requireAuth();
        
        $start = $this->input('start', date('Y-m-01'));
        $end = $this->input('end', date('Y-m-t'));
        $includeHolidays = $this->input('holidays', '1') === '1';
        
        $driverId = Session::isDriver() ? $this->getDriverId() : $this->input('driver_id');
        $clientId = Session::isClient() ? $this->getClientId() : $this->input('client_id');

        $events = [];
        
        // Cores por status
        $colors = [
            'pending' => '#f9ab00',
            'confirmed' => '#039be5',
            'in_progress' => '#7986cb',
            'completed' => '#33b679',
            'cancelled' => '#d93025'
        ];

        // 1. Bookings (agendamentos de transporte)
        $bookings = $this->bookingModel->getByDateRange($start, $end, $driverId, $clientId);
        foreach ($bookings as $b) {
            $events[] = [
                'id' => 'booking_' . $b['id'],
                'title' => ($b['client_name'] ?? 'Cliente') . ' - ' . substr($b['time'], 0, 5),
                'start' => $b['date'] . 'T' . $b['time'],
                'end' => $b['end_time'] ? $b['date'] . 'T' . $b['end_time'] : null,
                'backgroundColor' => $colors[$b['status']] ?? '#6c757d',
                'borderColor' => $colors[$b['status']] ?? '#6c757d',
                'textColor' => '#ffffff',
                'classNames' => ['event-booking', 'event-' . $b['status']],
                'extendedProps' => [
                    'type' => 'booking',
                    'bookingId' => $b['id'],
                    'code' => $b['code'],
                    'status' => $b['status'],
                    'origin' => $b['origin'] ?? '',
                    'destination' => $b['destination'] ?? '',
                    'driver' => $b['driver_name'] ?? '',
                    'client' => $b['client_name'] ?? '',
                    'editable' => true
                ]
            ];
        }
        
        // 2. Eventos personalizados do calendário
        $calendarEvents = $this->calendarModel->getEventsByDateRange($start, $end, Session::getUserId());
        foreach ($calendarEvents as $e) {
            $events[] = [
                'id' => 'event_' . $e['id'],
                'title' => $e['title'],
                'start' => $e['start_datetime'],
                'end' => $e['end_datetime'],
                'allDay' => (bool)$e['all_day'],
                'backgroundColor' => $e['color'] ?? '#1a73e8',
                'borderColor' => $e['color'] ?? '#1a73e8',
                'textColor' => '#ffffff',
                'classNames' => ['event-custom'],
                'extendedProps' => [
                    'type' => 'event',
                    'eventId' => $e['id'],
                    'description' => $e['description'] ?? '',
                    'location' => $e['location'] ?? '',
                    'status' => $e['status'] ?? 'confirmed',
                    'editable' => true
                ]
            ];
        }
        
        // 3. Feriados brasileiros
        if ($includeHolidays) {
            $startYear = (int)substr($start, 0, 4);
            $endYear = (int)substr($end, 0, 4);
            
            for ($year = $startYear; $year <= $endYear; $year++) {
                $holidays = $this->calendarModel->getBrazilianHolidays($year);
                foreach ($holidays as $h) {
                    if ($h['date'] >= $start && $h['date'] <= $end) {
                        $events[] = [
                            'id' => 'holiday_' . $h['date'],
                            'title' => $h['title'],
                            'start' => $h['date'],
                            'allDay' => true,
                            'backgroundColor' => '#e8f5e9',
                            'borderColor' => '#4caf50',
                            'textColor' => '#2e7d32',
                            'classNames' => ['event-holiday'],
                            'display' => 'background',
                            'extendedProps' => [
                                'type' => 'holiday',
                                'editable' => false
                            ]
                        ];
                    }
                }
            }
        }

        $this->json($events);
    }
    
    // API: Criar evento de agenda
    public function apiCreateEvent(): void
    {
        $this->requireAuth();
        
        if (!$this->validateCsrf()) {
            $this->jsonError('Token inválido', 403);
            return;
        }
        
        $data = $this->all();
        
        $validator = new Validator($data);
        $validator->rule('title', 'required|min:2|max:255', 'Título');
        $validator->rule('start_date', 'required|date:Y-m-d', 'Data de início');
        $validator->rule('start_time', 'required', 'Hora de início');
        
        if (!$validator->validate()) {
            $this->jsonError($validator->getFirstError(), 400);
            return;
        }
        
        $startDatetime = $data['start_date'] . ' ' . $data['start_time'] . ':00';
        $endDatetime = !empty($data['end_date']) && !empty($data['end_time'])
            ? $data['end_date'] . ' ' . $data['end_time'] . ':00'
            : date('Y-m-d H:i:s', strtotime($startDatetime . ' +1 hour'));
        
        $eventData = [
            'title' => $data['title'],
            'description' => $data['description'] ?? '',
            'location' => $data['location'] ?? '',
            'start_datetime' => $startDatetime,
            'end_datetime' => $endDatetime,
            'all_day' => isset($data['all_day']) ? 1 : 0,
            'color' => $data['color'] ?? '#1a73e8',
            'event_type' => $data['event_type'] ?? 'personal',
            'status' => 'confirmed',
            'source' => 'manual',
            'client_id' => $data['client_id'] ?? null,
            'driver_id' => $data['driver_id'] ?? null,
            'user_id' => Session::getUserId(),
            'notify_email' => isset($data['notify_email']) ? 1 : 0,
            'notify_client' => isset($data['notify_client']) ? 1 : 0,
            'notify_driver' => isset($data['notify_driver']) ? 1 : 0,
            'email_sent' => 0
        ];
        
        $id = $this->calendarModel->createEvent($eventData);
        
        if ($id) {
            // Envia notificações por e-mail se solicitado
            if ($eventData['notify_email']) {
                $this->sendEventNotification($id, 'created');
            }
            
            Helpers::logAction('Evento de agenda criado', 'calendar', null, ['id' => $id, 'title' => $data['title']]);
            
            $this->jsonSuccess('Evento criado com sucesso', ['id' => $id]);
        } else {
            $this->jsonError('Erro ao criar evento');
        }
    }
    
    // API: Atualizar evento
    public function apiUpdateEvent(): void
    {
        $this->requireAuth();
        
        if (!$this->validateCsrf()) {
            $this->jsonError('Token inválido', 403);
            return;
        }
        
        $id = (int)$this->input('id');
        $event = $this->calendarModel->find($id);
        
        if (!$event) {
            $this->jsonError('Evento não encontrado', 404);
            return;
        }
        
        // Verifica permissão
        if ($event['user_id'] != Session::getUserId() && !Session::isAdmin()) {
            $this->jsonError('Sem permissão', 403);
            return;
        }
        
        $data = $this->all();
        
        $updateData = [];
        if (isset($data['title'])) $updateData['title'] = $data['title'];
        if (isset($data['description'])) $updateData['description'] = $data['description'];
        if (isset($data['location'])) $updateData['location'] = $data['location'];
        if (isset($data['color'])) $updateData['color'] = $data['color'];
        if (isset($data['status'])) $updateData['status'] = $data['status'];
        
        if (!empty($data['start_date']) && !empty($data['start_time'])) {
            $updateData['start_datetime'] = $data['start_date'] . ' ' . $data['start_time'] . ':00';
        }
        if (!empty($data['end_date']) && !empty($data['end_time'])) {
            $updateData['end_datetime'] = $data['end_date'] . ' ' . $data['end_time'] . ':00';
        }
        
        if ($this->calendarModel->updateEvent($id, $updateData)) {
            // Envia notificação de atualização
            if ($event['notify_email']) {
                $this->sendEventNotification($id, 'updated');
            }
            
            Helpers::logAction('Evento de agenda atualizado', 'calendar', null, ['id' => $id]);
            $this->jsonSuccess('Evento atualizado');
        } else {
            $this->jsonError('Erro ao atualizar evento');
        }
    }
    
    // API: Excluir/Cancelar evento
    public function apiDeleteEvent(): void
    {
        $this->requireAuth();
        
        $id = (int)$this->input('id');
        $event = $this->calendarModel->find($id);
        
        if (!$event) {
            $this->jsonError('Evento não encontrado', 404);
            return;
        }
        
        // Verifica permissão
        if ($event['user_id'] != Session::getUserId() && !Session::isAdmin()) {
            $this->jsonError('Sem permissão', 403);
            return;
        }
        
        // Cancela ao invés de deletar (soft delete)
        if ($this->calendarModel->updateEvent($id, ['status' => 'cancelled'])) {
            // Envia notificação de cancelamento
            if ($event['notify_email']) {
                $this->sendEventNotification($id, 'cancelled');
            }
            
            Helpers::logAction('Evento de agenda cancelado', 'calendar', null, ['id' => $id]);
            $this->jsonSuccess('Evento cancelado');
        } else {
            $this->jsonError('Erro ao cancelar evento');
        }
    }
    
    // API: Buscar detalhes de um evento
    public function apiGetEvent(): void
    {
        $this->requireAuth();
        
        $id = (int)$this->input('id');
        $type = $this->input('type', 'event');
        
        if ($type === 'booking') {
            $event = $this->bookingModel->getWithDetails($id);
            if ($event) {
                $this->json([
                    'success' => true,
                    'type' => 'booking',
                    'data' => $event
                ]);
            } else {
                $this->jsonError('Agendamento não encontrado', 404);
            }
        } else {
            $event = $this->calendarModel->find($id);
            if ($event) {
                $this->json([
                    'success' => true,
                    'type' => 'event',
                    'data' => $event
                ]);
            } else {
                $this->jsonError('Evento não encontrado', 404);
            }
        }
    }
    
    // API: Retorna feriados brasileiros
    public function apiHolidays(): void
    {
        $this->requireAuth();
        
        $year = (int)$this->input('year', date('Y'));
        $holidays = $this->calendarModel->getBrazilianHolidays($year);
        
        $this->json([
            'success' => true,
            'year' => $year,
            'holidays' => $holidays
        ]);
    }
    
    // Envia notificação por e-mail
    private function sendEventNotification(int $eventId, string $action): void
    {
        $event = $this->calendarModel->find($eventId);
        if (!$event) return;
        
        try {
            $emailService = new \CHM\Core\EmailService();
            
            $subject = match($action) {
                'created' => 'Novo Evento: ' . $event['title'],
                'updated' => 'Evento Atualizado: ' . $event['title'],
                'cancelled' => 'Evento Cancelado: ' . $event['title'],
                default => 'Notificação de Evento: ' . $event['title']
            };
            
            $body = $this->renderEmailTemplate($event, $action);
            
            // E-mail para CHM interno
            $internalEmail = defined('SMTP_FROM') ? SMTP_FROM : 'contato@chm-sistema.com.br';
            $emailService->send($internalEmail, $subject, $body);
            
            // E-mail para cliente se configurado
            if ($event['notify_client'] && $event['client_id']) {
                $clientModel = new ClientModel();
                $client = $clientModel->find($event['client_id']);
                if ($client && !empty($client['email'])) {
                    $emailService->send($client['email'], $subject, $body);
                }
            }
            
            // E-mail para motorista se configurado
            if ($event['notify_driver'] && $event['driver_id']) {
                $driverModel = new DriverModel();
                $driver = $driverModel->find($event['driver_id']);
                if ($driver && !empty($driver['email'])) {
                    $emailService->send($driver['email'], $subject, $body);
                }
            }
            
            // Marca como enviado
            $this->calendarModel->markEmailSent($eventId);
            
        } catch (\Exception $e) {
            Helpers::logAction('Erro ao enviar e-mail de evento: ' . $e->getMessage(), 'calendar');
        }
    }
    
    // Renderiza template de e-mail
    private function renderEmailTemplate(array $event, string $action): string
    {
        $actionText = match($action) {
            'created' => 'foi criado',
            'updated' => 'foi atualizado',
            'cancelled' => 'foi cancelado',
            default => 'foi modificado'
        };
        
        $date = date('d/m/Y', strtotime($event['start_datetime']));
        $time = date('H:i', strtotime($event['start_datetime']));
        
        return "
        <html>
        <body style='font-family: Arial, sans-serif; padding: 20px;'>
            <h2 style='color: #1a73e8;'>CHM Sistema - Notificação de Evento</h2>
            <p>O evento <strong>{$event['title']}</strong> {$actionText}.</p>
            <hr style='border: none; border-top: 1px solid #ddd; margin: 20px 0;'>
            <p><strong>Data:</strong> {$date}</p>
            <p><strong>Hora:</strong> {$time}</p>
            " . (!empty($event['location']) ? "<p><strong>Local:</strong> {$event['location']}</p>" : "") . "
            " . (!empty($event['description']) ? "<p><strong>Descrição:</strong> {$event['description']}</p>" : "") . "
            <hr style='border: none; border-top: 1px solid #ddd; margin: 20px 0;'>
            <p style='color: #666; font-size: 12px;'>
                Este e-mail foi enviado automaticamente pelo CHM Sistema.<br>
                <a href='" . APP_URL . "calendar'>Acessar Agenda</a>
            </p>
        </body>
        </html>";
    }
    
    // Importar eventos de arquivo ICS (Google Calendar)
    public function import(): void
    {
        $this->requireAuth();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->setTitle('Importar Calendário');
            $this->view('calendar.import');
            return;
        }
        
        if (!isset($_FILES['ics_file']) || $_FILES['ics_file']['error'] !== UPLOAD_ERR_OK) {
            $this->jsonError('Arquivo não enviado ou erro no upload');
            return;
        }
        
        $file = $_FILES['ics_file'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        
        if ($ext !== 'ics') {
            $this->jsonError('Formato inválido. Use arquivo .ics');
            return;
        }
        
        $content = file_get_contents($file['tmp_name']);
        $events = $this->parseICS($content);
        
        if (empty($events)) {
            $this->jsonError('Nenhum evento encontrado no arquivo');
            return;
        }
        
        $imported = 0;
        $skipped = 0;
        
        foreach ($events as $event) {
            if ($this->importEvent($event)) {
                $imported++;
            } else {
                $skipped++;
            }
        }
        
        $this->jsonSuccess("Importação concluída: {$imported} eventos importados, {$skipped} ignorados");
    }
    
    // Exportar eventos para ICS
    public function export(): void
    {
        $this->requireAuth();
        
        $start = $this->input('start', '2008-01-01');
        $end = $this->input('end', date('Y-12-31'));
        
        $bookings = $this->bookingModel->getByDateRange($start, $end);
        $importedEvents = $this->getImportedEvents($start, $end);
        
        $ics = $this->generateICS($bookings, $importedEvents);
        
        header('Content-Type: text/calendar; charset=utf-8');
        header('Content-Disposition: attachment; filename="chm-calendario-' . date('Y-m-d') . '.ics"');
        echo $ics;
        exit;
    }
    
    // Parser de arquivo ICS
    private function parseICS(string $content): array
    {
        $events = [];
        $lines = explode("\n", str_replace("\r\n", "\n", $content));
        $event = null;
        $inEvent = false;
        
        foreach ($lines as $line) {
            $line = trim($line);
            
            if ($line === 'BEGIN:VEVENT') {
                $inEvent = true;
                $event = [
                    'uid' => '',
                    'summary' => '',
                    'description' => '',
                    'location' => '',
                    'dtstart' => '',
                    'dtend' => '',
                    'created' => '',
                    'status' => ''
                ];
            } elseif ($line === 'END:VEVENT' && $inEvent) {
                $inEvent = false;
                if (!empty($event['dtstart'])) {
                    $events[] = $event;
                }
            } elseif ($inEvent) {
                if (strpos($line, ':') !== false) {
                    [$key, $value] = explode(':', $line, 2);
                    $key = explode(';', $key)[0];
                    
                    switch (strtoupper($key)) {
                        case 'UID':
                            $event['uid'] = $value;
                            break;
                        case 'SUMMARY':
                            $event['summary'] = $this->decodeICSValue($value);
                            break;
                        case 'DESCRIPTION':
                            $event['description'] = $this->decodeICSValue($value);
                            break;
                        case 'LOCATION':
                            $event['location'] = $this->decodeICSValue($value);
                            break;
                        case 'DTSTART':
                            $event['dtstart'] = $this->parseICSDate($value);
                            break;
                        case 'DTEND':
                            $event['dtend'] = $this->parseICSDate($value);
                            break;
                        case 'CREATED':
                            $event['created'] = $this->parseICSDate($value);
                            break;
                        case 'STATUS':
                            $event['status'] = $value;
                            break;
                    }
                }
            }
        }
        
        return $events;
    }
    
    private function parseICSDate(string $value): ?string
    {
        $value = preg_replace('/[^0-9TZ]/', '', $value);
        
        if (strlen($value) >= 8) {
            $year = substr($value, 0, 4);
            $month = substr($value, 4, 2);
            $day = substr($value, 6, 2);
            $hour = strlen($value) >= 11 ? substr($value, 9, 2) : '00';
            $min = strlen($value) >= 13 ? substr($value, 11, 2) : '00';
            
            return "{$year}-{$month}-{$day} {$hour}:{$min}:00";
        }
        
        return null;
    }
    
    private function decodeICSValue(string $value): string
    {
        $value = str_replace(['\\n', '\\,', '\\;'], ["\n", ',', ';'], $value);
        return trim($value);
    }
    
    private function importEvent(array $event): bool
    {
        if (empty($event['dtstart']) || empty($event['summary'])) {
            return false;
        }
        
        // Verifica se já existe pelo UID
        if (!empty($event['uid'])) {
            $exists = $this->db->fetchOne(
                "SELECT id FROM " . DB_PREFIX . "calendar_events WHERE google_uid = :uid",
                ['uid' => $event['uid']]
            );
            if ($exists) {
                return false;
            }
        }
        
        $data = [
            'google_uid' => $event['uid'] ?: uniqid('evt_'),
            'title' => $event['summary'],
            'description' => $event['description'] ?? '',
            'location' => $event['location'] ?? '',
            'start_datetime' => $event['dtstart'],
            'end_datetime' => $event['dtend'] ?: $event['dtstart'],
            'all_day' => strlen($event['dtstart']) <= 10 ? 1 : 0,
            'source' => 'google_import',
            'created_at' => date('Y-m-d H:i:s'),
            'user_id' => Session::getUserId()
        ];
        
        return $this->db->insert(DB_PREFIX . 'calendar_events', $data);
    }
    
    private function getImportedEvents(string $start, string $end): array
    {
        $sql = "SELECT * FROM " . DB_PREFIX . "calendar_events 
                WHERE DATE(start_datetime) BETWEEN :start AND :end
                ORDER BY start_datetime";
        return $this->db->fetchAll($sql, ['start' => $start, 'end' => $end]) ?: [];
    }
    
    private function generateICS(array $bookings, array $importedEvents): string
    {
        $ics = "BEGIN:VCALENDAR\r\n";
        $ics .= "VERSION:2.0\r\n";
        $ics .= "PRODID:-//CHM Sistema//Agenda//PT\r\n";
        $ics .= "CALSCALE:GREGORIAN\r\n";
        $ics .= "METHOD:PUBLISH\r\n";
        $ics .= "X-WR-CALNAME:CHM Transportes\r\n";
        
        foreach ($bookings as $b) {
            $ics .= $this->bookingToVEVENT($b);
        }
        
        foreach ($importedEvents as $e) {
            $ics .= $this->importedEventToVEVENT($e);
        }
        
        $ics .= "END:VCALENDAR\r\n";
        
        return $ics;
    }
    
    private function bookingToVEVENT(array $b): string
    {
        $uid = 'booking-' . $b['id'] . '@chm-sistema.com.br';
        $start = date('Ymd\THis', strtotime($b['date'] . ' ' . $b['time']));
        $end = $b['end_time'] ? date('Ymd\THis', strtotime($b['date'] . ' ' . $b['end_time'])) : $start;
        $summary = $this->escapeICS($b['client_name'] ?? 'Agendamento');
        $location = $this->escapeICS($b['origin'] ?? '');
        $description = $this->escapeICS("Código: {$b['code']}\nDestino: " . ($b['destination'] ?? '') . "\nMotorista: " . ($b['driver_name'] ?? ''));
        
        $vevent = "BEGIN:VEVENT\r\n";
        $vevent .= "UID:{$uid}\r\n";
        $vevent .= "DTSTART:{$start}\r\n";
        $vevent .= "DTEND:{$end}\r\n";
        $vevent .= "SUMMARY:{$summary}\r\n";
        $vevent .= "LOCATION:{$location}\r\n";
        $vevent .= "DESCRIPTION:{$description}\r\n";
        $vevent .= "STATUS:CONFIRMED\r\n";
        $vevent .= "END:VEVENT\r\n";
        
        return $vevent;
    }
    
    private function importedEventToVEVENT(array $e): string
    {
        $uid = $e['google_uid'] ?: 'evt-' . $e['id'] . '@chm-sistema.com.br';
        $start = date('Ymd\THis', strtotime($e['start_datetime']));
        $end = date('Ymd\THis', strtotime($e['end_datetime']));
        
        $vevent = "BEGIN:VEVENT\r\n";
        $vevent .= "UID:{$uid}\r\n";
        $vevent .= "DTSTART:{$start}\r\n";
        $vevent .= "DTEND:{$end}\r\n";
        $vevent .= "SUMMARY:" . $this->escapeICS($e['title']) . "\r\n";
        if (!empty($e['location'])) {
            $vevent .= "LOCATION:" . $this->escapeICS($e['location']) . "\r\n";
        }
        if (!empty($e['description'])) {
            $vevent .= "DESCRIPTION:" . $this->escapeICS($e['description']) . "\r\n";
        }
        $vevent .= "END:VEVENT\r\n";
        
        return $vevent;
    }
    
    private function escapeICS(string $value): string
    {
        return str_replace(["\n", ',', ';'], ['\\n', '\\,', '\\;'], $value);
    }

    public function events(): void
    {
        $this->requireAuth();
        
        $start = $this->input('start', date('Y-m-01'));
        $end = $this->input('end', date('Y-m-t'));
        $driverId = Session::isDriver() ? $this->getDriverId() : $this->input('driver_id');
        $clientId = Session::isClient() ? $this->getClientId() : $this->input('client_id');

        $bookings = $this->bookingModel->getByDateRange($start, $end, $driverId, $clientId);

        $events = [];
        $colors = [
            'pending' => '#ffc107',
            'confirmed' => '#17a2b8',
            'in_progress' => '#007bff',
            'completed' => '#28a745',
            'cancelled' => '#dc3545'
        ];

        foreach ($bookings as $b) {
            $events[] = [
                'id' => $b['id'],
                'title' => ($b['client_name'] ?? 'Cliente') . ' - ' . substr($b['time'], 0, 5),
                'start' => $b['date'] . 'T' . $b['time'],
                'end' => $b['end_time'] ? $b['date'] . 'T' . $b['end_time'] : null,
                'backgroundColor' => $colors[$b['status']] ?? '#6c757d',
                'borderColor' => $colors[$b['status']] ?? '#6c757d',
                'extendedProps' => [
                    'code' => $b['code'],
                    'status' => $b['status'],
                    'origin' => $b['origin'],
                    'destination' => $b['destination'] ?? '',
                    'driver' => $b['driver_name'] ?? '',
                    'client' => $b['client_name'] ?? ''
                ]
            ];
        }

        $this->json($events);
    }

    public function dayView(): void
    {
        $this->requireAuth();
        $date = $this->input('date', date('Y-m-d'));
        
        $bookings = $this->bookingModel->getByDate($date);
        
        $this->setTitle('Agenda - ' . date('d/m/Y', strtotime($date)));
        $this->setData('date', $date);
        $this->setData('bookings', $bookings);
        $this->view('calendar.day');
    }

    public function weekView(): void
    {
        $this->requireAuth();
        $startOfWeek = $this->input('start', date('Y-m-d', strtotime('monday this week')));
        $endOfWeek = date('Y-m-d', strtotime($startOfWeek . ' +6 days'));
        
        $bookings = $this->bookingModel->getByDateRange($startOfWeek, $endOfWeek);
        
        $days = [];
        for ($i = 0; $i < 7; $i++) {
            $date = date('Y-m-d', strtotime($startOfWeek . " +{$i} days"));
            $days[$date] = array_filter($bookings, fn($b) => $b['date'] === $date);
        }

        $this->setTitle('Agenda Semanal');
        $this->setData('startOfWeek', $startOfWeek);
        $this->setData('days', $days);
        $this->view('calendar.week');
    }

    private function getDriverId(): ?int
    {
        $userId = Session::getUserId();
        $sql = "SELECT id FROM " . DB_PREFIX . "drivers WHERE user_id = :user_id LIMIT 1";
        $result = $this->db->fetchOne($sql, ['user_id' => $userId]);
        return $result ? (int)$result['id'] : null;
    }

    private function getClientId(): ?int
    {
        $userId = Session::getUserId();
        $sql = "SELECT id FROM " . DB_PREFIX . "clients WHERE user_id = :user_id LIMIT 1";
        $result = $this->db->fetchOne($sql, ['user_id' => $userId]);
        return $result ? (int)$result['id'] : null;
    }
}
