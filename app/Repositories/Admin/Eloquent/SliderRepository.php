<?php

namespace App\Repositories\Admin\Eloquent;
use App\Models\Admin\Slider;
use App\Repositories\Admin\Interfaces\SliderInterface;
use App\Repositories\BaseRepository;

class SliderRepository extends BaseRepository implements SliderInterface
{
    /**
    * @var Slider
    */
    protected $model;

    public function __construct(Slider $slider)
    {
      $this->model = $slider;
    }

    public function getDefaultSlider()
    {
        return $this->model->where(['is_default' => ACTIVE_STATUS_ACTIVE])->first();
    }
}
