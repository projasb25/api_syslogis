<?php

namespace App\Services\Web;

use App\Repositories\Web\PurchaseOrderRepository;

class PurchaseOrderService
{
    protected $repo;
    public function __construct(PurchaseOrderRepository $repository) {
        $this->repo = $repository;
    }

    public function index($request)
    {
        # code...
    }
}
