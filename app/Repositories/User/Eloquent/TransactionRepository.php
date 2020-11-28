<?php

namespace App\Repositories\User\Eloquent;

use App\Models\User\Transaction;
use App\Repositories\BaseRepository;
use App\Repositories\User\Interfaces\TransactionInterface;
use Illuminate\Support\Facades\DB;

class TransactionRepository extends BaseRepository implements TransactionInterface
{
    /**
     * @var Transaction
     */
    protected $model;

    public function __construct(Transaction $model)
    {
        $this->model = $model;
    }

    public function calculatedAmount($conditions = null, $groupBy = null)
    {
        $queryBuilder = $this->loadQueryBuilder();

        if (!empty($conditions)) {
            $this->where($queryBuilder, $conditions);
        }

        if (!empty($groupBy)) {
            $queryBuilder->groupBy($groupBy);
            $select = DB::raw('sum(amount) as amount');

            return $queryBuilder->select($select, $groupBy)->get()->pluck('amount', $groupBy)->toArray();
        }
        return $queryBuilder->sum('amount');
    }
}
