<?php
/**
 * CHM Sistema - Controller de Agendamentos
 * @author ch-mestriner (https://ch-mestriner.com.br)
 * @date 23/12/2025
 * @version 1.0.0
 */

namespace CHM\Bookings;

use CHM\Core\Controller;
use CHM\Core\Session;
use CHM\Core\Validator;
use CHM\Core\Helpers;
use CHM\Clients\ClientModel;
use CHM\Drivers\DriverModel;
use CHM\Vehicles\VehicleModel;
use CHM\WhatsApp\WhatsAppService;
use CHM\Finance\FinanceModel;

class BookingController extends Controller
{
    private BookingModel $model;

    public function __construct()
    {
        parent::__construct();
        $this->model = new BookingModel();
    }

    public function index(): void
    {
        $this->requireAuth();
        $page = (int)$this->input('page', 1);
        $status = $this->input('status');
        $date = $this->input('date');

        $conditions = [];
        if ($status) $conditions['status'] = $status;

        if ($date) {
            $bookings = $this->model->getByDate($date);
            $this->setData('bookings', $bookings);
        } else {
            $result = $this->model->paginate($page, 20, $conditions, 'date', 'DESC');
            $this->setData('bookings', $result['data']);
            $this->setData('pagination', $result);
        }

        $this->setTitle('Agendamentos');
        $this->view('bookings.index');
    }

    public function create(): void
    {
        $this->requireAuth();
        
        $clientModel = new ClientModel();
        $driverModel = new DriverModel();
        $vehicleModel = new VehicleModel();

        $this->setTitle('Novo Agendamento');
        $this->setData('clients', $clientModel->getForSelect());
        $this->setData('drivers', $driverModel->getForSelect());
        $this->setData('vehicles', $vehicleModel->getForSelect());
        $this->view('bookings.form');
    }

    public function store(): void
    {
        $this->requireAuth();
        if (!$this->validateCsrf()) {
            $this->error('Token inválido.');
            return;
        }

        $data = $this->all();
        $validator = new Validator($data);
        $validator->rule('client_id', 'required|exists:clients,id', 'Cliente');
        $validator->rule('date', 'required|date:Y-m-d', 'Data');
        $validator->rule('time', 'required', 'Hora');
        $validator->rule('origin', 'required|max:255', 'Origem');
        $validator->rule('value', 'required|numeric', 'Valor');

        if (!$validator->validate()) {
            $this->error($validator->getFirstError(), 400, $validator->getErrors());
            return;
        }

        $data['code'] = $this->model->generateCode();
        $data['value'] = Helpers::moedaFloat($data['value']);
        $data['extras'] = Helpers::moedaFloat($data['extras'] ?? '0');
        $data['discount'] = Helpers::moedaFloat($data['discount'] ?? '0');
        $data['total'] = $data['value'] + $data['extras'] - $data['discount'];
        $data['commission_rate'] = (float)($data['commission_rate'] ?? COMMISSION_RATE * 100);
        $data['commission_value'] = $this->model->calculateCommission($data['total'], $data['commission_rate']);
        $data['created_by'] = Session::getUserId();

        $id = $this->model->create($data);
        Helpers::logAction('Agendamento criado', 'bookings', null, ['id' => $id, 'code' => $data['code']]);
        
        $this->success('Agendamento criado com sucesso!', ['id' => $id, 'code' => $data['code']]);
    }

    public function edit(string $id): void
    {
        $this->requireAuth();
        $booking = $this->model->getWithDetails((int)$id);
        if (!$booking) {
            $this->redirect(APP_URL . 'bookings');
            return;
        }

        $clientModel = new ClientModel();
        $driverModel = new DriverModel();
        $vehicleModel = new VehicleModel();

        $this->setTitle('Editar Agendamento');
        $this->setData('booking', $booking);
        $this->setData('clients', $clientModel->getForSelect());
        $this->setData('drivers', $driverModel->getForSelect());
        $this->setData('vehicles', $vehicleModel->getForSelect());
        $this->view('bookings.form');
    }

    public function update(string $id): void
    {
        $this->requireAuth();
        if (!$this->validateCsrf()) {
            $this->error('Token inválido.');
            return;
        }

        $data = $this->all();
        $data['value'] = Helpers::moedaFloat($data['value']);
        $data['extras'] = Helpers::moedaFloat($data['extras'] ?? '0');
        $data['discount'] = Helpers::moedaFloat($data['discount'] ?? '0');
        $data['total'] = $data['value'] + $data['extras'] - $data['discount'];
        $data['commission_value'] = $this->model->calculateCommission($data['total'], $data['commission_rate']);

        $this->model->update((int)$id, $data);
        Helpers::logAction('Agendamento atualizado', 'bookings', null, ['id' => $id]);
        
        $this->success('Agendamento atualizado com sucesso!');
    }

    public function updateStatus(string $id): void
    {
        $this->requireAuth();
        $status = $this->input('status');
        $validStatuses = ['pending', 'confirmed', 'in_progress', 'completed', 'cancelled'];

        if (!in_array($status, $validStatuses)) {
            $this->error('Status inválido.');
            return;
        }

        $data = ['status' => $status];
        if ($status === 'cancelled') {
            $data['cancelled_at'] = date('Y-m-d H:i:s');
            $data['cancelled_reason'] = $this->input('reason', '');
        }

        $this->model->update((int)$id, $data);

        // Ao concluir agendamento, gerar contas financeiras automaticamente
        if ($status === 'completed') {
            $this->generateFinanceRecords((int)$id);
        }

        Helpers::logAction("Status alterado para {$status}", 'bookings', null, ['id' => $id]);
        
        $this->success('Status atualizado com sucesso!');
    }

