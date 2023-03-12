<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePurchasesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('purchases', function (Blueprint $table) {
            $table->id();
            $table->string('supplier_id');
            $table->string('purchase_id');
            $table->string('user_id')->nullable();
            $table->string('amount');
            $table->string('paid')->default(0);
            $table->string('opening')->default(0);
            $table->string('comment')->nullable();
            $table->string('date');
            $table->string('payment_method');
            $table->string('company_id')->nullable();
            $table->timestamps();
            $table->index(['company_id', 'supplier_id', 'purchase_id']);
            $table->index(['user_id', 'date']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('purchases');
    }
}
