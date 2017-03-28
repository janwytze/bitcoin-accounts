<?php

namespace Jwz104\BitcoinAccounts;

use Jwz104\BitcoinAccounts\Exceptions;

use Jwz104\BitcoinAccounts\Models\BitcoinUser;
use Jwz104\BitcoinAccounts\Models\BitcoinAddress;
use Jwz104\BitcoinAccounts\Models\BitcoinTransaction;
use Jwz104\BitcoinAccounts\Models\BitcoinHoldTransaction;

use Jwz104\BitcoinAccounts\Transaction\SingleTransaction;
use Jwz104\BitcoinAccounts\Transaction\HoldTransaction;

use Jwz104\BitcoinAccounts\Exceptions\CommandFailedException;
use Jwz104\BitcoinAccounts\Exceptions\LowBalanceException;
use Jwz104\BitcoinAccounts\Exceptions\InvalidAddressException;

class BitcoinAccounts {

    /**
     * The RPC connection
     *
     * @var \Nbobtc\Http\Client
     */
    protected $connection;

    /**
     * Instantiate a new HomepageController instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->connection = new \Nbobtc\Http\Client($this->getConnectionAddress());
    }

    /**
     * Get the connection address from config
     *
     * @return string
     */
    protected function getConnectionAddress()
    {
        $connectionconfig = config('bitcoinaccounts.connection');

        $url = sprintf('http://%s:%s@%s:%s',
            $connectionconfig['username'],
            $connectionconfig['password'],
            $connectionconfig['host'],
            $connectionconfig['port']
        );

        return $url;
    }

    /**
     * Execute a bitcoind command and get the output
     *
     * @param String $command The API command
     * @param mixed $params The command parameters
     * @return mixed
     */
    public function executeCommand($command, ...$params)
    {
        $command = new \Nbobtc\Command\Command($command, $params);
        $response = $this->connection->sendCommand($command);

        //Throw exception when command fails
        if ($response->getStatusCode() != 200) {
            throw new CommandFailedException($response->getStatusCode(), $response->getBody()->getContents());
        }

        return json_decode($response->getBody()->getContents(), true)['result'];
    }

    /**
     * Find an account by name
     *
     * @param $name string The account name
     * @return Jwz104\BitcoinAccounts\Models\BitcoinUser
     */
    public function getAccount($name)
    {
        return BitcoinUser::where('name', $name)->first();
    }

    /**
     * Find an account by id
     *
     * @param $id int The account id
     * @return Jwz104\BitcoinAccounts\Models\BitcoinUser
     */
    public function findAccount($id)
    {
        return BitcoinUser::find($id);
    }

    /**
     * Create an account and return it
     *
     * @param $name string The account name
     * @return Jwz104\BitcoinAccounts\Models\BitcoinUser
     */
    public function createAccount($name)
    {
        $user = new BitcoinUser();

        $user->name = $name;

        $user->save();

        //Auto create an address
        if (config('bitcoinaccounts.account.autocreate-address') == true) {
            $this->createAddress($user);
        }

        return $user;
    }

    /**
     * Return the account that belongs to the name or create one
     *
     * @param $name string The account name
     * @return Jwz104\BitcoinAccounts\Models\BitcoinUser
     */
    public function getOrCreateAccount($name)
    {
        if (($user = $this->getAccount($name)) != null) {
            return $user;
        }

        return $this->createAccount($name);
    }

    /**
     * Create an address for an account and return the id
     *
     * @param $user Jwz104\BitcoinAccounts\Models\BitcoinUser The user
     * @return Jwz104\BitcoinAccounts\Models\BitcoinAddress
     */
    public function createAddress(BitcoinUser $user = null)
    {
        $address = $this->executeCommand('getnewaddress');

        $bitcoinaddress = new BitcoinAddress();

        $bitcoinaddress->bitcoin_user_id = ($user == null) ? null : $user->id;
        $bitcoinaddress->address = $address;

        $bitcoinaddress->save();

        return $bitcoinaddress;
    }

    /**
     * Remove an account
     *
     * @param $user int The account
     * @return void
     */
    public function deleteAccount(BitcoinUser $user)
    {
        if ($user->transactions->count() > 0 || $user->addresses->count() > 0) {
            $user->delete();
        }
        //Force delete because this user doesn't have any relations
        $user->forceDelete();
    }

