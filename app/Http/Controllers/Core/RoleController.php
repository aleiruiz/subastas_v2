<?php

namespace App\Http\Controllers\Core;

use App\Http\Controllers\Controller;
use App\Http\Requests\Core\RoleRequest;
use App\Repositories\Core\Interfaces\RoleInterface;
use App\Services\Core\DataListService;
use Exception;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;


class RoleController extends Controller
{
    public $roleManagement;

    public function __construct(RoleInterface $roleManagement)
    {
        $this->roleManagement = $roleManagement;
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        $searchFields = [
            ['name', __('Role Name')],
        ];
        $orderFields = [
            ['id', __('Serial')],
            ['name', __('Role Name')],
        ];
        $query = $this->roleManagement->paginateWithFilters($searchFields, $orderFields);
        $data['list'] = app(DataListService::class)->dataList($query, $searchFields, $orderFields);
        $data['title'] = __('Role Management');
        $data['defaultRoles'] = config('commonconfig.fixed_roles');
        if (!is_array($data['defaultRoles'])) {
            $data['defaultRoles'] = [];
        }

        return view('backend.roles.index', $data);
    }

    public function create()
    {
        $data['routes'] = config('routepermission.configurable_routes');
        $data['title'] = __('Create User Role');

        return view('backend.roles.create', $data);
    }

    public function store(RoleRequest $request)
    {
        $parameters = [
            'name' => $request->name,
            'permissions' => $request->roles
        ];

        if ($role = $this->roleManagement->create($parameters)) {
            Cache::forever("roles{$role->id}", $parameters['permissions']);
            return redirect()->route('roles.edit', $role->id)->with(SERVICE_RESPONSE_SUCCESS, __('User role has been created successfully.'));
        }

        return redirect()->back()->with(SERVICE_RESPONSE_ERROR, __('Failed to create user role.'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param $id
     * @return Response
     */
    public function edit($id)
    {
        $data['routes'] = config('routepermission.configurable_routes');
        $data['role'] = $this->roleManagement->findOrFailById($id);
        $constantPermission = config('routepermission.' . ROUTE_CONSTANT_PERMISSION );
        if(isset($constantPermission[$data['role']->id])) {
            $data['currentUserRole'] = $constantPermission[$data['role']->id];
        }
        else {
            $data['currentUserRole'] = [
                ROUTE_MUST_ACCESSIBLE => [],
                ROUTE_NOT_ACCESSIBLE => [],
            ];
        }
        $data['title'] = __('Edit User Role');

        return view('backend.roles.edit', $data);
    }

    /**
     * Update the specified resource in storage.
     * @param RoleRequest $request
     * @param $id
     * @return Response
     * @throws Exception
     */
    public function update(RoleRequest $request, $id)
    {
        $roles = $request->roles;
        if ($id == USER_ROLE_SUPER_ADMIN) {
            $roles[ROUTE_SECTION_USER_MANAGEMENTS][ROUTE_SUB_SECTION_ROLE_MANAGEMENTS] = [
                ROUTE_GROUP_READER_ACCESS,
                ROUTE_GROUP_CREATION_ACCESS,
                ROUTE_GROUP_MODIFIER_ACCESS,
                ROUTE_GROUP_DELETION_ACCESS
            ];
        }

        $parameters = [
            'name' => $request->name,
            'permissions' => $roles
        ];


        if ($this->roleManagement->update($parameters, $id)) {
            Cache::forget("roles{$id}");
            Cache::forever("roles{$id}", $roles);
            return redirect()->route('roles.edit', $id)->with(SERVICE_RESPONSE_SUCCESS, __('User role has been updated successfully.'));
        }

        return redirect()->back()->with(SERVICE_RESPONSE_ERROR, __('Failed to update user role.'));
    }

    public function destroy($id)
    {
        if ($this->isNonDeletableRole($id)) {
            return redirect()->back()->with(SERVICE_RESPONSE_ERROR, __('This role cannot be deleted.'));
        }

        $role = $this->roleManagement->getFirstById($id, 'users');

        $userCount = $role->users->count();

        $deleted = false;
        if ($userCount <= 0) {
            $deleted = $this->roleManagement->deleteById($id);
        }

        if ($deleted) {
            return redirect()->route('roles.index')->with(SERVICE_RESPONSE_SUCCESS, __('User role has been deleted successfully.'));
        }

        return redirect()->back()->with(SERVICE_RESPONSE_ERROR, __('This role cannot be deleted.'));
    }

    private function isNonDeletableRole(int $id)
    {
        $rolesFromAdminSetting = settings(['default_role_to_register', 'signupable_user_roles']);
        $defaultRoles = config('commonconfig.fixed_roles');
        if ($rolesFromAdminSetting['default_role_to_register'] == $id || in_array($id, $defaultRoles) || in_array($id, $rolesFromAdminSetting['signupable_user_roles'])) {
            return true;
        }
        return false;
    }

    public function changeStatus($id)
    {
        if ($this->isNonDeletableRole($id)) {
            return redirect()->back()->with(SERVICE_RESPONSE_ERROR, __('User role status can not be changed.'));
        }

        if ($updatedState = $this->roleManagement->toggleStatus($id)) {
            return redirect()->route('roles.index')->with(SERVICE_RESPONSE_SUCCESS, __('User role has been changed successfully.'));
        }

        return redirect()->back()->with(SERVICE_RESPONSE_ERROR, __('User role status can not be changed.'));
    }
}
