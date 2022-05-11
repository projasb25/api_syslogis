<?php

namespace App\Console\Commands\Procesos\Integracion;

use App\Models\Services\Integration\MainService;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ProcesoRecoleccionTipo extends Command
{
    protected $mainService;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'procesos:recoleccion {type : Tipo de envio}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Lista tipos de servicio 1.- NextDay, 2.- SameDay';

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
            $type = $this->argument('type');
            $params = [];

            switch ($type) {
                case 1: # NEXTDAY
                    $params['type'] = 'NEXTDAY';
                    break;
                case 2: # SAMEDAY
                    $params['type'] = 'SAMEDAY';
                    break;
            }

            $this->line("PROCESAR CARGA INTEGRACION - RECOLECCION");
            $this->line("=============================================");
            $this->line('');

            $integracion = $this->mainService->procesar_recoleccion($params);
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
