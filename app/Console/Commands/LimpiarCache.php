<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class LimpiarCache extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cache:limpiar';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Limpia todas las cachés y regenera las optimizaciones del sistema';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🧹 Limpiando cachés del sistema...');

        // Limpiar todas las cachés
        $this->call('optimize:clear');
        $this->info('✅ Cachés limpiadas');

        $this->info('⚡ Regenerando cachés optimizadas...');

        // Regenerar cachés
        $this->call('config:cache');
        $this->info('✅ Config cache regenerada');

        $this->call('route:cache');
        $this->info('✅ Route cache regenerada');

        $this->call('view:cache');
        $this->info('✅ View cache regenerada');

        $this->newLine();
        $this->info('🚀 Sistema optimizado correctamente');

        return 0;
    }
}
