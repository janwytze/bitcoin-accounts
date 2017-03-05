# bitcoin-accounts
An alternative to the bitcoind build-in account system.
This package is developed because the bitcoind account system is deprecated.
With this package you can set the fee, so you know exacly what you send.
Account to account transactions are possible and don't cost any fee.

This package has an transaction table with all user transactions.
Transactions with addresses that are not registered will not be imported.


## Installation

Add this to your composer.json
```
{
    "require": {
        "jwz104/bitcoin-accounts": "^1.0.0"
    }
}
```

Add the service provider to your `config/app.php` file:
`Jwz104\BitcoinAccounts\BitcoinAccountsServiceProvider::class,`

Add the facade to your `config/app.php` file:
`'Bitcoin' => Jwz104\BitcoinAccounts\Facades\BitcoinAccounts::class,`

Run a database migration:
`php artisan migrate`

Create the config file:
`php artisan vendor:publish --provider="Jwz104\BitcoinAccounts\BitcoinAccountsServiceProvider"`

Add your bitcoind server credentials in the `.env` file:
```
BITCOIN_IP=127.0.0.1
BITCOIN_PORT=8332
BITCOIN_USER=yourusername
BITCOIN_PASS=yourpassword
```

## Basic usage

You can do almost everything with the Facade.

These are all the functions from the Facade:

### executeCommand($command, ...$params)
With executeCommand you can execute a bitcoind command the basic user won't need this command.
The `$command` parameter contains the command, and the `$params` parameter contains the command parameters

### getAccount($name)
With the getAccount function you can get an account object by name.

### findAccount($id)
With the findAccount function you can get an account object by id

### createAccount($name)
With the createAccount function you can create an account.
The BitcoinUser object will be returned.
And an address will be created if enabled in the config.

### getOrCreateAccount($name)
With the createAccount function you can get an account by name.
If the account doesn't exists it will be created.

### createAddress(BitcoinUser $user)
With the createAddress function you can create an address for an user.
The `$user` parameter contains the BitcoinUser object

### deleteAccount(BitcoinUser $user)
With the delete account function you can delete an account.
If the account has any relations, it will be softdeleted.
If it doesn't have relations it will be harddeleted.

### mergeAccount(BitcoinUser $user, BitcoinUser $mergeuser)
With the mergeAccount function you can merge to accounts.
The main account will receive all the balance and addresses.
The other user will be fully deleted.
The `$mergeuser` parameter is the one that will get removed.

### setAddressUser(BitcoinUser $user, BitcoinAddress $address)
With the setAddressUser function you can give an address to an user.
The previous owner will still keep the balance.
But all future transactions will go to the current owner.

### sendToUser(BitcoinUser $fromuser, BitcoinUser $touser, $amount)
With the sendToUser function you can send bitcoin to another user.
There will be no fee.

### sendToAddress(BitcoinUser $user, $address, $amount, $fee)
With the sendToAddress command you can send bitcoins to an address.
If the fee is left empty it will load the fee from the config file.

### The following functions are not neccesary for most user.

### listUnspent()
List all the unspent transactions.

### createRawTransaction($txids, $destination)
Create a raw transaction.

### decodeRawTransaction($rawtx)
Decode a raw transaction

### signRawTransaction($rawtx)
Sign a raw transaction

### listTransactions($from, $amount)
List the transactions
