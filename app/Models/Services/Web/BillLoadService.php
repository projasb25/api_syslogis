<?php

namespace App\Models\Services\Web;

use App\Models\Repositories\Web\BillLoadRepository;
use App\Helpers\ResponseHelper as Res;

class BillLoadService
{
    protected $repo;
    public function __construct(BillLoadRepository $repository)
    {
        $this->repo = $repository;
    }
}
