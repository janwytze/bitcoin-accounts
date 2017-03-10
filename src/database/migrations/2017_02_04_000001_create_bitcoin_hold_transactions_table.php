<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBitcoinHoldTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bitcoin_hold_transactions', function (Blueprint $table) {
            $table->increments('id');

            $table->integer('bitcoin_user_id')->unsigned();
            $table->string('address', 35);
            $table->double('amount', 20, 8);
            $table->double('fee', 20, 8);
            $table->boolean('reserved')->default(false);
            $table->enum('type', ['mass', 'single']);

            $table->timestamps();

            $table->foreign('bitcoin_user_id')->references('id')->on('bitcoin_users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('bitcoin_hold_transactions');
    }
}
