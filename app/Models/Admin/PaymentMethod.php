<?php

namespace App\Models\Admin;

use App\Models\T60design\Requirement;
use Illuminate\Database\Eloquent\Model;

class PaymentMethod extends Model
{
    protected $fillable = [
        'name',
        'api_service',
        'is_active',
        'logo',
    ];
    protected $fakeFields = [
        'name',
        'api_service',
        'is_active',
        'logo',
    ];

    public function currencies()
    {
        return $this->belongsToMany(Currency::class);
    }
}
