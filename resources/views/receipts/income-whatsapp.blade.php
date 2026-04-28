<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recibo de Ingreso N° {{ $payment->receipt_number }}</title>
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
            color: #16a34a;
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
            color: #16a34a;
        }

        .receipt-number {
            font-size: 15px;
            font-weight: 700;
            margin-top: 4px;
            color: #16a34a;
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
            background-color: #f0fdf4;
            border: 2px solid #16a34a;
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
            color: #16a34a;
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
            background-color: #f0fdf4;
            border-left: 4px solid #16a34a;
        }

        .concept-title {
            font-weight: 700;
            margin-bottom: 4px;
            font-size: 13px;
            color: #16a34a;
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

            <div class="receipt-title">RECIBO DE INGRESO</div>
            <div class="receipt-number">N° {{ $payment->receipt_number }}</div>
        </div>

        <!-- Información del Recibo -->
        <div class="info-section">
            <div class="info-row">
                <span class="info-label">Fecha:</span>
                <span class="info-value">{{ $payment->payment_date->format('d/m/Y H:i') }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Categoría:</span>
                <span class="info-value">{{ $payment->income_category ? \App\Models\MovementType::where('code', $payment->income_category)->first()?->name : 'Ingreso Manual' }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Registrado por:</span>
                <span class="info-value">{{ $payment->createdBy->name ?? 'Sistema' }}</span>
            </div>
        </div>

        <div class="divider"></div>

        <!-- Descripción/Concepto -->
        <div class="concept-section">
            <div class="concept-title">Concepto:</div>
            <div class="concept-text">{{ $payment->concept }}</div>
        </div>

        <div class="divider"></div>

        <!-- Método de Pago -->
        <div class="detail-row">
            <span class="detail-label">Forma de Ingreso:</span>
            <span>
                @php
                    $paymentMethods = [
                        'cash' => '💵 Efectivo',
                        'transfer' => '🏦 Transferencia',
                        'debit_card' => '💳 Tarjeta de Débito',
                        'credit_card' => '💳 Tarjeta de Crédito',
                        'qr' => '📱 QR',
                    ];
                @endphp
                {{ $paymentMethods[$payment->payment_method] ?? '-' }}
            </span>
        </div>

        <!-- Monto Total -->
        <div class="amount-section">
            <div class="amount-row">
                <span class="amount-label">MONTO RECIBIDO:</span>
                <span class="amount-value">${{ number_format($payment->amount, 2, ',', '.') }}</span>
            </div>
        </div>

        <!-- Nota Informativa -->
        <div style="margin-top: 20px; text-align: center; font-size: 10px; color: #666;">
        </div>
    </div>
</body>

</html>
