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
     * The fetched balances from the service, keyed by the player id
     *
     * @var string[]
     */
    public static array $balances = [];

    /**
     * The created wallets, a list of player ids whose wallets have been created
     *
     * @var string[]
     */
    public static array $createdWallets = [];

    /**
     * The sum of all wallets balance
     *
     * @var string|null
     */
    public static ?string $sumOfWalletBalances = null;
}
