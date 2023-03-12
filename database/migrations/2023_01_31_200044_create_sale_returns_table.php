<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSaleReturnsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sale_returns', function (Blueprint $table) {
            $table->id();
            $table->string('return_id');
            $table->string('invoice_id')->nullable();
            $table->string('return_amount')->default(0);
            $table->string('note')->nullable();
            $table->string('user_id')->nullable();
            $table->string('date');
            $table->string('account');
            $table->string('type');
            $table->string('company_id');
            $table->timestamps();
            $table->index(['company_id', 'return_id', 'invoice_id']);
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
        Schema::dropIfExists('sale_returns');
    }
}
