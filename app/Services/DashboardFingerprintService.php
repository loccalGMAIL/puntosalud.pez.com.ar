<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardFingerprintService
{
    /**
     * Calcula una huella liviana del estado de los datos que muestra el dashboard.
     * Si la huella cambia entre dos consultas, el dashboard debe refrescarse.
     */
    public function forDate(Carbon $date): string
    {
        $appointments = DB::table('appointments')
            ->whereDate('appointment_date', $date)
            ->selectRaw('COUNT(*) as total, MAX(id) as max_id, MAX(updated_at) as last_update')
            ->first();

        $patients = DB::table('patients')
            ->selectRaw('COUNT(*) as total, MAX(updated_at) as last_update')
            ->first();

        $payments = DB::table('payment_appointments')
            ->selectRaw('COUNT(*) as total, MAX(id) as max_id')
            ->first();

        $cash = DB::table('cash_movements')
            ->selectRaw('COUNT(*) as total, MAX(id) as max_id, MAX(updated_at) as last_update')
            ->first();

        $professionals = DB::table('professionals')
            ->selectRaw('COUNT(*) as total, MAX(updated_at) as last_update')
            ->first();

        return md5(json_encode([
            $date->toDateString(),
            $appointments,
            $patients,
            $payments,
            $cash,
            $professionals,
        ]));
    }
}
