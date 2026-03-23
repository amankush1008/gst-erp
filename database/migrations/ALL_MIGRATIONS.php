<?php
// =============================================================================
// MIGRATION FILES - Place each in database/migrations/
// =============================================================================

// ============================================================
// FILE: 2024_01_01_000001_create_tenants_table.php
// ============================================================
/*
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('tenants', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('domain')->nullable()->unique();
            $table->enum('plan', ['free', 'starter', 'professional', 'enterprise'])->default('free');
            $table->enum('status', ['active', 'inactive', 'suspended'])->default('active');
            $table->timestamp('trial_ends_at')->nullable();
            $table->timestamp('subscription_ends_at')->nullable();
            $table->json('settings')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }
    public function down(): void { Schema::dropIfExists('tenants'); }
};
*/

// ============================================================
// FILE: 2024_01_01_000002_create_users_table.php
// ============================================================
/*
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('email')->unique();
            $table->string('phone', 15)->nullable();
            $table->string('password');
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->string('profile_photo')->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->timestamp('last_login_at')->nullable();
            $table->string('last_login_ip', 45)->nullable();
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();
        });
    }
    public function down(): void { Schema::dropIfExists('users'); }
};
*/

// ============================================================
// FILE: 2024_01_01_000003_create_businesses_table.php
// ============================================================
/*
return new class extends Migration {
    public function up(): void
    {
        Schema::create('businesses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('business_name');
            $table->string('gstin', 15)->nullable();
            $table->string('pan', 10)->nullable();
            $table->string('email')->nullable();
            $table->string('phone', 15)->nullable();
            $table->string('mobile', 15)->nullable();
            $table->string('website')->nullable();
            $table->string('address_line1');
            $table->string('address_line2')->nullable();
            $table->string('city');
            $table->string('state');
            $table->string('pincode', 10);
            $table->string('country')->default('India');
            $table->string('logo')->nullable();
            $table->string('signature')->nullable();
            $table->string('bank_name')->nullable();
            $table->string('bank_account_no')->nullable();
            $table->string('bank_ifsc')->nullable();
            $table->string('bank_branch')->nullable();
            $table->string('upi_id')->nullable();
            $table->string('currency', 3)->default('INR');
            $table->string('financial_year_start')->default('04-01');
            $table->text('terms_conditions')->nullable();
            $table->text('declaration')->nullable();
            $table->boolean('is_default')->default(false);
            $table->json('invoice_settings')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('businesses'); }
};
*/

// ============================================================
// FILE: 2024_01_01_000004_create_warehouses_table.php
// ============================================================
/*
return new class extends Migration {
    public function up(): void
    {
        Schema::create('warehouses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('code')->nullable();
            $table->text('address')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('pincode', 10)->nullable();
            $table->boolean('is_default')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('warehouses'); }
};
*/

// ============================================================
// FILE: 2024_01_01_000005_create_product_categories_table.php
// ============================================================
/*
return new class extends Migration {
    public function up(): void
    {
        Schema::create('product_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('code')->nullable();
            $table->foreignId('parent_id')->nullable()->constrained('product_categories')->nullOnDelete();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('product_categories'); }
};
*/

// ============================================================
// FILE: 2024_01_01_000006_create_units_table.php
// ============================================================
/*
return new class extends Migration {
    public function up(): void
    {
        Schema::create('units', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->onDelete('cascade');
            $table->string('name');          // Pieces
            $table->string('short_name');    // PCS
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('units'); }
};
*/

