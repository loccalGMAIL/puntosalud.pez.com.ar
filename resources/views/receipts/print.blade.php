<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recibo N° {{ $payment->receipt_number }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
            line-height: 1.4;
            color: #000;
            background: white;
            padding: 0;
            margin: 0;
        }

        .receipt-container {
            width: 148mm;
            height: 210mm;
            padding: 15mm;
            margin: 0 auto;
            background: white;
        }

        .header {
            text-align: center;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 2px solid #000;
        }

        .clinic-name {
            font-size: 20px;
            font-weight: bold;
            margin-bottom: 3px;
            text-transform: uppercase;
        }

        .clinic-subtitle {
            font-size: 12px;
            color: #333;
            margin-bottom: 8px;
        }

        .receipt-title {
            font-size: 16px;
            font-weight: bold;
            margin-top: 8px;
            letter-spacing: 2px;
        }

        .receipt-number {
            font-size: 14px;
            font-weight: bold;
            margin-top: 5px;
            color: #2563eb;
        }

        .info-section {
            margin-bottom: 15px;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 6px;
            padding: 3px 0;
        }

        .info-label {
            font-weight: bold;
            width: 35%;
        }

        .info-value {
            width: 65%;
            text-align: right;
        }

        .divider {
            border-top: 1px solid #ccc;
            margin: 12px 0;
        }

        .amount-section {
            background-color: #f8f9fa;
            padding: 12px;
            margin: 15px 0;
            border: 2px solid #2563eb;
            border-radius: 5px;
        }

        .amount-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .amount-label {
            font-size: 14px;
            font-weight: bold;
        }

        .amount-value {
            font-size: 24px;
            font-weight: bold;
            color: #2563eb;
        }

        .payment-details {
            margin: 15px 0;
        }

        .detail-row {
            display: flex;
            justify-content: space-between;
            padding: 4px 0;
            font-size: 10px;
        }

        .detail-label {
            color: #666;
        }

        .concept-section {
            margin: 15px 0;
            padding: 10px;
            background-color: #f8f9fa;
            border-left: 3px solid #2563eb;
        }

        .concept-title {
            font-weight: bold;
            margin-bottom: 5px;
            font-size: 11px;
        }

        .concept-text {
            font-size: 10px;
            color: #333;
        }

        .footer {
            margin-top: 25px;
            padding-top: 15px;
            border-top: 1px solid #ccc;
            text-align: center;
            font-size: 9px;
            color: #666;
        }

        .signature-section {
            margin-top: 30px;
            padding-top: 20px;
        }

        .signature-line {
            border-top: 1px solid #000;
            width: 60%;
            margin: 0 auto;
            padding-top: 5px;
            text-align: center;
            font-size: 10px;
        }

        .print-button {
            position: fixed;
            top: 20px;
            right: 20px;
            background: #2563eb;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            z-index: 1000;
            box-shadow: 0 2px 4px rgba(0,0,0,0.2);
        }

        .print-button:hover {
            background: #1d4ed8;
        }

        @media print {
            body {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            .receipt-container {
                width: 148mm;
                height: 210mm;
                padding: 15mm;
                margin: 0;
            }

            .print-button {
                display: none !important;
            }

            .page-break {
                page-break-after: always;
            }

            @page {
                size: A5;
                margin: 0;
            }
        }

        .badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 3px;
            font-size: 9px;
            font-weight: bold;
        }

        .badge-green {
            background-color: #d4edda;
            color: #155724;
        }

        .badge-blue {
            background-color: #cfe2ff;
            color: #084298;
        }

        .badge-yellow {
            background-color: #fff3cd;
            color: #856404;
        }
    </style>
