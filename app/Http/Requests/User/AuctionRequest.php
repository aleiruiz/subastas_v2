<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

class AuctionRequest extends FormRequest
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
            'auction_type' => 'required|integer',
            'category_id' => 'required|integer',
            'starting_date' => 'required|date',
            'ending_date' => 'required|date',
            'bid_initial_price' => 'required|integer',
            'bid_increment_dif' => 'required|integer',
            'product_description' => 'required|min:3|max:5000',
            'images' => 'required|array',
            'is_shippable' => 'required|integer',
            'shipping_type' => 'required|integer',
            'is_multiple_bid_allowed' => 'required|integer',
        ];
    }

    public function attributes()
    {
        return [
            'title' => __('Title'),
            'category_id' => __('Category'),
            'bid_initial_price' => __('Initial Price'),
            'bid_increment_dif' => __('Bid Increment Difference'),
        ];
    }
}
