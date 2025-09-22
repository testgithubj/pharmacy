<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->string('tran_id')->unique();
            $table->date('date');
            $table->foreignId('debit_account_id')->nullable()->constrained('accounts');
            $table->foreignId('credit_account_id')->nullable()->constrained('accounts');
            $table->double('amount');
            $table->string('invoice_type')->nullable();
            $table->string('invoice_id')->nullable();
            $table->text('particular')->nullable();
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
        Schema::dropIfExists('transactions');
    }
}
