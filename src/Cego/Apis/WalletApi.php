<?php

namespace Cego\Apis;

use Ramsey\Uuid\Uuid;
use InvalidArgumentException;
use Cego\SeamlessWalletService;
use Cego\Enums\TransactionContext;
use Cego\Exceptions\SeamlessWalletRequestFailedException;

/**
 * Class WalletApi
 *
 * Used for seamless wallet interactions
 *
 * Handles requests to endpoints starting with:
 *      /wallets/{wallet_id}
 */
class WalletApi extends SeamlessWalletApi
{
    public const DEPOSIT_URL = '/api/v1/wallet/%s/deposit';
    public const WITHDRAW_URL = '/api/v1/wallet/%s/withdraw';
    public const BALANCE_URL = '/api/v1/wallet/%s/balance';

    /** @var string */
    private string $userId;

    /**
     * UserWallet constructor.
     *
     * @param string $userId
     * @param SeamlessWalletService $walletService
     */
    public function __construct(string $userId, SeamlessWalletService $walletService)
    {
        parent::__construct($walletService);
        $this->userId = $userId;
    }

    /**
     * Returns the balance of the wallet
     *
     * @return string
     * @throws SeamlessWalletRequestFailedException
     *
     */
    public function getBalance(): string
    {
        return $this->getRequest(self::BALANCE_URL)['amount'];
    }

    /**
     * Performs a deposit request to the wallet
     *
     * @param string $amount
     * @param string $transactionId
     * @param int|null $context
     * @param string|null $externalId
     *
     * @throws InvalidArgumentException
     * @throws SeamlessWalletRequestFailedException
     */
    public function deposit(string $amount, string $transactionId, int $context = null, string $externalId = null): void
    {
        $requestData = [
            'amount'              => $amount,
            'transaction_id'      => $transactionId,
            'transaction_context' => $context,
            'external_id'         => $externalId,
        ];

        $this->validateDepositOrWithdrawRequest($requestData);

        $this->postRequest(self::DEPOSIT_URL, $requestData);
    }

    /**
     * Performs a deposit request to the wallet
     *
     * @param string $amount
     * @param string $transactionId
     * @param int|null $context
     * @param string|null $externalId
     *
     * @throws InvalidArgumentException
     * @throws SeamlessWalletRequestFailedException
     */
    public function withdraw(string $amount, string $transactionId, int $context = null, string $externalId = null): void
    {
        $requestData = [
            'amount'              => $amount,
            'transaction_id'      => $transactionId,
            'transaction_context' => $context,
            'external_id'         => $externalId,
        ];

        $this->validateDepositOrWithdrawRequest($requestData);

        $this->postRequest(self::WITHDRAW_URL, $requestData);
    }

    /**
     * Validates a deposit request
     *
     * @param array $data
     *
     * @throws InvalidArgumentException
     */
    private function validateDepositOrWithdrawRequest(array $data): void
    {
        $this->validateIsPositiveAmount($data['amount']);
        $this->validateIsUuid($data['transaction_id']);
        $this->validateTransactionContext($data['transaction_context']);
        $this->validateIsUuid($data['external_id']);
    }

    /**
     * Validates an amount to be a positive number
     *
     * @param string $amount
     *
     * @throws InvalidArgumentException
     */
    private function validateIsPositiveAmount(string $amount): void
    {
        if ( ! is_numeric($amount) || $amount <= 0) {
            throw new InvalidArgumentException('Must be numeric and a positive number!');
        }
    }

    /**
     * Validates an uuid to be a valid uuid
     *
     * @param string $uuid
     *
     * @throws InvalidArgumentException
     */
    private function validateIsUuid(string $uuid): void
    {
        if (Uuid::isValid($uuid)) {
            throw new InvalidArgumentException('Must be valid uuid!');
        }
    }

    /**
     * Validates a transaction context to be defined in TransactionContext
     *
     * @param int $context
     *
     * @throws InvalidArgumentException
     * @see TransactionContext
     *
     */
    private function validateTransactionContext(int $context): void
    {
        if ( ! TransactionContext::all()->contains($context)) {
            throw new InvalidArgumentException('Must be a valid transaction context!');
        }
    }

    /**
     * Method used to inject the users id into the endpoint url
     *
     * @param string $endpoint
     *
     * @return string
     */
    protected function transformEndpoint(string $endpoint): string
    {
        return sprintf($endpoint, $this->userId);
    }
}
