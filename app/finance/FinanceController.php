<?php
/**
 * CHM Sistema - Controller Financeiro
 * @author ch-mestriner (https://ch-mestriner.com.br)
 * @date 25/12/2025
 * @version 1.0.0
 */

namespace CHM\Finance;

use CHM\Core\Controller;
use CHM\Core\Session;
use CHM\Core\Validator;
use CHM\Core\Helpers;
use CHM\Clients\ClientModel;
use CHM\Drivers\DriverModel;

class FinanceController extends Controller
{
    private FinanceModel $model;

    public function __construct()
    {
        parent::__construct();
        $this->model = new FinanceModel();
    }

    // Página principal do financeiro
    public function index(): void
    {
        $this->requireAuth();
        $this->requireAdmin();

        $startDate = $this->input('start', date('Y-m-01'));
        $endDate = $this->input('end', date('Y-m-t'));
        $type = $this->input('type', 'all');
        $status = $this->input('status');

        $payables = [];
        $receivables = [];

        if ($type === 'all' || $type === 'payable') {
            $payables = $this->model->getPayables($startDate, $endDate, $status);
        }
        if ($type === 'all' || $type === 'receivable') {
            $receivables = $this->model->getReceivables($startDate, $endDate, $status);
        }

        $summary = $this->model->getSummary($startDate, $endDate);

        $this->setTitle('Financeiro');
        $this->setData('payables', $payables);
        $this->setData('receivables', $receivables);
        $this->setData('summary', $summary);
        $this->setData('startDate', $startDate);
        $this->setData('endDate', $endDate);
        $this->setData('type', $type);
        $this->setData('status', $status);
        $this->view('finance.index');
    }

    // Formulário de conta a pagar
    public function createPayable(): void
    {
        $this->requireAuth();
        $this->requireAdmin();

        $driverModel = new DriverModel();

        $this->setTitle('Nova Conta a Pagar');
        $this->setData('drivers', $driverModel->getForSelect());
        $this->setData('type', 'payable');
        $this->view('finance.form');
    }

    // Formulário de conta a receber
    public function createReceivable(): void
    {
        $this->requireAuth();
        $this->requireAdmin();

        $clientModel = new ClientModel();

        $this->setTitle('Nova Conta a Receber');
        $this->setData('clients', $clientModel->getForSelect());
        $this->setData('type', 'receivable');
        $this->view('finance.form');
    }

    // Salvar conta a pagar
    public function storePayable(): void
    {
        $this->requireAuth();
        $this->requireAdmin();

        if (!$this->validateCsrf()) {
            $this->error('Token inválido.');
            return;
        }

        $data = $this->all();
        $validator = new Validator($data);
        $validator->rule('description', 'required|max:255', 'Descrição');
        $validator->rule('due_date', 'required|date:Y-m-d', 'Data de Vencimento');
        $validator->rule('value', 'required|numeric', 'Valor');

        if (!$validator->validate()) {
            $this->error($validator->getFirstError(), 400, $validator->getErrors());
            return;
        }

        $data['value'] = Helpers::moedaFloat($data['value']);
        $data['created_by'] = Session::getUserId();

        $id = $this->model->createPayable($data);
        Helpers::logAction('Conta a pagar criada', 'accounts_payable', null, ['id' => $id]);

        $this->success('Conta a pagar criada com sucesso!', ['id' => $id]);
    }

    // Salvar conta a receber
    public function storeReceivable(): void
    {
        $this->requireAuth();
        $this->requireAdmin();

        if (!$this->validateCsrf()) {
            $this->error('Token inválido.');
            return;
        }

        $data = $this->all();
        $validator = new Validator($data);
        $validator->rule('description', 'required|max:255', 'Descrição');
        $validator->rule('due_date', 'required|date:Y-m-d', 'Data de Vencimento');
        $validator->rule('value', 'required|numeric', 'Valor');

        if (!$validator->validate()) {
            $this->error($validator->getFirstError(), 400, $validator->getErrors());
            return;
        }

        $data['value'] = Helpers::moedaFloat($data['value']);
        $data['created_by'] = Session::getUserId();

        $id = $this->model->createReceivable($data);
        Helpers::logAction('Conta a receber criada', 'accounts_receivable', null, ['id' => $id]);

        $this->success('Conta a receber criada com sucesso!', ['id' => $id]);
    }

    // Registrar pagamento de conta a pagar
    public function payPayable(string $id): void
    {
        $this->requireAuth();
        $this->requireAdmin();

        $value = Helpers::moedaFloat($this->input('value', '0'));
        $method = $this->input('payment_method', 'pix');

        if ($value <= 0) {
            $this->error('Valor inválido.');
            return;
        }

        $result = $this->model->payPayable((int)$id, $value, $method);
        if ($result) {
            Helpers::logAction('Pagamento registrado', 'accounts_payable', null, ['id' => $id, 'value' => $value]);
            $this->success('Pagamento registrado com sucesso!');
        } else {
            $this->error('Erro ao registrar pagamento.');
        }
    }

    // Registrar recebimento de conta a receber
    public function receivePayment(string $id): void
    {
        $this->requireAuth();
        $this->requireAdmin();

        $value = Helpers::moedaFloat($this->input('value', '0'));
        $method = $this->input('payment_method', 'pix');

        if ($value <= 0) {
            $this->error('Valor inválido.');
            return;
        }

        $result = $this->model->receivePayment((int)$id, $value, $method);
        if ($result) {
            Helpers::logAction('Recebimento registrado', 'accounts_receivable', null, ['id' => $id, 'value' => $value]);
            $this->success('Recebimento registrado com sucesso!');
        } else {
            $this->error('Erro ao registrar recebimento.');
        }
    }

    // API para resumo financeiro (dashboard)
    public function apiSummary(): void
    {
        $this->requireAuth();

        $startDate = $this->input('start', date('Y-m-01'));
        $endDate = $this->input('end', date('Y-m-t'));

        $summary = $this->model->getSummary($startDate, $endDate);
        $this->json(['success' => true, 'data' => $summary]);
    }
}
