<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Auction\CurrencyRequest;
use App\Repositories\Admin\Interfaces\CurrencyInterface;
use App\Repositories\Admin\Interfaces\PaymentMethodInterface;
use App\Services\Core\DataListService;
use App\Services\Core\FileUploadService;
use Exception;
use Illuminate\Support\Facades\DB;

class CurrencyController extends Controller
{

    public $currency;

    public function __construct(CurrencyInterface $currency)
    {
        $this->currency = $currency;
    }

    public function index()
    {

        $searchFields = [
            ['name', __('name')],
        ];
        $orderFields = [
            ['name', __('name')],
        ];

        $currencies = $this->currency->paginateWithFilters($searchFields, $orderFields);
        $data['list'] = app(DataListService::class)->dataList($currencies, $searchFields, $orderFields);

        $data['title'] = 'All Currency';
        return view('backend.auction.currency.index', $data);
    }


    public function create()
    {
        $data['title'] = __('Create Currency');
        $data['paymentMethods'] = app(PaymentMethodInterface::class)->getByConditions(['is_active' => ACTIVE_STATUS_ACTIVE]);

        return view('backend.auction.currency.create', $data);
    }


    public function store(CurrencyRequest $request)
    {
        $parameters = $request->only('name', 'symbol', 'is_active');

        $new_name = 0;
        if ($request->hasfile('logo')) {
            $uploadedImage = app(FileUploadService::class)->upload($request->file('logo'), config('commonconfig.currency_logo'), 'logo', '', $new_name++, 'public', 100, 100);

            if (!empty($uploadedImage)) {
                $parameters['logo'] = $uploadedImage;
            }
        }

        if ($this->currency->create($parameters))
        {
            return redirect()->route('currency.index')->with(SERVICE_RESPONSE_SUCCESS, __('Currency has been created Successfully'));
        }

        return redirect()->back()->withInput()->with(SERVICE_RESPONSE_ERROR, __('Failed to create Currency'));
    }

    public function edit($id)
    {

        $data['currency'] = $this->currency->findOrFailById($id);
        $data['currencyArray'] = $data['currency']->paymentMethods->pluck('id', 'name')->toArray();
        $data['paymentMethods'] = app(PaymentMethodInterface::class)->getByConditions(['is_active' => ACTIVE_STATUS_ACTIVE]);
        $data['title'] = __('Update Currency');

        return view('backend.auction.currency.edit', $data);
    }


    public function update(CurrencyRequest $request, $id)
    {
        try {
            $parameters = $request->only('name', 'symbol', 'is_active');

            if ($request->hasFile('logo')) {
                $logoImage = app(FileUploadService::class)->upload($request->file('logo'), config('commonconfig.currency_logo'), 'logo', 'currency', $id, 'public', 100, 100);

                $parameters['logo'] = $logoImage;
            }

            $currency = $this->currency->update($parameters, $id);
            $currency->paymentMethods()->sync($request->payment_methods);

            return redirect()->route('currency.index')->with(SERVICE_RESPONSE_SUCCESS, __('Currency has been created Successfully'));

        } catch (Exception $exception) {

        }

        return redirect()->back()->withInput()->with(SERVICE_RESPONSE_ERROR, __('Failed to update Currency'));
    }

}
