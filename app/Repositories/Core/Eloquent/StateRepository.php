<?php

namespace App\Repositories\Core\Eloquent;
use App\Models\Core\State;
use App\Repositories\BaseRepository;
use App\Repositories\Core\Interfaces\StateInterface;

class StateRepository extends BaseRepository implements StateInterface
{
    /**
    * @var State
    */
     protected $model;

     public function __construct(State $state)
     {
        $this->model = $state;
     }
}