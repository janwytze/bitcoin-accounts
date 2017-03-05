# bitcoin-accounts
A Laravel bitcoin account system

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
