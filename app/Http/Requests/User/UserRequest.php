<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class UserRequest extends FormRequest
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
        $rules = [
            "first_name" => "required|alpha_space|between:2,255",
            "last_name" => "required|alpha_space|between:2,255",
            "address" => "max:500",
        ];

        if ($this->isMethod('POST')) {
            $rules["role_id"] = "required|exists:roles,id";
            $rules["email"] = "required|email|unique:users,email|between:5,255";
            $rules["username"] = "required|unique:users,username|max:255";
            $rules["is_email_verified"] = "required|in:" . array_to_string(email_status());
            $rules["is_financial_active"] = "required|in:" . array_to_string(financial_status());
            $rules["is_active"] = "required|in:" . array_to_string(account_status());
            $rules["is_accessible_under_maintenance"] = "required|in:" . array_to_string(maintenance_accessible_status());
        } else {
            if (
                $this->request->has('role_id') && !in_array($this->route('id'), config('commonconfig.fixed_users')) && $this->route('id') != Auth::user()->id
            ) {
                $rules['role_id'] = "required|exists:roles,id";
            }
        }
        return $rules;
    }

    public function attributes()
    {
        return [
            'role_id' => __('user role'),
            'is_email_verified' => __('email status'),
            'is_active' => __('account status'),
            'is_financial_active' => __('financial status'),
            'is_accessible_under_maintenance' => __('maintenace access status'),
        ];
    }
}
