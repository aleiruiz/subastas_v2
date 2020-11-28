<?php

namespace App\Repositories\User\Eloquent;

use App\Models\User\User;
use App\Repositories\BaseRepository;
use App\Repositories\User\Interfaces\UserInterface;

class UserRepository extends BaseRepository implements UserInterface
{
    /**
     * @var User
     */
    protected $model;

    public function __construct(User $model)
    {
        $this->model = $model;
    }


}
