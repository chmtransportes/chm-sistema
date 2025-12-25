<?php
/**
 * CHM Sistema - Controller de Motoristas
 * @author ch-mestriner (https://ch-mestriner.com.br)
 * @date 23/12/2025
 * @version 1.0.0
 */

namespace CHM\Drivers;

use CHM\Core\Controller;
use CHM\Core\Validator;
use CHM\Core\Helpers;
use CHM\Core\Database;

class DriverController extends Controller
{
    private DriverModel $model;

    public function __construct()
    {
        parent::__construct();
        $this->model = new DriverModel();
    }

    public function index(): void
    {
        $this->requireAuth();
        $page = (int)$this->input('page', 1);
        $result = $this->model->paginate($page, 15, [], 'name', 'ASC');

        $this->setTitle('Motoristas');
        $this->setData('drivers', $result['data']);
        $this->setData('pagination', $result);
        $this->view('drivers.index');
    }

    public function create(): void
    {
        $this->requireAuth();
        $this->setTitle('Novo Motorista');
        $this->setData('nextDriverNumber', 130);
        $this->view('drivers.form');
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
        $validator->rule('name', 'required|min:3|max:150', 'Nome');
        $validator->rule('document', 'required|cpf|unique:drivers,document', 'CPF');
        $validator->rule('cnh', 'required|unique:drivers,cnh', 'CNH');
        $validator->rule('cnh_category', 'required', 'Categoria CNH');
        $validator->rule('cnh_expiry', 'required|date', 'Validade CNH');

        if (!$validator->validate()) {
            $this->error($validator->getFirstError(), 400, $validator->getErrors());
            return;
        }

        $data['document'] = Helpers::onlyNumbers($data['document']);
        $data['cnh_expiry'] = Helpers::dataMysql($data['cnh_expiry']);
        $data['birth_date'] = !empty($data['birth_date']) ? Helpers::dataMysql($data['birth_date']) : null;
        $data['commission_rate'] = COMMISSION_RATE * 100;

        // Upload foto do motorista
        if (!empty($_FILES['photo']['name'])) {
            $data['photo'] = $this->uploadPhoto($_FILES['photo'], 'driver');
        }

        // Upload foto do carro
        if (!empty($_FILES['car_photo']['name'])) {
            $data['car_photo'] = $this->uploadPhoto($_FILES['car_photo'], 'car');
        }

        // Upload foto da CNH
        if (!empty($_FILES['cnh_photo']['name'])) {
            $data['cnh_photo'] = $this->uploadPhoto($_FILES['cnh_photo'], 'cnh');
        }

        $id = $this->model->create($data);
        Helpers::logAction('Motorista criado', 'drivers', null, ['id' => $id]);
        
        $this->success('Motorista cadastrado com sucesso!', ['id' => $id]);
    }

    public function edit(string $id): void
    {
        $this->requireAuth();
        $driver = $this->model->find((int)$id);
        if (!$driver) {
            $this->redirect(APP_URL . 'drivers');
            return;
        }

        $this->setTitle('Editar Motorista');
        $this->setData('driver', $driver);
        $this->view('drivers.form');
    }

    public function update(string $id): void
    {
        $this->requireAuth();
        if (!$this->validateCsrf()) {
            $this->error('Token inválido.');
            return;
        }

        $data = $this->all();
        $validator = new Validator($data);
        $validator->rule('name', 'required|min:3|max:150', 'Nome');
        $validator->rule('document', "required|cpf|unique:drivers,document,{$id}", 'CPF');
        $validator->rule('cnh', "required|unique:drivers,cnh,{$id}", 'CNH');

        if (!$validator->validate()) {
            $this->error($validator->getFirstError(), 400, $validator->getErrors());
            return;
        }

        $data['document'] = Helpers::onlyNumbers($data['document']);
        $data['cnh_expiry'] = Helpers::dataMysql($data['cnh_expiry']);

        // Upload foto do motorista
        if (!empty($_FILES['photo']['name'])) {
            $data['photo'] = $this->uploadPhoto($_FILES['photo'], 'driver');
        }

        // Upload foto do carro
        if (!empty($_FILES['car_photo']['name'])) {
            $data['car_photo'] = $this->uploadPhoto($_FILES['car_photo'], 'car');
        }

        // Upload foto da CNH
        if (!empty($_FILES['cnh_photo']['name'])) {
            $data['cnh_photo'] = $this->uploadPhoto($_FILES['cnh_photo'], 'cnh');
        }

        $this->model->update((int)$id, $data);
        Helpers::logAction('Motorista atualizado', 'drivers', null, ['id' => $id]);
        
        $this->success('Motorista atualizado com sucesso!');
    }

    public function delete(string $id): void
    {
        $this->requireAuth();
        $this->requireAdmin();
        
        $this->model->softDelete((int)$id);
        Helpers::logAction('Motorista excluído', 'drivers', null, ['id' => $id]);
        
        $this->success('Motorista excluído com sucesso!');
    }

    public function closing(string $id): void
    {
        $this->requireAuth();
        $driver = $this->model->find((int)$id);
        if (!$driver) {
            $this->redirect(APP_URL . 'drivers');
            return;
        }

        $startDate = $this->input('start', date('Y-m-01'));
        $endDate = $this->input('end', date('Y-m-t'));

        $bookings = $this->model->getDriverClosing((int)$id, $startDate, $endDate);
        $totals = ['services' => 0, 'value' => 0, 'commission' => 0];

        foreach ($bookings as $b) {
            $totals['services']++;
            $totals['value'] += $b['total'];
            $totals['commission'] += $b['commission_value'];
        }

        $this->setTitle('Fechamento - ' . $driver['name']);
        $this->setData('driver', $driver);
        $this->setData('bookings', $bookings);
        $this->setData('totals', $totals);
        $this->setData('startDate', $startDate);
        $this->setData('endDate', $endDate);
        $this->view('drivers.closing');
    }

    public function apiList(): void
    {
        $this->requireAuth();
        $drivers = $this->model->getForSelect();
        $this->json(['success' => true, 'data' => $drivers]);
    }

    public function apiAvailable(): void
    {
        $this->requireAuth();
        $date = $this->input('date', date('Y-m-d'));
        $time = $this->input('time', date('H:i'));
        
        $drivers = $this->model->getAvailableForDate($date, $time);
        $this->json(['success' => true, 'data' => $drivers]);
    }

    // Upload de foto
    private function uploadPhoto(array $file, string $prefix): ?string
    {
        $uploadDir = APP_PATH . 'uploads/drivers/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'webp'];

        if (!in_array($ext, $allowed)) {
            return null;
        }

        $filename = $prefix . '_' . uniqid() . '.' . $ext;
        $destination = $uploadDir . $filename;

        if (move_uploaded_file($file['tmp_name'], $destination)) {
            return $filename;
        }

        return null;
    }
}
