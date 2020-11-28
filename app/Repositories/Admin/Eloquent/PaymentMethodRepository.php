<?php

namespace App\Repositories\Admin\Eloquent;
use App\Models\Admin\PaymentMethod;
use App\Repositories\Admin\Interfaces\PaymentMethodInterface;
use App\Repositories\BaseRepository;

class PaymentMethodRepository extends BaseRepository implements PaymentMethodInterface
{
    /**
    * @var PaymentMethod
    */
     protected $model;

     public function __construct(PaymentMethod $paymentMethod)
     {
        $this->model = $paymentMethod;
     }
}