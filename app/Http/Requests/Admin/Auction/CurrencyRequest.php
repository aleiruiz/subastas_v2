<?php

namespace App\Http\Requests\Admin\Auction;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CurrencyRequest extends FormRequest
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
                Rule::unique('currencies', 'name')->ignore($this->route()->parameter('id')),
            ],
            'symbol'=> [
                'required',
                'max:255',
                Rule::unique('currencies', 'symbol')->ignore($this->route()->parameter('id')),
            ],
            'logo'=>'image:jpeg,png,jpg|max:1024',
            'is_active' => 'required|in:' . array_to_string(active_status()),
            'payment_methods' => 'required|array|min:1',
            'payment_methods.*' => 'exists:payment_methods,id',
        ];
    }
}
