<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

class MigrateToV260 extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'migrate:v2.6.0 {--rollback : Revertir las migraciones de v2.6.0} {--force : Forzar ejecución sin confirmación}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Ejecuta las migraciones específicas de v2.6.0 para reestructurar el sistema de pagos';

    /**
     * Migraciones de v2.6.0 en orden de ejecución
     */
    protected $migrations = [
        '2025_11_07_100000_restructure_payments_table.php',
        '2025_11_07_100001_create_payment_details_table.php',
        '2025_11_07_100002_create_packages_table.php',
        '2025_11_07_100003_create_patient_packages_table.php',
        // '2025_11_07_100004_migrate_existing_payment_data.php',
    ];

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if ($this->option('rollback')) {
            return $this->rollbackMigrations();
        }

        return $this->runMigrations();
    }

    /**
     * Ejecutar las migraciones de v2.6.0
     */
    protected function runMigrations()
    {
        $this->info('╔════════════════════════════════════════════════════════════╗');
        $this->info('║     Migraciones v2.6.0 - Reestructuración de Pagos        ║');
        $this->info('╚════════════════════════════════════════════════════════════╝');
        $this->newLine();

        // Verificar si ya fueron ejecutadas
        $executed = $this->checkExecutedMigrations();

        if (count($executed) === count($this->migrations)) {
            $this->warn('⚠️  Todas las migraciones de v2.6.0 ya fueron ejecutadas.');

            if (!$this->option('force')) {
                if (!$this->confirm('¿Desea re-ejecutarlas de todas formas?', false)) {
                    $this->info('Operación cancelada.');
                    return 0;
                }
            }
        } elseif (count($executed) > 0) {
            $this->warn('⚠️  Algunas migraciones ya fueron ejecutadas:');
            foreach ($executed as $migration) {
                $this->line("   • $migration");
            }
            $this->newLine();
        }

        // Confirmación de seguridad
        if (!$this->option('force')) {
            $this->warn('🔴 IMPORTANTE: Esta operación modificará la estructura de la base de datos.');
            $this->warn('   Se recomienda hacer un backup antes de continuar.');
            $this->newLine();

            if (!$this->confirm('¿Desea continuar?', false)) {
                $this->info('Operación cancelada.');
                return 0;
            }
        }

        $this->newLine();
        $this->info('Ejecutando migraciones...');
        $this->newLine();

        $bar = $this->output->createProgressBar(count($this->migrations));
        $bar->start();

        $success = 0;
        $errors = [];

        foreach ($this->migrations as $migration) {
            try {
                $migrationName = str_replace('.php', '', $migration);

                // Ejecutar la migración específica
                Artisan::call('migrate', [
                    '--path' => 'database/migrations/' . $migration,
                    '--force' => true,
                ]);

                $success++;
                $bar->advance();

            } catch (\Exception $e) {
                $errors[] = [
                    'migration' => $migration,
                    'error' => $e->getMessage()
                ];
                $bar->advance();
            }
        }

        $bar->finish();
        $this->newLine(2);

        // Mostrar resultados
        if ($success === count($this->migrations)) {
            $this->info('✅ Todas las migraciones se ejecutaron exitosamente!');
            $this->newLine();
            $this->info('📊 Resumen:');
            $this->line("   • Migraciones ejecutadas: $success/" . count($this->migrations));
            $this->newLine();
            $this->info('🎉 Sistema actualizado a v2.6.0 correctamente!');
        } else {
            $this->warn("⚠️  Ejecutadas: $success/" . count($this->migrations));

            if (count($errors) > 0) {
                $this->error('❌ Errores encontrados:');
                foreach ($errors as $error) {
                    $this->error("   • {$error['migration']}");
                    $this->line("     {$error['error']}");
                }
            }
        }

        return $success === count($this->migrations) ? 0 : 1;
    }

    /**
     * Revertir las migraciones de v2.6.0
     */
    protected function rollbackMigrations()
    {
        $this->warn('╔════════════════════════════════════════════════════════════╗');
        $this->warn('║        ROLLBACK v2.6.0 - Revertir Migraciones             ║');
        $this->warn('╚════════════════════════════════════════════════════════════╝');
        $this->newLine();

        $executed = $this->checkExecutedMigrations();

        if (count($executed) === 0) {
            $this->info('✓ No hay migraciones de v2.6.0 para revertir.');
            return 0;
        }

        $this->warn('Se revertirán las siguientes migraciones:');
        foreach ($executed as $migration) {
            $this->line("   • $migration");
        }
        $this->newLine();

        if (!$this->option('force')) {
            if (!$this->confirm('⚠️  ¿Está seguro de revertir estas migraciones?', false)) {
                $this->info('Operación cancelada.');
                return 0;
            }
        }

        $this->newLine();
        $this->info('Revirtiendo migraciones...');

        try {
            // Revertir en orden inverso
            foreach (array_reverse($this->migrations) as $migration) {
                if (in_array($migration, $executed)) {
                    Artisan::call('migrate:rollback', [
                        '--path' => 'database/migrations/' . $migration,
                        '--force' => true,
                    ]);
                    $this->line("   ✓ Revertida: $migration");
                }
            }

            $this->newLine();
            $this->info('✅ Rollback completado exitosamente!');
            return 0;

        } catch (\Exception $e) {
            $this->error('❌ Error durante el rollback: ' . $e->getMessage());
            return 1;
        }
    }

    /**
     * Verificar qué migraciones ya fueron ejecutadas
     */
    protected function checkExecutedMigrations(): array
    {
        $executed = [];

        try {
            $migrations = DB::table('migrations')
                ->whereIn('migration', array_map(function($m) {
                    return str_replace('.php', '', $m);
                }, $this->migrations))
                ->pluck('migration')
                ->toArray();

            foreach ($this->migrations as $migration) {
                $migrationName = str_replace('.php', '', $migration);
                if (in_array($migrationName, $migrations)) {
                    $executed[] = $migration;
                }
            }
        } catch (\Exception $e) {
            // Tabla migrations no existe o hay otro error
        }

        return $executed;
    }
}
