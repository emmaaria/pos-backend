<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInvoiceItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('invoice_items', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_id');
            $table->string('user_id')->nullable();
            $table->string('product_id');
            $table->string('discount_type')->nullable();
            $table->string('discount')->nullable();
            $table->string('discount_amount')->nullable();
            $table->string('price');
            $table->string('quantity');
            $table->string('total');
            $table->string('grand_total');
            $table->string('company_id')->nullable();
            $table->string('date');
            $table->timestamps();
            $table->index(['company_id', 'invoice_id', 'user_id']);
            $table->index(['product_id','date']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('invoice_items');
    }
}
