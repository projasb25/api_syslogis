<?php

namespace App\Console\Commands\Procesos\Integracion;

use App\Models\Services\Integration\MainService;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ProcesoRecoleccion extends Command
{
    protected $mainService;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'procesos:recoleccion';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Procesar carga a recoleccion de clientes de integracion';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(MainService $mainService)
    {
        $this->mainService = $mainService;
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        try {
            $this->line("PROCESAR CARGA INTEGRACION - RECOLECCION");
            $this->line("=============================================");
            $this->line('');

            $integracion = $this->mainService->procesar_recoleccion();
            if (!$integracion['success']) {
                throw new Exception($integracion['mensaje'], 500);
            }

            $this->info('IN RETAIL PROCESADO CON EXITO - RECOLECCION');
            $this->info('');

        } catch (Exception $exc) {
            $this->error($exc->getMessage());
        }
    }
}
