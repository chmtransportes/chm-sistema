<?php
/**
 * CHM Sistema - Controller de Notificações
 * @author ch-mestriner (https://ch-mestriner.com.br)
 * @date 25/12/2025
 * @version 1.0.0
 */

namespace CHM\Core;

class NotificationController extends Controller
{
    private NotificationService $service;

    public function __construct()
    {
        parent::__construct();
        $this->service = new NotificationService();
    }

    // Página de notificações
    public function index(): void
    {
        $this->requireAuth();

        $alerts = $this->service->getAlerts();
        $summary = $this->service->getAlertsSummary();

        $this->setTitle('Notificações');
        $this->setData('alerts', $alerts);
        $this->setData('summary', $summary);
        $this->view('notifications.index');
    }

    // API: Lista de alertas
    public function apiAlerts(): void
    {
        $this->requireAuth();

        $alerts = $this->service->getAlerts();
        $this->json(['success' => true, 'data' => $alerts, 'count' => count($alerts)]);
    }

    // API: Contagem de alertas
    public function apiCount(): void
    {
        $this->requireAuth();

        $summary = $this->service->getAlertsSummary();
        $this->json(['success' => true, 'data' => $summary]);
    }
}
