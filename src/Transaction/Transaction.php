<?php

namespace Jwz104\BitcoinAccounts\Transaction;

use Jwz104\BitcoinAccounts\Models\BitcoinUser;
use Jwz104\BitcoinAccounts\Models\BitcoinAddress;
use Jwz104\BitcoinAccounts\Models\BitcoinTransaction;

use Jwz104\BitcoinAccounts\Facades\BitcoinAccounts;

use Jwz104\BitcoinAccounts\Exceptions\LowBalanceException;
use Jwz104\BitcoinAccounts\Exceptions\LowUnspentException;
use Jwz104\BitcoinAccounts\Exceptions\InvalidTransactionException;

class Transaction {

    /**
     * The destination address
     *
     * @var string
     */
    protected $address;

    /**
     * The amount of bitcoins
     *
     * @var double
     */
    protected $amount;

    /**
     * The amount of fee
     *
     * @var double
     */
    protected $fee;

    /**
     * The amount of bitcoins from the full transaction
     *
     * @var double
     */
    protected $transactionamount;

    /**
     * The bitcoin user
     *
     * @var Jwz104\BitcoinAccounts\Models\BitcoinUser
     */
    protected $bitcoinuser;

    /**
     * The raw transaction id
     *
     * @var string
     */
    protected $rawtx;

    /**
     * The signed raw transaction id
     *
     * @var string
     */
    protected $signedrawtx;

    /**
     * The Transaction id of a sended transaction
     *
     * @var string
     */
    protected $txid;

    /**
     * The outgoing transactions
     *
     * @var array
     */
    protected $txout;

    /**
     * Is the unspent transaction locked
     * It is set after the created function
     *
     * @var boolean
     */
    protected $locked;

    /**
     * Instantiate a new Transaction instance.
     *
     * @param $bitcoinser Jwz104\BitcoinAccounts\Models\BitcoinUser The bitcoin user
     * @param $address string The destination address
     * @param $amount double The amount of bitcoins
     * @param $fee double The amount of fee, When null or empty use the fee of the config file
     * @return void
     */
    public function __construct(BitcoinUser $bitcoinuser, $address, $amount, $fee = null)
    {
        if ($fee == null) {
            $fee = config('bitcoinaccounts.bitcoin.transaction-fee');
        }
        $this->bitcoinuser = $bitcoinuser;
        $this->address = $address;
        $this->amount = $amount;
        $this->fee = $fee;

        $this->check();
    }

    /**
     * Check if the transaction is valid and if the balance is high enough
     *
     * @throws Jwz104\BitcoinAccounts\Exceptions\LowBalanceException Thrown when balance is to low
     * @throws Jwz104\BitcoinAccounts\Exceptions\InvalidTransactionException Thrown when transaction is invalid
     * @return void
     */
    protected function check()
    {
        //If the transation doesn't contain any bitcoins throw exception
        if ($this->amount <= 0 && $this->fee <= 0) {
            throw new InvalidTransactionException();
        }
        if ($this->amount < 0 || $this->fee < 0) {
            throw new InvalidTransactionException();
        }

        //Throw low balance exception when balance is to low
        if ($this->bitcoinuser->balance() < ($this->amount+$this->fee)) {
            throw new LowBalanceException($this->bitcoinuser);
        }
    }

    /**
     * Create the transaction and return the raw transaction
     * Return null if the transaction doesn't contain any bitcoins
     *
     * @param $lockunspent boolean Lock the select unspent transactions
     * @return string
     */
    public function create($lockunspent = true)
    {
        $this->check();
        $this->locked = $lockunspent;

        //Get all the unspent transactions
        $unspent = collect(BitcoinAccounts::listUnspent())
            ->where('spendable', true)
            ->sortByDesc('amount');

        $txout = [];
        $amount = ($this->amount+$this->fee);
        $total = 0;

        //Get the required amount of txout to create the transaction
        foreach ($unspent as $transaction) {
            $txout[] = [
                'txid' => $transaction['txid'], 
                'vout' => $transaction['vout'], 
            ];
            $total += $transaction['amount'];
            $amount-=$transaction['amount'];
            //Keep going untill there are enough bitcoins
            if ($amount <= 0) {
                break;
            }
        }
        $this->txout = $txout;

        //Check if there is enough balance in unspent
        if ($amount > 0) {
            throw new LowUnspentException();
        }

        //Calculate what is paid to much
        $change = $total - ($this->amount+$this->fee);

        $this->transactionamount = $total;

        //This is the address where the change goes, it is not linked to an account
        $changeaddress = BitcoinAddress::where('bitcoin_user_id', null)->first();
        if ($changeaddress == null) {
            $changeaddress = BitcoinAccounts::createAddress();
        } else {
            $changeaddress = $changeaddress->address;
        }

        //Create the raw transaction
        $rawtx = BitcoinAccounts::createRawTransaction($this->txout, [$this->address => $this->amount, $changeaddress => $change]);

        if ($this->locked) {
            BitcoinAccounts::lockUnspent($this->txout);
        }

        return ($this->rawtx = $rawtx);
    }

    /**
     * Unlock the unspent transaction
     * Use when you want to cancel the transaction
     * Only needed when the $locked variable is true
     *
     * @return void
     */
    public function unlock()
    {
        BitcoinAccounts::unlockUnspent($this->txout);
    }

    /**
     * Decode raw transaction
     *
     * @return mixed[]
     */
    public function decode()
    {
        if ($this->rawtx == null) {
            return null;
        }
        return BitcoinAccounts::decodeRawTransaction($this->rawtx);
    }

    /**
     * Sign the transaction and return signed tx
     *
     * @return string
     */
    public function sign()
    {
        if ($this->rawtx == null) {
            return null;
        }
        return ($this->signedrawtx = BitcoinAccounts::signRawTransaction($this->rawtx));
    }

    /**
     * Send the transaction and return the transaction id
     *
     * @return string
     */
    public function send()
    {
        $this->check();
        if ($this->signedrawtx == null) {
            return null;
        }

        $txid = ($this->txid = BitcoinAccounts::sendRawTransaction($this->signedrawtx));
        

        //Create the transaction
        $bitcointransaction = new BitcoinTransaction();

        $bitcointransaction->bitcoin_user_id = $this->bitcoinuser->id;
        $bitcointransaction->txid = $txid;
        $bitcointransaction->amount = $this->amount;
        $bitcointransaction->fee = $this->fee;
        $bitcointransaction->type = 'send';
        $bitcointransaction->other_address = $this->address;

        $bitcointransaction->save();

        //The change transaction doesn't have to be created because it isn't attached to an user

        return $txid;
    }
}
