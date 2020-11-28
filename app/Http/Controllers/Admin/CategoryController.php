<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Auction\CategoryRequest;
use App\Repositories\Admin\Interfaces\CategoryInterface;
use App\Services\Core\DataListService;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    public $category;

    public function __construct(CategoryInterface $category)
    {
        $this->category = $category;
    }

    public function index()
    {
        $searchFields = [
            ['name', __('name')],
        ];
        $orderFields = [
            ['name', __('name')],
        ];

        $categories = $this->category->paginateWithFilters($searchFields, $orderFields);
        $data['list'] = app(DataListService::class)->dataList($categories, $searchFields, $orderFields);

        $data['title'] = 'All Categories';
        return view('backend.auction.category.index', $data);
    }


    public function create()
    {
        $data['title'] = __('Create Category');
        return view('backend.auction.category.create', $data);
    }


    public function store(CategoryRequest $request)
    {
        $parameters = $request->only('name');
        $parameters['slug'] = Str::slug($parameters['name']);

        if ($this->category->create($parameters)) {
            return redirect()->route('category.index')->with(SERVICE_RESPONSE_SUCCESS, __('Category has been created successfully'));
        }

        return redirect()->back()->withInput()->with(SERVICE_RESPONSE_ERROR, __('Failed to create Category'));
    }


    public function edit($id)
    {
        $data['category'] = $this->category->findOrFailById($id);
        $data['title'] = __('Update Category');

        return view('backend.auction.category.edit', $data);
    }


    public function update(CategoryRequest $request, $id)
    {
        $parameters = $request->only('name');
        $parameters['slug'] = Str::slug($parameters['name']);

        if ($this->category->update($parameters, $id)) {
            return redirect()->route('category.index')->with(SERVICE_RESPONSE_SUCCESS, __('Category has been updated successfully'));
        }

        return redirect()->back()->withInput()->with(SERVICE_RESPONSE_ERROR, __('Failed to update category'));
    }


    public function destroy($id)
    {
        if ($this->category->deleteById($id)) {
            return redirect()->back()->with(SERVICE_RESPONSE_SUCCESS, __('Category has been deleted successfully.'));
        }

        return redirect()->back()->with(SERVICE_RESPONSE_ERROR, __('This Category can not be deleted.'));
    }
}
