<?php

namespace Tests\Unit;

use Tests\TestCase;
use Ramsey\Uuid\Uuid;
use InvalidArgumentException;
use Illuminate\Support\Facades\Http;
use Cego\SeamlessWallet\SeamlessWallet;
use Cego\SeamlessWallet\Exceptions\SeamlessWalletRequestFailedException;

class SeamlessWalletTest extends TestCase
{
    private SeamlessWallet $seamlessWallet;

    protected function setUp(): void
    {
        $this->seamlessWallet = SeamlessWallet::create("http://seamless-wallet-stage.whatup156453.dk", "root", "secret123");

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
    public function it_throws_an_exception_if_a_server_error_is_returned(): void
    {
        // Arrange
        $this->expectException(SeamlessWalletRequestFailedException::class);
        Http::fake(static function () {
            return Http::response(["success" => false, "message" => "error", "balance" => 0], 500);
        });

        // Act
        $this->seamlessWallet
            ->forPlayer(Uuid::uuid6())
            ->getBalance();
    }

    /** @test */
    public function it_throws_an_exception_if_a_client_error_is_returned(): void
    {
        // Arrange
        $this->expectException(SeamlessWalletRequestFailedException::class);
        Http::fake(static function () {
            return Http::response(["success" => false, "message" => "error", "balance" => 0], 400);
        });

        // Act
        $this->seamlessWallet
            ->forPlayer(Uuid::uuid6())
            ->getBalance();
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

    /** @test */
    public function it_throws_nothing_when_depositing_with_success(): void
    {
        // Arrange
        Http::fake(static function () {
            return Http::response(["success" => true, "balance" => "123.45"]);
        });

        // Act
        $this->seamlessWallet
            ->forPlayer(Uuid::uuid6())
            ->deposit(123.45, Uuid::uuid6());

        // Assert
        $this->assertTrue(true); // Everything went OK
    }

    /** @test */
    public function it_throws_nothing_when_withdrawing_with_success(): void
    {
        // Arrange
        Http::fake(static function () {
            return Http::response(["success" => true, "balance" => 0]);
        });

        // Act
        $this->seamlessWallet
            ->forPlayer(Uuid::uuid6())
            ->withdraw(123.45, Uuid::uuid6());

        // Assert
        $this->assertTrue(true); // Everything went OK
    }

    /** @test */
    public function it_throws_nothing_on_successful_rollback(): void
    {
        // Arrange
        Http::fake(static function () {
            return Http::response(["success" => true]);
        });

        // Act
        $this->seamlessWallet
            ->rollbackTransaction(Uuid::uuid6());

        // Assert
        $this->assertTrue(true); // Everything went OK
    }

    /** @test */
    public function it_throws_an_exception_if_entering_empty_base_url(): void
    {
        // Arrange
        $this->expectException(InvalidArgumentException::class);

        // Act
        SeamlessWallet::create("", "user", "pass");
    }

    /** @test */
    public function it_throws_an_exception_if_entering_empty_username(): void
    {
        // Arrange
        $this->expectException(InvalidArgumentException::class);

        // Act
        SeamlessWallet::create("base_url", "", "pass");
    }

    /** @test */
    public function it_throws_an_exception_if_entering_empty_password(): void
    {
        // Arrange
        $this->expectException(InvalidArgumentException::class);

        // Act
        SeamlessWallet::create("base_url", "user", "");
    }

    /** @test */
    public function it_throws_no_exception_for_non_empty_credentials(): void
    {
        // Act
        SeamlessWallet::create("base_url", "user", "pass");

        // Assert
        $this->assertTrue(true); // Nothing went wrong
    }
}