// ============================================================
// FILE: 2024_01_01_000007_create_products_table.php
// ============================================================
/*
return new class extends Migration {
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->onDelete('cascade');
            $table->foreignId('category_id')->nullable()->constrained('product_categories')->nullOnDelete();
            $table->foreignId('unit_id')->nullable()->constrained('units')->nullOnDelete();
            $table->string('name');
            $table->string('sku')->nullable();
            $table->string('item_code')->nullable();
            $table->string('barcode')->nullable();
            $table->string('upc')->nullable();
            $table->string('hsn_code', 20)->nullable();
            $table->string('description')->nullable();
            $table->enum('type', ['product', 'service'])->default('product');
            $table->decimal('sale_price', 15, 2)->default(0);
            $table->decimal('purchase_price', 15, 2)->default(0);
            $table->decimal('mrp', 15, 2)->nullable();
            $table->decimal('tax_rate', 5, 2)->default(0);  // GST %
            $table->enum('tax_type', ['inclusive', 'exclusive'])->default('exclusive');
            $table->decimal('opening_stock', 10, 3)->default(0);
            $table->decimal('min_stock_alert', 10, 3)->default(0);
            $table->decimal('max_stock', 10, 3)->nullable();
            $table->boolean('track_inventory')->default(true);
            $table->boolean('has_batch')->default(false);
            $table->boolean('has_expiry')->default(false);
            $table->string('image')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['business_id', 'sku']);
            $table->index(['business_id', 'barcode']);
        });
    }
    public function down(): void { Schema::dropIfExists('products'); }
};
*/

// ============================================================
// FILE: 2024_01_01_000008_create_product_meta_table.php
// ============================================================
/*
return new class extends Migration {
    public function up(): void
    {
        Schema::create('product_meta', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->string('meta_key');
            $table->text('meta_value')->nullable();
            $table->timestamps();
        });

        // Custom field definitions
        Schema::create('custom_fields', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->onDelete('cascade');
            $table->enum('module', ['product', 'invoice', 'party', 'purchase', 'expense']);
            $table->string('field_name');
            $table->string('field_label');
            $table->enum('field_type', ['text', 'number', 'date', 'select', 'checkbox', 'textarea']);
            $table->json('field_options')->nullable(); // For select fields
            $table->boolean('is_required')->default(false);
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('custom_fields');
        Schema::dropIfExists('product_meta');
    }
};
*/

// ============================================================
// FILE: 2024_01_01_000009_create_stock_table.php
// ============================================================
/*
return new class extends Migration {
    public function up(): void
    {
        Schema::create('stock', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->foreignId('warehouse_id')->constrained()->onDelete('cascade');
            $table->decimal('quantity', 10, 3)->default(0);
            $table->timestamps();
            $table->unique(['product_id', 'warehouse_id']);
        });

        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->foreignId('warehouse_id')->constrained()->onDelete('cascade');
            $table->foreignId('business_id')->constrained()->onDelete('cascade');
            $table->enum('type', ['in', 'out', 'adjustment', 'transfer']);
            $table->decimal('quantity', 10, 3);
            $table->decimal('balance', 10, 3);
            $table->decimal('rate', 15, 2)->nullable();
            $table->string('reference_type')->nullable(); // invoice, purchase, etc.
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->string('notes')->nullable();
            $table->foreignId('user_id')->constrained();
            $table->timestamps();
        });

        Schema::create('batches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->foreignId('warehouse_id')->constrained()->onDelete('cascade');
            $table->string('batch_number');
            $table->date('manufacturing_date')->nullable();
            $table->date('expiry_date')->nullable();
            $table->decimal('quantity', 10, 3)->default(0);
            $table->decimal('purchase_price', 15, 2)->nullable();
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('batches');
        Schema::dropIfExists('stock_movements');
        Schema::dropIfExists('stock');
    }
};
*/

