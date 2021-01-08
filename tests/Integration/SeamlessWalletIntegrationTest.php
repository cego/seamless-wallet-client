<?php

namespace Tests\Integration;

use Tests\TestCase;
use Ramsey\Uuid\Uuid;
use Cego\SeamlessWallet\SeamlessWallet;

class SeamlessWalletIntegrationTest extends TestCase
{
    private SeamlessWallet $seamlessWallet;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seamlessWallet = SeamlessWallet::create(
            env("SEAMLESS_WALLET_BASE_URL"),
            env("SEAMLESS_WALLET_USERNAME"),
            env("SEAMLESS_WALLET_PASSWORD")
        );
    }

    /** @test */
    public function it_can_deposit(): void
    {
        // Arrange
        $depositAmount = 1000.12;

        $this->seamlessWallet->forPlayer(random_int(100000, 9999999))->createWallet();

        $this->assertEquals(0, $this->seamlessWallet->getBalance());

        // Act
        $this->seamlessWallet->deposit($depositAmount, Uuid::uuid6());
        $balance = $this->seamlessWallet->getBalance();

        // Assert
        $this->assertEquals($depositAmount, $balance);
    }

    /** @test */
    public function it_can_withdraw(): void
    {
        // Arrange
        $depositAmount = 1000.12;
        $withdrawAmount = 200.01;
        $expectedBalance = 800.11;

        $this->seamlessWallet->forPlayer(random_int(100000, 9999999))->createWallet();

        $this->assertEquals(0, $this->seamlessWallet->getBalance());

        // Act
        $this->seamlessWallet->deposit($depositAmount, Uuid::uuid6());
        $this->seamlessWallet->withdraw($withdrawAmount, Uuid::uuid6());
        $actualBalance = $this->seamlessWallet->getBalance();

        // Assert
        $this->assertEquals($expectedBalance, $actualBalance);
    }

    /** @test */
    public function it_can_return_the_balance_of_initial_zero(): void
    {
        // Arrange
        $this->seamlessWallet->forPlayer(random_int(100000, 9999999))->createWallet();

        // Act
        $balance = $this->seamlessWallet->getBalance();

        // Assert
        $this->assertEquals(0, $balance);
    }

    /** @test */
    public function it_can_return_the_balance_after_consecutive_transactions(): void
    {
        // Arrange
        $this->seamlessWallet->forPlayer(random_int(100000, 9999999))->createWallet();

        // Act
        $this->seamlessWallet->deposit(100, Uuid::uuid6());
        $this->seamlessWallet->deposit(123, Uuid::uuid6());
        $this->seamlessWallet->withdraw(23, Uuid::uuid6());
        $this->seamlessWallet->deposit(0.99, Uuid::uuid6());

        $balance = $this->seamlessWallet->getBalance();

        // Assert
        $this->assertEquals(200.99, $balance);
    }

    /** @test */
    public function it_can_rollback_a_withdrawal(): void
    {
        // Arrange
        $this->seamlessWallet->forPlayer(random_int(100000, 9999999))->createWallet();
        $withdrawTransactionId = Uuid::uuid6();

        $this->assertEquals(0, $this->seamlessWallet->getBalance());

        // Act
        $this->seamlessWallet->deposit(100, Uuid::uuid6());
        $this->seamlessWallet->withdraw(50, $withdrawTransactionId);
        $this->seamlessWallet->rollbackTransaction($withdrawTransactionId);

        // Assert
        $this->assertEquals(100, $this->seamlessWallet->getBalance());
    }
}
