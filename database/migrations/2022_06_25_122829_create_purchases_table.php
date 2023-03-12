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
            $table->index('supplier_id')->change();
            $table->index('purchase_id')->change();
            $table->index('user_id')->nullable()->change();
            $table->string('amount');
            $table->string('paid')->default(0);
            $table->string('opening')->default(0);
            $table->string('comment')->nullable();
            $table->index('date')->change();
            $table->index('company_id')->nullable()->change();
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
        Schema::dropIfExists('purchases');
    }
}
