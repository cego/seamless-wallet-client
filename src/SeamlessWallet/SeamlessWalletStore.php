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

    /**
     * The latest player id, is used to know if the store should be invalidated
     *
     * @var string|null $playerId
     */
    public static ?string $playerId = null;
}
