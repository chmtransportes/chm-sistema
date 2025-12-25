<?php
/**
 * CHM Sistema - Controller de Clientes
 * @author ch-mestriner (https://ch-mestriner.com.br)
 * @date 23/12/2025
 * @version 1.0.0
 */

namespace CHM\Clients;

use CHM\Core\Controller;
use CHM\Core\Validator;
use CHM\Core\Helpers;
use CHM\Core\Database;

class ClientController extends Controller
{
    private ClientModel $model;

    public function __construct()
    {
        parent::__construct();
        $this->model = new ClientModel();
    }

    public function index(): void
    {
        $this->requireAuth();
        $page = (int)$this->input('page', 1);
        $search = $this->input('search', '');

        if ($search) {
            $clients = $this->model->search($search);
            $this->setData('clients', $clients);
        } else {
            $result = $this->model->paginate($page, 15, [], 'name', 'ASC');
            $this->setData('clients', $result['data']);
            $this->setData('pagination', $result);
        }

        $this->setTitle('Clientes');
        $this->setBreadcrumb([['label' => 'Dashboard', 'url' => APP_URL . 'dashboard'], ['label' => 'Clientes']]);
        $this->view('clients.index');
    }

    public function create(): void
    {
        $this->requireAuth();
        $this->setTitle('Novo Cliente');
        $this->setData('nextClientNumber', 23787);
        $this->view('clients.form');
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
        $validator->rule('document', 'required|unique:clients,document', 'CPF/CNPJ');
        $validator->rule('email', 'email', 'E-mail');
        $validator->rule('phone', 'phone', 'Telefone');

        if (!$validator->validate()) {
            $this->error($validator->getFirstError(), 400, $validator->getErrors());
            return;
        }

        $data['document'] = Helpers::onlyNumbers($data['document']);
        $data['phone'] = Helpers::onlyNumbers($data['phone'] ?? '');
        $data['phone2'] = Helpers::onlyNumbers($data['phone2'] ?? '');
        $data['whatsapp'] = Helpers::onlyNumbers($data['whatsapp'] ?? '');
        $data['zipcode'] = Helpers::onlyNumbers($data['zipcode'] ?? '');

        $id = $this->model->create($data);
        Helpers::logAction('Cliente criado', 'clients', null, ['id' => $id]);
        
        $this->success('Cliente cadastrado com sucesso!', ['id' => $id]);
    }

    public function edit(string $id): void
    {
        $this->requireAuth();
        $client = $this->model->find((int)$id);
        if (!$client) {
            $this->redirect(APP_URL . 'clients');
            return;
        }

        $this->setTitle('Editar Cliente');
        $this->setData('client', $client);
        $this->view('clients.form');
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
        $validator->rule('document', "required|unique:clients,document,{$id}", 'CPF/CNPJ');

        if (!$validator->validate()) {
            $this->error($validator->getFirstError(), 400, $validator->getErrors());
            return;
        }

        $data['document'] = Helpers::onlyNumbers($data['document']);
        $data['phone'] = Helpers::onlyNumbers($data['phone'] ?? '');

        $this->model->update((int)$id, $data);
        Helpers::logAction('Cliente atualizado', 'clients', null, ['id' => $id]);
        
        $this->success('Cliente atualizado com sucesso!');
    }

    public function delete(string $id): void
    {
        $this->requireAuth();
        $this->requireAdmin();
        
        $this->model->softDelete((int)$id);
        Helpers::logAction('Cliente excluído', 'clients', null, ['id' => $id]);
        
        $this->success('Cliente excluído com sucesso!');
    }

    public function show(string $id): void
    {
        $this->requireAuth();
        $client = $this->model->find((int)$id);
        if (!$client) {
            $this->redirect(APP_URL . 'clients');
            return;
        }

        $this->setTitle('Detalhes do Cliente');
        $this->setData('client', $client);
        $this->view('clients.show');
    }

    public function apiList(): void
    {
        $this->requireAuth();
        $clients = $this->model->getForSelect();
        $this->json(['success' => true, 'data' => $clients]);
    }
}
