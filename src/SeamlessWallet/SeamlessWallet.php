<?php

namespace Cego\SeamlessWallet;

use Carbon\Carbon;
use InvalidArgumentException;
use Illuminate\Support\Collection;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Cego\SeamlessWallet\Paginators\TransactionsPaginator;
use Cego\SeamlessWallet\Exceptions\SeamlessWalletRequestFailedException;

/**
 * Class SeamlessWallet
 */
class SeamlessWallet
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

    // Endpoint & credentials
    protected string $serviceBaseUrl;
    protected string $username;
    protected string $password;

    /**
     * Private constructor to disallow using new
     *
     * @param string $serviceBaseUrl
     * @param string $username
     * @param string $password
     */
    protected function __construct(string $serviceBaseUrl, string $username, string $password)
    {
        // Validate data
        if (empty($serviceBaseUrl) || empty($username) || empty($password)) {
            throw new InvalidArgumentException("serviceBaseUrl, username, and password cannot be empty!");
        }

        $this->serviceBaseUrl = $serviceBaseUrl;
        $this->username = $username;
        $this->password = $password;
    }

    /**
     * Named constructor
     *
     * @param string $serviceBaseUrl
     * @param string $username
     * @param string $password
     *
     * @return static
     */
    public static function create(string $serviceBaseUrl, string $username, string $password): self
    {
        /** @phpstan-ignore-next-line */
        return new static($serviceBaseUrl, $username, $password);
    }

    /**
     * Creates the users wallet
     *
     * @return self
     *
     * @throws SeamlessWalletRequestFailedException
     */
    public function createWallet(): self
    {
        $this->guardAgainstMissingPlayerId();

        // If we already created this players wallet, simply bail out
        if (in_array($this->playerId, SeamlessWalletStore::$createdWallets)) {
            return $this;
        }

        $this->postRequest(self::WALLET_CREATE_ENDPOINT, [
            "player_id" => $this->playerId,
        ]);

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
     *
     * @return string
     *
     * @throws SeamlessWalletRequestFailedException
     */
    public function getBalance(bool $forceFresh = false): string
    {
        $this->guardAgainstMissingPlayerId();

        if ($this->shouldMakeBalanceRequest($forceFresh)) {
            SeamlessWalletStore::$balances[$this->playerId] = $this->getRequest(sprintf(self::WALLET_BALANCE_ENDPOINT, $this->playerId))['balance'];
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
     * Performs a deposit request to the wallet
     *
     * @param string|float|int $amount
     * @param string $transactionId
     * @param int $context
     * @param string|null $externalId
     *
     * @return string
     *
     * @throws InvalidArgumentException
     * @throws SeamlessWalletRequestFailedException
     */
    public function deposit($amount, string $transactionId, int $context = 1, string $externalId = null): string
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

        return SeamlessWalletStore::$balances[$this->playerId] = $this->postRequest(sprintf(self::WALLET_DEPOSIT_ENDPOINT, $this->playerId), $requestData)['balance'];
    }

    /**
     * Performs a deposit request to the wallet
     *
     * @param string|float|int $amount
     * @param string $transactionId
     * @param int $context
     * @param string|null $externalId
     *
     * @return string
     *
     * @throws InvalidArgumentException
     * @throws SeamlessWalletRequestFailedException
     */
    public function withdraw($amount, string $transactionId, int $context = 1, string $externalId = null): string
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

        return SeamlessWalletStore::$balances[$this->playerId] = $this->postRequest(sprintf(self::WALLET_WITHDRAW_ENDPOINT, $this->playerId), $requestData)['balance'];
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
     *
     * @return TransactionsPaginator
     *
     * @throws SeamlessWalletRequestFailedException
     */
    public function getPaginatedTransactions(Carbon $fromDate, Carbon $toDate, array $contexts = [], int $page = 1, ?int $perPage = null): TransactionsPaginator
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

        $response = $this->getRequest(sprintf(static::WALLET_TRANSACTIONS_ENDPOINT, $this->playerId), $queryParameters);

        return new TransactionsPaginator(
            collect($response->get('transactions')),
            $this,
            $queryParameters,
        );
    }

    /**
     * Rollback the transaction
     *
     * @param string $transactionId
     *
     * @throws SeamlessWalletRequestFailedException
     */
    public function rollbackTransaction(string $transactionId): void
    {
        $this->postRequest(sprintf(self::TRANSACTION_ROLLBACK_ENDPOINT, $transactionId));

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
        return sprintf('%s/%s', $this->serviceBaseUrl, $endpoint);
    }

    /**
     * Performs a post request
     *
     * @param string $endpoint
     * @param array $data
     *
     * @return array
     *
     * @throws SeamlessWalletRequestFailedException
     */
    protected function postRequest(string $endpoint, array $data = []): array
    {
        return $this->makeRequest('post', $endpoint, $data)->json();
    }

    /**
     * Performs a get request
     *
     * @param string $endpoint
     * @param array $queryParameters
     *
     * @return Collection
     *
     * @throws SeamlessWalletRequestFailedException
     */
    protected function getRequest(string $endpoint, array $queryParameters = []): Collection
    {
        return collect($this->makeRequest('get', $endpoint, $queryParameters)->json());
    }

    /**
     * Makes a request to the service
     *
     * @param string $method
     * @param string $endpoint
     * @param array $data
     *
     * @return Response
     *
     * @throws SeamlessWalletRequestFailedException
     */
    protected function makeRequest(string $method, string $endpoint, array $data = []): Response
    {
        $maxTries = env("SEAMLESS_WALLET_CLIENT_MAXIMUM_NUMBER_OF_RETRIES", 3);
        $try = 0;

        do {
            /** @var Response $response */
            $response = Http::withBasicAuth($this->username, $this->password)
                            ->asJson()      // Content-type header
                            ->acceptJson()  // Accept header
                            ->timeout(env("SEAMLESS_WALLET_CLIENT_TIMEOUT", 1))
                            ->$method($this->getFullEndpointUrl($endpoint), $data);

            // Bailout if successful
            if ($response->successful()) {
                return $response;
            }

            // Do not retry client errors
            if ($response->clientError()) {
                throw new SeamlessWalletRequestFailedException($response);
            }

            // Wait 1 sec before trying again, if server error
            usleep(env("SEAMLESS_WALLET_CLIENT_RETRY_DELAY", 1000000));
            $try++;
        } while ($try < $maxTries);

        throw new SeamlessWalletRequestFailedException($response);
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
     * @return string
     *
     * @throws SeamlessWalletRequestFailedException
     */
    public function getSumOfWalletBalances(): string
    {
        if (SeamlessWalletStore::$sumOfWalletBalances == null) {
            SeamlessWalletStore::$sumOfWalletBalances = $this->getRequest(self::METRICS_SUM_OF_WALLET_BALANCES)['sum'];
        }

        return SeamlessWalletStore::$sumOfWalletBalances;
    }
}
