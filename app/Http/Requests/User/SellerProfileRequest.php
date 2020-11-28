<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SellerProfileRequest extends FormRequest
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
            'name' => 'required|max:255',
            'description' => 'string|max:255',
            'image' => 'image:jpeg,png,jpg|max:5120',
            'phone_number'=> [
                'required',
                'max:25',
                Rule::unique('sellers', 'phone_number')->ignore($this->route()->parameter('id')),
            ],
            'email' => 'required|email|string|between:5,255',
        ];
    }
}
