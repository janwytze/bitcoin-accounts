<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBitcoinAddressesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bitcoin_addresses', function (Blueprint $table) {
            $table->increments('id');

            $table->integer('bitcoin_user_id')->unsigned();
            $table->string('address', 35)->unique();

            $table->timestamps();
            $table->softDeletes();

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
        Schema::drop('bitcoin_users');
    }
}
