<?php

namespace App\Models\User;

use Illuminate\Database\Eloquent\Model;

class Dispute extends Model
{
    protected $fillable = [
        'title',
        'user_id',
        'dispute_type',
        'dispute_status',
        'description',
        'images',
        'read_at',
        'ref_id',
    ];

    protected $fakeFields = [
        'title',
        'user_id',
        'dispute_type',
        'dispute_status',
        'description',
        'images',
        'read_at',
        'ref_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
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
