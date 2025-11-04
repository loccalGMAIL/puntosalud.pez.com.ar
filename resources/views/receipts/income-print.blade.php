<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recibo de Ingreso N° {{ $receiptNumber }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            font-size: 13px;
            line-height: 1.6;
            color: #000;
            background: white;
            padding: 0;
            margin: 0;
            width: 100vw;
            height: 100vh;
            overflow: hidden;
            display: flex;
            justify-content: flex-end;
            align-items: flex-start;
        }

        .receipt-container {
            width: 12cm;
            min-height: 18cm;
            max-width: 12cm;
            max-height: 18cm;
            padding: 10mm 8mm;
            margin: 0 1cm 0 0;
            background: white;
            page-break-after: always;
            display: flex;
            flex-direction: column;
            justify-content: flex-start;
        }

        .header {
            text-align: center;
            margin-bottom: 10px;
            padding-bottom: 4px;
            border-bottom: 2px solid #000;
        }

        .logo {
            width: 100px;
            height: 100px;
            margin: 0 auto 0;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .logo img {
            width: 180%;
            height: 180%;
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

        .print-button {
            position: fixed;
            top: 20px;
            right: 20px;
            background: #16a34a;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            z-index: 1000;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
        }

        .print-button:hover {
            background: #15803d;
        }

        @media print {
            body {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
                margin: 0 !important;
                padding: 0 !important;
                width: 100vw;
                height: 100vh;
                overflow: hidden;
                display: flex;
                justify-content: flex-end;
                align-items: flex-start;
            }

            .receipt-container {
                width: 12cm;
                min-height: 18cm;
                max-width: 12cm;
                max-height: 18cm;
                padding: 10mm 8mm;
                margin: 0 1cm 0 0;
                background: white;
                display: flex;
                flex-direction: column;
                justify-content: flex-start;
            }

            .print-button {
                display: none !important;
            }

            .page-break {
                page-break-after: always;
            }

            @page {
                size: A4 landscape;
                margin: 0;
            }
        }

        .badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 3px;
            font-size: 10px;
            font-weight: 600;
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
            <!-- Logo -->
            <div class="logo">
                <img src="{{ asset('logo.png') }}" alt="Logo Punto Salud">
            </div>

            <div class="clinic-info">Centro de Atención Médica</div>
            <div class="clinic-info">Dirección: Tucumán 925, Cosquín</div>
            <div class="clinic-info">Tel: (3541) 705-281 | Email: puntosalud94@gmail.com</div>

            <div class="receipt-title">RECIBO DE INGRESO</div>
            <div class="receipt-number">N° {{ $receiptNumber }}</div>
        </div>

        <!-- Información del Recibo -->
        <div class="info-section">
            <div class="info-row">
                <span class="info-label">Fecha:</span>
                <span class="info-value">{{ $cashMovement->created_at->format('d/m/Y H:i') }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Recibido de:</span>
                <span class="info-value">
                    @if($cashMovement->reference_type === 'App\\Models\\Professional' && $cashMovement->reference)
                        Dr. {{ $cashMovement->reference->full_name }}
                    @else
                        -
                    @endif
                </span>
            </div>
            <div class="info-row">
                <span class="info-label">Categoría:</span>
                <span class="info-value">{{ $cashMovement->movementType->name ?? 'Ingreso Manual' }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Registrado por:</span>
                <span class="info-value">{{ $cashMovement->user->name ?? 'Sistema' }}</span>
            </div>
        </div>

        <div class="divider"></div>

        <!-- Descripción/Concepto -->
        <div class="concept-section">
            <div class="concept-title">Concepto:</div>
            <div class="concept-text">{{ $cashMovement->description }}</div>
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
                    ];
                    // Extraer el método de pago de la descripción si está disponible
                    $paymentMethod = '-';
                    foreach($paymentMethods as $key => $label) {
                        if(stripos($cashMovement->description, $key) !== false) {
                            $paymentMethod = $label;
                            break;
                        }
                    }
                @endphp
                {{ $paymentMethod }}
            </span>
        </div>

        <!-- Monto Total -->
        <div class="amount-section">
            <div class="amount-row">
                <span class="amount-label">MONTO RECIBIDO:</span>
                <span class="amount-value">${{ number_format($cashMovement->amount, 2, ',', '.') }}</span>
            </div>
        </div>

        <!-- Nota Informativa -->
        <div style="margin-top: 20px; text-align: center; font-size: 10px; color: #666;">
            <p>Este recibo es válido como comprobante de ingreso</p>
            <p>Conserve para control administrativo</p>
        </div>
    </div>

    <script>
        // Auto-imprimir si viene con parámetro print
        if (window.location.search.includes('print=1')) {
            window.onload = function() {
                setTimeout(function() {
                    window.print();

                    // Cerrar la ventana después de imprimir o cancelar
                    setTimeout(function() {
                        window.close();
                    }, 100);
                }, 500);
            }
        }
    </script>
</body>

</html>
