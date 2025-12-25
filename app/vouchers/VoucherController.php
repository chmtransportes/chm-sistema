<?php
/**
 * CHM Sistema - Controller de Vouchers/Recibos
 * @author ch-mestriner (https://ch-mestriner.com.br)
 * @date 23/12/2025
 * @version 1.0.0
 */

namespace CHM\Vouchers;

use CHM\Core\Controller;
use CHM\Core\Helpers;
use CHM\Bookings\BookingModel;

class VoucherController extends Controller
{
    private BookingModel $bookingModel;

    public function __construct()
    {
        parent::__construct();
        $this->bookingModel = new BookingModel();
    }

    public function voucher(string $id): void
    {
        $booking = $this->bookingModel->getWithDetails((int)$id);
        if (!$booking) {
            echo 'Agendamento não encontrado.';
            return;
        }

        $this->generateDocument($booking, 'voucher');
    }

    public function receipt(string $id): void
    {
        $booking = $this->bookingModel->getWithDetails((int)$id);
        if (!$booking) {
            echo 'Agendamento não encontrado.';
            return;
        }

        if ($booking['status'] !== 'completed') {
            echo 'Recibo disponível apenas para serviços concluídos.';
            return;
        }

        $this->generateDocument($booking, 'receipt');
    }

    private function generateDocument(array $booking, string $type): void
    {
        $title = $type === 'voucher' ? 'VOUCHER DE SERVIÇO' : 'RECIBO DE PAGAMENTO';
        
        $html = '<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>' . $title . ' - ' . $booking['code'] . '</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; padding: 20px; max-width: 800px; margin: 0 auto; }
        .header { text-align: center; border-bottom: 2px solid #1a1a2e; padding-bottom: 20px; margin-bottom: 20px; }
        .logo { font-size: 28px; font-weight: bold; color: #1a1a2e; }
        .title { font-size: 18px; color: #666; margin-top: 10px; }
        .code { font-size: 14px; color: #999; }
        .section { margin-bottom: 20px; }
        .section-title { font-size: 14px; font-weight: bold; color: #1a1a2e; border-bottom: 1px solid #ddd; padding-bottom: 5px; margin-bottom: 10px; }
        .row { display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid #eee; }
        .label { color: #666; }
        .value { font-weight: 500; }
        .total { font-size: 20px; font-weight: bold; color: #1a1a2e; text-align: right; padding: 20px 0; border-top: 2px solid #1a1a2e; }
        .footer { text-align: center; color: #999; font-size: 12px; margin-top: 40px; padding-top: 20px; border-top: 1px solid #ddd; }
        .status { display: inline-block; padding: 4px 12px; border-radius: 4px; font-size: 12px; font-weight: bold; }
        .status-completed { background: #d4edda; color: #155724; }
        .status-confirmed { background: #d1ecf1; color: #0c5460; }
        .status-pending { background: #fff3cd; color: #856404; }
        @media print { body { padding: 0; } .no-print { display: none; } }
    </style>
</head>
<body>
    <div class="header">
        <div class="logo">CHM Transportes Executivos</div>
        <div class="title">' . $title . '</div>
        <div class="code">Código: ' . $booking['code'] . '</div>
    </div>

    <div class="section">
        <div class="section-title">Dados do Serviço</div>
        <div class="row"><span class="label">Data:</span><span class="value">' . Helpers::dataBr($booking['date']) . '</span></div>
        <div class="row"><span class="label">Horário:</span><span class="value">' . substr($booking['time'], 0, 5) . '</span></div>
        <div class="row"><span class="label">Tipo:</span><span class="value">' . Helpers::serviceLabel($booking['service_type']) . '</span></div>
        <div class="row"><span class="label">Origem:</span><span class="value">' . htmlspecialchars($booking['origin']) . '</span></div>';
        
        if ($booking['destination']) {
            $html .= '<div class="row"><span class="label">Destino:</span><span class="value">' . htmlspecialchars($booking['destination']) . '</span></div>';
        }
        if ($booking['flight_number']) {
            $html .= '<div class="row"><span class="label">Voo:</span><span class="value">' . htmlspecialchars($booking['flight_number']) . '</span></div>';
        }
        
        $html .= '</div>

    <div class="section">
        <div class="section-title">Cliente</div>
        <div class="row"><span class="label">Nome:</span><span class="value">' . htmlspecialchars($booking['client_name']) . '</span></div>';
        if ($booking['client_phone']) {
            $html .= '<div class="row"><span class="label">Telefone:</span><span class="value">' . Helpers::formatPhone($booking['client_phone']) . '</span></div>';
        }
        $html .= '</div>';

        if ($booking['driver_name']) {
            $html .= '<div class="section">
                <div class="section-title">Motorista / Veículo</div>
                <div class="row"><span class="label">Motorista:</span><span class="value">' . htmlspecialchars($booking['driver_name']) . '</span></div>';
            if ($booking['vehicle_model']) {
                $html .= '<div class="row"><span class="label">Veículo:</span><span class="value">' . htmlspecialchars($booking['vehicle_model']) . ' - ' . Helpers::formatPlate($booking['vehicle_plate']) . '</span></div>';
            }
            $html .= '</div>';
        }

        $html .= '<div class="section">
            <div class="section-title">Valores</div>
            <div class="row"><span class="label">Valor do Serviço:</span><span class="value">' . Helpers::moeda($booking['value']) . '</span></div>';
        if ($booking['extras'] > 0) {
            $html .= '<div class="row"><span class="label">Extras (pedágios, etc):</span><span class="value">' . Helpers::moeda($booking['extras']) . '</span></div>';
        }
        if ($booking['discount'] > 0) {
            $html .= '<div class="row"><span class="label">Desconto:</span><span class="value">-' . Helpers::moeda($booking['discount']) . '</span></div>';
        }
        $html .= '<div class="row"><span class="label">Forma de Pagamento:</span><span class="value">' . Helpers::paymentLabel($booking['payment_method']) . '</span></div>
        </div>

        <div class="total">TOTAL: ' . Helpers::moeda($booking['total']) . '</div>

        <div class="footer">
            <p>Documento gerado em ' . date('d/m/Y H:i') . '</p>
            <p>CHM Transportes Executivos - Excelência em Transporte</p>
        </div>

        <div class="no-print" style="text-align:center; margin-top:20px;">
            <button onclick="window.print()" style="padding:10px 30px; font-size:16px; cursor:pointer;">Imprimir</button>
        </div>
</body>
</html>';

        echo $html;
    }

    public function list(): void
    {
        $this->requireAuth();
        
        $sql = "SELECT v.*, b.code as booking_code, b.date, c.name as client_name
                FROM " . DB_PREFIX . "vouchers v
                INNER JOIN " . DB_PREFIX . "bookings b ON b.id = v.booking_id
                LEFT JOIN " . DB_PREFIX . "clients c ON c.id = b.client_id
                ORDER BY v.created_at DESC";
        
        $vouchers = $this->db->fetchAll($sql);

        $this->setTitle('Vouchers e Recibos');
        $this->setData('vouchers', $vouchers);
        $this->view('vouchers.index');
    }
}
