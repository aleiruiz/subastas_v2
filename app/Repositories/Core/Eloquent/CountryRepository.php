<?php

namespace App\Repositories\Core\Eloquent;
use App\Models\Core\Country;
use App\Repositories\BaseRepository;
use App\Repositories\Core\Interfaces\CountryInterface;

class CountryRepository extends BaseRepository implements CountryInterface
{
    /**
    * @var Country
    */
     protected $model;

     public function __construct(Country $country)
     {
        $this->model = $country;
     }
}