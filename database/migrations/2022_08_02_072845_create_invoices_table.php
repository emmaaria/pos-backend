<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInvoicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->string('customer_id');
            $table->string('invoice_id');
            $table->string('total');
            $table->string('comment')->nullable();
            $table->string('date');
            $table->string('discount')->nullable();
            $table->string('discountAmount')->nullable();
            $table->string('user_id')->nullable();
            $table->string('discountType')->nullable();
            $table->string('paid_amount')->default(0);
            $table->string('payment_method')->default('cash');
            $table->string('grand_total');
            $table->string('discount_setting');
            $table->string('company_id')->nullable();
            $table->timestamps();
            $table->index(['company_id', 'customer_id', 'invoice_id']);
            $table->index(['date','user_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('invoices');
    }
}
