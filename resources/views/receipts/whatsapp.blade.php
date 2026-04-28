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

        @page {
            size: A5 portrait;
            margin: 8mm 6mm;
        }

        body {
            font-family: Arial, sans-serif;
            font-size: 13px;
            line-height: 1.6;
            color: #000;
            background: white;
        }

        .receipt-container {
            width: 100%;
            padding: 0;
            margin: 0;
        }

        .header {
            text-align: center;
            margin-bottom: 10px;
            padding-bottom: 4px;
            border-bottom: 2px solid #000;
        }

        .logo {
            width: 100%;
            height: 70px;
            margin: 0 auto 6px;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .logo img {
            width: auto;
            height: auto;
            max-width: 100%;
            max-height: 70px;
            object-fit: contain;
        }

        .clinic-name {
            font-size: 20px;
            font-weight: 700;
            margin-bottom: 4px;
            text-transform: uppercase;
            color: #2563eb;
        }

        .clinic-info {
            font-size: 10px;
            color: #666;
            margin-bottom: 2px;
            font-weight: 400;
        }

        .receipt-title {
            font-size: 16px;
            font-weight: 700;
            margin-top: 8px;
            letter-spacing: 1.5px;
        }

        .receipt-number {
            font-size: 15px;
            font-weight: 700;
            margin-top: 4px;
            color: #2563eb;
        }

        .info-section,
        .payment-details,
        .concept-section {
            margin-bottom: 10px;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 6px;
            padding: 3px 0;
        }

        .info-label {
            font-size: 14px;
            font-weight: 600;
            color: #333;
        }

        .info-value {
            font-size: 14px;
            font-weight: 400;
            color: #000;
            text-align: right;
        }

        .divider {
            border-top: 2px solid #000000;
            margin: 12px 0;
        }

        .amount-section {
            margin: 12px 0;
            padding: 10px;
            background-color: #f8f9fa;
            border: 2px solid #2563eb;
            border-radius: 4px;
        }

        .amount-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .amount-label {
            font-size: 14px;
            font-weight: 700;
        }

        .amount-value {
            font-size: 24px;
            font-weight: 700;
            color: #2563eb;
        }

        .payment-details {
            margin: 8px 0;
        }

        .detail-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 4px 0;
            font-size: 13px;
        }

        .detail-label {
            color: #555;
            font-weight: 600;
            font-size: 13px;
        }

        .detail-row span {
            font-size: 13px;
            font-weight: 400;
        }

        .concept-section {
            margin: 10px 0;
            padding: 8px;
            background-color: #f8f9fa;
            border-left: 4px solid #2563eb;
        }

        .concept-title {
            font-weight: 700;
            margin-bottom: 4px;
            font-size: 13px;
            color: #2563eb;
        }

        .concept-text {
            font-size: 12px;
            color: #333;
            line-height: 1.5;
        }
    </style>
</head>

<body>
    @php
        $logoDataUri = null;
        foreach (['png', 'jpg', 'jpeg', 'webp'] as $ext) {
            $candidate = public_path("center/logo.{$ext}");
            if (is_file($candidate)) {
                $mime = match ($ext) {
                    'jpg', 'jpeg' => 'image/jpeg',
                    'webp' => 'image/webp',
                    default => 'image/png',
                };
                $logoDataUri = 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($candidate));
                break;
            }
        }
    @endphp

    <div class="receipt-container">
        <!-- Header -->
        <div class="header">
            <!-- Logo -->
            @if ($logoDataUri)
                <div class="logo">
                    <img src="{{ $logoDataUri }}" alt="{{ setting('center_name', config('app.name')) }}">
                </div>
            @endif

            <div class="clinic-info">{{ setting('center_name', 'Centro de Atención Médica') }}</div>
            @if(setting('center_address'))
                <div class="clinic-info">Dirección: {{ setting('center_address') }}</div>
            @endif
            @if(setting('center_phone') || setting('center_email'))
                <div class="clinic-info">
                    @if(setting('center_phone'))Tel: {{ setting('center_phone') }}@endif
                    @if(setting('center_phone') && setting('center_email')) | @endif
                    @if(setting('center_email'))Email: {{ setting('center_email') }}@endif
                </div>
            @endif

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
        </div>

        <div class="divider"></div>

        <!-- Concepto y Profesionales -->
        @if ($payment->concept || $professionals->count() > 0)
            <div class="concept-section">
                @if ($payment->concept)
                    <div class="concept-title">Concepto:</div>
                    <div class="concept-text">{{ $payment->concept }}</div>
                @endif

                <!-- Profesionales Asociados -->
                @if ($professionals->count() > 0)
                    <div class="concept-title" style="margin-top: {{ $payment->concept ? '12px' : '0' }};">Profesionales:</div>
                    @foreach ($professionals as $professional)
                        <div class="concept-text" style="margin-bottom: 4px;">
                            • Dr. {{ $professional->full_name }} - {{ $professional->specialty->name }}
                        </div>
                    @endforeach
                @endif
            </div>
        @endif

        <div class="divider"></div>

        <!-- Métodos de Pago (v2.6.0: soporta múltiples payment_details) -->
        @if ($payment->paymentDetails->count() === 1)
            {{-- Pago simple: un solo método --}}
            <div class="detail-row">
                <span class="detail-label">Método de Pago:</span>
                <span>
                    @php
                        $method = $payment->paymentDetails->first()->payment_method;
                    @endphp
                    {{ match($method) {
                        'cash' => '💵 Efectivo',
                        'transfer' => '🏦 Transferencia',
                        'debit_card' => '💳 Tarjeta de Débito',
                        'credit_card' => '💳 Tarjeta de Crédito',
                        'qr' => '📱 QR',
                        default => ucfirst($method)
                    } }}
                </span>
            </div>
        @else
            {{-- Pago mixto: múltiples métodos --}}
            <div class="detail-row">
                <span class="detail-label">Método de Pago:</span>
                <span style="font-weight: 600; color: #2563eb;">Mixto</span>
            </div>
            @foreach ($payment->paymentDetails as $detail)
                <div class="detail-row" style="padding-left: 20px; font-size: 12px;">
                    <span>
                        {{ match($detail->payment_method) {
                            'cash' => '💵 Efectivo',
                            'transfer' => '🏦 Transferencia',
                            'debit_card' => '💳 Débito',
                            'credit_card' => '💳 Crédito',
                            'qr' => '📱 QR',
                            default => ucfirst($detail->payment_method)
                        } }}
                    </span>
                    <span style="font-weight: 600;">${{ number_format($detail->amount, 2, ',', '.') }}</span>
                </div>
            @endforeach
        @endif

        <!-- Monto Total -->
        <div class="amount-section">
            <div class="amount-row">
                <span class="amount-label">MONTO TOTAL:</span>
                <span class="amount-value">${{ number_format($payment->total_amount, 2, ',', '.') }}</span>
            </div>
        </div>

        <!-- Disclaimer Legal -->
        <div style="margin-top: 16px; padding-top: 12px; border-top: 1px solid #e5e7eb; text-align: center;">
            <p style="font-size: 9px; color: #6b7280; line-height: 1.4; margin: 0;">
                Comprobante interno - No válido como factura
            </p>
        </div>
    </div>
</body>

</html>
