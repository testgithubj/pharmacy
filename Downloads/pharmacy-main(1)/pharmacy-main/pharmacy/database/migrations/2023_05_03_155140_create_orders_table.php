<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_id');
            $table->integer('customer_id');
            $table->double('sub_total', 2);
            $table->double('total_discount',2);
            $table->double('total_amount',2);
            $table->integer('delivery_address_id');
            $table->string('payment_method');
            $table->string('delivery_method');
            $table->double('delivery_fee');
            $table->string('tran_id')->nullable();
            $table->integer('total_quantity');
            $table->enum('order_status', ['pending', 'placed_order', 'approved', 'cancel', 'delivered', 'hold'])->default('pending');
            $table->enum('payment_status', ['paid', 'unpaid','canceled','failed'])->default('unpaid');
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
        Schema::dropIfExists('orders');
    }
}
