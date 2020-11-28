<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

class WithdrawalRequest extends FormRequest
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
            'payment_method' => 'required|in:' . array_to_string(payment_methods()),
            'amount' => 'required|numeric|min:' . settings('min_withdrawal_amount') . ',max:' . auth()->user()->wallet,
            'address' => 'required|email|max:255'
        ];
    }
}
