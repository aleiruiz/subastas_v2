<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

class ShippingDescriptionRequest extends FormRequest
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
        if ($this->request->has('address_id') == 0)
        {
            return [
                'name' => 'required|max:55',
                'address' => 'required|string|max:255',
                'phone_number'=> 'required|max:25',
                'post_code' => 'required|string|max:25',
                'city' => 'required|string:max:55',
                'country_id' => 'required|integer|exists:countries,id',
                'state_id' => 'required_with:country_id|integer|exists:states,id,country_id,' . $this->country_id,
            ];
        }

        return [];

    }
}
