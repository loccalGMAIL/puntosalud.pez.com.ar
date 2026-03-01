<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte de Caja - {{ $dateFrom }} al {{ $dateTo }}</title>
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

        .section-title {
            font-size: 12px;
            font-weight: bold;
            margin: 15px 0 10px 0;
            padding: 5px;
            background-color: #e9ecef;
            border-left: 3px solid #333;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }

        .data-table th,
        .data-table td {
            border: 1px solid #dee2e6;
            padding: 6px 8px;
            text-align: left;
            font-size: 10px;
        }

        .data-table th {
            background-color: #e9ecef;
            font-weight: bold;
            font-size: 9px;
            text-transform: uppercase;
        }

        .data-table tr:nth-child(even) {
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

        .type-cards {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-bottom: 15px;
        }

        .type-card {
            flex: 1 1 calc(33.333% - 10px);
            min-width: 150px;
            padding: 10px;
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 4px;
        }

        .type-card-header {
            font-weight: bold;
            margin-bottom: 5px;
            font-size: 10px;
            display: flex;
            justify-content: space-between;
        }

        .type-card-count {
            color: #666;
            font-weight: normal;
        }

        .type-card-amount {
            font-size: 12px;
            font-weight: bold;
        }

        .totals-row {
            background-color: #e9ecef !important;
            font-weight: bold;
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

        .print-button:hover {
            background: #0056b3;
        }

        @media print {
            body {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            .no-print {
                display: none !important;
            }

            .type-card {
                break-inside: avoid;
            }
        }
    </style>
</head>
<body>
    <button class="print-button no-print" onclick="window.print()">Imprimir</button>

    <!-- Header -->
    <div class="header">
        <div class="clinic-name">PUNTO SALUD</div>
        <div class="report-title">REPORTE DE CAJA</div>
        <div class="report-date">
            Periodo: {{ \Carbon\Carbon::parse($dateFrom)->format('d/m/Y') }} al {{ \Carbon\Carbon::parse($dateTo)->format('d/m/Y') }}
            @php
                $groupLabels = ['day' => 'Diario', 'week' => 'Semanal', 'month' => 'Mensual'];
            @endphp
            (Agrupado {{ $groupLabels[$groupBy] ?? $groupBy }})
        </div>
    </div>

    <!-- Resumen -->
    <div class="summary-section">
        <div class="summary-item">
            <div class="summary-label">Total Ingresos</div>
            <div class="summary-value positive">+${{ number_format($summary['total_inflows'], 2, ',', '.') }}</div>
        </div>
        <div class="summary-item">
            <div class="summary-label">Total Egresos</div>
            <div class="summary-value negative">-${{ number_format($summary['total_outflows'], 2, ',', '.') }}</div>
        </div>
        <div class="summary-item">
            <div class="summary-label">Resultado Neto</div>
            <div class="summary-value {{ $summary['net_amount'] >= 0 ? 'positive' : 'negative' }}">
                {{ $summary['net_amount'] >= 0 ? '+' : '' }}${{ number_format($summary['net_amount'], 2, ',', '.') }}
            </div>
        </div>
        <div class="summary-item">
            <div class="summary-label">Movimientos</div>
            <div class="summary-value">{{ number_format($summary['movements_count']) }}</div>
            <div style="font-size: 9px; color: #666;">en {{ $summary['period_days'] }} dias</div>
        </div>
    </div>

    <!-- Análisis por Tipo -->
    @if($movementsByType->count() > 0)
    <div class="section-title">Analisis por Tipo de Movimiento</div>
    <div class="type-cards">
        @foreach($movementsByType as $type => $data)
        <div class="type-card">
            <div class="type-card-header">
                <span>{{ $data['icon'] }} {{ $data['type_name'] }}</span>
                <span class="type-card-count">{{ $data['count'] }} mov.</span>
            </div>
            <div class="type-card-amount">
                @if($data['inflows'] > 0)
                    <span class="amount-positive">+${{ number_format($data['inflows'], 2, ',', '.') }}</span>
                @else
                    <span class="amount-negative">-${{ number_format($data['outflows'], 2, ',', '.') }}</span>
                @endif
            </div>
        </div>
        @endforeach
    </div>
    @endif

    <!-- Detalle por Período -->
    @if($reportData->count() > 0)
    <div class="section-title">Detalle por Periodo ({{ $reportData->count() }} periodos)</div>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 30%;">Periodo</th>
                <th style="width: 17%;" class="text-right">Ingresos</th>
                <th style="width: 17%;" class="text-right">Egresos</th>
                <th style="width: 18%;" class="text-right">Neto</th>
                <th style="width: 18%;" class="text-right">Movimientos</th>
            </tr>
        </thead>
        <tbody>
            @foreach($reportData as $period)
            <tr>
                <td>{{ $period['period_label'] }}</td>
                <td class="text-right amount-positive">+${{ number_format($period['inflows'], 2, ',', '.') }}</td>
                <td class="text-right amount-negative">-${{ number_format($period['outflows'], 2, ',', '.') }}</td>
                <td class="text-right {{ $period['net'] >= 0 ? 'amount-positive' : 'amount-negative' }}">
                    {{ $period['net'] >= 0 ? '+' : '' }}${{ number_format($period['net'], 2, ',', '.') }}
                </td>
                <td class="text-right">{{ $period['count'] }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="totals-row">
                <td><strong>TOTALES</strong></td>
                <td class="text-right amount-positive">+${{ number_format($summary['total_inflows'], 2, ',', '.') }}</td>
                <td class="text-right amount-negative">-${{ number_format($summary['total_outflows'], 2, ',', '.') }}</td>
                <td class="text-right {{ $summary['net_amount'] >= 0 ? 'amount-positive' : 'amount-negative' }}">
                    {{ $summary['net_amount'] >= 0 ? '+' : '' }}${{ number_format($summary['net_amount'], 2, ',', '.') }}
                </td>
                <td class="text-right">{{ $summary['movements_count'] }}</td>
            </tr>
        </tfoot>
    </table>
    @endif

    <!-- Footer -->
    <div class="footer">
        <div>Punto Salud - Reporte generado el {{ now()->format('d/m/Y H:i:s') }}</div>
        <div>Total de movimientos: {{ $summary['movements_count'] }}</div>
    </div>

    <script>
        window.onload = function() {
            setTimeout(function() {
                window.print();
                window.addEventListener('afterprint', function() {
                    window.close();
                });
                setTimeout(function() { window.close(); }, 3000);
            }, 500);
        }
    </script>
</body>
</html>
