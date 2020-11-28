<?php

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Model;

class Slider extends Model
{
    protected $fillable = ['title', 'images'];

    protected $fakeFields = ['title', 'images'];

    public function setImagesAttribute($images)
    {
        $this->attributes['images'] = json_encode($images);
    }

    public function getImagesAttribute($images)
    {
        return json_decode($images ,true);
    }
}
