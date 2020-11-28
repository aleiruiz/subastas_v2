<?php

namespace App\Http\Controllers\User;


use App\Http\Controllers\Controller;

class DashboardController extends Controller
{
    public function __invoke()
    {
        $data['title'] = __('Dashboard');

        return view('backend.dashboard.superadmin', $data);
    }
}
