<?php

namespace App\Repositories\Admin\Eloquent;
use App\Models\Admin\Currency;
use App\Repositories\Admin\Interfaces\CurrencyInterface;
use App\Repositories\BaseRepository;

class CurrencyRepository extends BaseRepository implements CurrencyInterface
{
    /**
    * @var Currency
    */
     protected $model;

     public function __construct(Currency $currency)
     {
        $this->model = $currency;
     }
}