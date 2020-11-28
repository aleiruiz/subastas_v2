<?php

namespace App\Models\User;

use App\Models\Admin\Category;
use App\Models\Admin\Currency;
use Illuminate\Database\Eloquent\Model;

class Auction extends Model
{
    protected $fillable = [
        'title',
        'ref_id',
        'seller_id',
        'address_id',
        'auction_type',
        'category_id',
        'currency_id',
        'starting_date',
        'ending_date',
        'delivery_date',
        'bid_initial_price',
        'bid_increment_dif',
        'product_description',
        'images',
        'is_shippable',
        'shipping_type',
        'shipping_description',
        'terms_description',
        'status',
        'product_claim_status',
        'is_multiple_bid_allowed',
    ];

    protected $fakeFields = [
        'title',
        'ref_id',
        'seller_id',
        'address_id',
        'auction_type',
        'category_id',
        'currency_id',
        'starting_date',
        'ending_date',
        'delivery_date',
        'bid_initial_price',
        'bid_increment_dif',
        'product_description',
        'images',
        'is_shippable',
        'shipping_type',
        'shipping_description',
        'terms_description',
        'status',
        'product_claim_status',
        'is_multiple_bid_allowed',
    ];

    public function seller()
    {
        return $this->belongsTo(Seller::class);
    }

    public function address()
    {
        return $this->hasOne(Address::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    public function bids()
    {
        return $this->hasMany(Bid::class);
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }

    public function setImagesAttribute($images)
    {
        $this->attributes['images'] = json_encode($images);
    }

    public function getImagesAttribute($images)
    {
        return json_decode($images ,true);
    }

}
