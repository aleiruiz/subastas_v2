<?php

namespace App\Repositories\User\Eloquent;
use App\Models\User\Wallet;
use App\Repositories\BaseRepository;
use App\Repositories\User\Interfaces\WalletInterface;

class WalletRepository extends BaseRepository implements WalletInterface
{
    /**
    * @var Wallet
    */
     protected $model;

     public function __construct(Wallet $wallet)
     {
        $this->model = $wallet;
     }
}