<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Movimientos de Caja - {{ $cashSummary['date']->format('d/m/Y') }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
            line-height: 1.3;
            color: #333;
            background: white;
        }

        .header {
            text-align: center;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 2px solid #333;
        }

        .clinic-name {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .report-title {
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .report-date {
            font-size: 12px;
            color: #666;
        }

        .summary-section {
            display: flex;
            justify-content: space-between;
            margin-bottom: 15px;
            padding: 10px;
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
        }

        .summary-item {
            text-align: center;
            flex: 1;
        }

        .summary-label {
            font-size: 10px;
            color: #666;
            margin-bottom: 3px;
        }

        .summary-value {
            font-size: 14px;
            font-weight: bold;
        }

        .summary-value.positive {
            color: #28a745;
        }

        .summary-value.negative {
            color: #dc3545;
        }

        .movements-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }

        .movements-table th,
        .movements-table td {
            border: 1px solid #dee2e6;
            padding: 4px 6px;
            text-align: left;
            font-size: 10px;
        }

        .movements-table th {
            background-color: #e9ecef;
            font-weight: bold;
            font-size: 9px;
            text-transform: uppercase;
        }

        .movements-table tr:nth-child(even) {
            background-color: #f8f9fa;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .amount-positive {
            color: #28a745;
            font-weight: bold;
        }

        .amount-negative {
            color: #dc3545;
            font-weight: bold;
        }

        .type-badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 10px;
            font-size: 9px;
            font-weight: 500;
        }

        .footer {
            margin-top: 20px;
            padding-top: 10px;
            border-top: 1px solid #dee2e6;
            display: flex;
            justify-content: space-between;
            font-size: 9px;
            color: #6c757d;
        }

        .print-button {
            position: fixed;
            top: 20px;
            right: 20px;
            background: #007bff;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            z-index: 1000;
        }

        .totals-row {
            background-color: #e9ecef !important;
            font-weight: bold;
        }

        @media print {
            body {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            .no-print {
                display: none !important;
            }
        }
    </style>
</head>
<body>
    <button class="print-button no-print" onclick="window.print()">🖨️ Imprimir</button>

    <!-- Header -->
    <div class="header">
        <div class="clinic-name">PUNTO SALUD</div>
        <div class="report-title">MOVIMIENTOS DE CAJA</div>
        <div class="report-date">{{ $cashSummary['date']->translatedFormat('l, d \d\e F \d\e Y') }}</div>
    </div>

    <!-- Resumen -->
    <div class="summary-section">
        <div class="summary-item">
            <div class="summary-label">Saldo Inicial</div>
            <div class="summary-value">${{ number_format($cashSummary['initial_balance'], 2, ',', '.') }}</div>
        </div>
        <div class="summary-item">
            <div class="summary-label">Ingresos</div>
            <div class="summary-value positive">+${{ number_format($cashSummary['total_inflows'], 2, ',', '.') }}</div>
        </div>
        <div class="summary-item">
            <div class="summary-label">Egresos</div>
            <div class="summary-value negative">-${{ number_format($cashSummary['total_outflows'], 2, ',', '.') }}</div>
        </div>
        <div class="summary-item">
            <div class="summary-label">Saldo Final</div>
            <div class="summary-value">${{ number_format($cashSummary['final_balance'], 2, ',', '.') }}</div>
        </div>
    </div>

    <!-- Tabla de Movimientos -->
    <table class="movements-table">
        <thead>
            <tr>
                <th style="width: 5%;">ID</th>
                <th style="width: 6%;">Hora</th>
                <th style="width: 12%;">Tipo</th>
                <th style="width: 8%;">Usuario</th>
                <th style="width: 30%;">Concepto</th>
                <th style="width: 12%;">Método</th>
                <th style="width: 12%;" class="text-right">Monto</th>
                <th style="width: 12%;" class="text-right">Saldo</th>
            </tr>
        </thead>
        <tbody>
            @foreach($movements as $movement)
            <tr>
                <td class="text-center">#{{ $movement->id }}</td>
                <td class="text-center">{{ $movement->created_at->format('H:i') }}</td>
                <td>
                    {{ $movement->movementType?->icon }} {{ $movement->movementType?->name ?? 'Desconocido' }}
                </td>
                <td>{{ $movement->user->name ?? 'Sistema' }}</td>
                <td>
                    @if($movement->movementType?->code === 'professional_payment' && $movement->reference_type === 'App\\Models\\Professional' && $movement->reference_id)
                        @php
                            $professional = \App\Models\Professional::find($movement->reference_id);
                        @endphp
                        @if($professional)
                            {{ $movement->description }} - Dr. {{ $professional->first_name }} {{ $professional->last_name }}
                        @else
                            {{ $movement->description }}
                        @endif
                    @else
                        {{ $movement->description }}
                    @endif
                </td>
                <td>
                    @if($movement->reference_type === 'App\\Models\\Payment' && $movement->reference)
                        @php
                            $methodLabels = [
                                'cash' => 'Efectivo',
                                'transfer' => 'Transfer.',
                                'debit_card' => 'Débito',
                                'credit_card' => 'Crédito',
                                'qr' => 'QR',
                            ];
                            $paymentMethods = $movement->reference->paymentDetails
                                ->pluck('payment_method')
                                ->map(fn($method) => $methodLabels[$method] ?? ucfirst($method))
                                ->unique();
                        @endphp
                        {{ $paymentMethods->join(', ') }}
                    @else
                        -
                    @endif
                </td>
                <td class="text-right {{ $movement->amount > 0 ? 'amount-positive' : 'amount-negative' }}">
                    @if($movement->amount > 0)
                        +${{ number_format($movement->amount, 2, ',', '.') }}
                    @else
                        ${{ number_format($movement->amount, 2, ',', '.') }}
                    @endif
                </td>
                <td class="text-right">${{ number_format($movement->balance_after, 2, ',', '.') }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="totals-row">
                <td colspan="6" class="text-right">TOTALES:</td>
                <td class="text-right">
                    <span class="amount-positive">+${{ number_format($cashSummary['total_inflows'], 2, ',', '.') }}</span>
                    <br>
                    <span class="amount-negative">-${{ number_format($cashSummary['total_outflows'], 2, ',', '.') }}</span>
                </td>
                <td class="text-right">${{ number_format($cashSummary['final_balance'], 2, ',', '.') }}</td>
            </tr>
        </tfoot>
    </table>

    <!-- Footer -->
    <div class="footer">
        <div>Punto Salud - Reporte generado el {{ now()->format('d/m/Y H:i:s') }}</div>
        <div>Total de movimientos: {{ $movements->count() }}</div>
    </div>

    <script>
        if (window.location.search.includes('print=1')) {
            window.onload = function() {
                setTimeout(function() {
                    window.print();

                    // Cerrar la pestaña después de imprimir
                    window.addEventListener('afterprint', function() {
                        window.close();
                    });

                    // Fallback: cerrar después de 3 segundos si afterprint no se dispara
                    setTimeout(function() {
                        window.close();
                    }, 3000);
                }, 500);
            }
        }
    </script>
</body>
</html>
