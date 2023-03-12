<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateExpensesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->string('expense_id');
            $table->string('category');
            $table->string('note')->nullable();
            $table->string('user_id')->nullable();
            $table->string('account');
            $table->string('amount');
            $table->string('date');
            $table->string('company_id');
            $table->timestamps();
            $table->index(['company_id', 'expense_id', 'category']);
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
        Schema::dropIfExists('expenses');
    }
}
