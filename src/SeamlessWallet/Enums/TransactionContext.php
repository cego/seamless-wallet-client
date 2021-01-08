<?php

namespace Cego\SeamlessWallet\Enums;

/**
 * Class TransactionContext
 */
class TransactionContext
{
    public const NONE = 1;
    public const SPIN_PRIZE = 2;
    public const SPIN_COST = 3;
    public const BINGO_PURCHASE = 4;
    public const JACKPOT = 5;
    public const BINGO_MEGA_PRIZE = 6;
    public const BINGO_WIN = 7;
    public const BONUS = 8;
    public const BONUS_CANCELED = 9;
    public const PAYOUT = 10;
    public const PAYOUT_CANCELED = 11;
    public const LEFTOVER_BALANCE = 12;
    public const MANUEL = 13;
    public const PAYMENT = 14;
    public const PAYMENT_CANCELED = 15;
}
