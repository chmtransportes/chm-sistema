<?php
/**
 * CHM Sistema - Controller WhatsApp
 * @author ch-mestriner (https://ch-mestriner.com.br)
 * @date 25/12/2025
 * @version 1.0.0
 */

namespace CHM\WhatsApp;

use CHM\Core\Controller;
use CHM\Core\Session;
use CHM\Core\Helpers;

class WhatsAppController extends Controller
{
    private WhatsAppService $service;

    public function __construct()
    {
        parent::__construct();
        $this->service = new WhatsAppService();
    }

    // Página principal do WhatsApp
    public function index(): void
    {
        $this->requireAuth();
        $this->requireAdmin();

        $templates = $this->service->getTemplates();
        $tags = $this->service->getTags();

        // Verificar se API está configurada
        $apiConfigured = !empty(WHATSAPP_PHONE_ID) && !empty(WHATSAPP_TOKEN);

        $this->setTitle('WhatsApp');
        $this->setData('templates', $templates);
        $this->setData('tags', $tags);
        $this->setData('apiConfigured', $apiConfigured);
        $this->view('whatsapp.index');
    }

    // Enviar mensagem de teste
    public function sendTest(): void
    {
        $this->requireAuth();
        $this->requireAdmin();

        $phone = $this->input('phone');
        $message = $this->input('message');

        if (empty($phone) || empty($message)) {
            $this->error('Telefone e mensagem são obrigatórios.');
            return;
        }

        $result = $this->service->sendText($phone, $message);

        if ($result['success']) {
            Helpers::logAction('Mensagem WhatsApp enviada', 'whatsapp', null, ['phone' => $phone]);
            $this->success('Mensagem enviada com sucesso!');
        } else {
            $this->error('Erro ao enviar: ' . ($result['error'] ?? 'API não configurada'));
        }
    }

    // Criar template
    public function storeTemplate(): void
    {
        $this->requireAuth();
        $this->requireAdmin();

        $name = $this->input('name');
        $category = $this->input('category', 'quick_reply');
        $content = $this->input('content');

        if (empty($name) || empty($content)) {
            $this->error('Nome e conteúdo são obrigatórios.');
            return;
        }

        $id = $this->service->createTemplate($name, $category, $content);
        Helpers::logAction('Template WhatsApp criado', 'whatsapp_templates', null, ['id' => $id]);
        
        $this->success('Template criado com sucesso!', ['id' => $id]);
    }

    // Criar tag
    public function storeTag(): void
    {
        $this->requireAuth();
        $this->requireAdmin();

        $tag = $this->input('tag');
        $description = $this->input('description');
        $fieldReference = $this->input('field_reference');

        if (empty($tag) || empty($description)) {
            $this->error('Tag e descrição são obrigatórias.');
            return;
        }

        $id = $this->service->createTag($tag, $description, $fieldReference);
        Helpers::logAction('Tag WhatsApp criada', 'whatsapp_tags', null, ['id' => $id]);
        
        $this->success('Tag criada com sucesso!', ['id' => $id]);
    }

    // Webhook para receber mensagens (público)
    public function webhook(): void
    {
        // Verificação do webhook (GET)
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $mode = $_GET['hub_mode'] ?? '';
            $token = $_GET['hub_verify_token'] ?? '';
            $challenge = $_GET['hub_challenge'] ?? '';

            if ($mode === 'subscribe' && $token === WHATSAPP_VERIFY_TOKEN) {
                echo $challenge;
                exit;
            }
            http_response_code(403);
            exit;
        }

        // Receber mensagens (POST)
        $payload = json_decode(file_get_contents('php://input'), true);
        if ($payload) {
            $this->service->handleWebhook($payload);
        }
        http_response_code(200);
        exit;
    }

    // API: Status da configuração
    public function apiStatus(): void
    {
        $this->requireAuth();

        $configured = !empty(WHATSAPP_PHONE_ID) && !empty(WHATSAPP_TOKEN);
        
        $this->json([
            'success' => true,
            'configured' => $configured,
            'phone_id' => $configured ? substr(WHATSAPP_PHONE_ID, 0, 5) . '***' : null
        ]);
    }
}
