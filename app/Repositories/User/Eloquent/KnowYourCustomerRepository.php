<?php

namespace App\Repositories\User\Eloquent;
use App\Models\User\KnowYourCustomer;
use App\Repositories\BaseRepository;
use App\Repositories\User\Interfaces\KnowYourCustomerInterface;

class KnowYourCustomerRepository extends BaseRepository implements KnowYourCustomerInterface
{
    /**
    * @var KnowYourCustomer
    */
     protected $model;

     public function __construct(KnowYourCustomer $knowYourCustomer)
     {
        $this->model = $knowYourCustomer;
     }
}