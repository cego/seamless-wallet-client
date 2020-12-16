<?php

namespace Cego\DTOs;

use Carbon\Carbon;

/**
 * Class TransactionDto
 */
class TransactionDTO
{
    public string $transactionId;
    public string $walletId;
    public string $userId;
    public int $transactionType;
    public int $transactionContext;
    public ?string $externalId;
    public string $amount;
    public ?Carbon $rollbackedAt;
    public ?Carbon $updatedAt;
    public ?Carbon $createdAt;

    private array $dates = [
        'rollbacked_at',
        'updated_at',
        'created_at',
    ];

    /**
     * TransactionDto constructor.
     *
     * @param array $data
     */
    public function __construct(array $data)
    {
        foreach ($data as $key => $value) {
            if ($value !== null && in_array($key, $this->dates, true)) {
                $this->$key = Carbon::parse($value);
            } else {
                $this->$key = $value;
            }
        }
    }
}
