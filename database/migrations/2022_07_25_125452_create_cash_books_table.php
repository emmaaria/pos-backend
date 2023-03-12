<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCashBooksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cash_books', function (Blueprint $table) {
            $table->id();
            $table->string('transaction_id');
            $table->string('reference_no')->nullable();
            $table->string('user_id')->nullable();
            $table->string('comment')->nullable();
            $table->string('type');
            $table->string('payment')->default(0);
            $table->string('receive')->default(0);
            $table->string('company_id')->nullable();
            $table->string('date');
            $table->timestamps();
            $table->index(['company_id', 'transaction_id', 'reference_no']);
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
        Schema::dropIfExists('cash_books');
    }
}
