<?php

namespace Tests\Unit;

use Tests\TestCase;
use Ramsey\Uuid\Uuid;
use InvalidArgumentException;
use Illuminate\Support\Facades\Http;
use Cego\SeamlessWallet\SeamlessWallet;

class SeamlessWalletTest extends TestCase
{
    private SeamlessWallet $seamlessWallet;

    protected function setUp(): void
    {
        $this->seamlessWallet = SeamlessWallet::create("http://seamless-wallet-stage.whatup156453.dk")
                                              ->auth("root", "secret123");

        parent::setUp();
    }

    /** @test */
    public function it_throws_an_exception_if_no_userid_is_set_for_deposit(): void
    {
        // Arrange
        $this->expectException(InvalidArgumentException::class);

        // Act
        $this->seamlessWallet->deposit(100, Uuid::uuid6());
    }

    /** @test */
    public function it_throws_an_exception_if_no_userid_is_set_for_withdraw(): void
    {
        // Arrange
        $this->expectException(InvalidArgumentException::class);

        // Act
        $this->seamlessWallet->withdraw(100, Uuid::uuid6());
    }

    /** @test */
    public function it_throws_an_exception_if_no_userid_is_set_for_get_balance(): void
    {
        // Arrange
        $this->expectException(InvalidArgumentException::class);

        // Act
        $this->seamlessWallet->getBalance();
    }

    /** @test */
    public function deposit_returns_the_users_current_balance_on_success(): void
    {
        // Arrange
        $expectAmount = 123.12;

        Http::fake(static function () use ($expectAmount) {
            return Http::response(["success" => true, "message" => "", "balance" => $expectAmount]);
        });

        // Act
        $actualAmount = $this->seamlessWallet
            ->forPlayer(Uuid::uuid6())
            ->getBalance();

        // Assert
        $this->assertEquals($expectAmount, $actualAmount);
    }
}
