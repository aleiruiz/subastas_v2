<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class LayoutRequest extends FormRequest
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
            'title' => 'required|string|min:3|max:255',
            'layout_type'=> [
                'required',
                'in:'.array_to_string(layout_types()),
                Rule::unique('layouts')->ignore($this->route()->parameter('id')),
            ],
            'total' => 'required|integer',
            'is_active' => 'required|integer',
        ];
    }
}
