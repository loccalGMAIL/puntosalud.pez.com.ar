<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Listado de Pacientes - Dr. {{ $reportData['professional']->full_name }}</title>
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
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 10px;
            margin-bottom: 20px;
        }
        
        .stat-card {
            text-align: center;
            padding: 8px;
            border: 1px solid #dee2e6;
            background-color: #f8f9fa;
        }
        
        .stat-number {
            font-size: 18px;
            font-weight: bold;
            color: #2563eb;
        }
        
        .stat-label {
            font-size: 10px;
            text-transform: uppercase;
            margin-top: 2px;
        }
        
        .appointments-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        
        .appointments-table th,
        .appointments-table td {
            border: 1px solid #dee2e6;
            padding: 8px;
            text-align: left;
            font-size: 11px;
        }
        
        .appointments-table th {
            background-color: #e9ecef;
            font-weight: bold;
            font-size: 10px;
            text-transform: uppercase;
        }
        
        .time-column {
            width: 60px;
            text-align: center;
            font-weight: bold;
        }
        
        .patient-column {
            width: 30%;
        }

        .status-column {
            width: 100px;
            text-align: center;
        }

        .notes-column {
            width: 40%;
            font-size: 10px;
        }
        
        .status-badge {
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 9px;
            font-weight: bold;
            text-transform: uppercase;
        }
        
        .status-scheduled {
            background-color: #fff3cd;
            color: #856404;
        }
        
        .status-attended {
            background-color: #d4edda;
            color: #155724;
        }
        
        .status-cancelled {
            background-color: #f8d7da;
            color: #721c24;
        }
        
        .status-absent {
            background-color: #e2e3e5;
            color: #41464b;
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
        
        .no-appointments {
            text-align: center;
            padding: 40px;
            color: #6c757d;
            font-style: italic;
        }
        
        @media print {
            body {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
            
            .no-print {
                display: none !important;
            }
            
            .page-break {
                page-break-after: always;
            }
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
    </style>
</head>
<body>
    <button class="print-button no-print" onclick="window.print()">🖨️ Imprimir</button>
    
    <!-- Header -->
    <div class="header">
        <div class="clinic-name">PUNTO SALUD</div>
        <div class="report-title">LISTADO DE PACIENTES A ATENDER</div>
    </div>
    
    <!-- Report Info -->
    <div class="report-info">
        <div class="info-section">
            <div class="info-label">Profesional:</div>
            <div>Dr. {{ $reportData['professional']->full_name }}</div>
            <div style="font-size: 10px; color: #666;">{{ $reportData['professional']->specialty->name }}</div>
        </div>
        <div class="info-section">
            <div class="info-label">Fecha:</div>
            <div>{{ $reportData['date']->format('d/m/Y') }}</div>
            <div style="font-size: 10px; color: #666;">{{ $reportData['date']->translatedFormat('l') }}</div>
        </div>
        <div class="info-section">
            <div class="info-label">Generado:</div>
            <div>{{ $reportData['generated_at']->format('d/m/Y H:i') }}</div>
            <div style="font-size: 10px; color: #666;">Por: {{ $reportData['generated_by'] }}</div>
        </div>
    </div>
    
    <!-- Stats -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-number">{{ $reportData['stats']['total_appointments'] }}</div>
            <div class="stat-label">Total Pacientes</div>
        </div>
        <div class="stat-card">
            <div class="stat-number">{{ $reportData['stats']['scheduled'] }}</div>
            <div class="stat-label">Programados</div>
        </div>
    </div>
    
    <!-- Appointments Table -->
    @if($reportData['appointments']->count() > 0)
        <table class="appointments-table">
            <thead>
                <tr>
                    <th class="time-column">Hora</th>
                    <th class="patient-column">Paciente</th>
                    <th class="status-column">Estado</th>
                    <th class="notes-column">Observaciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach($reportData['appointments'] as $appointment)
                    <tr>
                        <td class="time-column">{{ $appointment['time'] }}</td>
                        <td class="patient-column">
                            <div><strong>{{ $appointment['patient_name'] }}</strong></div>
                            <div style="font-size: 9px; color: #666;">
                                DNI: {{ $appointment['patient_dni'] }}
                                @if($appointment['patient_insurance'])
                                    | {{ $appointment['patient_insurance'] }}
                                @endif
                            </div>
                        </td>
                        <td class="status-column">
                            <span class="status-badge status-{{ $appointment['status'] }}">
                                {{ $appointment['status_label'] }}
                            </span>
                        </td>
                        <td class="notes-column">
                            {{ $appointment['notes'] ?: '-' }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <div class="no-appointments">
            <strong>No hay pacientes para atender este día</strong>
            <br>
            <small>El profesional no tiene pacientes asignados para {{ $reportData['date']->format('d/m/Y') }}</small>
        </div>
    @endif
    
    <!-- Footer -->
    <div class="footer">
        <div>Punto Salud - Sistema de Gestión Médica</div>
        <div>Página generada el {{ now()->format('d/m/Y H:i:s') }}</div>
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