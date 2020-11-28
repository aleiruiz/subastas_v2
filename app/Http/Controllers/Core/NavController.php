<?php

namespace App\Http\Controllers\Core;

use App\Http\Requests\Core\NavigationRequest;
use App\Services\Core\NavService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class NavController extends Controller
{
    public function index($slug = 'back-end')
    {
        $data = app(NavService::class)->backendMenuBuilder($slug);
        $data['title'] = __('Navigation');
        $data['slug'] = $slug;

        return view('backend.navigation.index', $data);
    }

    public function save(NavigationRequest $request, $slug)
    {
        $response = app(NavService::class)->backendMenuSave($request, $slug);
        $status = $response[SERVICE_RESPONSE_STATUS] ? SERVICE_RESPONSE_SUCCESS : SERVICE_RESPONSE_ERROR;

        return redirect()->back()->with($status, $response[SERVICE_RESPONSE_MESSAGE]);
    }
}
