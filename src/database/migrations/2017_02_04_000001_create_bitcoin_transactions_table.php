<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBitcoinTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bitcoin_transactions', function (Blueprint $table) {
            $table->increments('id');

            $table->integer('bitcoin_user_id')->unsigned();
            $table->integer('bitcoin_address_id')->unsigned()->nullable();
            $table->string('txid', 64)->nullable();
            $table->double('amount', 20, 8);
            $table->double('fee', 20, 8)->nullable();
            $table->enum('type', ['send', 'receive', 'account']);
            $table->string('other_address')->nullable();
            $table->integer('other_bitcoin_user_id')->unsigned()->nullable();
            $table->boolean('confirmed');

            $table->timestamps();

            $table->foreign('bitcoin_user_id')->references('id')->on('bitcoin_users');
            $table->foreign('bitcoin_address_id')->references('id')->on('bitcoin_addresses');
            $table->foreign('other_bitcoin_user_id')->references('id')->on('bitcoin_users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('bitcoin_transactions');
    }
}
