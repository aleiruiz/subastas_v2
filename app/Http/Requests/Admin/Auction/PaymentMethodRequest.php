<?php

namespace App\Http\Requests\Admin\Auction;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PaymentMethodRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name'=> [
                'required',
                'max:255',
                Rule::unique('payment_methods', 'name')->ignore($this->route()->parameter('id')),
            ],
            'is_active' => 'required|in:' . array_to_string(active_status()),
            'logo'=>'image:jpeg,png,jpg|max:1024',
            'api_service' => 'required|integer',
        ];
    }
}