</head>
<body>
    <button class="print-button" onclick="window.print()">🖨️ Imprimir Recibo</button>

    <div class="receipt-container">
        <!-- Header -->
        <div class="header">
            <div class="clinic-name">Punto Salud</div>
            <div class="clinic-subtitle">Centro de Atención Médica</div>
            <div class="receipt-title">RECIBO DE PAGO</div>
            <div class="receipt-number">N° {{ $payment->receipt_number }}</div>
        </div>

        <!-- Información del Recibo -->
        <div class="info-section">
            <div class="info-row">
                <span class="info-label">Fecha:</span>
                <span class="info-value">{{ $payment->payment_date->format('d/m/Y H:i') }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Paciente:</span>
                <span class="info-value">{{ $payment->patient->full_name }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">DNI:</span>
                <span class="info-value">{{ $payment->patient->dni }}</span>
            </div>
            @if($payment->patient->health_insurance)
            <div class="info-row">
                <span class="info-label">Obra Social:</span>
                <span class="info-value">{{ $payment->patient->health_insurance }}</span>
            </div>
            @endif
        </div>

        <div class="divider"></div>

        <!-- Monto Total -->
        <div class="amount-section">
            <div class="amount-row">
                <span class="amount-label">MONTO TOTAL:</span>
                <span class="amount-value">${{ number_format($payment->amount, 2, ',', '.') }}</span>
            </div>
        </div>

        <!-- Detalles del Pago -->
        <div class="payment-details">
            <div class="detail-row">
                <span class="detail-label">Tipo de Pago:</span>
                <span>
                    @if($payment->payment_type === 'single')
                        <span class="badge badge-green">Pago Individual</span>
                    @elseif($payment->payment_type === 'package')
                        <span class="badge badge-blue">Paquete de Tratamiento ({{ $payment->sessions_included }} sesiones)</span>
                    @elseif($payment->payment_type === 'refund')
                        <span class="badge badge-yellow">Reintegro</span>
                    @endif
                </span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Método de Pago:</span>
                <span>
                    @if($payment->payment_method === 'cash')
                        💵 Efectivo
                    @elseif($payment->payment_method === 'transfer')
                        🏦 Transferencia
                    @elseif($payment->payment_method === 'card')
                        💳 Tarjeta
                    @endif
                </span>
            </div>

            @if($payment->payment_type === 'package')
            <div class="detail-row">
                <span class="detail-label">Sesiones Usadas:</span>
                <span>{{ $payment->sessions_used }} de {{ $payment->sessions_included }}</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Sesiones Restantes:</span>
                <span><strong>{{ $payment->sessions_remaining }}</strong></span>
            </div>
            @endif
        </div>

        <!-- Concepto -->
        @if($payment->concept)
        <div class="concept-section">
            <div class="concept-title">Concepto:</div>
            <div class="concept-text">{{ $payment->concept }}</div>
        </div>
        @endif

        <!-- Profesionales Asociados -->
        @if($professionals->count() > 0)
        <div class="divider"></div>
        <div class="info-section">
            <div style="font-weight: bold; margin-bottom: 8px; font-size: 11px;">Profesionales:</div>
            @foreach($professionals as $professional)
            <div class="detail-row">
                <span class="detail-label">• Dr. {{ $professional->full_name }}</span>
                <span>{{ $professional->specialty->name }}</span>
            </div>
            @endforeach
        </div>
        @endif

        <!-- Firma -->
        <div class="signature-section">
            <div class="signature-line">
                Firma y Aclaración
            </div>
        </div>

        <!-- Footer -->
        <div class="footer">
            <div>Punto Salud - Sistema de Gestión Médica</div>
            <div>Este recibo es válido como comprobante de pago</div>
            <div style="margin-top: 5px;">Impreso el {{ now()->format('d/m/Y H:i:s') }}</div>
        </div>
    </div>

    <script>
        // Auto-imprimir si viene con parámetro print
        if (window.location.search.includes('print=1')) {
            window.onload = function() {
                setTimeout(function() {
                    window.print();
                }, 500);
            }
        }
    </script>
</body>
</html>
