<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCustomerLedgersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('customer_ledgers', function (Blueprint $table) {
            $table->id();
            $table->string('customer_id');
            $table->index('customer_id');
            $table->string('transaction_id');
            $table->index('transaction_id');
            $table->string('reference_no')->nullable();
            $table->index('reference_no')->nullable();
            $table->string('user_id')->nullable();
            $table->index('user_id')->nullable();
            $table->string('type');
            $table->string('due')->default(0);
            $table->string('deposit')->default(0);
            $table->string('date');
            $table->index('date');
            $table->string('comment');
            $table->string('company_id');
            $table->index('company_id');
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
        Schema::dropIfExists('customer_ledgers');
    }
}
