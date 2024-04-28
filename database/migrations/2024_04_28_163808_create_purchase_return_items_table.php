<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePurchaseReturnItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('purchase_return_items', function (Blueprint $table) {
            $table->id();
            $table->string('return_id');
            $table->string('invoice_id')->nullable();
            $table->string('product_id');
            $table->string('user_id')->nullable();
            $table->string('price');
            $table->string('date');
            $table->string('quantity');
            $table->string('total');
            $table->string('company_id');
            $table->string('customer_id');
            $table->timestamps();
            $table->index(['company_id', 'return_id', 'invoice_id']);
            $table->index(['user_id', 'date', 'product_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('purchase_return_items');
    }
}
