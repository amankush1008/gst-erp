<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Demo User
        $userId = DB::table('users')->insertGetId([
            'name'       => 'Demo Admin',
            'email'      => 'demo@gsterp.com',
            'password'   => Hash::make('password'),
            'mobile'     => '9876543210',
            'role'       => 'admin',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Demo Business
        $businessId = DB::table('businesses')->insertGetId([
            'user_id'        => $userId,
            'name'           => 'Demo Traders Pvt Ltd',
            'gstin'          => '27AABCU9603R1ZX',
            'pan'            => 'AABCU9603R',
            'mobile'         => '9876543210',
            'email'          => 'demo@gsterp.com',
            'address'        => '123, Commerce Street, Andheri West',
            'city'           => 'Mumbai',
            'state'          => 'Maharashtra',
            'pincode'        => '400058',
            'bank_name'      => 'State Bank of India',
            'bank_account'   => '31234567891',
            'bank_ifsc'      => 'SBIN0000234',
            'bank_branch'    => 'Andheri West, Mumbai',
            'financial_year' => '2024-2025',
            'terms'          => 'Payment due within 30 days. Goods once sold will not be taken back.',
            'declaration'    => 'We declare that this invoice shows the actual price of the goods described.',
            'created_at'     => now(),
            'updated_at'     => now(),
        ]);

        // Default Warehouse
        DB::table('warehouses')->insert([
            'business_id' => $businessId,
            'name'        => 'Main Godown',
            'address'     => '123, Commerce Street, Andheri West, Mumbai',
            'is_default'  => true,
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);

        // Units
        $units = [
            ['name' => 'Piece',      'symbol' => 'PCS'],
            ['name' => 'Kilogram',   'symbol' => 'KG'],
            ['name' => 'Gram',       'symbol' => 'GM'],
            ['name' => 'Litre',      'symbol' => 'LTR'],
            ['name' => 'Millilitre', 'symbol' => 'ML'],
            ['name' => 'Metre',      'symbol' => 'MTR'],
            ['name' => 'Box',        'symbol' => 'BOX'],
            ['name' => 'Dozen',      'symbol' => 'DZ'],
            ['name' => 'Set',        'symbol' => 'SET'],
            ['name' => 'Unit',       'symbol' => 'UNT'],
        ];

        foreach ($units as $unit) {
            DB::table('units')->insert(array_merge($unit, [
                'business_id' => $businessId,
                'created_at'  => now(),
                'updated_at'  => now(),
            ]));
        }

        $pcsUnitId = DB::table('units')->where('symbol', 'PCS')->where('business_id', $businessId)->value('id');
        $kgUnitId  = DB::table('units')->where('symbol', 'KG')->where('business_id', $businessId)->value('id');

        // Product Categories
        $categories = ['Electronics', 'Clothing', 'Food & Beverages', 'Furniture', 'Stationery', 'Raw Material'];
        $catIds = [];
        foreach ($categories as $cat) {
            $catIds[$cat] = DB::table('product_categories')->insertGetId([
                'business_id' => $businessId,
                'name'        => $cat,
                'created_at'  => now(),
                'updated_at'  => now(),
            ]);
        }

        // Expense Categories
        $expenseCategories = ['Rent', 'Electricity', 'Salaries', 'Transport', 'Marketing', 'Maintenance', 'Office Supplies', 'Miscellaneous'];
        foreach ($expenseCategories as $cat) {
            DB::table('expense_categories')->insert([
                'business_id' => $businessId,
                'name'        => $cat,
                'created_at'  => now(),
                'updated_at'  => now(),
            ]);
        }

        // Products
        $products = [
            ['name' => 'Dell Laptop 15.6"',      'sku' => 'DL-001', 'hsn_code' => '8471', 'gst_rate' => 18, 'sale_price' => 55000, 'purchase_price' => 45000, 'category' => 'Electronics', 'unit_id' => $pcsUnitId],
            ['name' => 'HP Wireless Mouse',       'sku' => 'HM-001', 'hsn_code' => '8471', 'gst_rate' => 18, 'sale_price' => 999,   'purchase_price' => 650,   'category' => 'Electronics', 'unit_id' => $pcsUnitId],
            ['name' => 'Samsung 27" Monitor',     'sku' => 'SM-001', 'hsn_code' => '8528', 'gst_rate' => 18, 'sale_price' => 18000, 'purchase_price' => 13500, 'category' => 'Electronics', 'unit_id' => $pcsUnitId],
            ['name' => 'A4 Copy Paper (500 sh)',  'sku' => 'PP-001', 'hsn_code' => '4802', 'gst_rate' => 12, 'sale_price' => 350,   'purchase_price' => 280,   'category' => 'Stationery',  'unit_id' => $pcsUnitId],
            ['name' => 'Stapler (Full Strip)',    'sku' => 'ST-001', 'hsn_code' => '8305', 'gst_rate' => 18, 'sale_price' => 250,   'purchase_price' => 180,   'category' => 'Stationery',  'unit_id' => $pcsUnitId],
            ['name' => 'Basmati Rice Premium',   'sku' => 'BR-001', 'hsn_code' => '1006', 'gst_rate' => 5,  'sale_price' => 120,   'purchase_price' => 95,    'category' => 'Food & Beverages', 'unit_id' => $kgUnitId],
            ['name' => 'Green Tea (100 bags)',    'sku' => 'GT-001', 'hsn_code' => '0902', 'gst_rate' => 5,  'sale_price' => 450,   'purchase_price' => 320,   'category' => 'Food & Beverages', 'unit_id' => $pcsUnitId],
        ];

        foreach ($products as $product) {
            $productId = DB::table('products')->insertGetId([
                'business_id'    => $businessId,
                'category_id'    => $catIds[$product['category']],
                'unit_id'        => $product['unit_id'],
                'name'           => $product['name'],
                'sku'            => $product['sku'],
                'hsn_code'       => $product['hsn_code'],
                'gst_rate'       => $product['gst_rate'],
                'sale_price'     => $product['sale_price'],
                'purchase_price' => $product['purchase_price'],
                'opening_stock'  => 50,
                'min_stock'      => 5,
                'is_active'      => true,
                'created_at'     => now(),
                'updated_at'     => now(),
            ]);

            // Opening stock
            DB::table('stock')->insert([
                'business_id' => $businessId,
                'product_id'  => $productId,
                'quantity'    => 50,
                'created_at'  => now(),
                'updated_at'  => now(),
            ]);
        }

        // Parties
        $parties = [
            ['name' => 'Reliance Retail Ltd',     'type' => 'customer', 'gstin' => '27AAACR5055K1ZD', 'state' => 'Maharashtra', 'mobile' => '9000000001', 'balance' => 25000],
            ['name' => 'Infosys Technologies',     'type' => 'customer', 'gstin' => '29AABCI1681G1ZW', 'state' => 'Karnataka',   'mobile' => '9000000002', 'balance' => 0],
            ['name' => 'Tata Consultancy Services','type' => 'customer', 'gstin' => '27AABCT1332L1ZD', 'state' => 'Maharashtra', 'mobile' => '9000000003', 'balance' => 15500],
            ['name' => 'ABC Distributors',        'type' => 'supplier', 'gstin' => '27AABCA1234B1Z5', 'state' => 'Maharashtra', 'mobile' => '9000000004', 'balance' => 0],
            ['name' => 'XYZ Wholesale Mart',      'type' => 'supplier', 'gstin' => '27AABCX9876X1Z3', 'state' => 'Maharashtra', 'mobile' => '9000000005', 'balance' => 8000],
        ];

        foreach ($parties as $party) {
            DB::table('parties')->insert([
                'business_id'   => $businessId,
                'name'          => $party['name'],
                'type'          => $party['type'],
                'gstin'         => $party['gstin'],
                'mobile'        => $party['mobile'],
                'billing_state' => $party['state'],
                'balance'       => $party['balance'],
                'opening_balance' => 0,
                'created_at'    => now(),
                'updated_at'    => now(),
            ]);
        }

        // Number Sequences
        $modules = ['invoice', 'purchase', 'credit_note', 'debit_note', 'proforma', 'payment_receipt'];
        $prefixes = ['INV-', 'PUR-', 'CN-', 'DN-', 'PRO-', 'RCP-'];

        foreach ($modules as $i => $module) {
            DB::table('number_sequences')->insert([
                'business_id'    => $businessId,
                'module'         => $module,
                'prefix'         => $prefixes[$i],
                'current_number' => 1,
                'padding'        => 4,
                'financial_year' => '2024-2025',
                'created_at'     => now(),
                'updated_at'     => now(),
            ]);
        }

        $this->command->info('✅ Demo data seeded successfully!');
        $this->command->info('📧 Login: demo@gsterp.com / password');
    }
}
