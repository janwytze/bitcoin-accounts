<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

use DB;

class CreateBitcoinTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $procedure = '
            DELIMITER //
                CREATE FUNCTION GetBitcoinUserBalance(user_id INTEGER)
                RETURNS DECIMAL(20, 8)
                BEGIN
                    DECLARE receive DECIMAL(20,8);
                    DECLARE sent DECIMAL(20,8);
                    SELECT COALESCE(SUM(amount),0) INTO receive FROM bitcoin_transactions WHERE (bitcoin_user_id = user_id AND type = "receive") XOR (bitcoin_user_id != user_id && other_bitcoin_user_id = user_id AND type = "account");
                    SELECT (COALESCE(SUM(amount),0)+COALESCE(SUM(fee),0)) INTO sent FROM bitcoin_transactions WHERE (bitcoin_user_id = user_id AND type = "send") XOR (bitcoin_user_id = user_id && other_bitcoin_user_id != user_id AND type = "account");
                    RETURN (receive-sent);
                END//
            DELIMITER ;
        ';
        DB::unprepared($procedure);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $procedure = '
            DROP FUNCTION IF EXISTS GetBitcoinUserBalance;
        ';
        DB::unprepared($procedure);
    }
}