// ============================================================
// FILE: 2024_01_01_000010_create_parties_table.php
// ============================================================
/*
return new class extends Migration {
    public function up(): void
    {
        Schema::create('parties', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->onDelete('cascade');
            $table->enum('type', ['customer', 'supplier', 'both'])->default('customer');
            $table->string('name');
            $table->string('gstin', 15)->nullable();
            $table->boolean('gstin_verified')->default(false);
            $table->string('pan', 10)->nullable();
            $table->string('email')->nullable();
            $table->string('phone', 15)->nullable();
            $table->string('mobile', 15)->nullable();
            $table->string('contact_person')->nullable();

            // Billing Address
            $table->text('billing_address')->nullable();
            $table->string('billing_city')->nullable();
            $table->string('billing_state')->nullable();
            $table->string('billing_pincode', 10)->nullable();
            $table->string('billing_country')->default('India');

            // Shipping Address
            $table->boolean('same_as_billing')->default(true);
            $table->text('shipping_address')->nullable();
            $table->string('shipping_city')->nullable();
            $table->string('shipping_state')->nullable();
            $table->string('shipping_pincode', 10)->nullable();

            $table->decimal('credit_limit', 15, 2)->default(0);
            $table->integer('payment_terms_days')->default(0); // Net days
            $table->decimal('opening_balance', 15, 2)->default(0);
            $table->enum('opening_balance_type', ['debit', 'credit'])->default('debit');

            $table->string('website')->nullable();
            $table->text('notes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['business_id', 'type']);
            $table->index(['business_id', 'gstin']);
        });
    }
    public function down(): void { Schema::dropIfExists('parties'); }
};
*/

// ============================================================
// FILE: 2024_01_01_000011_create_invoices_table.php
// ============================================================
/*
return new class extends Migration {
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->onDelete('cascade');
            $table->foreignId('party_id')->constrained()->onDelete('restrict');
            $table->foreignId('user_id')->constrained();
            $table->foreignId('warehouse_id')->nullable()->constrained()->nullOnDelete();

            $table->enum('invoice_type', [
                'tax_invoice', 'retail_invoice', 'proforma_invoice',
                'credit_note', 'debit_note', 'delivery_challan'
            ])->default('tax_invoice');

            $table->string('invoice_number')->unique();
            $table->string('reference_invoice_id')->nullable(); // for credit/debit note
            $table->date('invoice_date');
            $table->date('due_date')->nullable();
            $table->string('po_number')->nullable();
            $table->string('place_of_supply')->nullable();

            // Tax
            $table->boolean('reverse_charge')->default(false);
            $table->boolean('is_interstate')->default(false);

            // Amounts
            $table->decimal('subtotal', 15, 2)->default(0);
            $table->decimal('discount_amount', 15, 2)->default(0);
            $table->decimal('discount_percent', 5, 2)->default(0);
            $table->decimal('taxable_amount', 15, 2)->default(0);
            $table->decimal('cgst_amount', 15, 2)->default(0);
            $table->decimal('sgst_amount', 15, 2)->default(0);
            $table->decimal('igst_amount', 15, 2)->default(0);
            $table->decimal('cess_amount', 15, 2)->default(0);
            $table->decimal('other_charges', 15, 2)->default(0);
            $table->decimal('round_off', 5, 2)->default(0);
            $table->decimal('total_amount', 15, 2)->default(0);
            $table->decimal('paid_amount', 15, 2)->default(0);
            $table->decimal('balance_amount', 15, 2)->default(0);

            $table->enum('status', ['draft', 'sent', 'partial', 'paid', 'overdue', 'cancelled'])->default('draft');
            $table->enum('payment_status', ['unpaid', 'partial', 'paid'])->default('unpaid');

            $table->string('notes')->nullable();
            $table->string('terms_conditions')->nullable();
            $table->string('eway_bill_number')->nullable();
            $table->json('eway_bill_data')->nullable();
            $table->string('template')->default('default');

            $table->timestamps();
            $table->softDeletes();

            $table->index(['business_id', 'invoice_date']);
            $table->index(['business_id', 'party_id']);
            $table->index(['business_id', 'status']);
        });
    }
    public function down(): void { Schema::dropIfExists('invoices'); }
};
*/

