<?php
/**
 * CHM Sistema - Serviço WhatsApp Business API
 * @author ch-mestriner (https://ch-mestriner.com.br)
 * @date 23/12/2025
 * @version 1.0.0
 */

namespace CHM\WhatsApp;

use CHM\Core\Database;
use CHM\Core\Helpers;

class WhatsAppService
{
    private string $apiUrl;
    private string $phoneId;
    private string $token;
    private Database $db;

    public function __construct()
    {
        $this->apiUrl = WHATSAPP_API_URL;
        $this->phoneId = WHATSAPP_PHONE_ID;
        $this->token = WHATSAPP_TOKEN;
        $this->db = Database::getInstance();
    }

    // Envia mensagem de texto
    public function sendText(string $phone, string $message, ?int $bookingId = null): array
    {
        $phone = $this->formatPhone($phone);
        
        $payload = [
            'messaging_product' => 'whatsapp',
            'recipient_type' => 'individual',
            'to' => $phone,
            'type' => 'text',
            'text' => ['body' => $message]
        ];

        $result = $this->sendRequest($payload);
        $this->logMessage($phone, 'outgoing', 'text', $message, $result, $bookingId);
        
        return $result;
    }

    // Envia template
    public function sendTemplate(string $phone, string $templateName, array $params = [], ?int $bookingId = null): array
    {
        $phone = $this->formatPhone($phone);
        
        $components = [];
        if (!empty($params)) {
            $parameters = array_map(fn($p) => ['type' => 'text', 'text' => $p], $params);
            $components[] = ['type' => 'body', 'parameters' => $parameters];
        }

        $payload = [
            'messaging_product' => 'whatsapp',
            'to' => $phone,
            'type' => 'template',
            'template' => [
                'name' => $templateName,
                'language' => ['code' => 'pt_BR'],
                'components' => $components
            ]
        ];

        $result = $this->sendRequest($payload);
        $this->logMessage($phone, 'outgoing', 'template', $templateName, $result, $bookingId, $templateName, $params);
        
        return $result;
    }

    // Envia documento
    public function sendDocument(string $phone, string $documentUrl, string $filename, ?string $caption = null): array
    {
        $phone = $this->formatPhone($phone);
        
        $document = ['link' => $documentUrl, 'filename' => $filename];
        if ($caption) $document['caption'] = $caption;

        $payload = [
            'messaging_product' => 'whatsapp',
            'to' => $phone,
            'type' => 'document',
            'document' => $document
        ];

        $result = $this->sendRequest($payload);
        $this->logMessage($phone, 'outgoing', 'document', $filename, $result);
        
        return $result;
    }

    // Processa tags dinâmicas
    public function processMessage(string $message, array $data): string
    {
        $tags = $this->getTags();
        
        foreach ($tags as $tag) {
            $placeholder = $tag['tag'];
            $field = $tag['field_reference'];
            
            if (isset($data[$field])) {
                $message = str_replace($placeholder, $data[$field], $message);
            }
        }

        // Tags padrão
        $message = str_replace('#data', date('d/m/Y'), $message);
        $message = str_replace('#hora', date('H:i'), $message);
        
        return $message;
    }

    // Obtém tags disponíveis
    public function getTags(): array
    {
        $sql = "SELECT * FROM " . DB_PREFIX . "whatsapp_tags ORDER BY tag ASC";
        return $this->db->fetchAll($sql);
    }

    // Cria nova tag
    public function createTag(string $tag, string $description, ?string $fieldReference = null): int
    {
        if (strpos($tag, '#') !== 0) $tag = '#' . $tag;
        return $this->db->insert('whatsapp_tags', [
            'tag' => strtolower($tag),
            'description' => $description,
            'field_reference' => $fieldReference,
            'created_at' => date('Y-m-d H:i:s')
        ]);
    }

    // Obtém templates
    public function getTemplates(): array
    {
        $sql = "SELECT * FROM " . DB_PREFIX . "whatsapp_templates WHERE status = 'active' ORDER BY name ASC";
        return $this->db->fetchAll($sql);
    }

    // Cria template
    public function createTemplate(string $name, string $category, string $content, ?array $variables = null): int
    {
        return $this->db->insert('whatsapp_templates', [
            'name' => $name,
            'category' => $category,
            'content' => $content,
            'variables' => $variables ? json_encode($variables) : null,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ]);
    }

    // Importa respostas rápidas do WaSeller (JSON)
    public function importQuickReplies(string $jsonContent): int
    {
        $data = json_decode($jsonContent, true);
        if (!$data) return 0;

        $imported = 0;
        foreach ($data as $item) {
            if (isset($item['name']) && isset($item['content'])) {
                $this->createTemplate(
                    $item['name'],
                    $item['category'] ?? 'quick_reply',
                    $item['content'],
                    $item['variables'] ?? null
                );
                $imported++;
            }
        }
        return $imported;
    }

    // Webhook para receber mensagens
    public function handleWebhook(array $payload): void
    {
        if (!isset($payload['entry'][0]['changes'][0]['value']['messages'])) return;

        foreach ($payload['entry'][0]['changes'][0]['value']['messages'] as $message) {
            $phone = $message['from'];
            $type = $message['type'];
            $content = '';

            switch ($type) {
                case 'text':
                    $content = $message['text']['body'];
                    break;
                case 'image':
                case 'document':
                case 'audio':
                case 'video':
                    $content = $message[$type]['id'] ?? '';
                    break;
            }

            $this->logMessage($phone, 'incoming', $type, $content, ['message_id' => $message['id']]);
        }
    }

    // Formata número de telefone
    private function formatPhone(string $phone): string
    {
        $phone = preg_replace('/\D/', '', $phone);
        if (strlen($phone) === 11) $phone = '55' . $phone;
        if (strlen($phone) === 10) $phone = '55' . $phone;
        return $phone;
    }

    // Envia requisição para API
    private function sendRequest(array $payload): array
    {
        if (empty($this->phoneId) || empty($this->token)) {
            return ['success' => false, 'error' => 'WhatsApp API não configurada'];
        }

        $url = $this->apiUrl . $this->phoneId . '/messages';
        
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $this->token,
                'Content-Type: application/json'
            ]
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $result = json_decode($response, true) ?? [];
        $result['success'] = $httpCode >= 200 && $httpCode < 300;
        
        return $result;
    }

    // Registra mensagem no banco
    private function logMessage(string $phone, string $direction, string $type, string $content, array $result, ?int $bookingId = null, ?string $templateName = null, ?array $templateParams = null): void
    {
        $this->db->insert('whatsapp_messages', [
            'message_id' => $result['messages'][0]['id'] ?? null,
            'phone' => $phone,
            'direction' => $direction,
            'type' => $type,
            'content' => $content,
            'template_name' => $templateName,
            'template_params' => $templateParams ? json_encode($templateParams) : null,
            'status' => $result['success'] ? 'sent' : 'failed',
            'error_message' => $result['error'] ?? null,
            'booking_id' => $bookingId,
            'sent_at' => $direction === 'outgoing' ? date('Y-m-d H:i:s') : null,
            'created_at' => date('Y-m-d H:i:s')
        ]);
    }
}
