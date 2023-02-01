<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSaleReturnItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sale_return_items', function (Blueprint $table) {
            $table->id();
            $table->string('return_id');
            $table->string('invoice_id');
            $table->string('product_id');
            $table->string('date');
            $table->string('returnQuantity');
            $table->string('returnAmount');
            $table->string('company_id');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('sale_return_items');
    }
}
