<?php

namespace App\Repositories\Core\Eloquent;
use App\Models\Core\Language;
use App\Repositories\BaseRepository;
use App\Repositories\Core\Interfaces\LanguageInterface;

class LanguageRepository extends BaseRepository implements LanguageInterface
{
    /**
    * @var Language
    */
     protected $model;

     public function __construct(Language $language)
     {
        $this->model = $language;
     }
}