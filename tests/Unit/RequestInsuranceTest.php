<?php

namespace Tests\Unit;

use Tests\TestCase;
use Ramsey\Uuid\Uuid;
use Cego\SeamlessWallet\SeamlessWallet;
use Cego\SeamlessWallet\Enums\TransactionContext;
use Cego\RequestInsurance\Models\RequestInsurance;
use Illuminate\Foundation\Testing\RefreshDatabase;

class RequestInsuranceTest extends TestCase
{
    use RefreshDatabase;

    private SeamlessWallet $seamlessWallet;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seamlessWallet = SeamlessWallet::create("http://seamless-wallet.service.dk")
                                              ->auth("root", "secret123");

        $this->loadMigrationsFrom(__DIR__ . '/../../vendor/cego/request-insurance/publishable/migrations');
    }

    /** @test */
    public function it_can_create_request_insurances_for_deposits(): void
    {
        // Arrange
        $this->assertCount(0, RequestInsurance::all());
        $transactionId = Uuid::uuid6()->toString();
        $expectedPayload = json_encode([
            'amount'              => '100',
            'transaction_id'      => $transactionId,
            'transaction_context' => TransactionContext::NONE
        ], JSON_THROW_ON_ERROR);

        // Act
        $this->seamlessWallet
            ->useRequestInsurance()
            ->forPlayer(1)
            ->deposit('100', $transactionId);

        // Assert
        $this->assertCount(1, RequestInsurance::all());
        $this->assertEquals($expectedPayload, RequestInsurance::find(1)->payload);
        $this->assertEquals('http://seamless-wallet.service.dk/api/v1/wallets/1/deposit', RequestInsurance::find(1)->url);
        $this->assertEquals('post', RequestInsurance::find(1)->method);
    }

    /** @test */
    public function it_can_create_request_insurances_for_withdrawals(): void
    {
        // Arrange
        $this->assertCount(0, RequestInsurance::all());
        $transactionId = Uuid::uuid6()->toString();
        $expectedPayload = json_encode([
            'amount'              => '100',
            'transaction_id'      => $transactionId,
            'transaction_context' => TransactionContext::NONE
        ], JSON_THROW_ON_ERROR);

        // Act
        $this->seamlessWallet
            ->useRequestInsurance()
            ->forPlayer(1)
            ->withdraw('100', $transactionId);

        // Assert
        $this->assertCount(1, RequestInsurance::all());
        $this->assertEquals($expectedPayload, RequestInsurance::find(1)->payload);
        $this->assertEquals('http://seamless-wallet.service.dk/api/v1/wallets/1/withdraw', RequestInsurance::find(1)->url);
        $this->assertEquals('post', RequestInsurance::find(1)->method);
    }
}
