<?php

namespace Cego\SeamlessWallet;

use Carbon\Carbon;
use InvalidArgumentException;
use Cego\ServiceClientBase\AbstractServiceClient;
use Cego\SeamlessWallet\Paginators\TransactionsPaginator;
use Cego\ServiceClientBase\Exceptions\ServiceRequestFailedException;

/**
 * Class SeamlessWallet
 */
class SeamlessWallet extends AbstractServiceClient
{
    // Transaction endpoints
    public const TRANSACTION_ROLLBACK_ENDPOINT = '/api/v1/transactions/%s/rollback';

    // Wallet endpoints
    public const WALLET_DEPOSIT_ENDPOINT = '/api/v1/wallets/%s/deposit';
    public const WALLET_WITHDRAW_ENDPOINT = '/api/v1/wallets/%s/withdraw';
    public const WALLET_BALANCE_ENDPOINT = '/api/v1/wallets/%s/balance';
    public const WALLET_CREATE_ENDPOINT = '/api/v1/wallets/create';
    public const WALLET_TRANSACTIONS_ENDPOINT = '/api/v1/wallets/%s/transactions';

    // Metrics endpoints
    public const METRICS_SUM_OF_WALLET_BALANCES = '/api/v1/metrics/sum_of_wallet_balances';

    public string $playerId;

    /**
     * Creates the users wallet
     *
     * @param array $options
     *
     * @return self
     *
     * @throws ServiceRequestFailedException
     */
    public function createWallet($options = []): self
    {
        $this->guardAgainstMissingPlayerId();

        // If we already created this players wallet, simply bail out
        if (in_array($this->playerId, SeamlessWalletStore::$createdWallets)) {
            return $this;
        }

        $this->postRequest(self::WALLET_CREATE_ENDPOINT, [
            "player_id" => $this->playerId,
        ], $options);

        // We successfully created the wallet, save the player if so we do not try to recreate the wallet later.
        SeamlessWalletStore::$createdWallets[] = $this->playerId;

        return $this;
    }

    /**
     * Returns a wallet api instance linked to the given user
     *
     * @param string $playerId
     *
     * @return self
     */
    public function forPlayer(string $playerId): self
    {
        $this->playerId = $playerId;

        return $this;
    }

    /**
     * Returns the balance of the wallet
     *
     * If force fresh is set, then we will always make a request towards the seamless wallet even if we already know the balance value in static-store
     *
     * @param bool $forceFresh
     * @param array $options
     *
     * @return string|null
     *
     * @throws ServiceRequestFailedException
     */
    public function getBalance(bool $forceFresh = false, array $options = []): ?string
    {
        $this->guardAgainstMissingPlayerId();

        if ($this->shouldMakeBalanceRequest($forceFresh)) {
            SeamlessWalletStore::$balances[$this->playerId] = $this->getRequest(sprintf(self::WALLET_BALANCE_ENDPOINT, $this->playerId), $options)['balance'];
        }

        return SeamlessWalletStore::$balances[$this->playerId];
    }

    /**
     * Checks if we should perform a balance request, or if we can fetch the balance from static store
     *
     * @param bool $forceFresh
     *
     * @return bool
     */
    public function shouldMakeBalanceRequest(bool $forceFresh): bool
    {
        return $forceFresh || ! array_key_exists($this->playerId, SeamlessWalletStore::$balances);
    }

    /**
     * The inverse of shouldMakeBalanceRequest
     *
     * @param bool $forceFresh
     *
     * @return bool
     */
    public function shouldNotMakeBalanceRequest(bool $forceFresh): bool
    {
        return ! $this->shouldMakeBalanceRequest($forceFresh);
    }

    /**
     * Performs a deposit request to the wallet
     *
     * @param string|float|int $amount
     * @param string $transactionId
     * @param int $context
     * @param string|null $externalId
     * @param array $options
     *
     * @return string|null
     *
     * @throws ServiceRequestFailedException
     */
    public function deposit($amount, string $transactionId, int $context = 1, string $externalId = null, array $options = []): ?string
    {
        return $this->makeTransaction(self::WALLET_DEPOSIT_ENDPOINT, $amount, $transactionId, $context, $externalId, $options);
    }

    /**
     * Performs a deposit request to the wallet
     *
     * @param string|float|int $amount
     * @param string $transactionId
     * @param int $context
     * @param string|null $externalId
     * @param array $options
     *
     * @return string
     *
     * @throws ServiceRequestFailedException
     */
    public function withdraw($amount, string $transactionId, int $context = 1, string $externalId = null, array $options = []): ?string
    {
        return $this->makeTransaction(self::WALLET_WITHDRAW_ENDPOINT, $amount, $transactionId, $context, $externalId, $options);
    }

