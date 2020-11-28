<?php
/**
 * Created by PhpStorm.
 * User: zahid
 * Date: 2018-07-30
 * Time: 2:47 PM
 */

namespace App\Repositories\Core\Eloquent;


use App\Models\Core\ApplicationSetting;
use App\Repositories\BaseRepository;
use App\Repositories\Core\Interfaces\ApplicationSettingInterface;


class ApplicationSettingRepository extends BaseRepository implements ApplicationSettingInterface
{
    /**
     * @var ApplicationSetting
     */
    protected $model;

    public function __construct(ApplicationSetting $model)
    {
        $this->model = $model;
    }

    public function getBySlug($slug)
    {
        return $this->model->where('slug', $slug)->value('value');
    }

    public function getBySlugs($slugs)
    {
        return $this->model->whereIn('slug', $slugs)->pluck('value', 'slug')->toArray();
    }

    public function updateOrCreate($attributes, $conditions)
    {
        return $this->model->updateOrCreate($conditions, $attributes);
    }

}
