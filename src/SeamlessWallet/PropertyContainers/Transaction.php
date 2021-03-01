<?php

namespace Cego\SeamlessWallet\PropertyContainers;

use Carbon\Carbon;

/**
 * Class Transaction
 *
 * @property-read int $transaction_type_id
 * @property-read int $transaction_context_id
 * @property-read string $amount
 * @property-read ?string $externalName
 * @property-read Carbon|null $rolled_back_at
 * @property-read Carbon|null $created_at
 * @property-read Carbon|null $updated_at
 *
 * @package Cego\SeamlessWallet\DTOs
 */
class Transaction extends CarbonPropertyContainer
{
    protected array $dateProperties = [
        'rolled_back_at',
        'created_at',
        'updated_at',
    ];
}