    // Gera conta a receber e conta a pagar (comissão) ao concluir agendamento
    private function generateFinanceRecords(int $bookingId): void
    {
        $booking = $this->model->getWithDetails($bookingId);
        if (!$booking) return;

        $financeModel = new FinanceModel();

        // 1. Criar conta a RECEBER (entrada) vinculada ao agendamento
        $receivableData = [
            'description' => "Serviço {$booking['code']} - {$booking['client_name']}",
            'category' => 'servico',
            'client_id' => $booking['client_id'],
            'booking_id' => $bookingId,
            'due_date' => $booking['date'],
            'value' => $booking['total'],
            'status' => 'pending',
            'notes' => "Gerado automaticamente ao concluir agendamento {$booking['code']}"
        ];
        $financeModel->createReceivable($receivableData);
        Helpers::logAction('Conta a receber gerada', 'accounts_receivable', null, ['booking_id' => $bookingId]);

        // 2. Criar conta a PAGAR (comissão motorista) se houver motorista e comissão
        if ($booking['driver_id'] && $booking['commission_value'] > 0) {
            $payableData = [
                'description' => "Comissão {$booking['code']} - {$booking['driver_name']}",
                'category' => 'comissao',
                'supplier' => $booking['driver_name'],
                'driver_id' => $booking['driver_id'],
                'booking_id' => $bookingId,
                'due_date' => $booking['date'],
                'value' => $booking['commission_value'],
                'status' => 'pending',
                'notes' => "Comissão de {$booking['commission_rate']}% sobre {$booking['total']} - Agendamento {$booking['code']}"
            ];
            $financeModel->createPayable($payableData);
            Helpers::logAction('Comissão motorista gerada', 'accounts_payable', null, ['booking_id' => $bookingId, 'driver_id' => $booking['driver_id']]);
        }
    }

    public function show(string $id): void
    {
        $this->requireAuth();
        $booking = $this->model->getWithDetails((int)$id);
        if (!$booking) {
            $this->redirect(APP_URL . 'bookings');
            return;
        }

        $this->setTitle('Detalhes do Agendamento');
        $this->setData('booking', $booking);
        $this->view('bookings.show');
    }

    public function sendVoucher(string $id): void
    {
        $this->requireAuth();
        $booking = $this->model->getWithDetails((int)$id);
        if (!$booking) {
            $this->error('Agendamento não encontrado.');
            return;
        }

        $whatsapp = new WhatsAppService();
        $phone = $booking['client_phone'];

        if (!$phone) {
            $this->error('Cliente não possui telefone cadastrado.');
            return;
        }

        $message = "🚗 *CHM Transportes Executivos*\n\n";
        $message .= "Olá {$booking['client_name']}!\n\n";
        $message .= "Seu serviço está confirmado:\n";
        $message .= "📅 Data: " . Helpers::dataBr($booking['date']) . "\n";
        $message .= "⏰ Hora: " . substr($booking['time'], 0, 5) . "\n";
        $message .= "📍 Origem: {$booking['origin']}\n";
        if ($booking['destination']) {
            $message .= "📍 Destino: {$booking['destination']}\n";
        }
        if ($booking['driver_name']) {
            $message .= "👤 Motorista: {$booking['driver_name']}\n";
        }
        if ($booking['vehicle_model']) {
            $message .= "🚘 Veículo: {$booking['vehicle_model']} - {$booking['vehicle_plate']}\n";
        }
        $message .= "\n💰 Valor: " . Helpers::moeda($booking['total']) . "\n";
        $message .= "\nCódigo: *{$booking['code']}*";

        $result = $whatsapp->sendText($phone, $message, (int)$id);

        if ($result['success']) {
            $this->model->update((int)$id, [
                'voucher_sent' => 1,
                'voucher_sent_at' => date('Y-m-d H:i:s')
            ]);
            $this->success('Voucher enviado com sucesso!');
        } else {
            $this->error('Erro ao enviar voucher: ' . ($result['error'] ?? 'Erro desconhecido'));
        }
    }

    public function apiCalendar(): void
    {
        $this->requireAuth();
        $start = $this->input('start', date('Y-m-01'));
        $end = $this->input('end', date('Y-m-t'));
        
        $bookings = $this->model->getForCalendar($start, $end);
        
        $events = array_map(function($b) {
            $colors = [
                'pending' => '#ffc107',
                'confirmed' => '#17a2b8',
                'in_progress' => '#007bff',
                'completed' => '#28a745',
                'cancelled' => '#dc3545'
            ];
            return [
                'id' => $b['id'],
                'title' => $b['client_name'] . ' - ' . $b['origin'],
                'start' => $b['date'] . 'T' . $b['time'],
                'end' => $b['end_time'] ? $b['date'] . 'T' . $b['end_time'] : null,
                'backgroundColor' => $colors[$b['status']] ?? '#6c757d',
                'extendedProps' => $b
            ];
        }, $bookings);
        
        $this->json($events);
    }

    public function apiStats(): void
    {
        $this->requireAuth();
        $start = $this->input('start', date('Y-m-01'));
        $end = $this->input('end', date('Y-m-t'));
        
        $stats = $this->model->getStats($start, $end);
        $this->json(['success' => true, 'data' => $stats]);
    }
}
