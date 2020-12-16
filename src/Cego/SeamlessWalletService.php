<?php

namespace Cego;

use Cego\Apis\WalletApi;
use Cego\Apis\TransactionApi;

/**
 * Class SeamlessWalletService
 */
class SeamlessWalletService
{
    /** @var SeamlessWalletService */
    private static SeamlessWalletService $instance;

    /** @var string */
    private string $serviceEndpoint;

    /** @var string */
    private string $username;

    /** @var string */
    private string $password;

    /**
     * Private constructor to disallow using new
     *
     * @param string $serviceEndpoint
     * @param string $username
     * @param string $password
     */
    private function __construct(string $serviceEndpoint, string $username, string $password)
    {
        $this->serviceEndpoint = $serviceEndpoint;
        $this->username = $username;
        $this->password = $password;
    }

    /**
     * Singleton get instance method
     *
     * @return SeamlessWalletService
     */
    public static function getInstance(): SeamlessWalletService
    {
        return self::$instance ?? self::$instance = new self("What to", "do here?", ":(");
    }

    /**
     * Returns a wallet api instance linked to the given user
     *
     * @param string $userId
     *
     * @return WalletApi
     */
    public function forUser($userId): WalletApi
    {
        return new WalletApi($userId, $this);
    }

    /**
     * Returns a transaction api instance, linked to the given transaction
     *
     * @param string $transactionId
     *
     * @return TransactionApi
     */
    public function forTransaction(string $transactionId): TransactionApi
    {
        $this->forUser(123);

        return new TransactionApi($transactionId, $this);
    }

    /**
     * Returns the full endpoint url
     *
     * @param string $endpoint
     *
     * @return string
     */
    public function getFullEndpointUrl(string $endpoint): string
    {
        return sprintf('%s/%s', $this->serviceEndpoint, $endpoint);
    }
}
