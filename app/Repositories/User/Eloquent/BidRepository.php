<?php

namespace App\Repositories\User\Eloquent;
use App\Models\User\Bid;
use App\Repositories\BaseRepository;
use App\Repositories\User\Interfaces\BidInterface;
use Illuminate\Support\Facades\DB;

class BidRepository extends BaseRepository implements BidInterface
{
    /**
    * @var Bid
    */
     protected $model;

     public function __construct(Bid $bid)
     {
        $this->model = $bid;
     }

     public function uniqueBid($auctionId)
     {
        return $this->model->whereHas('auction',function($query) use($auctionId){
            $query->where(['id'=>$auctionId]);
        })
            ->select('amount', DB::raw('count(*) as total'))
            ->groupBy('amount')
            ->orderBy('total', 'asc')
            ->orderBy('amount', 'asc' )
            ->first();
     }

     public function returnAmount($auctionId)
     {
         return $this->model
             ->whereHas('auction',function($query) use($auctionId){
                 $query->where(['id'=>$auctionId]);
             })
             ->select('user_id', DB::raw('max(amount) as amount'))
             ->groupBy('user_id')
             ->get();
     }

    public function returnAmountOfCurrentUser($auctionId, $userId)
    {
        return $this->model
            ->whereHas('auction',function($query) use($auctionId, $userId){
                $query->where(['id'=>$auctionId, 'user_id' => $userId]);
            })
            ->select('user_id', DB::raw('max(amount) as amount'))
            ->groupBy('user_id')
            ->get();
    }
}
