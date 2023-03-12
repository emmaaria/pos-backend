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
            $table->index('customer_id');
            $table->index('transaction_id');
            $table->index('reference_no')->nullable();
            $table->index('user_id')->nullable();
            $table->string('type');
            $table->string('due')->default(0);
            $table->string('deposit')->default(0);
            $table->index('date');
            $table->string('comment');
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
