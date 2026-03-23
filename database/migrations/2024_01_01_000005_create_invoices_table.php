<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            $table->foreignId('party_id')->nullable()->constrained('parties')->nullOnDelete();
            $table->foreignId('warehouse_id')->nullable()->constrained('warehouses')->nullOnDelete();
            $table->string('invoice_number', 50)->unique();
            $table->enum('invoice_type', [
                'tax_invoice', 'retail_invoice', 'proforma_invoice',
                'credit_note', 'debit_note', 'delivery_challan',
                'purchase_order', 'quotation',
            ])->default('tax_invoice');
            $table->date('invoice_date');
            $table->date('due_date')->nullable();
            $table->enum('payment_status', ['draft','unpaid','partial','paid','cancelled'])
                  ->default('unpaid');
            $table->string('payment_method', 30)->nullable();

            // Amounts
            $table->decimal('subtotal', 14, 2)->default(0);       // qty * rate - item discounts
            $table->decimal('taxable_amount', 14, 2)->default(0); // after all discounts
            $table->decimal('discount_amount', 14, 2)->default(0);
            $table->decimal('cgst_amount', 12, 2)->default(0);
            $table->decimal('sgst_amount', 12, 2)->default(0);
            $table->decimal('igst_amount', 12, 2)->default(0);
            $table->decimal('cess_amount', 12, 2)->default(0);
            $table->decimal('tax_amount', 12, 2)->default(0);      // total GST
            $table->decimal('round_off', 8, 2)->default(0);
            $table->decimal('total_amount', 14, 2)->default(0);    // final invoice total
            $table->decimal('paid_amount', 12, 2)->default(0);
            $table->decimal('balance_amount', 12, 2)->default(0);

            // GST flags
            $table->boolean('is_interstate')->default(false);
            $table->string('place_of_supply', 50)->nullable();
            $table->boolean('reverse_charge')->default(false);

            // Transport / E-way
            $table->string('eway_bill_number', 20)->nullable();
            $table->string('eway_bill_status', 20)->nullable();
            $table->timestamp('eway_bill_date')->nullable();
            $table->string('vehicle_number', 20)->nullable();
            $table->string('transport_name')->nullable();
            $table->string('lr_number', 30)->nullable();
            $table->date('dispatch_date')->nullable();

            $table->string('template', 30)->default('default');
            $table->text('notes')->nullable();
            $table->string('reference')->nullable(); // original invoice # for CN/DN

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['business_id', 'invoice_date']);
            $table->index(['business_id', 'payment_status']);
            $table->index(['party_id']);
        });

        Schema::create('invoice_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained('products')->nullOnDelete();
            $table->string('product_name');           // snapshot at time of invoice
            $table->string('hsn_code', 20)->nullable();
            $table->string('description')->nullable();
            $table->decimal('qty', 12, 3);
            $table->string('unit', 15)->nullable();
            $table->decimal('rate', 12, 2);
            $table->decimal('discount', 12, 2)->default(0);
            $table->decimal('taxable_amount', 14, 2)->default(0);
            $table->decimal('gst_rate', 5, 2)->default(0);
            $table->decimal('cgst_rate', 5, 2)->default(0);
            $table->decimal('sgst_rate', 5, 2)->default(0);
            $table->decimal('igst_rate', 5, 2)->default(0);
            $table->decimal('cgst_amount', 12, 2)->default(0);
            $table->decimal('sgst_amount', 12, 2)->default(0);
            $table->decimal('igst_amount', 12, 2)->default(0);
            $table->decimal('cess_amount', 12, 2)->default(0);
            $table->decimal('tax_amount', 12, 2)->default(0);
            $table->decimal('total_amount', 14, 2)->default(0);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('invoice_meta', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained()->cascadeOnDelete();
            $table->string('key', 100);
            $table->text('value')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoice_meta');
        Schema::dropIfExists('invoice_items');
        Schema::dropIfExists('invoices');
    }
};