// ============================================================
// FILE: 2024_01_01_000012_create_invoice_items_table.php
// ============================================================
/*
return new class extends Migration {
    public function up(): void
    {
        Schema::create('invoice_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            $table->string('item_name');
            $table->string('description')->nullable();
            $table->string('hsn_code', 20)->nullable();
            $table->string('unit')->nullable();
            $table->decimal('quantity', 10, 3)->default(1);
            $table->decimal('rate', 15, 2)->default(0);
            $table->decimal('discount_percent', 5, 2)->default(0);
            $table->decimal('discount_amount', 15, 2)->default(0);
            $table->decimal('taxable_amount', 15, 2)->default(0);
            $table->decimal('tax_rate', 5, 2)->default(0);
            $table->decimal('cgst_rate', 5, 2)->default(0);
            $table->decimal('sgst_rate', 5, 2)->default(0);
            $table->decimal('igst_rate', 5, 2)->default(0);
            $table->decimal('cgst_amount', 15, 2)->default(0);
            $table->decimal('sgst_amount', 15, 2)->default(0);
            $table->decimal('igst_amount', 15, 2)->default(0);
            $table->decimal('total_amount', 15, 2)->default(0);
            $table->integer('sort_order')->default(0);
            $table->json('meta')->nullable(); // custom fields
            $table->timestamps();
        });

        Schema::create('invoice_meta', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained()->onDelete('cascade');
            $table->string('meta_key');
            $table->text('meta_value')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('invoice_meta');
        Schema::dropIfExists('invoice_items');
    }
};
*/

// ============================================================
// FILE: 2024_01_01_000013_create_transport_details_table.php
// ============================================================
/*
return new class extends Migration {
    public function up(): void
    {
        Schema::create('transport_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained()->onDelete('cascade');
            $table->string('transporter_name')->nullable();
            $table->string('transporter_id')->nullable();
            $table->string('vehicle_number')->nullable();
            $table->string('lr_number')->nullable();
            $table->date('dispatch_date')->nullable();
            $table->text('delivery_address')->nullable();
            $table->string('delivery_city')->nullable();
            $table->string('delivery_state')->nullable();
            $table->string('delivery_pincode')->nullable();
            $table->string('mode_of_transport')->nullable(); // road, rail, air, ship
            $table->string('distance_km')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('transport_details'); }
};
*/

// ============================================================
// FILE: 2024_01_01_000014_create_purchases_table.php
// ============================================================
/*
return new class extends Migration {
    public function up(): void
    {
        Schema::create('purchases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->onDelete('cascade');
            $table->foreignId('party_id')->constrained()->onDelete('restrict');
            $table->foreignId('user_id')->constrained();
            $table->foreignId('warehouse_id')->nullable()->constrained()->nullOnDelete();
            $table->string('purchase_number')->unique();
            $table->string('supplier_invoice_number')->nullable();
            $table->date('purchase_date');
            $table->date('due_date')->nullable();
            $table->boolean('reverse_charge')->default(false);
            $table->decimal('subtotal', 15, 2)->default(0);
            $table->decimal('discount_amount', 15, 2)->default(0);
            $table->decimal('taxable_amount', 15, 2)->default(0);
            $table->decimal('cgst_amount', 15, 2)->default(0);
            $table->decimal('sgst_amount', 15, 2)->default(0);
            $table->decimal('igst_amount', 15, 2)->default(0);
            $table->decimal('total_amount', 15, 2)->default(0);
            $table->decimal('paid_amount', 15, 2)->default(0);
            $table->decimal('balance_amount', 15, 2)->default(0);
            $table->enum('status', ['draft', 'received', 'partial', 'cancelled'])->default('received');
            $table->enum('payment_status', ['unpaid', 'partial', 'paid'])->default('unpaid');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('purchase_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            $table->string('item_name');
            $table->string('hsn_code', 20)->nullable();
            $table->string('unit')->nullable();
            $table->decimal('quantity', 10, 3)->default(1);
            $table->decimal('rate', 15, 2)->default(0);
            $table->decimal('discount_percent', 5, 2)->default(0);
            $table->decimal('taxable_amount', 15, 2)->default(0);
            $table->decimal('tax_rate', 5, 2)->default(0);
            $table->decimal('cgst_amount', 15, 2)->default(0);
            $table->decimal('sgst_amount', 15, 2)->default(0);
            $table->decimal('igst_amount', 15, 2)->default(0);
            $table->decimal('total_amount', 15, 2)->default(0);
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('purchase_items');
        Schema::dropIfExists('purchases');
    }
};
*/

