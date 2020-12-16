<?php

namespace Cego\Apis;

use Cego\DTOs\TransactionDTO;
use Cego\SeamlessWalletService;
use Cego\Exceptions\SeamlessWalletRequestFailedException;

/**
 * Class TransactionApi
 *
 * Used for seamless wallet interactions
 *
 * Handles requests to endpoints starting with:
 *      /transactions/{transaction_id}
 */
class TransactionApi extends SeamlessWalletApi
{
    public const GET_URL = '/api/v1/transactions/%s';
    public const ROLLBACK_URL = '/api/v1/transactions/%s/rollback';

    /** @var string */
    private string $transactionId;

    /**
     * TransactionApi constructor.
     *
     * @param string $transactionId
     * @param SeamlessWalletService $walletService
     */
    public function __construct(string $transactionId, SeamlessWalletService $walletService)
    {
        parent::__construct($walletService);
        $this->transactionId = $transactionId;
    }

    /**
     * Returns a single transaction
     *
     * @return TransactionDto
     * @throws SeamlessWalletRequestFailedException
     */
    public function get(): TransactionDto
    {
        return new TransactionDto($this->getRequest(self::GET_URL));
    }

    /**
     * Rollback the transaction
     *
     * @throws SeamlessWalletRequestFailedException
     */
    public function rollback(): void
    {
        $this->postRequest(self::ROLLBACK_URL);
    }

    /**
     * Method used to inject the transaction id into the endpoint url
     *
     * @param string $endpoint
     *
     * @return string
     */
    protected function transformEndpoint(string $endpoint): string
    {
        return sprintf($endpoint, $this->transactionId);
    }
}
