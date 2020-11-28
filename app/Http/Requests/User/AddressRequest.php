<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AddressRequest extends FormRequest
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
            'name' => 'required|max:55',
            'address' => 'required|string|max:255',
            'phone_number'=> [
                'required',
                'max:25',
                Rule::unique('addresses', 'phone_number')->ignore($this->route()->parameter('id')),
            ],
            'post_code' => 'required|string|max:25',
            'city' => 'required|string:max:55',
            'country_id' => 'required|integer|exists:countries,id',
            'state_id' => 'required_with:country_id|integer|exists:states,id,country_id,' . $this->country_id,
        ];
    }
}
