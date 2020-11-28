<?php

namespace App\Repositories\User\Eloquent;


use App\Models\User\UserProfile;
use App\Repositories\BaseRepository;
use App\Repositories\User\Interfaces\ProfileInterface;

class ProfileRepository extends BaseRepository implements ProfileInterface
{
    /**
     * @var UserProfile
     */
    protected $model;

    public function __construct(UserProfile $model)
    {

        $this->model = $model;
    }

}
