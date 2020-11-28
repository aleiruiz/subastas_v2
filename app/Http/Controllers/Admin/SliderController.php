<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SliderRequest;
use App\Repositories\Admin\Interfaces\SliderInterface;
use App\Services\Core\DataListService;
use App\Services\Core\FileUploadService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SliderController extends Controller
{
    private $slider;

    public function __construct(SliderInterface $slider)
    {
        $this->slider = $slider;
    }

    public function index()
    {
        $searchFields = [
            ['id', __('Slider ID')],
            ['title', __('Slider Title')],
        ];
        $orderFields = [
            ['title', __('Slider Title')],
        ];

        $query = $this->slider->paginateWithFilters($searchFields, $orderFields);
        $data['list'] = app(DataListService::class)->dataList($query, $searchFields, $orderFields);
        $data['title'] = 'All Sliders';

        return view('backend.home_page_management.slider.index', $data);
    }

    public function create()
    {
        $data['title'] = __('Create Slider');
        return view('backend.home_page_management.slider.create', $data);
    }

    public function store(SliderRequest $request)
    {
        $parameters = $this->uploadImages($request);
        $slider = $this->slider->create($parameters);

        if ($slider) {

            return redirect()->route('slider.show', $slider->id)->with(SERVICE_RESPONSE_SUCCESS, __('Slider has been created successfully'));
        }

        return redirect()->back()->withInput()->with(SERVICE_RESPONSE_ERROR, __('Failed to create Slider'));
    }

    public function show($id)
    {
        $data['slider'] = $this->slider->findOrFailById($id);
        $data['title'] = __('Slider Details');

        return view('backend.home_page_management.slider.show', $data);
    }

    public function edit($id)
    {
        $data['slider'] = $this->slider->findOrFailById($id);
        $data['title'] = __('Edit Slider');

        return view('backend.home_page_management.slider.edit',$data);
    }

    public function update(SliderRequest $request, $id)
    {

        $parameters = $this->uploadImages($request);
        $slider = $this->slider->update($parameters, $id);

        if ($slider) {

            return redirect()->route('slider.show', $slider->id)->with(SERVICE_RESPONSE_SUCCESS, __('Slider has been created successfully'));
        }

        return redirect()->back()->withInput()->with(SERVICE_RESPONSE_ERROR, __('Failed to create Slider'));
    }

    public function destroy($id)
    {
        if ($this->slider->deleteById($id))
        {
            return redirect()->back()->with(SERVICE_RESPONSE_SUCCESS, __('Slider has been deleted'));
        }

        return redirect()->back()->with(SERVICE_RESPONSE_ERROR,__('Unable to delete'));
    }

    public function makeSliderDefault($id)
    {
        $checkStatus = $this->slider->getFirstByConditions(['id' => $id]);
        $parameters['is_default'] = ACTIVE_STATUS_ACTIVE;

        if ($checkStatus->is_default == ACTIVE_STATUS_ACTIVE)
        {
            return redirect()->back()->with(SERVICE_RESPONSE_ERROR, __('Unable to make default'));
        }

        $checkExistedDefaultSlider = $this->slider->getFirstByConditions(['is_default' => ACTIVE_STATUS_ACTIVE]);

        $existedActiveId = !is_null($checkExistedDefaultSlider) ? $checkExistedDefaultSlider->id : null;

        $statusAttributes = [
            [
                'conditions' => ['id' => $id, 'is_default' => ACTIVE_STATUS_INACTIVE ],
                'fields' => [
                    'is_default' => ACTIVE_STATUS_ACTIVE,
                ]
            ],
            [
                'conditions' => ['id' => $existedActiveId, 'is_default' => ACTIVE_STATUS_ACTIVE ],
                'fields' => [
                    'is_default' => ACTIVE_STATUS_INACTIVE,
                ]
            ],
        ];

        if ($this->slider->bulkUpdate($statusAttributes))
        {
            return redirect()->back()->with(SERVICE_RESPONSE_SUCCESS, __('Status has been changed successfully'));
        }

        return redirect()->back()->with(SERVICE_RESPONSE_ERROR, __('Failed to change the status'));

    }

    public function uploadImages($request)
    {
        $parameters = $request->only('title');
        $parameters['is_default'] = ACTIVE_STATUS_ACTIVE;

        $new_name = 0;
        $randomId = Str::uuid();
        if ($request->hasfile('images')) {
            $uploadedImage = [];
            foreach ($request->images as $files) {
                $uploadedImage[] = app(FileUploadService::class)->upload($files, config('commonconfig.slider_images'), 'slider', $randomId, $new_name++, 'public', 1000, 400, false, $files->getClientOriginalExtension());
            }

            if (!empty($uploadedImage)) {
                $parameters['images'] = $uploadedImage;
            }
        }

        return $parameters;
    }
}
