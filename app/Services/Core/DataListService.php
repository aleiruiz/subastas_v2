<?php

namespace App\Services\Core;
use Illuminate\Support\HtmlString;

class DataListService
{
    public function dataList($query, $searchFields, $orderFields=null, $dataFilter=null, $isDateFilterable=true, $itemName='data', $is_frontend = false)
    {
        $route = url()->current();
        $paginationKey = $query->getPageName();
        if (in_array('api', \Request::route()->middleware())) {
            return ['items'=>$query->toArray(), 'searchFields'=>$searchFields, 'orderFields'=>$orderFields, 'path'=>$route];
        }

        $view = $is_frontend ? config('commonconfig.front_end_view') : config('commonconfig.back_end_view');
        $itemPerPage = $query->perPage();
        $itemName = !empty($itemName) ? $itemName : __('Table Data');

        // prepare query
        $data['items'] = $query;

        // prepare filters
        $data['search'] = new HtmlString(view('backend.renderable_template.search', compact('paginationKey', 'orderFields', 'route', 'searchFields','isDateFilterable','dataFilter'))->render());
        if(!empty($dataFilter)){
            $data['filters'] = new HtmlString(view('backend.renderable_template.filters', compact( 'paginationKey', 'route','dataFilter'))->render());
        }
        // prepare pagination
        $data['pagination'] = new HtmlString(view('backend.renderable_template.pagination', compact('itemPerPage', 'query', 'itemName', 'paginationKey', 'route'))->render());

        return $data;
    }
}
