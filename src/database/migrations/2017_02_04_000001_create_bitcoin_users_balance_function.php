<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBitcoinUsersBalanceFunction extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $procedure = '
            DROP FUNCTION IF EXISTS GetBitcoinUserBalance;
            CREATE FUNCTION "GetBitcoinUserBalance"(user_id INTEGER)
            RETURNS DECIMAL(20, 8)
            BEGIN
                DECLARE receive DECIMAL(20,8);
                DECLARE sent DECIMAL(20,8);
                DECLARE holdsent DECIMAL(20,8);
                SELECT COALESCE(SUM(amount),0) INTO receive FROM bitcoin_transactions
                    WHERE (bitcoin_user_id = user_id AND type = "receive") XOR (bitcoin_user_id != user_id AND other_bitcoin_user_id = user_id AND type = "account");
                SELECT (COALESCE(SUM(amount),0)+COALESCE(SUM(fee),0)) INTO sent FROM bitcoin_transactions
                    WHERE (bitcoin_user_id = user_id AND type = "send") XOR (bitcoin_user_id = user_id AND other_bitcoin_user_id != user_id AND type = "account");
                SELECT (COALESCE(SUM(amount),0)+COALESCE(SUM(fee),0)) INTO holdsent FROM bitcoin_hold_transactions
                    WHERE bitcoin_user_id = user_id;
                RETURN (receive-sent-holdsent);
            END
        ';
        \DB::unprepared($procedure);
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
        \DB::unprepared($procedure);
    }
}
