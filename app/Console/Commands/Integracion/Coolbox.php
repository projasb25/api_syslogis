<?php

namespace App\Console\Commands\Integracion;

use Exception;
use Illuminate\Console\Command;
use App\Models\Services\IntegracionService;

class Coolbox extends Command
{
    protected $integracionServi;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'integracion:coolbox';

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
            $this->line("INTEGRACION CON COOLBOX");
            $this->line("=============================================");
            $this->line('');

            $integracion = $this->integracionServi->integracionCoolbox();
            if (!$integracion['success']) {
                throw new Exception($integracion['mensaje'], 500);
            }

            $this->info('Integracion con coolbox');
            $this->info('');

        } catch (Exception $exc) {
            $this->error($exc->getMessage());
        }
    }
}
