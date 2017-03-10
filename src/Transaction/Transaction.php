<?php

namespace Jwz104\BitcoinAccounts\Transaction;

use Jwz104\BitcoinAccounts\Models\BitcoinUser;
use Jwz104\BitcoinAccounts\Models\BitcoinAddress;
use Jwz104\BitcoinAccounts\Models\BitcoinTransaction;

use Jwz104\BitcoinAccounts\Transaction\TransactionLine;

use Jwz104\BitcoinAccounts\Exceptions\InvalidTransactionException;
use Jwz104\BitcoinAccounts\Exceptions\LowUnspentException;

use Jwz104\BitcoinAccounts\Facades\BitcoinAccounts;

class Transaction {

    /**
     * The a
     *
     * @var Jwz104\BitcoinAccounts\Transfer\TransactionLine[]
     */
    protected $transactionlines;

    /**
     * The transaction transaction fee, this is the sum of all destination fees
     *
     * @var mixed[]
     */
    protected $fee;

    /**
     * Lock the unspent transactions after create
     *
     * @var boolean
     */
    protected $locked;

    /**
     * The total transaction amount, without fees and change
     *
     * @var double
     */
    protected $amount;

    /**
     * The amount of money to send back
     *
     * @var double
     */
    protected $change;

    /**
     * The total transaction amount, so with change and fees
     *
     * @var double
     */
    protected $transactionamount;

    /**
     * The raw tx
     * This is created after calling the create function
     *
     * @var string
     */
    protected $rawtx;

    /**
     * The signed raw tx
     * This is created after calling the sign function
     *
     * @var string
     */
    protected $signedrawtx;

    /**
     * The outgoing unspent transactions
     * This is created after the transaction is created
     *
     * @var mixed[]
     */
    protected $txout;

    /**
     * The transaction id of the transaction
     * This is created after the transaction id sent
     *
     * @var string
     */
    protected $txid;

    /**
     * Instantiate a new Transaction instance.
     *
     * @param $destinations Jwz104\BitcoinAccounts\Transfer\TransactionLine[] The destinations
     * @return void
     */
    public function __construct($transactionlines = [])
    {
        foreach ($transactionlines as $transactionline) {
            if (!($transactionline instanceof TransactionLine)) {
                throw new InvalidTransactionException();
            }
            $transactionline->check();
        }

        $this->transactionlines = $transactionlines;
    }

    /**
     * Add a destination
     * 
     * @param $destination Jwz104\BitcoinAccounts\Transfer\TransactionLine The destination
     */
    public function addLine(TransactionLine $transactionline)
    {
        $this->transactionlines[] = $transactionline;
    }

    /**
     * Set the unspent outgoing transactions
     *
     * @return void
     */

    /**
     * Create the transaction
     *
     * @param $locked boolean
     * @return string
     */
    public function create($lockunspent = true)
    {
        $this->locked = $lockunspent;
        $this->calculate();
        $this->setTxout();

        //Set the change transaction
        $transactions = [$this->getChangeAddress() => $this->change];

        //Add the other transactions
        foreach ($this->transactionlines as $transactionline) {
            $transactions[$transactionline->address] = $transactionline->amount;
        }

        $rawtx = BitcoinAccounts::createRawTransaction($this->txout, $transactions);

        if ($this->locked) {
            BitcoinAccounts::lockUnspent($this->txout);
        }

        return ($this->rawtx = $rawtx);
    }

    /**
     * Calculate the transaction totals, fee and txout
     *
     * @return void
     */
    protected function calculate()
    {
        $fee = 0;
        $amount = 0;
        //Loop though the transactionlines to get the amount and fee
        foreach ($this->transactionlines as $transactionline) {
            $fee += $transactionline->fee;
            $amount += $transactionline->amount;
        }
        $this->fee = $fee;
        $this->amount = $amount;
    }

    /**
     * Set the unspent transactions needed for the transaction
     *
     * @return void
     */
    protected function setTxout()
    {
        //Get all the unspent transactions
        $unspent = collect(BitcoinAccounts::listUnspent())
            ->where('spendable', true)
            ->sortByDesc('amount');

        $txout = [];
        $amount = ($this->amount+$this->fee);
        $total = 0;
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

        $this->transactionamount = $total;

        //Check if there is enough balance in unspent
        if ($amount > 0) {
            throw new LowUnspentException();
        }

        //Calculate what is paid to much
        $this->change = $this->transactionamount - ($this->amount+$this->fee);
        $this->txout = $txout;
    }

    /**
     * Get an address to send to change to
     * An address without an user attached
     *
     * @return string
     */
    public function getChangeAddress()
    {
        $changeaddress = BitcoinAddress::where('bitcoin_user_id', null)->first();
        if ($changeaddress == null) {
            $changeaddress = BitcoinAccounts::createAddress();
        } else {
            $changeaddress = $changeaddress->address;
        }
        return $changeaddress;
    }

    /**
     * Is the transaction created
     *
     * @return boolean
     */
    public function isCreated()
    {
        return ($this->rawtx != null);
    }

    /**
     * Check if the transaction and lines are valid
     *
     * @return void
     */
    public function check()
    {
        foreach ($this->transactionlines as $transactionline) {
            $transactionline->check();
        }
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
        if ($this->signedrawtx == null) {
            return null;
        }

        $this->check();

        $txid = ($this->txid = BitcoinAccounts::sendRawTransaction($this->signedrawtx));

        foreach ($this->transactionlines as $transactionline) {
            $bitcointransaction = new BitcoinTransaction();

            $bitcointransaction->bitcoin_user_id = $transactionline->bitcoinuser->id;
            $bitcointransaction->txid = $txid;
            $bitcointransaction->amount = $transactionline->amount;
            $bitcointransaction->fee = $transactionline->fee;
            $bitcointransaction->type = 'send';
            $bitcointransaction->other_address = $transactionline->address;

            $bitcointransaction->save();
        }

        return $txid;
    }
}
