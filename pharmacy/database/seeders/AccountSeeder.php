<?php

namespace Database\Seeders;

use App\Enum\AccountTypeEnum;
use App\Models\Account\Account;
use Illuminate\Database\Seeder;

class AccountSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $accounts = [
            [
                'name' => 'Cost of Sales',
                'account_type_id' => AccountTypeEnum::EXPENSE,
                'is_deletable' => false,
            ],
            [
                'name' => 'Sales',
                'account_type_id' => AccountTypeEnum::REVENUE,
                'is_deletable' => false,
            ],
            [
                'name' => 'Accounts Payable',
                'account_type_id' => AccountTypeEnum::LIABILITY,
                'is_deletable' => false,
            ],
            [
                'name' => 'Accounts Receivable',
                'account_type_id' => AccountTypeEnum::ASSET,
                'is_deletable' => false,
            ],
        ];

        foreach ($accounts as $key => $account) {
            $account['serial'] = $key + 1;
            Account::create($account);
        }
    }
}
