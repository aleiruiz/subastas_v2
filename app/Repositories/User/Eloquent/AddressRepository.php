<?php

namespace App\Repositories\User\Eloquent;
use App\Models\User\Address;
use App\Repositories\BaseRepository;
use App\Repositories\User\Interfaces\AddressInterface;

class AddressRepository extends BaseRepository implements AddressInterface
{
    /**
    * @var Address
    */
     protected $model;

     public function __construct(Address $address)
     {
        $this->model = $address;
     }

     public function sellerDefaultAddress()
     {
         $ownerableTypeSeller = get_class(auth()->user()->seller);
         return $this->model->where(['is_default' => ACTIVE_STATUS_ACTIVE,'ownerable_type' => $ownerableTypeSeller, 'ownerable_id' => auth()->user()->seller->id])->first();
     }
}
