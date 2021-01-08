<?php

namespace Cego\SeamlessWallet;

/**
 * Class SeamlessWalletStore
 *
 * Used as a static store so multiple requests with the same parameters are not repeated
 * within the same request cycle
 *
 * @package Cego\SeamlessWallet
 */
class SeamlessWalletStore
{
    /**
     * The latest fetched balance from the service
     *
     * @var string|null $balance
     */
    public static ?string $balance = null;
}
