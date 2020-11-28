<?php

namespace App\Repositories\User\Eloquent;
use App\Models\User\Seller;
use App\Repositories\BaseRepository;
use App\Repositories\User\Interfaces\SellerInterface;

class SellerRepository extends BaseRepository implements SellerInterface
{
    /**
    * @var Seller
    */
     protected $model;

     public function __construct(Seller $seller)
     {
        $this->model = $seller;
     }
}