    /**
     * Creates a transaction in the players wallet
     *
     * @param string $endpoint
     * @param string|float|int $amount
     * @param string $transactionId
     * @param int $context
     * @param string|null $externalId
     * @param array $options
     *
     * @return string|null
     *
     * @throws ServiceRequestFailedException
     */
    protected function makeTransaction(string $endpoint, $amount, string $transactionId, int $context = 1, string $externalId = null, array $options = []): ?string
    {
        $this->guardAgainstMissingPlayerId();

        $requestData = [
            'amount'              => $amount,
            'transaction_id'      => $transactionId,
            'transaction_context' => $context,
        ];

        if ($externalId !== null) {
            $requestData['external_id'] = $externalId;
        }

        $response = $this->postRequest(sprintf($endpoint, $this->playerId), $requestData, $options);

        if ( ! $response->isSynchronous) {
            return null;
        }

        return SeamlessWalletStore::$balances[$this->playerId] = $response['balance'];
    }

    /**
     * Returns transactions belonging to a players wallet.
     *
     * FromDate and ToDate are required, but context and pagination size is optional.
     * Default pagination size is determined by the Service if no perPage size is given.
     *
     * @param Carbon $fromDate
     * @param Carbon $toDate
     * @param array $contexts
     * @param int $page
     * @param int|null $perPage
     * @param array $options
     *
     * @return TransactionsPaginator
     *
     * @throws ServiceRequestFailedException
     */
    public function getPaginatedTransactions(Carbon $fromDate, Carbon $toDate, array $contexts = [], int $page = 1, ?int $perPage = null, array $options = []): TransactionsPaginator
    {
        $this->guardAgainstMissingPlayerId();

        $queryParameters = [
            'from' => $fromDate->toDateString(),
            'to'   => $toDate->toDateString(),
            'page' => $page,
        ];

        if ($contexts) {
            $queryParameters['contexts'] = implode(',', $contexts);
        }

        if ($perPage) {
            $queryParameters['per_page'] = $perPage;
        }

        $response = $this->getRequest(sprintf(static::WALLET_TRANSACTIONS_ENDPOINT, $this->playerId), $queryParameters, $options);

        return new TransactionsPaginator(
            collect($response['transactions']),
            $this,
            $queryParameters,
        );
    }

    /**
     * Rollback the transaction
     *
     * @param string $transactionId
     * @param array $options
     *
     * @throws ServiceRequestFailedException
     */
    public function rollbackTransaction(string $transactionId, array $options = []): void
    {
        $this->postRequest(sprintf(self::TRANSACTION_ROLLBACK_ENDPOINT, $transactionId), $options);

        // Since we do not know who the transaction belongs to,
        // it means we have to clear all known balances.
        SeamlessWalletStore::$balances = [];
    }

    /**
     * Returns the full endpoint url
     *
     * @param string $endpoint
     *
     * @return string
     */
    protected function getFullEndpointUrl(string $endpoint): string
    {
        return sprintf('%s%s', $this->serviceBaseUrl, $endpoint);
    }

    /**
     * Guards against calling endpoints without first setting the userId
     */
    protected function guardAgainstMissingPlayerId(): void
    {
        /**
         * The debug_backtrace() returns the current callstack
         *
         * debug_backtrace()[0] refers to the current method => guardAgainstMissingUserId()
         * debug_backtrace()[1] refers to the place that called guardAgainstMissingUserId()
         */
        $calledMethod = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2)[1]["function"];

        if ( ! isset($this->playerId)) {
            throw new InvalidArgumentException(sprintf("UserId is not set - Make sure to call ->forPlayer() before ->%s()", $calledMethod));
        }
    }

    /**
     * Returns the sum of all wallet balances
     *
     * @param array $options
     *
     * @return string
     *
     * @throws ServiceRequestFailedException
     */
    public function getSumOfWalletBalances(array $options = []): string
    {
        if (SeamlessWalletStore::$sumOfWalletBalances == null) {
            SeamlessWalletStore::$sumOfWalletBalances = $this->getRequest(self::METRICS_SUM_OF_WALLET_BALANCES, $options)['sum'];
        }

        return SeamlessWalletStore::$sumOfWalletBalances;
    }
}
