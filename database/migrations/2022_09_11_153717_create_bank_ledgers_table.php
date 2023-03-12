<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBankLedgersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bank_ledgers', function (Blueprint $table) {
            $table->id();
            $table->string('bank_id');
            $table->string('transaction_id');
            $table->string('reference_no')->nullable();
            $table->string('comment')->nullable();
            $table->string('user_id')->nullable();
            $table->string('type');
            $table->string('withdraw')->default(0);
            $table->string('deposit')->default(0);
            $table->string('company_id')->nullable();
            $table->string('date');
            $table->timestamps();
            $table->index(['company_id', 'transaction_id', 'reference_no']);
            $table->index(['date','user_id', 'bank_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('bank_ledgers');
    }
}
