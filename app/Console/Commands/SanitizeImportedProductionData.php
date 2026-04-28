<?php

namespace App\Console\Commands;

use App\Services\SettingService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SanitizeImportedProductionData extends Command
{
    protected $signature = 'dev:sanitizar'
        . ' {--phone=3541693961 : Telefono de prueba a aplicar (pacientes y profesionales)}'
        . ' {--wa-instance=PuntoSalud_dev : WhatsApp instance a configurar}'
        . ' {--wa-api-key=0B69985238C0-4533-8D27-4E6383F13F5F : WhatsApp API key a configurar}'
        . ' {--force : Ejecuta sin pedir confirmacion}';

    protected $description = 'Sanitiza datos importados (telefonos) y configura WhatsApp para entorno de desarrollo';

    public function handle(SettingService $settings): int
    {
        if (app()->environment('production')) {
            $this->error('Este comando esta bloqueado en produccion para evitar perdida de datos.');

            return self::FAILURE;
        }

        $phone = trim((string) $this->option('phone'));
        $waInstance = trim((string) $this->option('wa-instance'));
        $waApiKey = trim((string) $this->option('wa-api-key'));

        if ($phone === '' || $waInstance === '' || $waApiKey === '') {
            $this->error('Los parametros --phone, --wa-instance y --wa-api-key no pueden estar vacios.');

            return self::FAILURE;
        }

        if (! $this->option('force')) {
            $this->line('Se van a aplicar los siguientes cambios:');
            $this->line("- Patients.phone y patients.phone_landline => {$phone}");
            $this->line("- Professionals.phone => {$phone}");
            $this->line("- setting('whatsapp.instance') => {$waInstance}");
            $this->line("- setting('whatsapp.api_key') => {$waApiKey}");

            if (! $this->confirm('Confirmas ejecutar este saneamiento?', false)) {
                $this->warn('Operacion cancelada.');

                return self::SUCCESS;
            }
        }

        $updatedPatients = 0;
        $updatedProfessionals = 0;

        DB::transaction(function () use ($phone, $waInstance, $waApiKey, $settings, &$updatedPatients, &$updatedProfessionals): void {
            $updatedPatients = DB::table('patients')->update([
                'phone' => $phone,
                'phone_landline' => $phone,
            ]);

            $updatedProfessionals = DB::table('professionals')->update([
                'phone' => $phone,
            ]);

            $group = 'whatsapp';
            $settings->set('whatsapp.instance', $group, $waInstance);
            $settings->set('whatsapp.api_key', $group, $waApiKey);
        });

        $this->info('Saneamiento completado.');
        $this->line("Patients actualizados: {$updatedPatients}");
        $this->line("Professionals actualizados: {$updatedProfessionals}");
        $this->line("WhatsApp instance: {$waInstance}");
        $this->line('WhatsApp api_key: [configurada]');

        return self::SUCCESS;
    }
}
