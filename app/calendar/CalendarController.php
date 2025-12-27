<?php
/**
 * CHM Sistema - Controller de Calendário
 * @author ch-mestriner (https://ch-mestriner.com.br)
 * @date 25/12/2025
 * @version 2.0.0
 */

namespace CHM\Calendar;

use CHM\Core\Controller;
use CHM\Core\Session;
use CHM\Core\Database;
use CHM\Bookings\BookingModel;

class CalendarController extends Controller
{
    private BookingModel $bookingModel;

    public function __construct()
    {
        parent::__construct();
        $this->bookingModel = new BookingModel();
    }

    public function index(): void
    {
        $this->requireAuth();
        $this->setTitle('Agenda');
        $this->view('calendar.index');
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
