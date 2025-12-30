<?php
/**
 * CHM Sistema - Controller de Veículos
 * @author ch-mestriner (https://ch-mestriner.com.br)
 * @date 23/12/2025
 * @version 1.0.0
 */

namespace CHM\Vehicles;

use CHM\Core\Controller;
use CHM\Core\Validator;
use CHM\Core\Helpers;

class VehicleController extends Controller
{
    private VehicleModel $model;

    public function __construct()
    {
        parent::__construct();
        $this->model = new VehicleModel();
    }

    public function index(): void
    {
        $this->requireAuth();
        $page = (int)$this->input('page', 1);
        $result = $this->model->paginate($page, 15, [], 'model', 'ASC');

        $this->setTitle('Veículos');
        $this->setData('vehicles', $result['data']);
        $this->setData('pagination', $result);
        $this->view('vehicles.index');
    }

    public function create(): void
    {
        $this->requireAuth();
        $this->setTitle('Novo Veículo');
        $this->view('vehicles.form');
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
        $validator->rule('plate', 'required|plate|unique:vehicles,plate', 'Placa');
        $validator->rule('brand', 'required|max:50', 'Marca');
        $validator->rule('model', 'required|max:100', 'Modelo');
        $validator->rule('year', 'required|integer', 'Ano');
        $validator->rule('color', 'required|max:30', 'Cor');

        if (!$validator->validate()) {
            $this->error($validator->getFirstError(), 400, $validator->getErrors());
            return;
        }

        $data['plate'] = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $data['plate']));

        // Upload foto do veículo
        if (!empty($_FILES['photo']['name'])) {
            $data['photo'] = $this->uploadPhoto($_FILES['photo']);
        }

        $id = $this->model->create($data);
        Helpers::logAction('Veículo criado', 'vehicles', null, ['id' => $id]);
        
        $this->success('Veículo cadastrado com sucesso!', ['id' => $id]);
    }

    public function edit(string $id): void
    {
        $this->requireAuth();
        $vehicle = $this->model->find((int)$id);
        if (!$vehicle) {
            $this->redirect(APP_URL . 'vehicles');
            return;
        }

        $this->setTitle('Editar Veículo');
        $this->setData('vehicle', $vehicle);
        $this->view('vehicles.form');
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
        $validator->rule('plate', "required|plate|unique:vehicles,plate,{$id}", 'Placa');
        $validator->rule('brand', 'required|max:50', 'Marca');
        $validator->rule('model', 'required|max:100', 'Modelo');

        if (!$validator->validate()) {
            $this->error($validator->getFirstError(), 400, $validator->getErrors());
            return;
        }

        $data['plate'] = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $data['plate']));

        // Upload foto do veículo
        if (!empty($_FILES['photo']['name'])) {
            $data['photo'] = $this->uploadPhoto($_FILES['photo']);
        }

        $this->model->update((int)$id, $data);
        Helpers::logAction('Veículo atualizado', 'vehicles', null, ['id' => $id]);
        
        $this->success('Veículo atualizado com sucesso!');
    }

    public function delete(string $id): void
    {
        $this->requireAuth();
        $this->requireAdmin();
        
        $this->model->softDelete((int)$id);
        Helpers::logAction('Veículo excluído', 'vehicles', null, ['id' => $id]);
        
        $this->success('Veículo excluído com sucesso!');
    }

    public function apiList(): void
    {
        $this->requireAuth();
        $vehicles = $this->model->getForSelect();
        $this->json(['success' => true, 'data' => $vehicles]);
    }

    public function apiAvailable(): void
    {
        $this->requireAuth();
        $date = $this->input('date', date('Y-m-d'));
        $time = $this->input('time', date('H:i'));
        
        $vehicles = $this->model->getAvailableForDate($date, $time);
        $this->json(['success' => true, 'data' => $vehicles]);
    }

    // API: Busca veículo por ID para visualização rápida
    public function apiShow(string $id): void
    {
        $this->requireAuth();
        $vehicle = $this->model->find((int)$id);
        
        if (!$vehicle) {
            $this->error('Veículo não encontrado', 404);
            return;
        }
        
        $this->json(['success' => true, 'data' => $vehicle]);
    }

    // Upload de foto
    private function uploadPhoto(array $file): ?string
    {
        $uploadDir = APP_PATH . 'uploads/vehicles/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'webp'];

        if (!in_array($ext, $allowed)) {
            return null;
        }

        $filename = 'vehicle_' . uniqid() . '.' . $ext;
        $destination = $uploadDir . $filename;

        if (move_uploaded_file($file['tmp_name'], $destination)) {
            return $filename;
        }

        return null;
    }
}
