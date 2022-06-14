<?php

namespace App\Console\Commands\Guias;

use Illuminate\Console\Command;
use App\Models\Services\Integration\MainService;
use Exception;

class RestaurarRecoleccionInretail extends Command
{
    protected $mainService;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'guias:restaurar_recoleccion_inretail {list : Lista de numero de guias}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Restaurar guias de recoleccion por una lista de numero de guias';

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
        $list = $this->argument('list');
        $list_arr = explode(',', $list);

        $this->line("PROCESAR RESTAURACION - RECOLECCION");
        $this->info('');
        
        $integracion = $this->mainService->restaurar_recoleccion_inretail($list_arr);

        if (!$integracion['success']) {
            throw new Exception($integracion['mensaje'], 500);
        }

        $this->info('PROCESAR RESTAURACION - EXITOSO');
        $this->info('');
    }
}
