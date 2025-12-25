<?php
/**
 * CHM Sistema - Controller de Backup
 * @author ch-mestriner (https://ch-mestriner.com.br)
 * @date 25/12/2025
 * @version 1.0.0
 */

namespace CHM\Core;

class BackupController extends Controller
{
    private BackupService $service;

    public function __construct()
    {
        parent::__construct();
        $this->service = new BackupService();
    }

    // Página de gerenciamento de backups
    public function index(): void
    {
        $this->requireAuth();
        $this->requireAdmin();

        $backups = $this->service->listBackups();

        $this->setTitle('Backups');
        $this->setData('backups', $backups);
        $this->view('backup.index');
    }

    // Criar backup manual
    public function create(): void
    {
        $this->requireAuth();
        $this->requireAdmin();

        $type = $this->input('type', 'database');

        if ($type === 'full') {
            $result = $this->service->createFull();
        } else {
            $result = $this->service->createDatabaseOnly();
        }

        if ($result['success']) {
            Helpers::logAction('Backup criado', 'backup', null, ['name' => $result['name']]);
            $this->success('Backup criado com sucesso!', $result);
        } else {
            $this->error('Erro ao criar backup: ' . ($result['error'] ?? 'Erro desconhecido'));
        }
    }

    // Limpar backups antigos
    public function clean(): void
    {
        $this->requireAuth();
        $this->requireAdmin();

        $deleted = $this->service->cleanOldBackups();
        Helpers::logAction('Backups antigos removidos', 'backup', null, ['count' => $deleted]);
        
        $this->success("$deleted backup(s) antigo(s) removido(s).");
    }

    // API: Status do backup
    public function apiStatus(): void
    {
        $this->requireAuth();

        $backups = $this->service->listBackups();
        $lastBackup = !empty($backups) ? $backups[0] : null;

        $this->json([
            'success' => true,
            'total' => count($backups),
            'last_backup' => $lastBackup,
            'auto_enabled' => defined('BACKUP_INTERVAL') && BACKUP_INTERVAL > 0
        ]);
    }
}
