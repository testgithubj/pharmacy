<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBanktransTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('banktrans', function (Blueprint $table) {
            $table->id();
            $table->string('date');
            $table->string('paymentmethord_id');
            $table->string('bank_id');
            $table->string('amount');
            $table->string('serialnumber');
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
        Schema::dropIfExists('banktrans');
    }
}
