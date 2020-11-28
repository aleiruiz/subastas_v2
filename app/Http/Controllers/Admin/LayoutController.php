<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\LayoutRequest;
use App\Repositories\Admin\Interfaces\LayoutInterface;
use App\Services\Core\DataListService;
use Illuminate\Http\Request;

class LayoutController extends Controller
{
    private $layout;

    public function __construct(LayoutInterface $layout)
    {
        $this->layout = $layout;
    }

    public function index()
    {
        $searchFields = [
            ['id', __('Layout ID')],
            ['title', __('Layout Title')],
        ];
        $orderFields = [
            ['title', __('Layout Title')],
            ['layout_type', __('Layout Type')],
        ];

        $query = $this->layout->paginateWithFilters($searchFields, $orderFields);
        $data['list'] = app(DataListService::class)->dataList($query, $searchFields, $orderFields);
        $data['title'] = 'All Layouts';

        return view('backend.home_page_management.layouts.index', $data);
    }

    public function create()
    {
        $data['title'] = __('Create Section');
        $data['types'] = $this->getFilteredTypes();

        return view('backend.home_page_management.layouts.create', $data);
    }

    public function store(LayoutRequest $request)
    {
        $parameters = $request->only('title', 'layout_type', 'total', 'is_active');

        $checkLayouts = $this->layout->getFirstByConditions(['layout_type' => $request['layout_type']]);
        if (!is_null($checkLayouts))
        {
            return redirect()->back()->with(SERVICE_RESPONSE_ERROR, __('This layout is already existed'));
        }

        if ($this->layout->create($parameters))
        {
            return redirect()->back()->with(SERVICE_RESPONSE_SUCCESS, __('New Layout has been created Successfully'));
        }

        return redirect()->back()->with(SERVICE_RESPONSE_ERROR, __('Failed to create new layout'));
    }

    public function makeLayoutActive($id)
    {
        $checkStatus = $this->layout->getFirstByConditions(['id' => $id]);

        if ($checkStatus->is_active == ACTIVE_STATUS_INACTIVE)
        {
            $parameters['is_active'] = ACTIVE_STATUS_ACTIVE;
            if ($this->layout->update($parameters, $id))
            {
                return redirect()->back()->with(SERVICE_RESPONSE_SUCCESS, __('Status has been changed successfully'));
            }

        }

        if ($checkStatus->is_active == ACTIVE_STATUS_ACTIVE){

            $parameters['is_active'] = ACTIVE_STATUS_INACTIVE;
            if ($this->layout->update($parameters, $id))
            {
                return redirect()->back()->with(SERVICE_RESPONSE_SUCCESS, __('Status has been changed successfully'));
            }
        }

        return redirect()->back()->with(SERVICE_RESPONSE_ERROR, __('Failed to change the status'));
    }

    public function edit($id)
    {
        $data['layout'] = $this->layout->findOrFailById($id);
        $data['title'] = __('Edit Layout');

        return view('backend.home_page_management.layouts.edit',$data);
    }

    public function update(LayoutRequest $request, $id)
    {

        $parameters = $request->only('title','layout_type', 'total', 'is_active');

        if ($this->layout->update($parameters, $id))
        {
            return redirect()->route('layout.index')->with(SERVICE_RESPONSE_SUCCESS, __('New Layout has been created Successfully'));
        }

        return redirect()->back()->with(SERVICE_RESPONSE_ERROR, __('Failed to create new layout'));
    }

    public function getFilteredTypes()
    {
        $types = layout_types();
        $existedTypes = $this->layout->getExistingTypes();

        $filteredTypes = array_diff( array_flip($types), $existedTypes);
        return array_flip($filteredTypes);
    }
}
