<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Auction\PaymentMethodRequest;
use App\Repositories\Admin\Interfaces\PaymentMethodInterface;
use App\Services\Core\DataListService;
use App\Services\Core\FileUploadService;

class PaymentMethodController extends Controller
{
    public $paymentMethod;

    public function __construct(PaymentMethodInterface $paymentMethod)
    {
        $this->paymentMethod = $paymentMethod;
    }

    public function index()
    {

        $searchFields = [
            ['name', __('name')],
        ];
        $orderFields = [
            ['name', __('name')],
        ];

        $paymentMethods = $this->paymentMethod->paginateWithFilters($searchFields, $orderFields);
        $data['list'] = app(DataListService::class)->dataList($paymentMethods, $searchFields, $orderFields);

        $data['title'] = 'All Payment Methods';
        return view('backend.auction.payment_method.index', $data);
    }


    public function create()
    {
        $data['title'] = __('Create Payment Method');
        return view('backend.auction.payment_method.create', $data);
    }


    public function store(PaymentMethodRequest $request)
    {
        $parameters = $request->only('name', 'api_service', 'is_active');

        if ($paymentMethod = $this->paymentMethod->create($parameters)) {
            if ($request->hasFile('logo')) {
                $logoImage = app(FileUploadService::class)->upload($request->file('logo'), config('commonconfig.payment_method_logo'), 'logo', 'paymentMethod', $paymentMethod->id, 'public', 100, 100);

                if (!empty($logoImage)) {
                    $this->paymentMethod->update(['logo' => $logoImage], $paymentMethod->id);
                }
            }

            return redirect()->route('payment-method.index')->with(SERVICE_RESPONSE_SUCCESS, __('Payment method has been created successfully'));
        }

        return redirect()->back()->withInput()->with(SERVICE_RESPONSE_ERROR, __('Failed to create Payment method'));
    }

    public function edit($id)
    {
        $data['paymentMethod'] = $this->paymentMethod->findOrFailById($id);
        $data['title'] = __('Update Payment Method');

        return view('backend.auction.payment_method.edit', $data);
    }


    public function update(PaymentMethodRequest $request, $id)
    {

        $parameters = $request->only('name', 'api_service', 'is_active');

        if ($request->hasFile('logo')) {
            $logoImage = app(FileUploadService::class)->upload($request->file('logo'), config('commonconfig.payment_method_logo'), 'logo', 'paymentMethod', $id, 'public', 100, 100);

            $parameters['logo'] = $logoImage;
        }

        if ($paymentMethod = $this->paymentMethod->update($parameters, $id)) {
            return redirect()->route('payment-method.index')->with(SERVICE_RESPONSE_SUCCESS, __('Payment method has been updated successfully'));

        }

        return redirect()->back()->withInput()->with(SERVICE_RESPONSE_ERROR, __('Failed to update Payment method'));
    }


    public function destroy($id)
    {
        if ($this->paymentMethod->deleteById($id)) {
            return redirect()->back()->with(SERVICE_RESPONSE_SUCCESS, __('Category has been deleted successfully.'));
        }

        return redirect()->back()->with(SERVICE_RESPONSE_ERROR, __('This Category can not be deleted.'));
    }
}
