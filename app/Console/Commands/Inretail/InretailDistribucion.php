<?php

namespace App\Console\Commands\Inretail;

use App\Models\Services\Integration\MainService;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class InretailDistribucion extends Command
{
    protected $mainService;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'inretail:ditribucion {type : Tipo de servicio}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Lista tipos de servicio 1.- Provincia, 2.- Logistica inversa, 3.- Logistica inversa Provincia, 4.- InRetail';

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
                case 1: # Provincia
                    $params['type'] = 'Provincia';
                    $params['organization'] = 65;
                    break;
                case 2: # Logistica Inversa
                    $params['type'] = 'Logistica Inversa';
                    $params['organization'] = 100;
                    break;
                case 3: # Logistica Inversa Provincia
                    $params['type'] = 'Logistica Inversa Provincia';
                    $params['organization'] = 122;
                    break;
                case 4: # Default InRetail
                    $params['type'] = 'Default InRetail';
                    $params['organization'] = 53;
                    break;
            }

            $this->line("PROCESAR CARGA INTEGRACION INRETAIL - RECOLECCION");
            $this->line("=============================================");
            $this->line('');

            $integracion = $this->mainService->inretailDistribucion($params);
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
