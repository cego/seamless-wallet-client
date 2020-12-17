# SeamlessWallet PHP Client

Project was initially created by:

- Niki Ewald Zakariassen (NIZA)
- Nikolaj Boel Jensen (NBJ)

## Usage
The seamless wallet client implements a fluid interface for interacting with the seamless wallet service.

### Interface
```php
// Getting a client instance
$seamlessWallet = SeamlessWallet::create(/* < credentials Here >*/);

// Setting the target player
$seamlessWallet->forPlayer($playerId);
```

```php
// Creating a user wallet
SeamlessWallet::create(/* < credentials Here >*/)
              ->forPlayer($playerId)
              ->createWallet();
```

```php
// Deposits / withdraws / balance / rollback
SeamlessWallet::create(/* < credentials Here >*/)
              ->forPlayer($playerId)
              ->deposit(100, "UUID6" /*, $transaction_context, $external_id */);

SeamlessWallet::create(/* < credentials Here >*/)
              ->forPlayer($playerId)
              ->withdraw(20, "UUID6" /*, $transaction_context, $external_id */);

SeamlessWallet::create(/* < credentials Here >*/)
              ->forPlayer($playerId)
              ->getBalance();

SeamlessWallet::create(/* < credentials Here >*/)
              ->rollbackTransaction("UUID6");
```
<sub>Note: After calling ->forPlayer() the id is kept in memory for later use</sub>

### Error Handling

The client does not use error return values, meaning if a request failed then an exception will be thrown: [SeamlessWalletRequestFailedException.php](src/SeamlessWallet/Exceptions/SeamlessWalletRequestFailedException.php).

The client has a configurable amount of retries on server errors, before throwing an exception.
- env("SEAMLESS_WALLET_CLIENT_MAXIMUM_NUMBER_OF_RETRIES")
- env("SEAMLESS_WALLET_CLIENT_TIMEOUT")
