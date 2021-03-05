<?php

namespace Cego\SeamlessWallet\PropertyContainers;

use Carbon\Carbon;
use Nbj\PropertyContainer;

/**
 * Class Transaction
 *
 * @property-read string $id
 * @property-read string $wallet_id
 * @property-read string $user_id
 * @property-read int $transaction_type_id
 * @property-read int $transaction_context_id
 * @property-read string $external_id
 * @property-read ?string $externalName
 * @property-read string $amount
 * @property-read string $new_balance
 * @property-read Carbon|null $rolled_back_at
 * @property-read Carbon|null $created_at
 * @property-read Carbon|null $updated_at
 *
 * @package Cego\SeamlessWallet\PropertyContainers
 */
class Transaction extends PropertyContainer
{
    protected $dateProperties = [
        'rolled_back_at',
        'created_at',
        'updated_at',
    ];
}
