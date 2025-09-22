<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePharmacyExpensesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pharmacy_expenses', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->string('title');
            $table->foreignId('category_id')->constrained('expense_categories');
            $table->decimal('amount')->default(0);
            $table->string('reference')->nullable();
            $table->string('note')->nullable();
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
        Schema::dropIfExists('pharmacy_expenses');
    }
}
