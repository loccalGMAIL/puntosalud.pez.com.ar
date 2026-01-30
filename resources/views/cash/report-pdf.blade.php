<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Reporte de Caja - {{ $dateFrom }} al {{ $dateTo }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 10px;
            line-height: 1.3;
            color: #333;
        }

        .header {
            text-align: center;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 2px solid #333;
        }

        .clinic-name {
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 3px;
        }

        .report-title {
            font-size: 13px;
            font-weight: bold;
            margin-bottom: 3px;
        }

        .report-date {
            font-size: 11px;
            color: #666;
        }

        .summary-table {
            width: 100%;
            margin-bottom: 15px;
            border-collapse: collapse;
        }

        .summary-table td {
            width: 25%;
            text-align: center;
            padding: 8px;
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
        }

        .summary-label {
            font-size: 9px;
            color: #666;
            margin-bottom: 3px;
        }

        .summary-value {
            font-size: 13px;
            font-weight: bold;
        }

        .positive {
            color: #28a745;
        }

        .negative {
            color: #dc3545;
        }

        .section-title {
            font-size: 11px;
            font-weight: bold;
            margin: 15px 0 8px 0;
            padding: 4px 6px;
            background-color: #e9ecef;
            border-left: 3px solid #333;
        }

        .type-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }

        .type-table th,
        .type-table td {
            border: 1px solid #dee2e6;
            padding: 5px 8px;
            text-align: left;
            font-size: 9px;
        }

        .type-table th {
            background-color: #e9ecef;
            font-weight: bold;
            text-transform: uppercase;
        }

        .type-table tr:nth-child(even) {
            background-color: #f8f9fa;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }

        .data-table th,
        .data-table td {
            border: 1px solid #dee2e6;
            padding: 5px 8px;
            text-align: left;
            font-size: 9px;
        }

        .data-table th {
            background-color: #e9ecef;
            font-weight: bold;
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

        .totals-row {
            background-color: #e9ecef !important;
            font-weight: bold;
        }

        .footer {
            margin-top: 20px;
            padding-top: 8px;
            border-top: 1px solid #dee2e6;
            font-size: 8px;
            color: #6c757d;
        }

        .footer-table {
            width: 100%;
        }

        .footer-table td {
            padding: 0;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <div class="clinic-name">PUNTO SALUD</div>
        <div class="report-title">REPORTE DE CAJA</div>
        <div class="report-date">
            @php
                $groupLabels = ['day' => 'Diario', 'week' => 'Semanal', 'month' => 'Mensual'];
            @endphp
            Periodo: {{ \Carbon\Carbon::parse($dateFrom)->format('d/m/Y') }} al {{ \Carbon\Carbon::parse($dateTo)->format('d/m/Y') }}
            (Agrupado {{ $groupLabels[$groupBy] ?? $groupBy }})
        </div>
    </div>

    <!-- Resumen -->
    <table class="summary-table">
        <tr>
            <td>
                <div class="summary-label">Total Ingresos</div>
                <div class="summary-value positive">+${{ number_format($summary['total_inflows'], 2, ',', '.') }}</div>
            </td>
            <td>
                <div class="summary-label">Total Egresos</div>
                <div class="summary-value negative">-${{ number_format($summary['total_outflows'], 2, ',', '.') }}</div>
            </td>
            <td>
                <div class="summary-label">Resultado Neto</div>
                <div class="summary-value {{ $summary['net_amount'] >= 0 ? 'positive' : 'negative' }}">
                    {{ $summary['net_amount'] >= 0 ? '+' : '' }}${{ number_format($summary['net_amount'], 2, ',', '.') }}
                </div>
            </td>
            <td>
                <div class="summary-label">Movimientos</div>
                <div class="summary-value">{{ number_format($summary['movements_count']) }}</div>
                <div style="font-size: 8px; color: #666;">en {{ $summary['period_days'] }} dias</div>
            </td>
        </tr>
    </table>

    <!-- Análisis por Tipo -->
    @if($movementsByType->count() > 0)
    <div class="section-title">Analisis por Tipo de Movimiento</div>
    <table class="type-table">
        <thead>
            <tr>
                <th style="width: 40%;">Tipo</th>
                <th style="width: 20%;" class="text-right">Ingresos</th>
                <th style="width: 20%;" class="text-right">Egresos</th>
                <th style="width: 20%;" class="text-right">Cantidad</th>
            </tr>
        </thead>
        <tbody>
            @foreach($movementsByType as $type => $data)
            <tr>
                <td>{{ $data['type_name'] }}</td>
                <td class="text-right {{ $data['inflows'] > 0 ? 'amount-positive' : '' }}">
                    @if($data['inflows'] > 0)
                        +${{ number_format($data['inflows'], 2, ',', '.') }}
                    @else
                        -
                    @endif
                </td>
                <td class="text-right {{ $data['outflows'] > 0 ? 'amount-negative' : '' }}">
                    @if($data['outflows'] > 0)
                        -${{ number_format($data['outflows'], 2, ',', '.') }}
                    @else
                        -
                    @endif
                </td>
                <td class="text-right">{{ $data['count'] }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
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
        <table class="footer-table">
            <tr>
                <td>Punto Salud - Reporte generado el {{ now()->format('d/m/Y H:i:s') }}</td>
                <td class="text-right">Total de movimientos: {{ $summary['movements_count'] }}</td>
            </tr>
        </table>
    </div>
</body>
</html>
