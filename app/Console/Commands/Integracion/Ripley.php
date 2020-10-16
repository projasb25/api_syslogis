<?php

namespace App\Console\Commands\Integracion;

use App\Models\Services\IntegracionService;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class Ripley extends Command
{
    protected $integracionServi;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'integracion:ripley';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Integracion Ripley. 1.- Ripley CD, 2.- Ripley Chorrillos, 3.- Ripley Begonias';

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
            $this->line("INTEGRACION CON RIPLEY");
            $this->line("=============================================");
            $this->line('');

            $integracion = $this->integracionServi->integracionRipley();
            if (!$integracion['success']) {
                throw new Exception($integracion['mensaje'], 500);
            }

            $this->info('Integracion con ripley');
            $this->info('');

        } catch (Exception $exc) {
            $this->error($exc->getMessage());
        }
    }
}