<?php

namespace App\Repositories\Admin\Eloquent;
use App\Models\Admin\Layout;
use App\Repositories\Admin\Interfaces\LayoutInterface;
use App\Repositories\BaseRepository;

class LayoutRepository extends BaseRepository implements LayoutInterface
{
    /**
    * @var Layout
    */
     protected $model;

     public function __construct(Layout $layout)
     {
        $this->model = $layout;
     }

     public function getExistingTypes()
     {
         return $this->model->pluck('layout_type')->toArray();
     }
}
