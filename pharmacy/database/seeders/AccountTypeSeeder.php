<?php

namespace Database\Seeders;

use App\Models\Account\AccountType;
use Illuminate\Database\Seeder;

class AccountTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $categories = [
            ['name' => 'Asset', 'is_deletable' => 0],
            ['name' => 'Equity', 'is_deletable' => 0],
            ['name' => 'Expense', 'is_deletable' => 0],
            ['name' => 'Liability', 'is_deletable' => 0],
            ['name' => 'Revenue', 'is_deletable' => 0],
            ['name' => 'Withdrawal', 'is_deletable' => 0],
        ];

        foreach ($categories as $key => $category) {
            $category['serial'] = $key+1;
            AccountType::create($category);
        }
    }
}
