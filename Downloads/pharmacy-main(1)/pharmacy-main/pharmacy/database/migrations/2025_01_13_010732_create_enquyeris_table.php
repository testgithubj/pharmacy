<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEnquyerisTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('enquyeris', function (Blueprint $table) {
            $table->id();
            $table->string('Full_Name');
            $table->string('phone_number');
            $table->string('email');
            $table->string('address');
            $table->string('brnd')->nullable();
            $table->string('model_number')->nullable();
            $table->date('purchase_date')->nullable();
            $table->string('service_type')->nullable();
            $table->text('discription')->nullable();
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
        Schema::dropIfExists('enquyeris');
    }
}