    /**
     * Merge account together, the second account will be removed,
     * and the addresses and balance will go to the first account.
     * Return true if successfull
     *
     * @param $id int The account id
     * @return bool
     */
    public function mergeAccount(BitcoinUser $user, BitcoinUser $mergeuser)
    {
        //Transfer all the address and transactions
        BitcoinAddress::where('bitcoin_user_id', $mergeuser->id)->update('bitcoin_user_id', $user->id);
        BitcoinTransaction::where('bitcoin_user_id', $mergeuser->id)->update(['bitcoin_user_id' => $user->id]);
        BitcoinTransaction::where('other_bitcoin_user_id', $mergeuser->id)->update(['other_bitcoin_user_id' => $user->id]);

        //Force delete because this user doesn't have any relations
        $mergeuser->forceDelete();
        return true;
    }

    /**
     * Set the user for an address and return true is successfull
     *
     * @param $user Jwz104\BitcoinAccounts\Models\BitcoinUser
     * @param $address Jwz104\BitcoinAccounts\Models\BitcoinAddress
     * @return bool
     */
    public function setAddressUser(BitcoinUser $user, BitcoinAddress $address)
    {
        $address->user_id = $user->id;
        $address->save();
        return true;
    }

    /**
     * Send bitcoins to an user
     *
     * @param $fromuser Jwz104\BitcoinAccounts\Models\BitcoinUser From user
     * @param $touser Jwz104\BitcoinAccounts\Models\BitcoinUser The destination user
     * @param $amount double The amount of bitcoins
     * @return bool
     */
    public function sendToUser(BitcoinUser $fromuser, BitcoinUser $touser, $amount)
    {
        if ($fromuser->balance() < $amount) {
            throw new LowBalanceException($fromuser);
        }

        $transaction = new BitcoinTransaction();

        $transaction->bitcoin_user_id = $fromuser->id;
        $transaction->amount = $amount;
        $transaction->type = 'account';
        $transaction->other_bitcoin_user_id = $touser->id;

        $transaction->save();
        return true;
    }

    /**
     * Send bitcoins to an address and return the txid
     * the amount of fee defined in the config file will be added to the amount
     *
     * @param $user Jwz104\BitcoinAccounts\Models\BitcoinUser From user
     * @param $address string To address
     * @param $amount double The amount of bitcoins
     * @param $fee double The amount of fee, leave empty for default amount
     * @return string
     */
    public function sendToAddress(BitcoinUser $user, $address, $amount, $fee = null)
    {
        if (!$this->validateAddress($address)) {
            throw new InvalidAddressException($address);
        }
        $transaction = new SingleTransaction($user, $address, $amount, $fee, $holdid);

        $transaction->create();
        $transaction->sign();
        return $transaction->send();
    }

    /**
     * Send the bitcoin to an address using a hold transaction
     *
     * @param $user Jwz104\BitcoinAccounts\Models\BitcoinUser From user
     * @param $type string The transaction type, "mass" or "single"
     * @param $address string To address
     * @param $amount double The amount of bitcoins
     * @param $fee double The amount of fee, leave empty for default amount
     * @return void
     */
    public function holdSendToAddress(BitcoinUser $user, $type, $address, $amount, $fee = null)
    {
        if (!$this->validateAddress($address)) {
            throw new InvalidAddressException($address);
        }
        $transaction = new HoldTransaction($user, $type, $address, $amount, $fee);
        $transaction->send();
    }

    /**
     * Get all removed accounts that have balance
     *
     * @return Jwz104\BitcoinAccounts\Models\BitcoinUser
     */
    public function getRemovedUsersWithBalance()
    {
        $deletedusers = BitcoinUser::onlyTrashed()->get();

        $balanceusers = [];

        foreach ($deletedusers as $deleteduser) {
            if ($user->balance() > 0) {
                $balanceusers[] = $deleteduser;
            }
        }

        return $balanceusers;
    }

    /**
     * Transfer all bitcoin of the user to an address and return the txid
     *
     * @param $user Jwz104\BitcoinAccounts\Models\BitcoinUser The user
     * @param $address string The address
     * @param $fee double The fee, null for default fee
     * @return string
     */
    public function emptyAccountToAddress(BitcoinUser $user, $address, $fee = null)
    {
        if ($this->validateAddress($address)) {
            throw new InvalidAddressException($address);
        }
        if ($fee == null) {
            $fee = config('bitcoinaccounts.bitcoin.transaction-fee');
        }

        $amount = ($user->balance() - $fee);
        
        if ($amount <= 0) {
            throw new LowBalanceException($user);
        }

        return $this->sendToAddress($user, $address, $amount, $fee);
    }

