<?php

namespace App\Repositories\User\Eloquent;
use App\Models\User\Comment;
use App\Repositories\BaseRepository;
use App\Repositories\User\Interfaces\CommentInterface;

class CommentRepository extends BaseRepository implements CommentInterface
{
    /**
    * @var Comment
    */
     protected $model;

     public function __construct(Comment $comment)
     {
        $this->model = $comment;
     }
}