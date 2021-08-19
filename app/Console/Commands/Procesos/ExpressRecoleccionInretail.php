<?php

namespace App\Console\Commands\Procesos;

use App\Models\Services\IntegracionService;
use App\Models\Services\Integration\MainService;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ExpressRecoleccionInretail extends Command
{
    protected $mainService;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'procesos:express_reco_inretail';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Procesar carga a recoleccion de cliente InRetail EXPRESS';

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

            $integracion = $this->mainService->recoleccion_express();
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
