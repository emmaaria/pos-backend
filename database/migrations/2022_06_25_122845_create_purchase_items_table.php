<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePurchaseItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('purchase_items', function (Blueprint $table) {
            $table->id();
            $table->string('purchase_id');
            $table->string('product_id');
            $table->string('user_id')->nullable();
            $table->string('price');
            $table->string('quantity');
            $table->string('total');
            $table->string('date');
            $table->string('company_id')->nullable();
            $table->timestamps();
            $table->index(['company_id', 'purchase_id', 'product_id']);
            $table->index(['user_id','date']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('purchase_items');
    }
}
