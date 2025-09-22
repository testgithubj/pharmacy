<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $permissions = [
            'Role' => [
                ['label' => 'Index', 'name' => 'role.index'],
                ['label' => 'Create', 'name' => 'role.create'],
                ['label' => 'Store', 'name' => 'role.store'],
                ['label' => 'Edit', 'name' => 'role.edit'],
                ['label' => 'Update', 'name' => 'role.update'],
                ['label' => 'Delete', 'name' => 'role.destroy'],
            ],
            'User' => [
                ['label' => 'Index', 'name' => 'user.index'],
                ['label' => 'Create', 'name' => 'user.create'],
                ['label' => 'Store', 'name' => 'user.store'],
                ['label' => 'Edit', 'name' => 'user.edit'],
                ['label' => 'Update', 'name' => 'user.update'],
                ['label' => 'Delete', 'name' => 'user.destroy'],
            ],
            'Customer' => [
                ['label' => 'Index', 'name' => 'customer.index'],
                ['label' => 'Create', 'name' => 'customer.create'],
                ['label' => 'Store', 'name' => 'customer.store'],
                ['label' => 'Edit', 'name' => 'customer.edit'],
                ['label' => 'Update', 'name' => 'customer.update'],
                ['label' => 'Delete', 'name' => 'customer.destroy'],
            ],
            'Supplier' => [
                ['label' => 'Index', 'name' => 'supplier.list'],
                ['label' => 'Create', 'name' => 'supplier.add'],
                ['label' => 'Edit', 'name' => 'supplier.edit'],
                ['label' => 'show', 'name' => 'supplier.view'],
                ['label' => 'Update', 'name' => 'supplier.update'],
                ['label' => 'Delete', 'name' => 'supplier.delete'],
                ['label' => 'Due pay', 'name' => 'supplier.paydue'],
            ],
            'Vendor' => [
                ['label' => 'Index', 'name' => 'vendor.index'],
                ['label' => 'Create', 'name' => 'vendor.create'],
                ['label' => 'Store', 'name' => 'vendor.store'],
                ['label' => 'Edit', 'name' => 'vendor.edit'],
                ['label' => 'show', 'name' => 'vendor.show'],
                ['label' => 'Update', 'name' => 'vendor.update'],
                ['label' => 'Delete', 'name' => 'vendor.destroy'],
            ],
            'Category' => [
                ['label' => 'Index', 'name' => 'category.index'],
                ['label' => 'Create', 'name' => 'category.create'],
                ['label' => 'Store', 'name' => 'category.store'],
                ['label' => 'Edit', 'name' => 'category.edit'],
                ['label' => 'Update', 'name' => 'category.update'],
                ['label' => 'Delete', 'name' => 'category.destroy'],
            ],
            'Medicine' => [
                ['label' => 'Index', 'name' => 'medicine.list'],
                ['label' => 'Create', 'name' => 'medicine.create'],
                ['label' => 'Store', 'name' => 'medicine.store'],
                ['label' => 'Show', 'name' => 'medicine.show'],
                ['label' => 'Edit', 'name' => 'medicine.edit'],
                ['label' => 'Update', 'name' => 'medicine.update'],
                ['label' => 'Delete', 'name' => 'medicine.destroy'],
                ['label' => 'Import', 'name' => 'medicine.import'],
                ['label' => 'CSV Export', 'name' => 'medicine.csv.export'],
            ],
            'Purchase' => [
                ['label' => 'Index', 'name' => 'purchase.index'],
                ['label' => 'Create', 'name' => 'purchase.create'],
                ['label' => 'Store', 'name' => 'purchase.store'],
                ['label' => 'Show', 'name' => 'purchase.show'],
                ['label' => 'Edit', 'name' => 'purchase.edit'],
                ['label' => 'Update', 'name' => 'purchase.update'],
                ['label' => 'Delete', 'name' => 'purchase.destroy'],
            ],
            'Sale' => [
                ['label' => 'Index', 'name' => 'sale.index'],
                ['label' => 'Create', 'name' => 'sale.create'],
                ['label' => 'Store', 'name' => 'sale.store'],
                ['label' => 'Show', 'name' => 'sale.show'],
                ['label' => 'Edit', 'name' => 'sale.edit'],
                ['label' => 'Update', 'name' => 'sale.update'],
                ['label' => 'Delete', 'name' => 'sale.destroy'],
            ],
            'Payment Method' => [
                ['label' => 'Index', 'name' => 'paymentmethod.index'],
                ['label' => 'Create', 'name' => 'paymentmethod.create'],
                ['label' => 'Store', 'name' => 'paymentmethod.store'],
                ['label' => 'Edit', 'name' => 'paymentmethod.edit'],
                ['label' => 'Update', 'name' => 'paymentmethod.update'],
                ['label' => 'Delete', 'name' => 'paymentmethod.destroy'],
            ],
            'Medicine Stock' => [
                ['label' => 'Instock', 'name' => 'report.instock'],
                ['label' => 'Low Stock', 'name' => 'report.low_stock'],
                ['label' => 'Stockout', 'name' => 'report.stockout'],
                ['label' => 'Upcoming Expired', 'name' => 'report.upcoming_expire'],
                ['label' => 'Already Expired', 'name' => 'report.already_expire'],
            ],
            'Reports' => [
                ['label' => 'Due Customer', 'name' => 'report.due_customer'],
                ['label' => 'Payable Manufacturer', 'name' => 'report.payable_manufacturer'],
                ['label' => 'Sale Purchase', 'name' => 'report.sale_purchase'],
                ['label' => 'Profit Loss', 'name' => 'report.profit_loss'],
            ],
            'Doctor' => [
                ['label' => 'Index', 'name' => 'doctor.index'],
                ['label' => 'Create', 'name' => 'doctor.create'],
                ['label' => 'Store', 'name' => 'doctor.store'],
                ['label' => 'Edit', 'name' => 'doctor.edit'],
                ['label' => 'Update', 'name' => 'doctor.update'],
                ['label' => 'Delete', 'name' => 'doctor.destroy'],
            ],
            'Patient' => [
                ['label' => 'Index', 'name' => 'patient.index'],
                ['label' => 'Create', 'name' => 'patient.create'],
                ['label' => 'Store', 'name' => 'patient.store'],
                ['label' => 'Edit', 'name' => 'patient.edit'],
                ['label' => 'Update', 'name' => 'patient.update'],
                ['label' => 'Delete', 'name' => 'patient.destroy'],
            ],
            'Test' => [
                ['label' => 'Index', 'name' => 'test.index'],
                ['label' => 'Create', 'name' => 'test.create'],
                ['label' => 'Store', 'name' => 'test.store'],
                ['label' => 'Edit', 'name' => 'test.edit'],
                ['label' => 'Update', 'name' => 'test.update'],
                ['label' => 'Delete', 'name' => 'test.destroy'],
            ],
            'Prescription' => [
                ['label' => 'Index', 'name' => 'prescription.index'],
                ['label' => 'Create', 'name' => 'prescription.create'],
                ['label' => 'Store', 'name' => 'prescription.store'],
                ['label' => 'Show', 'name' => 'prescription.show'],
                ['label' => 'Delete', 'name' => 'prescription.destroy'],
            ],
            'Language' => [
                ['label' => 'Index', 'name' => 'language.index'],
                ['label' => 'Create', 'name' => 'language.create'],
                ['label' => 'Store', 'name' => 'language.store'],
                ['label' => 'Delete', 'name' => 'language.destroy'],
                ['label' => 'Terms Edit', 'name' => 'language.destroy'],
            ],
            'Expense Category' => [
                ['label' => 'Index', 'name' => 'expense-categories.index'],
                ['label' => 'Create', 'name' => 'expense-categories.create'],
                ['label' => 'Store', 'name' => 'expense-categories.store'],
                ['label' => 'Update', 'name' => 'expense-categories.update'],
                ['label' => 'Delete', 'name' => 'expense-categories.destroy'],
            ],
            'Expense' => [
                ['label' => 'Index', 'name' => 'expenses.index'],
                ['label' => 'Create', 'name' => 'expenses.create'],
                ['label' => 'Store', 'name' => 'expenses.store'],
                ['label' => 'Update', 'name' => 'expenses.update'],
                ['label' => 'Delete', 'name' => 'expenses.destroy'],
            ],
            'Setting' => [
                ['label' => 'General Setting', 'name' => 'setting.generalSetting'],
                ['label' => 'Email Setting', 'name' => 'email.update'],
            ],
        ];

        foreach ($permissions as $group => $permission) {
            foreach ($permission as $action) {
                $action['module'] = $group;
                $alreadyHas = Permission::where('name', $action['name'])->first();
                if (!$alreadyHas) {
                    Permission::create($action);
                } else {
                    $alreadyHas->update(['name' => $action['name']]);
                }
            }
        }
    }
}
