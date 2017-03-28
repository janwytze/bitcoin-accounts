# bitcoin-accounts
An alternative to the bitcoind build-in account system for Laravel.
This package is developed because the bitcoind account system is deprecated.
With this package you can set the fee, so you know exacly what you send.
Account to account transactions are possible and don't cost any fee.

This package has an transaction table with all user transactions.
Transactions with addresses that are not registered will not be imported.

This package runs a cronjob every minute to import the transactions.


Please check out the wiki for installation and documentation
