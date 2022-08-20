<?php

namespace App\Console\Commands\Integracion;

use App\Models\Services\IntegracionService;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class TaiLoy extends Command
{
    protected $integracionServi;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'integracion:tailoy';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Integracion TaiLoy';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(IntegracionService $integracionService)
    {
        $this->integracionServi = $integracionService;
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
            $this->line("INTEGRACION CON TAILOY");
            $this->line("=============================================");
            $this->line('');

            $integracion = $this->integracionServi->integracionTailoy();
            if (!$integracion['success']) {
                throw new Exception($integracion['mensaje'], 500);
            }

            $this->info('Integracion con TaiLoy');
            $this->info('');

        } catch (Exception $exc) {
            $this->error($exc->getMessage());
        }
    }
}
