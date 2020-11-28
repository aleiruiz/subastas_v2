<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

class DisputeRequest extends FormRequest
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
        $disputes=dispute_type();
        array_pop($disputes);

        return [
            'title' => 'required|min:3|max:150',
            'dispute_type' => 'required|in:'.array_to_string(dispute_type()),
            'ref_id' => 'required_if:dispute_type,'.array_to_string($disputes),
            'description' => 'required|min:10|max:500',
            'images' => 'array',

        ];
    }
}
