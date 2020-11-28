<?php

namespace App\Http\Controllers\Core;

use App\Http\Controllers\Controller;
use App\Services\Core\ApplicationSettingService;
use Illuminate\Http\Request;

class ApplicationSettingController extends Controller
{
    public $applicationSettingService;

    public function __construct(ApplicationSettingService $adminSettingService)
    {
        $this->applicationSettingService = $adminSettingService;
    }

    public function index($type = 'general')
    {
        $data['settings'] = $this->applicationSettingService->loadForm($type, true);
        $data['type'] = $type;
        $data['title'] = __(':type Settings', ['type' => ucfirst($type)]);

        return view('backend.applicationSettings.index', $data);
    }

    public function edit($type)
    {
        $data['settings'] = $this->applicationSettingService->loadForm($type);
        $data['type'] = $type;
        $data['title'] = __('Edit - :type Settings', ['type' => ucfirst($type)]);

        return view('backend.applicationSettings.edit', $data);
    }

    public function update(Request $request, $type)
    {
        $response = $this->applicationSettingService->update($request, $type);
        $status = $response[SERVICE_RESPONSE_STATUS] ? SERVICE_RESPONSE_SUCCESS : SERVICE_RESPONSE_ERROR;


        return redirect()->route('application-settings.edit',$type)->withInput($response['inputs'])->withErrors($response['errors'])->with($status, $response[SERVICE_RESPONSE_MESSAGE]);
    }
}
