<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCompaniesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->string('company_id');
            $table->string('name')->nullable();
            $table->string('address')->nullable();
            $table->string('mobile')->nullable();
            $table->string('email')->nullable();
            $table->text('logo')->nullable();
            $table->tinyInteger('stock_over_selling')->default(0);
            $table->string('vat_number')->nullable();
            $table->string('mushok_number')->nullable();
            $table->string('contact_mobile')->nullable();
            $table->string('status')->nullable();
            $table->string('payment_term')->nullable();
            $table->string('expiry_date')->nullable();
            $table->string('discount_type')->default('invoice');
            $table->string('customer_based_price')->default('no');
            $table->timestamps();
            $table->index(['company_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('companies');
    }
}