// ============================================================
// FILE: 2024_01_01_000015_create_payments_table.php
// ============================================================
/*
return new class extends Migration {
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->onDelete('cascade');
            $table->foreignId('party_id')->constrained()->onDelete('restrict');
            $table->foreignId('user_id')->constrained();
            $table->string('payment_number')->unique();
            $table->enum('type', ['receipt', 'payment']); // receipt=from customer, payment=to supplier
            $table->date('payment_date');
            $table->decimal('amount', 15, 2);
            $table->enum('payment_mode', ['cash', 'bank', 'upi', 'cheque', 'neft', 'rtgs', 'dd', 'other'])->default('cash');
            $table->string('reference_number')->nullable(); // cheque/transaction no
            $table->string('bank_name')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('payment_allocations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payment_id')->constrained()->onDelete('cascade');
            $table->string('reference_type'); // invoice / purchase
            $table->unsignedBigInteger('reference_id');
            $table->decimal('allocated_amount', 15, 2);
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('payment_allocations');
        Schema::dropIfExists('payments');
    }
};
*/

// ============================================================
// FILE: 2024_01_01_000016_create_expenses_table.php
// ============================================================
/*
return new class extends Migration {
    public function up(): void
    {
        Schema::create('expense_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('color', 7)->default('#3B82F6');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->onDelete('cascade');
            $table->foreignId('category_id')->nullable()->constrained('expense_categories')->nullOnDelete();
            $table->foreignId('user_id')->constrained();
            $table->string('expense_number')->unique();
            $table->string('title');
            $table->date('expense_date');
            $table->decimal('amount', 15, 2);
            $table->decimal('tax_amount', 15, 2)->default(0);
            $table->decimal('total_amount', 15, 2);
            $table->enum('payment_mode', ['cash', 'bank', 'upi', 'card', 'other'])->default('cash');
            $table->string('reference_number')->nullable();
            $table->string('bill_attachment')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }
    public function down(): void {
        Schema::dropIfExists('expenses');
        Schema::dropIfExists('expense_categories');
    }
};
*/

// ============================================================
// FILE: 2024_01_01_000017_create_activity_logs_table.php
// ============================================================
/*
return new class extends Migration {
    public function up(): void
    {
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('business_id')->nullable()->constrained()->nullOnDelete();
            $table->string('module');
            $table->string('action');
            $table->string('description');
            $table->string('model_type')->nullable();
            $table->unsignedBigInteger('model_id')->nullable();
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent')->nullable();
            $table->timestamps();

            $table->index(['business_id', 'created_at']);
            $table->index(['user_id', 'created_at']);
        });
    }
    public function down(): void { Schema::dropIfExists('activity_logs'); }
};
*/

// ============================================================
// FILE: 2024_01_01_000018_create_number_sequences_table.php
// ============================================================
/*
return new class extends Migration {
    public function up(): void
    {
        Schema::create('number_sequences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->onDelete('cascade');
            $table->string('type'); // invoice, purchase, payment, expense
            $table->string('prefix')->nullable();
            $table->string('suffix')->nullable();
            $table->integer('next_number')->default(1);
            $table->integer('padding')->default(4);
            $table->string('financial_year')->nullable();
            $table->timestamps();
            $table->unique(['business_id', 'type', 'financial_year']);
        });
    }
    public function down(): void { Schema::dropIfExists('number_sequences'); }
};
*/

echo "All migration definitions above. Create individual files from each block.\n";
echo "Run: php artisan migrate --seed\n";
