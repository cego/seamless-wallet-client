<?php

namespace Cego\SeamlessWallet;

use InvalidArgumentException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Cego\SeamlessWallet\Exceptions\SeamlessWalletRequestFailedException;

/**
 * Class SeamlessWallet
 */
class SeamlessWallet
{
    // Transaction endpoints
    public const TRANSACTION_ROLLBACK_ENDPOINT = '/api/v1/transactions/%s/rollback';

    // Wallet endpoints
    public const WALLET_DEPOSIT_ENDPOINT = '/api/v1/wallet/%s/deposit';
    public const WALLET_WITHDRAW_ENDPOINT = '/api/v1/wallet/%s/withdraw';
    public const WALLET_BALANCE_ENDPOINT = '/api/v1/wallet/%s/balance';

    public string $userId;

    // Endpoint & credentials
    private string $serviceBaseUrl;
    private string $username;
    private string $password;

    /**
     * Private constructor to disallow using new
     *
     * @param string $serviceBaseUrl
     * @param string $username
     * @param string $password
     */
    private function __construct(string $serviceBaseUrl, string $username, string $password)
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
     * @return self
     */
    public static function create(string $serviceBaseUrl, string $username, string $password): self
    {
        return new self($serviceBaseUrl, $username, $password);
    }

    /**
     * Returns a wallet api instance linked to the given user
     *
     * @param string $userId
     *
     * @return self
     */
    public function forUser(string $userId): self
    {
        $this->userId = $userId;

        return $this;
    }

    /**
     * Returns the balance of the wallet
     *
     * @return string
     *
     * @throws SeamlessWalletRequestFailedException
     */
    public function getBalance(): string
    {
        $this->guardAgainstMissingUserId();

        return $this->getRequest(sprintf(self::WALLET_BALANCE_ENDPOINT, $this->userId))['amount'];
    }

    /**
     * Performs a deposit request to the wallet
     *
     * @param string|float|int $amount
     * @param string $transactionId
     * @param int|null $context
     * @param string|null $externalId
     *
     * @throws InvalidArgumentException
     * @throws SeamlessWalletRequestFailedException
     */
    public function deposit($amount, string $transactionId, int $context = null, string $externalId = null): void
    {
        $this->guardAgainstMissingUserId();

        $requestData = [
            'amount'              => $amount,
            'transaction_id'      => $transactionId,
            'transaction_context' => $context,
            'external_id'         => $externalId,
        ];

        $this->postRequest(sprintf(self::WALLET_DEPOSIT_ENDPOINT, $this->userId), $requestData);
    }

    /**
     * Performs a deposit request to the wallet
     *
     * @param string|float|int $amount
     * @param string $transactionId
     * @param int|null $context
     * @param string|null $externalId
     *
     * @throws InvalidArgumentException
     * @throws SeamlessWalletRequestFailedException
     */
    public function withdraw($amount, string $transactionId, int $context = null, string $externalId = null): void
    {
        $this->guardAgainstMissingUserId();

        $requestData = [
            'amount'              => $amount,
            'transaction_id'      => $transactionId,
            'transaction_context' => $context,
            'external_id'         => $externalId,
        ];

        $this->postRequest(sprintf(self::WALLET_WITHDRAW_ENDPOINT, $this->userId), $requestData);
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
    }

    /**
     * Returns the full endpoint url
     *
     * @param string $endpoint
     *
     * @return string
     */
    private function getFullEndpointUrl(string $endpoint): string
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
        return $this->makeRequest('post', $this->getFullEndpointUrl($endpoint), $data)->json();
    }

    /**
     * Performs a get request
     *
     * @param string $endpoint
     *
     * @return array
     *
     * @throws SeamlessWalletRequestFailedException
     */
    protected function getRequest(string $endpoint): array
    {
        return $this->makeRequest('get', $this->getFullEndpointUrl($endpoint))->json();
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
    private function guardAgainstMissingUserId(): void
    {
        /**
         * The debug_backtrace() returns the current callstack
         *
         * debug_backtrace()[0] refers to the current method => guardAgainstMissingUserId()
         * debug_backtrace()[1] refers to the place that called guardAgainstMissingUserId()
         */
        $calledMethod = debug_backtrace()[1]["function"];

        if ( ! isset($this->userId)) {
            throw new InvalidArgumentException(sprintf("UserId is not set - Make sure to call ->forUser() before ->%s()", $calledMethod));
        }
    }
}
