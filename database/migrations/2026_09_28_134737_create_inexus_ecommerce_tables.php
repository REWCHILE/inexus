<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('icon')->nullable();
            $table->text('description')->nullable();
            $table->decimal('margin_percentage', 5, 2)->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->nullable()->constrained('categories')->nullOnDelete();
            $table->string('ingram_part_number')->nullable()->index();
            $table->string('vendor_part_number')->nullable()->index();
            $table->string('sku')->unique()->index();
            $table->string('name');
            $table->string('slug')->unique()->index();
            $table->string('brand')->nullable()->index();
            $table->text('short_description')->nullable();
            $table->longText('description')->nullable();
            $table->json('specifications')->nullable();
            $table->json('faqs')->nullable();
            $table->decimal('cost_price_usd', 10, 2)->default(0.00);
            $table->decimal('cost_price_clp', 12, 2)->default(0.00);
            $table->decimal('margin_percentage', 5, 2)->nullable();
            $table->decimal('calculated_price_clp', 12, 2)->default(0.00);
            $table->decimal('regular_price', 12, 2)->default(0.00);
            $table->decimal('sale_price', 12, 2)->nullable();
            $table->integer('stock')->default(0);
            $table->string('stock_status')->default('in_stock');
            $table->string('main_image')->nullable();
            $table->json('gallery')->nullable();
            $table->string('scraper_source')->nullable();
            $table->string('scraper_status')->default('pending');
            $table->timestamp('scraper_last_run')->nullable();
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_active')->default(true);
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->timestamps();
        });

        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->longText('value')->nullable();
            $table->string('type')->default('text');
            $table->string('group')->default('store');
            $table->timestamps();
        });

        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number')->unique();
            $table->string('customer_name');
            $table->string('customer_email');
            $table->string('customer_phone');
            $table->string('customer_rut')->nullable();
            $table->string('document_type')->default('boleta'); // boleta o factura
            $table->string('company_name')->nullable();
            $table->string('company_rut')->nullable();
            $table->string('company_giro')->nullable();
            $table->string('shipping_address');
            $table->string('shipping_city');
            $table->string('shipping_region')->default('Metropolitana');
            $table->text('shipping_notes')->nullable();
            $table->string('payment_method')->default('mercadopago');
            $table->string('payment_status')->default('pending');
            $table->string('payment_id')->nullable();
            $table->decimal('subtotal', 12, 2)->default(0.00);
            $table->decimal('tax_amount', 12, 2)->default(0.00);
            $table->decimal('shipping_cost', 12, 2)->default(0.00);
            $table->decimal('total', 12, 2)->default(0.00);
            $table->string('status')->default('pending'); // pending, processing, completed, cancelled
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained('products')->nullOnDelete();
            $table->string('product_name');
            $table->string('product_sku');
            $table->decimal('price', 12, 2);
            $table->integer('quantity')->default(1);
            $table->decimal('subtotal', 12, 2);
            $table->timestamps();
        });

        Schema::create('sync_logs', function (Blueprint $table) {
            $table->id();
            $table->string('type'); // ingram_catalog, ingram_prices, scraper_batch, scraper_single
            $table->string('status')->default('info'); // info, success, warning, error
            $table->string('message');
            $table->json('details')->nullable();
            $table->integer('items_processed')->default(0);
            $table->integer('items_success')->default(0);
            $table->integer('items_failed')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
        Schema::dropIfExists('sync_logs');
        Schema::dropIfExists('products');
        Schema::dropIfExists('categories');
        Schema::dropIfExists('settings');
    }
};
