<?php

namespace App\Console\Commands\Procesos;

use App\Models\Services\IntegracionService;
use App\Models\Services\Integration\MainService;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ProcesarRecoleccionInRetail extends Command
{
    protected $mainService;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'procesos:reco_inretail';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Procesar carga a recoleccion de cliente InRetail';

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
            $this->line("PROCESAR CARGA IN RETAIL - RECOLECCION");
            $this->line("=============================================");
            $this->line('');

            $integracion = $this->mainService->procesar();
            if (!$integracion['success']) {
                throw new Exception($integracion['error']['mensaje'], 500);
            }

            $this->info('IN RETAIL PROCESADO CON EXITO - RECOLECCION');
            $this->info('');

        } catch (Exception $exc) {
            $this->error($exc->getMessage());
        }
    }
}
