<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;
use function GuzzleHttp\Promise\all;

class KnowYourCustomerRequest extends FormRequest
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
        $type = identification_type_with_id();
        unset($type[IDENTIFICATION_TYPE_WITH_ID_PASSPORT]);

        return [
            'identification_type' => 'required|integer',
            'front_image' => 'required_with:address_id|mimes:jpeg,png,jpg|max:5120',
            'back_image' => 'mimes:jpeg,png,jpg|max:5120|required_if:identification_type,' .array_to_string($type),
        ];
    }
}
