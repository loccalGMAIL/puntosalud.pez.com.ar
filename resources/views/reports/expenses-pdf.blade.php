@php
    $logoPath = public_path('logo.png');
    $logoBase64 = file_exists($logoPath) ? base64_encode(file_get_contents($logoPath)) : null;
    $avgAmount = $totalCount > 0 ? $totalAmount / $totalCount : 0;
@endphp
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Informe de Gastos - {{ $dateFrom }} al {{ $dateTo }}</title>
    <style>
        @page {
            margin-top: 1.5cm;
            margin-right: 1.5cm;
            margin-bottom: 1.5cm;
            margin-left: 1.5cm;
        }

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

        /* Header */
        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 2px solid #333;
        }

        .header-table td {
            padding: 0;
            vertical-align: middle;
        }

        .header-logo {
            width: 80px;
        }

        .header-info {
            text-align: right;
        }

        .clinic-name {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 3px;
        }

        .report-title {
            font-size: 14px;
            font-weight: bold;
            color: #555;
            margin-bottom: 3px;
        }

        .report-date {
            font-size: 12px;
            color: #666;
        }

        /* Summary */
        .summary-table {
            width: 100%;
            margin-bottom: 15px;
            border-collapse: collapse;
        }

        .summary-table td {
            width: 25%;
            text-align: center;
            padding: 10px 8px;
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
        }

        .summary-label {
            font-size: 9px;
            color: #666;
            margin-bottom: 4px;
        }

        .summary-value {
            font-size: 14px;
            font-weight: bold;
        }

        .negative {
            color: #dc3545;
        }

        /* Section title */
        .section-title {
            font-size: 11px;
            font-weight: bold;
            margin: 15px 0 8px 0;
            padding: 4px 6px;
            background-color: #e9ecef;
            border-left: 3px solid #333;
        }

        /* Type table */
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

        /* Data table */
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

        .amount-negative {
            color: #dc3545;
            font-weight: bold;
        }

        .totals-row {
            background-color: #e9ecef !important;
            font-weight: bold;
        }

        /* Footer */
        .footer {
            margin-top: 20px;
            padding-top: 8px;
            border-top: 1px solid #dee2e6;
            font-size: 8px;
            color: #6c757d;
        }

        .footer-table {
            width: 100%;
            border-collapse: collapse;
        }

        .footer-table td {
            padding: 0;
        }
    </style>
</head>
<body>

    <!-- Header -->
    <table class="header-table">
        <tr>
            <td class="header-logo">
                @if($logoBase64)
                    <img src="data:image/png;base64,{{ $logoBase64 }}" style="height: 40px; width: auto;">
                @endif
            </td>
            <td class="header-info">
                <div class="clinic-name">PUNTO SALUD</div>
                <div class="report-title">INFORME DE GASTOS</div>
                <div class="report-date">
                    Período: {{ \Carbon\Carbon::parse($dateFrom)->format('d/m/Y') }} al {{ \Carbon\Carbon::parse($dateTo)->format('d/m/Y') }}
                </div>
            </td>
        </tr>
    </table>

    <!-- Resumen -->
    <table class="summary-table">
        <tr>
            <td>
                <div class="summary-label">Total Gastos</div>
                <div class="summary-value negative">-${{ number_format($totalAmount, 2, ',', '.') }}</div>
            </td>
            <td>
                <div class="summary-label">Cant. Registros</div>
                <div class="summary-value">{{ $totalCount }}</div>
            </td>
            <td>
                <div class="summary-label">Tipos de Gasto</div>
                <div class="summary-value">{{ $byType->count() }}</div>
            </td>
            <td>
                <div class="summary-label">Promedio por Registro</div>
                <div class="summary-value negative">-${{ number_format($avgAmount, 2, ',', '.') }}</div>
            </td>
        </tr>
    </table>

    <!-- Desglose por tipo -->
    @if($byType->count() > 0)
    <div class="section-title">Desglose por Tipo de Gasto</div>
    <table class="type-table">
        <thead>
            <tr>
                <th style="width: 55%;">Tipo</th>
                <th style="width: 20%;" class="text-right">Cantidad</th>
                <th style="width: 25%;" class="text-right">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach($byType as $item)
            <tr>
                <td>{{ $item['name'] }}</td>
                <td class="text-right">{{ $item['count'] }}</td>
                <td class="text-right amount-negative">-${{ number_format($item['total'], 2, ',', '.') }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="totals-row">
                <td><strong>TOTAL</strong></td>
                <td class="text-right">{{ $totalCount }}</td>
                <td class="text-right amount-negative">-${{ number_format($totalAmount, 2, ',', '.') }}</td>
            </tr>
        </tfoot>
    </table>
    @endif

    <!-- Detalle -->
    @if($movements->count() > 0)
    <div class="section-title">Detalle de Gastos ({{ $totalCount }} registros)</div>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 13%;">Fecha</th>
                <th style="width: 8%;">Hora</th>
                <th style="width: 22%;">Tipo</th>
                <th style="width: 37%;">Descripción</th>
                <th style="width: 12%;" class="text-right">Monto</th>
                <th style="width: 8%;">Usuario</th>
            </tr>
        </thead>
        <tbody>
            @foreach($movements as $m)
            <tr>
                <td>{{ $m->created_at->format('d/m/Y') }}</td>
                <td>{{ $m->created_at->format('H:i') }}</td>
                <td>{{ $m->movementType?->name ?? '-' }}</td>
                <td>{{ $m->description ?: '-' }}</td>
                <td class="text-right amount-negative">-${{ number_format(abs($m->amount), 2, ',', '.') }}</td>
                <td>{{ $m->user?->name ?? 'Sis.' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif

    <!-- Footer -->
    <div class="footer">
        <table class="footer-table">
            <tr>
                <td>Punto Salud · Informe de Gastos · Generado el {{ now()->format('d/m/Y H:i') }}</td>
                <td class="text-right">Total: -${{ number_format($totalAmount, 2, ',', '.') }} · {{ $totalCount }} registros</td>
            </tr>
        </table>
    </div>

</body>
</html>
