<?php

namespace App\Services\User;

use App\Repositories\Admin\Interfaces\CurrencyInterface;
use App\Repositories\User\Interfaces\ProfileInterface;
use App\Repositories\User\Interfaces\UserInterface;
use App\Repositories\User\Interfaces\WalletInterface;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserService
{
    public function generate($parameters)
    {

        $userParams = Arr::only($parameters, ['email', 'username', 'is_email_verified', 'is_financial_active', 'is_accessible_under_maintenance', 'is_active', 'created_by', 'password']);

        $userParams['password'] = Hash::make($userParams['password']);
        $userParams['ref_id'] = Str::uuid();
        $userParams['role_id'] = settings('default_role_to_register');

        if (Arr::has($parameters, 'role_id')) {
            $userParams['role_id'] = $parameters['role_id'];
        }

        DB::beginTransaction();
        $user = app(UserInterface::class)->create($userParams);

        if (empty($user)) {
            DB::rollBack();
            return false;
        }

        $profileParams = Arr::only($parameters, ['first_name', 'last_name', 'address', 'phone']);
        $profileParams['user_id'] = $user->id;
        $profile = app(ProfileInterface::class)->create($profileParams);

        if (empty($profile)) {
            DB::rollBack();
            return false;
        }

        $activeCurrencies = app(CurrencyInterface::class)->getByConditions(['is_active' => ACTIVE_STATUS_ACTIVE]);

        if (!$activeCurrencies->isEmpty()) {
            $activeCurrencies = $activeCurrencies->pluck('id')->toArray();

            $walletAttributes = [];

            foreach ($activeCurrencies as $activeCurrency) {
                $date = now();
                $walletAttributes[] = [
                    'user_id' => $user->id,
                    'currency_id' => $activeCurrency,
                    'created_at' => $date,
                    'updated_at' => $date,
                ];
            }

            $wallets = app(WalletInterface::class)->insert($walletAttributes);

            if (empty($wallets)) {
                DB::rollBack();
                return false;
            }
        }

        DB::commit();
        return $user;
    }
}
