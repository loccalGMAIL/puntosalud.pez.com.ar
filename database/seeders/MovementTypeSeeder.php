<?php

namespace Database\Seeders;

use App\Models\MovementType;
use Illuminate\Database\Seeder;

class MovementTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // ========================================
        // TIPOS DE SISTEMA (main_type)
        // ========================================

        MovementType::create([
            'code' => 'patient_payment',
            'name' => 'Pago de Paciente',
            'description' => 'Pago recibido de un paciente por consultas o tratamientos',
            'category' => 'main_type',
            'affects_balance' => 1, // Ingreso
            'icon' => '💰',
            'color' => 'green',
            'is_active' => true,
            'order' => 1,
        ]);

        MovementType::create([
            'code' => 'professional_payment',
            'name' => 'Pago a Profesional',
            'description' => 'Liquidación de comisiones a profesionales',
            'category' => 'main_type',
            'affects_balance' => -1, // Egreso
            'icon' => '👨‍⚕️',
            'color' => 'blue',
            'is_active' => true,
            'order' => 2,
        ]);

        MovementType::create([
            'code' => 'cash_opening',
            'name' => 'Apertura de Caja',
            'description' => 'Apertura de caja al inicio del día',
            'category' => 'main_type',
            'affects_balance' => 0, // Neutral (ajusta al saldo inicial)
            'icon' => '🔓',
            'color' => 'orange',
            'is_active' => true,
            'order' => 3,
        ]);

        MovementType::create([
            'code' => 'cash_closing',
            'name' => 'Cierre de Caja',
            'description' => 'Cierre de caja al final del día',
            'category' => 'main_type',
            'affects_balance' => 0, // Neutral
            'icon' => '🔒',
            'color' => 'orange',
            'is_active' => true,
            'order' => 4,
        ]);

        MovementType::create([
            'code' => 'cash_control',
            'name' => 'Control de Caja',
            'description' => 'Auditoría o control de caja',
            'category' => 'main_type',
            'affects_balance' => 0, // Neutral
            'icon' => '🔍',
            'color' => 'purple',
            'is_active' => true,
            'order' => 5,
        ]);

        MovementType::create([
            'code' => 'shift_handover',
            'name' => 'Entrega de Turno',
            'description' => 'Traspaso de caja entre turnos',
            'category' => 'main_type',
            'affects_balance' => 0, // Neutral
            'icon' => '🔄',
            'color' => 'indigo',
            'is_active' => true,
            'order' => 6,
        ]);

        MovementType::create([
            'code' => 'shift_receive',
            'name' => 'Recibo de Turno',
            'description' => 'Recepción de caja de turno anterior',
            'category' => 'main_type',
            'affects_balance' => 0, // Neutral
            'icon' => '📥',
            'color' => 'teal',
            'is_active' => true,
            'order' => 7,
        ]);

        // ========================================
        // GASTOS (expense_detail)
        // ========================================

        MovementType::create([
            'code' => 'refund',
            'name' => 'Reembolso',
            'description' => 'Devolución de dinero a pacientes',
            'category' => 'expense_detail',
            'affects_balance' => -1, // Egreso
            'icon' => '🔄',
            'color' => 'yellow',
            'is_active' => true,
            'order' => 1,
        ]);

        MovementType::create([
            'code' => 'office_supplies',
            'name' => 'Insumos de Oficina',
            'description' => 'Gastos en materiales de oficina',
            'category' => 'expense_detail',
            'affects_balance' => -1,
            'icon' => '📎',
            'color' => 'red',
            'is_active' => true,
            'order' => 2,
        ]);

        MovementType::create([
            'code' => 'medical_supplies',
            'name' => 'Insumos Médicos',
            'description' => 'Gastos en material médico y sanitario',
            'category' => 'expense_detail',
            'affects_balance' => -1,
            'icon' => '💉',
            'color' => 'red',
            'is_active' => true,
            'order' => 3,
        ]);

        MovementType::create([
            'code' => 'services',
            'name' => 'Servicios',
            'description' => 'Pago de servicios (luz, agua, internet, etc.)',
            'category' => 'expense_detail',
            'affects_balance' => -1,
            'icon' => '🔌',
            'color' => 'red',
            'is_active' => true,
            'order' => 4,
        ]);

        MovementType::create([
            'code' => 'maintenance',
            'name' => 'Mantenimiento',
            'description' => 'Gastos en mantenimiento y reparaciones',
            'category' => 'expense_detail',
            'affects_balance' => -1,
            'icon' => '🔧',
            'color' => 'red',
            'is_active' => true,
            'order' => 5,
        ]);

        MovementType::create([
            'code' => 'taxes',
            'name' => 'Impuestos',
            'description' => 'Pago de impuestos y cargas fiscales',
            'category' => 'expense_detail',
            'affects_balance' => -1,
            'icon' => '🏛️',
            'color' => 'red',
            'is_active' => true,
            'order' => 6,
        ]);

        MovementType::create([
            'code' => 'professional_payments',
            'name' => 'Pagos a Profesionales',
            'description' => 'Pagos varios a profesionales (no liquidaciones)',
            'category' => 'expense_detail',
            'affects_balance' => -1,
            'icon' => '👨‍⚕️',
            'color' => 'red',
            'is_active' => true,
            'order' => 7,
        ]);

        MovementType::create([
            'code' => 'other_expense',
            'name' => 'Otros Gastos',
            'description' => 'Otros gastos no categorizados',
            'category' => 'expense_detail',
            'affects_balance' => -1,
            'icon' => '📋',
            'color' => 'red',
            'is_active' => true,
            'order' => 9,
        ]);

        // ========================================
        // INGRESOS (income_detail)
        // ========================================

        MovementType::create([
            'code' => 'professional_module_payment',
            'name' => 'Pago Módulo Profesional',
            'description' => 'Pago recibido por módulo de profesionales',
            'category' => 'income_detail',
            'affects_balance' => 1,
            'icon' => '💳',
            'color' => 'green',
            'is_active' => true,
            'order' => 1,
        ]);

        MovementType::create([
            'code' => 'zalazar_balance_payment',
            'name' => 'Pago de Saldos Dra. Zalazar',
            'description' => 'Pagos de saldos de la Dra. Zalazar',
            'category' => 'income_detail',
            'affects_balance' => 1,
            'icon' => '💰',
            'color' => 'green',
            'is_active' => true,
            'order' => 2,
        ]);

        MovementType::create([
            'code' => 'correction',
            'name' => 'Corrección de Ingreso',
            'description' => 'Ajuste o corrección de ingresos',
            'category' => 'income_detail',
            'affects_balance' => 1,
            'icon' => '✏️',
            'color' => 'blue',
            'is_active' => true,
            'order' => 3,
        ]);

        MovementType::create([
            'code' => 'other_income',
            'name' => 'Otros Ingresos',
            'description' => 'Otros ingresos no categorizados',
            'category' => 'income_detail',
            'affects_balance' => 1,
            'icon' => '📋',
            'color' => 'green',
            'is_active' => true,
            'order' => 4,
        ]);

        // ========================================
        // RETIROS (withdrawal_detail)
        // ========================================

        MovementType::create([
            'code' => 'bank_deposit',
            'name' => 'Depósito Bancario',
            'description' => 'Retiro para depósito en banco',
            'category' => 'withdrawal_detail',
            'affects_balance' => -1,
            'icon' => '🏦',
            'color' => 'orange',
            'is_active' => true,
            'order' => 1,
        ]);

        MovementType::create([
            'code' => 'expense_payment',
            'name' => 'Pago de Gastos',
            'description' => 'Retiro para pago de gastos',
            'category' => 'withdrawal_detail',
            'affects_balance' => -1,
            'icon' => '💸',
            'color' => 'orange',
            'is_active' => true,
            'order' => 2,
        ]);

        MovementType::create([
            'code' => 'professional_liquidation',
            'name' => 'Liquidación de Profesional',
            'description' => 'Retiro para liquidación a profesionales',
            'category' => 'withdrawal_detail',
            'affects_balance' => -1,
            'icon' => '👨‍⚕️',
            'color' => 'orange',
            'is_active' => true,
            'order' => 3,
        ]);

        MovementType::create([
            'code' => 'safe_custody',
            'name' => 'Custodia en Caja Fuerte',
            'description' => 'Retiro para guardar en caja fuerte',
            'category' => 'withdrawal_detail',
            'affects_balance' => -1,
            'icon' => '🔐',
            'color' => 'orange',
            'is_active' => true,
            'order' => 4,
        ]);

        MovementType::create([
            'code' => 'other_withdrawal',
            'name' => 'Otro Retiro',
            'description' => 'Otros retiros no categorizados',
            'category' => 'withdrawal_detail',
            'affects_balance' => -1,
            'icon' => '📋',
            'color' => 'orange',
            'is_active' => true,
            'order' => 5,
        ]);
    }
}
