<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Informe de Gastos - {{ \Carbon\Carbon::parse($dateFrom)->format('d/m/Y') }} al {{ \Carbon\Carbon::parse($dateTo)->format('d/m/Y') }}</title>
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

        .print-button {
            position: fixed;
            top: 20px;
            right: 20px;
            background: #4b5563;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            z-index: 1000;
        }

        .print-button:hover {
            background: #374151;
        }

        .header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 2px solid #333;
        }

        .header-logo img {
            height: 40px;
            width: auto;
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

        .summary-section {
            display: flex;
            justify-content: space-between;
            margin-bottom: 15px;
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
        }

        .summary-item {
            flex: 1;
            text-align: center;
            padding: 10px 8px;
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

        .summary-value.negative {
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
            display: flex;
            justify-content: space-between;
            font-size: 8px;
            color: #6c757d;
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
        <div class="header-logo">
            <img src="{{ asset('logo.png') }}" alt="Punto Salud">
        </div>
        <div class="header-info">
            <div class="clinic-name">PUNTO SALUD</div>
            <div class="report-title">INFORME DE GASTOS</div>
            <div class="report-date">
                Período: {{ \Carbon\Carbon::parse($dateFrom)->format('d/m/Y') }} al {{ \Carbon\Carbon::parse($dateTo)->format('d/m/Y') }}
            </div>
        </div>
    </div>

    <!-- Resumen -->
    <div class="summary-section">
        <div class="summary-item">
            <div class="summary-label">Total Gastos</div>
            <div class="summary-value negative">-${{ number_format($totalAmount, 2, ',', '.') }}</div>
        </div>
        <div class="summary-item">
            <div class="summary-label">Cant. Registros</div>
            <div class="summary-value">{{ $totalCount }}</div>
        </div>
        <div class="summary-item">
            <div class="summary-label">Tipos de Gasto</div>
            <div class="summary-value">{{ $byType->count() }}</div>
        </div>
        <div class="summary-item">
            <div class="summary-label">Promedio por Registro</div>
            <div class="summary-value negative">-${{ number_format($avgAmount, 2, ',', '.') }}</div>
        </div>
    </div>

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
        <div>Punto Salud · Informe de Gastos · Generado el {{ now()->format('d/m/Y H:i') }}</div>
        <div>Total: -${{ number_format($totalAmount, 2, ',', '.') }} · {{ $totalCount }} registros</div>
    </div>

    <script>
        if (window.location.search.includes('print=1')) {
            window.onload = function() {
                setTimeout(function() {
                    window.print();
                    window.addEventListener('afterprint', function() {
                        window.close();
                    });
                    setTimeout(function() { window.close(); }, 3000);
                }, 500);
            }
        }
    </script>

</body>
</html>