    /**
     * Transfer all bitcoin of the user to an user
     *
     * @param $user Jwz104\BitcoinAccounts\Models\BitcoinUser The user
     * @param $fromuser Jwz104\BitcoinAccounts\Models\BitcoinUser The user
     * @return string
     */
    public function emptyAccountToUser(BitcoinUser $user, BitcoinUser $touser)
    {
        if ($user->balance() <= 0) {
            throw new LowBalanceException($user);
        }

        return $this->sendToUser($user, $touser, $user->balance);
    }

    /**
     * Check if the given address is valid
     *
     * @param $address string The bitcoin address to check
     * @return boolean
     */
    public function validateAddress($address)
    {
        $alphabet = '123456789ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz';
        $out = array_fill(0, 25, 0);
        for ($i=0;$i<strlen($address);$i++) {
            if (($p=strpos($alphabet, $address[$i]))===false) {
                return false;
            }
            $c = $p;
            for ($j = 25; $j--;) {
                $c += (int)(58 * $out[$j]);
                $out[$j] = (int)($c % 256);
                $c /= 256;
                $c = (int)$c;
            }
            if ($c != 0) {
                return false;
            }
        }
        $decoded = '';
        foreach ($out as $val) {
            $decoded .= chr($val);
        }
        $d1 = hash('sha256', substr($decoded,0,21), true);
        $d2 = hash('sha256', $d1, true);
        if(substr_compare($decoded, $d2, 21, 4)){
            return false;
        }
        return true;
    }

    /**
     * List all the unspent bitcoins
     *
     * @param $listlocked boolean List the locked transactions
     * @return mixed
     */
    public function listUnspent($listlocked = false)
    {
        $unspent = $this->executeCommand('listunspent');
        if (!$listlocked) {
            $lockedunspent = $this->listLockedUnspent();
            /**
             * @todo Remove the locked unspent from the unspent
             */
        }
        return $unspent;
    }

    /**
     * Create raw transaction and return true if successfull
     *
     * @param $txids mixed[] The transaction ids from listunspent
     * @param $destination mixed[] The address and amount
     * @return bool
     */
    public function createRawTransaction($txids, $destination)
    {
        return $this->executeCommand('createrawtransaction', $txids, $destination);
    }

    /**
     * Lock unspent transactions
     *
     * @param $txids mixed[] The transaction ids from listunspent
     * @return bool
     */
    public function lockUnspent($txids)
    {
        return $this->executeCommand('lockunspent', false, $txids);
    }

    /**
     * Unlock unspent transactions
     *
     * @param $txids mixed[] The transaction ids from listunspent
     * @return bool
     */
    public function unlockUnspent($txids)
    {
        return $this->executeCommand('lockunspent', true, $txids);
    }

    /**
     * List the locked unspent transactions
     *
     * @return mixed[]
     */
    public function listLockedUnspent()
    {
        return $this->executeCommand('listlockunspent');
    }

    /**
     * Get a raw transaction
     *
     * @param $txid string The transaction id
     * @return mixed[]
     */
    public function getRawTransaction($txid)
    {
        return $this->executeCommand('getrawtransaction', $txid);
    }

    /**
     * Decode a raw transaction
     *
     * @param $rawtx string The transaction
     * @return mixed[]
     */
    public function decodeRawTransaction($rawtx)
    {
        return $this->executeCommand('decoderawtransaction', $rawtx);
    }

    /**
     * Sign a raw transaction
     *
     * @param $rawtx string The transaction
     * @return mixed[]
     */
    public function signRawTransaction($rawtx)
    {
        $signedtransaction = $this->executeCommand('signrawtransaction', $rawtx);
        return $signedtransaction['hex'];
    }

    /**
     * Send a raw transaction
     *
     * @param $signedrawtx string The transaction
     * @return mixed[]
     */
    public function sendRawTransaction($signedrawtx)
    {
        return $this->executeCommand('sendrawtransaction', $signedrawtx);
    }

    /**
     * List the transactions and filter out bitcoind user to user transactions
     *
     * @param $rawtx string The transaction
     * @return mixed[]
     */
    public function listTransactions($from, $amount)
    {
        $transactions = collect($this->executeCommand('listtransactions', '*', $amount, $from));
        return $transactions->where('category', '!=', 'move')->where('confirmations', '>=', config('bitcoinaccounts.bitcoin.confirmations'));
    }
}

