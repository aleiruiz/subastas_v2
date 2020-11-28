<?php

namespace App\Models\User;

use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    protected $fillable = [
        'user_id',
        'auction_id',
        'content',
        'comment_id',
    ];

    protected $fakeFields = [
        'user_id',
        'auction_id',
        'content',
        'comment_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function auction()
    {
        return $this->belongsTo(Auction::class);
    }
}
