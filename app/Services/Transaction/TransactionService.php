<?php

namespace App\Services\Transaction;

use App\Repositories\User\Interfaces\TransactionInterface;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Request;

class TransactionService
{
    protected $repository;

    public function __construct(TransactionInterface $repository)
    {
        $this->repository = $repository;
    }

    public function transactionSummary($userTransactionType, $isAdmin = false, $paginationKey = 'p')
    {
        $coreCondition = !is_array($userTransactionType) ? ['journal' => $userTransactionType] : [$userTransactionType];
        if (!$isAdmin) {
            $coreCondition['user_id'] = Auth::user()->id;
        }

        $frm = Request::get($paginationKey . '_frm');
        $to = Request::get($paginationKey . '_to');

        if (!validate_date($frm)) {
            $frm = null;
        }
        if (!validate_date($to)) {
            $to = null;
        }

        $data['previousTotal'] = '0.00';

        if (!empty($frm)) {
            $condition = array_merge($coreCondition, ['created_at', '<', $frm]);
            $data['previousTotal'] = $this->repository->calculatedAmount($condition);
        }

        if (!empty($frm)) {
            $coreCondition[] = ['created_at', '>', $frm];
        }
        if (!empty($to)) {
            $coreCondition[] = ['created_at', '<', Carbon::parse($to)->addDay()];
        }
        $currentCalculation = $this->repository->calculatedAmount($coreCondition, 'journal');
        $data['currentCalculation'] = $this->repository->calculatedAmount($coreCondition, 'journal');
        return $data;
    }
}
