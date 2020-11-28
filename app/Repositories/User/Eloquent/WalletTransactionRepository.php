<?php

namespace App\Repositories\User\Eloquent;

use App\Models\User\WalletTransaction;
use App\Repositories\BaseRepository;
use App\Repositories\User\Interfaces\WalletTransactionInterface;

class WalletTransactionRepository extends BaseRepository implements WalletTransactionInterface
{
    /**
     * @var WalletTransaction
     */
    protected $model;

    public function __construct(WalletTransaction $model)
    {
        $this->model = $model;
    }

    public function getAllByTnxType(array $conditions, array $orderBy)
    {
        $queryBuilder = $this->loadQueryBuilder();

        $queryBuilder->with('user');

        if (!empty($conditions)) {
            if(isset($conditions['username'])){
                $queryBuilder->whereHas('user', function($query) use($conditions){
                    $query->where('username', $conditions['username']);
                });
                unset($conditions['username']);
            }
            $this->where($queryBuilder, $conditions);
        }
        if (!empty($orderBy)) {
            $this->buildOrderBy($queryBuilder, $orderBy);
        }

        return $queryBuilder->paginate(12);

    }

}
