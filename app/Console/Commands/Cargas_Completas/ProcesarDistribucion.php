<?php

namespace App\Console\Commands\Cargas_Completas;

use App\Models\Services\Web\CompleteLoadService;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ProcesarDistribucion extends Command
{
    protected $service;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ccompletas:distribucion';

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
    public function __construct(CompleteLoadService $completeService)
    {
        $this->service = $completeService;
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

            $this->line("PROCESAR CARGA COMPLETA - DISTRIBUCION");
            $this->line("=============================================");
            $this->line('');

            $completa = $this->service->process_distribution();
            if (!$completa['success']) {
                throw new Exception($completa['mensaje'], 500);
            }

            $this->info('PROCESAR CARGA COMPLETA CON EXITO - DISTRIBUCION');
            $this->info('');

        } catch (Exception $exc) {
            $this->error($exc->getMessage());
        }
    }
}
