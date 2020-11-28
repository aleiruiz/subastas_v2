<?php

namespace App\Repositories\User\Eloquent;
use App\Models\User\Dispute;
use App\Repositories\BaseRepository;
use App\Repositories\User\Interfaces\DisputeInterface;
use Carbon\Carbon;

class DisputeRepository extends BaseRepository implements DisputeInterface
{
    /**
    * @var Dispute
    */
     protected $model;

     public function __construct(Dispute $dispute)
     {
        $this->model = $dispute;
     }

    public function read($id)
    {
        $dispute = $this->model->where('id', $id)->firstOrFail();
        if (empty($dispute->read_at)) {
            $dispute->read_at = Carbon::now();
            return $dispute->update();
        }
        return false;
    }

    public function unread($id)
    {
        $dispute = $this->model->where('id', $id)->firstOrFail();
        if (!empty($dispute->read_at)) {
            $dispute->read_at = null;
            return $dispute->update();
        }
        return false;
    }

}
