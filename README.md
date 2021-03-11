# SeamlessWallet PHP Client

Project was initially created by:

- Niki Ewald Zakariassen (NIZA)
- Nikolaj Boel Jensen (NBJ)

## Usage
The seamless wallet client implements a fluid interface for interacting with the seamless wallet service.

### Interface
```php
// Getting a client instance
$seamlessWallet = SeamlessWallet::create('base_url')
    ->auth(/* < Credentials > */);

// Setting the target player
$seamlessWallet->forPlayer($playerId);
```

```php
// Creating a user wallet
SeamlessWallet::create(/* < Base Url > */)
    ->auth(/* < Credentials > */)
    ->forPlayer($playerId)
    ->createWallet();
```

```php
// Deposits / withdraws / balance / rollback
SeamlessWallet::create(/* < Base Url > */)
    ->auth(/* < Credentials > */)
    ->forPlayer($playerId)
    ->deposit(100, "UUID6" /*, $transaction_context, $external_id */);

SeamlessWallet::create(/* < Base Url > */)
    ->auth(/* < Credentials > */)
    ->forPlayer($playerId)
    ->withdraw(20, "UUID6" /*, $transaction_context, $external_id */);

SeamlessWallet::create(/* < Base Url > */)
    ->auth(/* < Credentials > */)
    ->forPlayer($playerId)
    ->getBalance();

SeamlessWallet::create(/* < Base Url > */)
    ->auth(/* < Credentials > */)
    ->rollbackTransaction("UUID6");
```

```php
// Using request insurance
SeamlessWallet::create(/* < Base Url > */)
    ->auth(/* < Credentials > */)
    ->useRequestInsurance()
    ->forPlayer($playerId)
    ->deposit(100, "UUID6" /*, $transaction_context, $external_id */);
```

<sub>Note: After calling ->forPlayer() the id is kept in memory for later use</sub>
\
<sub>Note: Request insurance is only usable for POST requests, and is remembered for following requests. GET requests will always use the synchronous HTTP driver</sub>


### Error Handling

The client does not use error return values, meaning if a request failed then an exception will be thrown: [SeamlessWalletRequestFailedException.php](src/SeamlessWallet/Exceptions/SeamlessWalletRequestFailedException.php).

The client has a configurable amount of retries on server errors, before throwing an exception.
- env("SEAMLESS_WALLET_CLIENT_MAXIMUM_NUMBER_OF_RETRIES")
- env("SEAMLESS_WALLET_CLIENT_TIMEOUT")
