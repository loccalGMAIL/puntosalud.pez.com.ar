<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Liquidación - Dr. {{ $liquidationData['professional']->full_name }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.4;
            color: #333;
            background: white;
        }
        
        .header {
            text-align: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #333;
        }
        
        .clinic-name {
            font-size: 20px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .report-title {
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 10px;
        }
        
        .report-info {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
            background-color: #f8f9fa;
            padding: 10px;
            border: 1px solid #dee2e6;
        }
        
        .info-section {
            flex: 1;
        }
        
        .info-label {
            font-weight: bold;
            margin-bottom: 3px;
        }
        
        .liquidation-summary {
            background-color: #e8f5e8;
            border: 2px solid #4caf50;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
        }
        
        .summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 5px;
        }
        
        .summary-row.total {
            border-top: 2px solid #4caf50;
            padding-top: 8px;
            margin-top: 8px;
            font-weight: bold;
            font-size: 14px;
        }
        
        .amount-to-pay {
            color: #2e7d32;
            font-size: 18px;
            font-weight: bold;
        }
        
        .section-title {
            font-size: 14px;
            font-weight: bold;
            margin: 20px 0 10px 0;
            padding: 5px 0;
            border-bottom: 1px solid #dee2e6;
        }
        
        .appointments-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        
        .appointments-table th,
        .appointments-table td {
            border: 1px solid #dee2e6;
            padding: 6px 8px;
            text-align: left;
            font-size: 11px;
        }
        
        .appointments-table th {
            background-color: #e9ecef;
            font-weight: bold;
            font-size: 10px;
            text-transform: uppercase;
        }
        
        .prepaid-section {
            background-color: #fff3cd;
            border-left: 4px solid #ffc107;
        }
        
        .today-paid-section {
            background-color: #d4edda;
            border-left: 4px solid #28a745;
        }
        
        .unpaid-section {
            background-color: #f8d7da;
            border-left: 4px solid #dc3545;
        }
        
        .section-header {
            padding: 8px 12px;
            font-weight: bold;
            font-size: 12px;
        }
        
        .time-column {
            width: 50px;
            text-align: center;
            font-weight: bold;
        }
        
        .patient-column {
            width: 35%;
        }
        
        .amount-column {
            width: 80px;
            text-align: right;
        }
        
        .payment-column {
            width: 15%;
            font-size: 10px;
        }
        
        .footer {
            margin-top: 30px;
            padding-top: 15px;
            border-top: 1px solid #dee2e6;
            display: flex;
            justify-content: space-between;
            font-size: 10px;
            color: #6c757d;
        }
        
        .signature-section {
            margin-top: 40px;
            display: flex;
            justify-content: space-between;
        }
        
        .signature-box {
            text-align: center;
            width: 45%;
            border-top: 1px solid #333;
            padding-top: 5px;
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
        <div class="report-title">LIQUIDACIÓN DIARIA DEL PROFESIONAL</div>
    </div>
    
    <!-- Report Info -->
    <div class="report-info">
        <div class="info-section">
            <div class="info-label">Profesional:</div>
            <div>Dr. {{ $liquidationData['professional']->full_name }}</div>
            <div style="font-size: 10px; color: #666;">{{ $liquidationData['professional']->specialty->name }}</div>
        </div>
        <div class="info-section">
            <div class="info-label">Fecha:</div>
            <div>{{ $liquidationData['date']->format('d/m/Y') }}</div>
            <div style="font-size: 10px; color: #666;">{{ $liquidationData['date']->translatedFormat('l') }}</div>
        </div>
        <div class="info-section">
            <div class="info-label">Comisión:</div>
            <div>{{ $liquidationData['totals']['commission_percentage'] }}%</div>
            <div style="font-size: 10px; color: #666;">Pacientes: {{ $liquidationData['appointments']->count() }}</div>
        </div>
    </div>
    
    <!-- Liquidation Summary v2.6.0 -->
    <div class="liquidation-summary">
        <div class="summary-row" style="font-weight: bold; border-bottom: 1px solid #4caf50; padding-bottom: 5px; margin-bottom: 8px;">
            <span>Total Facturado del Día:</span>
            <span>${{ number_format($liquidationData['totals']['total_amount'], 0, ',', '.') }}</span>
        </div>

        @if($liquidationData['totals']['total_collected_by_center'] > 0)
        <div style="background: #e3f2fd; padding: 8px; margin-bottom: 8px; border-left: 3px solid #2196f3;">
            <div class="summary-row" style="font-weight: bold;">
                <span>💵 Pagos recibidos por el centro:</span>
                <span>${{ number_format($liquidationData['totals']['total_collected_by_center'], 0, ',', '.') }}</span>
            </div>
            <div class="summary-row" style="font-size: 10px; padding-left: 15px; color: #1976d2;">
                <span>Comisión profesional ({{ $liquidationData['totals']['commission_percentage'] }}%):</span>
                <span style="color: #2e7d32;">+${{ number_format($liquidationData['totals']['professional_commission'], 0, ',', '.') }}</span>
            </div>
            <div class="summary-row" style="font-size: 10px; padding-left: 15px; color: #1976d2;">
                <span>Parte del centro ({{ $liquidationData['totals']['clinic_percentage'] }}%):</span>
                <span>${{ number_format($liquidationData['totals']['clinic_amount'], 0, ',', '.') }}</span>
            </div>
        </div>
        @endif

        @if($liquidationData['totals']['total_collected_by_professional'] > 0)
        <div style="background: #fff8e1; padding: 8px; margin-bottom: 8px; border-left: 3px solid #ffc107;">
            <div class="summary-row" style="font-weight: bold;">
                <span>🏦 Pagos directos al profesional:</span>
                <span>${{ number_format($liquidationData['totals']['total_collected_by_professional'], 0, ',', '.') }}</span>
            </div>
            <div class="summary-row" style="font-size: 10px; padding-left: 15px; color: #f57c00;">
                <span>Parte del centro a descontar ({{ $liquidationData['totals']['clinic_percentage'] }}%):</span>
                <span style="color: #c62828;">-${{ number_format($liquidationData['totals']['clinic_amount_from_direct'], 0, ',', '.') }}</span>
            </div>
        </div>
        @endif

        @if($liquidationData['totals']['total_refunds'] > 0)
        <div class="summary-row" style="color: #c62828;">
            <span>🔄 Reintegros a Pacientes:</span>
            <span>-${{ number_format($liquidationData['totals']['total_refunds'], 0, ',', '.') }}</span>
        </div>
        @endif

        <div style="border-top: 2px solid #4caf50; padding-top: 8px; margin-top: 8px;">
            <div style="font-size: 10px; color: #666; margin-bottom: 5px;">
                <div class="summary-row">
                    <span>Comisión sobre pagos al centro:</span>
                    <span>+${{ number_format($liquidationData['totals']['professional_commission'], 0, ',', '.') }}</span>
                </div>
                @if($liquidationData['totals']['total_collected_by_professional'] > 0)
                <div class="summary-row">
                    <span>Menos: parte del centro sobre pagos directos:</span>
                    <span>-${{ number_format($liquidationData['totals']['clinic_amount_from_direct'], 0, ',', '.') }}</span>
                </div>
                @endif
                @if($liquidationData['totals']['total_refunds'] > 0)
                <div class="summary-row">
                    <span>Menos: reintegros:</span>
                    <span>-${{ number_format($liquidationData['totals']['total_refunds'], 0, ',', '.') }}</span>
                </div>
                @endif
            </div>
            <div class="summary-row total">
                <span>
                    @if($liquidationData['totals']['net_professional_amount'] >= 0)
                        MONTO A ENTREGAR AL PROFESIONAL:
                    @else
                        MONTO QUE EL PROFESIONAL DEBE AL CENTRO:
                    @endif
                </span>
                <span class="amount-to-pay" style="color: {{ $liquidationData['totals']['net_professional_amount'] >= 0 ? '#2e7d32' : '#c62828' }}">
                    ${{ number_format(abs($liquidationData['totals']['net_professional_amount']), 0, ',', '.') }}
                </span>
            </div>
        </div>
    </div>
    
    <!-- Turnos Pagados Previamente -->
    @if($liquidationData['prepaid_appointments']->count() > 0)
        <div class="section-title">💳 Turnos Pagados Previamente ({{ $liquidationData['prepaid_appointments']->count() }})</div>
        <div class="prepaid-section">
            <div class="section-header">
                Total: ${{ number_format($liquidationData['totals']['prepaid_amount'], 0, ',', '.') }} | 
                Su comisión: ${{ number_format($liquidationData['totals']['prepaid_professional'], 0, ',', '.') }}
            </div>
            <table class="appointments-table">
                <thead>
                    <tr>
                        <th class="time-column">Hora</th>
                        <th class="patient-column">Paciente</th>
                        <th class="amount-column">Monto</th>
                        <th class="payment-column">Método</th>
                        <th style="width: 15%;">Receptor</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($liquidationData['prepaid_appointments'] as $appointment)
                        <tr>
                            <td class="time-column">{{ $appointment['time'] }}</td>
                            <td class="patient-column">
                                <strong>{{ $appointment['patient_name'] }}</strong>
                                <br><small>DNI: {{ $appointment['patient_dni'] }}</small>
                            </td>
                            <td class="amount-column">${{ number_format($appointment['final_amount'], 0, ',', '.') }}</td>
                            <td class="payment-column">
                                <small>
                                    {{ match($appointment['payment_method']) {
                                        'cash' => 'Efectivo',
                                        'transfer' => 'Transferencia',
                                        'card' => 'Tarjeta',
                                        'qr' => 'QR',
                                        default => $appointment['payment_method']
                                    } }}<br>
                                    {{ $appointment['payment_date'] }}
                                    @if($appointment['receipt_number'])
                                        <br>Rec: {{ $appointment['receipt_number'] }}
                                    @endif
                                </small>
                            </td>
                            <td style="text-align: center; font-size: 10px;">
                                @if($appointment['received_by'] === 'profesional')
                                    <strong style="color: #f57c00;">👤 Profesional</strong>
                                @elseif($appointment['received_by'] === 'centro')
                                    <strong style="color: #1976d2;">🏥 Centro</strong>
                                @elseif($appointment['received_by'] === 'mixed')
                                    <strong style="color: #7e22ce;">🔀 Mixto</strong>
                                @else
                                    -
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
    
    <!-- Turnos Pagados Hoy -->
    @if($liquidationData['today_paid_appointments']->count() > 0)
        <div class="section-title">💰 Turnos Cobrados Hoy ({{ $liquidationData['today_paid_appointments']->count() }})</div>
        <div class="today-paid-section">
            <div class="section-header">
                Total: ${{ number_format($liquidationData['totals']['today_paid_amount'], 0, ',', '.') }} | 
                Su comisión: ${{ number_format($liquidationData['totals']['today_paid_professional'], 0, ',', '.') }}
            </div>
            <table class="appointments-table">
                <thead>
                    <tr>
                        <th class="time-column">Hora</th>
                        <th class="patient-column">Paciente</th>
                        <th class="amount-column">Monto</th>
                        <th class="payment-column">Método</th>
                        <th style="width: 15%;">Receptor</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($liquidationData['today_paid_appointments'] as $appointment)
                        <tr>
                            <td class="time-column">{{ $appointment['time'] }}</td>
                            <td class="patient-column">
                                <strong>{{ $appointment['patient_name'] }}</strong>
                                <br><small>DNI: {{ $appointment['patient_dni'] }}</small>
                            </td>
                            <td class="amount-column">${{ number_format($appointment['final_amount'], 0, ',', '.') }}</td>
                            <td class="payment-column">
                                <small>
                                    {{ match($appointment['payment_method']) {
                                        'cash' => 'Efectivo',
                                        'transfer' => 'Transferencia',
                                        'card' => 'Tarjeta',
                                        'qr' => 'QR',
                                        default => $appointment['payment_method']
                                    } }}
                                    @if($appointment['receipt_number'])
                                        <br>Rec: {{ $appointment['receipt_number'] }}
                                    @endif
                                </small>
                            </td>
                            <td style="text-align: center; font-size: 10px;">
                                @if($appointment['received_by'] === 'profesional')
                                    <strong style="color: #f57c00;">👤 Profesional</strong>
                                @elseif($appointment['received_by'] === 'centro')
                                    <strong style="color: #1976d2;">🏥 Centro</strong>
                                @elseif($appointment['received_by'] === 'mixed')
                                    <strong style="color: #7e22ce;">🔀 Mixto</strong>
                                @else
                                    -
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
    
    <!-- Turnos Sin Pagar -->
    @if($liquidationData['unpaid_appointments']->count() > 0)
        <div class="section-title">⚠️ Turnos Pendientes de Pago ({{ $liquidationData['unpaid_appointments']->count() }})</div>
        <div class="unpaid-section">
            <div class="section-header">
                Total pendiente: ${{ number_format($liquidationData['totals']['unpaid_amount'], 0, ',', '.') }} | 
                Su comisión pendiente: ${{ number_format($liquidationData['totals']['unpaid_professional'], 0, ',', '.') }}
            </div>
            <table class="appointments-table">
                <thead>
                    <tr>
                        <th class="time-column">Hora</th>
                        <th class="patient-column">Paciente</th>
                        <th class="amount-column">Monto</th>
                        <th class="payment-column">Estado</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($liquidationData['unpaid_appointments'] as $appointment)
                        <tr>
                            <td class="time-column">{{ $appointment['time'] }}</td>
                            <td class="patient-column">
                                <strong>{{ $appointment['patient_name'] }}</strong>
                                <br><small>DNI: {{ $appointment['patient_dni'] }}</small>
                            </td>
                            <td class="amount-column">${{ number_format($appointment['final_amount'], 0, ',', '.') }}</td>
                            <td class="payment-column">
                                <small style="color: #dc3545;">PENDIENTE</small>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
    
    <!-- Signature Section -->
    <div class="signature-section">
        <div class="signature-box">
            <strong>Firma del Profesional</strong>
            <br><small>Dr. {{ $liquidationData['professional']->full_name }}</small>
        </div>
        <div class="signature-box">
            <strong>Firma Autorizada</strong>
            <br><small>{{ $liquidationData['generated_by'] }}</small>
        </div>
    </div>
    
    <!-- Footer -->
    <div class="footer">
        <div>Punto Salud - Liquidación generada el {{ $liquidationData['generated_at']->format('d/m/Y H:i:s') }}</div>
        <div>Dr. {{ $liquidationData['professional']->full_name }} - {{ $liquidationData['date']->format('d/m/Y') }}</div>
    </div>
    
    <script>
        if (window.location.search.includes('print=1')) {
            window.onload = function() {
                setTimeout(function() {
                    window.print();
                    
                    // Auto-close after printing
                    window.addEventListener('afterprint', function() {
                        window.close();
                    });
                    
                    // Fallback: close after 3 seconds if afterprint doesn't fire
                    setTimeout(function() {
                        window.close();
                    }, 3000);
                }, 500);
            }
        }
    </script>
</body>
</html>