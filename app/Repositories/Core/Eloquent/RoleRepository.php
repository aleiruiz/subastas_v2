<?php
/**
 * Created by PhpStorm.
 * User: rabbi
 * Date: 7/24/18
 * Time: 4:34 PM
 */

namespace App\Repositories\Core\Eloquent;


use App\Models\Core\Role;
use App\Repositories\BaseRepository;
use App\Repositories\Core\Interfaces\RoleInterface;
use Illuminate\Database\Eloquent\Model;


class RoleRepository extends BaseRepository implements RoleInterface
{
    /**
     * @var Role
     */
    protected $model;

    public function __construct(Role $model)
    {
        $this->model = $model;
    }

    public function getUserRoles()
    {
        return $this->model->where('is_active', ACTIVE_STATUS_ACTIVE)->pluck('name', 'id');
    }

    public function getDefaultRole()
    {
        return $this->model->where('id', settings('default_role_to_register'))->firstOrFail();
    }

}
