<?php

namespace Tests\Integration;

use Carbon\Carbon;
use Tests\TestCase;
use Ramsey\Uuid\Uuid;
use Cego\SeamlessWallet\SeamlessWallet;
use Cego\SeamlessWallet\Enums\TransactionType;
use Cego\SeamlessWallet\Enums\TransactionContext;
use Cego\SeamlessWallet\Exceptions\NoSuchPageException;

class FilteredTransactionsTest extends TestCase
{
    private SeamlessWallet $seamlessWallet;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seamlessWallet = SeamlessWallet::create(env("SEAMLESS_WALLET_BASE_URL"))
            ->auth(env("SEAMLESS_WALLET_USERNAME"), env("SEAMLESS_WALLET_PASSWORD"));

        $this->seamlessWallet->forPlayer(random_int(10000000, 999999999));
        $this->seamlessWallet->createWallet();
    }

    /** @test */
    public function it_can_get_data_from_filtered_endpoint(): void
    {
        // Arrange
        $this->seamlessWallet->deposit(100, Uuid::uuid6(), TransactionContext::PAYMENT);
        $this->seamlessWallet->withdraw(50, Uuid::uuid6(), TransactionContext::PAYOUT);

        // Act
        $paginator = $this->seamlessWallet->getPaginatedTransactions(Carbon::now(), Carbon::now(), [], 1, 100);

        // Assert
        $this->assertCount(2, $paginator->getData());
        $this->assertEquals(2, $paginator->getTotalEntries());
        $this->assertEquals(1, $paginator->getCurrentPageNumber());
        $this->assertEquals(1, $paginator->getLastPageNumber());
        $this->assertEquals(1, $paginator->getFrom());
        $this->assertEquals(2, $paginator->getTo());
    }

    /** @test */
    public function it_can_walk_between_pages(): void
    {
        // Arrange
        $this->seamlessWallet->deposit(100, Uuid::uuid6(), TransactionContext::PAYMENT);
        $this->seamlessWallet->withdraw(50, Uuid::uuid6(), TransactionContext::PAYOUT);

        // Act
        $paginatorPage1 = $this->seamlessWallet->getPaginatedTransactions(Carbon::now(), Carbon::now(), [], 1, 1);
        $paginatorPage2 = $paginatorPage1->getNextPage();
        $paginatorPage1 = $paginatorPage2->getPreviousPage();

        // Assert
        $this->assertCount(1, $paginatorPage1->getData());
        $this->assertEquals(2, $paginatorPage1->getTotalEntries());
        $this->assertEquals(1, $paginatorPage1->getCurrentPageNumber());
        $this->assertEquals(2, $paginatorPage1->getLastPageNumber());
        $this->assertEquals(1, $paginatorPage1->getFrom());
        $this->assertEquals(1, $paginatorPage1->getTo());

        $this->assertCount(1, $paginatorPage2->getData());
        $this->assertEquals(2, $paginatorPage2->getTotalEntries());
        $this->assertEquals(2, $paginatorPage2->getCurrentPageNumber());
        $this->assertEquals(2, $paginatorPage2->getLastPageNumber());
        $this->assertEquals(2, $paginatorPage2->getFrom());
        $this->assertEquals(2, $paginatorPage2->getTo());
    }

    /** @test */
    public function it_can_walk_between_pages_with_filtered_contexts(): void
    {
        // Arrange
        $this->seamlessWallet->deposit(100, Uuid::uuid6(), TransactionContext::PAYMENT);
        $this->seamlessWallet->deposit(100, Uuid::uuid6(), TransactionContext::PAYMENT);
        $this->seamlessWallet->withdraw(50, Uuid::uuid6(), TransactionContext::PAYOUT);
        $this->seamlessWallet->withdraw(50, Uuid::uuid6(), TransactionContext::PAYOUT);

        // Act
        $paginatorPage1 = $this->seamlessWallet->getPaginatedTransactions(Carbon::now(), Carbon::now(), [TransactionContext::PAYMENT], 1, 1);
        $paginatorPage2 = $paginatorPage1->getNextPage();
        $paginatorPage1 = $paginatorPage2->getPreviousPage();

        // Assert
        $this->assertCount(1, $paginatorPage1->getData());
        $this->assertEquals(2, $paginatorPage1->getTotalEntries());
        $this->assertEquals(1, $paginatorPage1->getCurrentPageNumber());
        $this->assertEquals(2, $paginatorPage1->getLastPageNumber());
        $this->assertEquals(1, $paginatorPage1->getFrom());
        $this->assertEquals(1, $paginatorPage1->getTo());
        $this->assertEquals(TransactionContext::PAYMENT, $paginatorPage1->getData()[0]->transaction_context_id);

        $this->assertCount(1, $paginatorPage2->getData());
        $this->assertEquals(2, $paginatorPage2->getTotalEntries());
        $this->assertEquals(2, $paginatorPage2->getCurrentPageNumber());
        $this->assertEquals(2, $paginatorPage2->getLastPageNumber());
        $this->assertEquals(2, $paginatorPage2->getFrom());
        $this->assertEquals(2, $paginatorPage2->getTo());
        $this->assertEquals(TransactionContext::PAYMENT, $paginatorPage2->getData()[0]->transaction_context_id);
    }

    /** @test */
    public function it_can_filter_transactions(): void
    {
        // Arrange
        $this->seamlessWallet->deposit(100, Uuid::uuid6(), TransactionContext::PAYMENT);
        $this->seamlessWallet->withdraw(50, Uuid::uuid6(), TransactionContext::PAYOUT);

        $this->seamlessWallet->deposit(10, Uuid::uuid6(), TransactionContext::SPIN_PRIZE);
        $this->seamlessWallet->deposit(10, Uuid::uuid6(), TransactionContext::SPIN_PRIZE);
        $this->seamlessWallet->deposit(10, Uuid::uuid6(), TransactionContext::SPIN_PRIZE);

        // Act
        $paginator = $this->seamlessWallet->getPaginatedTransactions(Carbon::now(), Carbon::now(), [TransactionContext::PAYOUT, TransactionContext::PAYMENT], 1, 100);

        // Assert
        $this->assertCount(2, $paginator->getData());
        $this->assertEquals(2, $paginator->getTotalEntries());
        $this->assertEquals(1, $paginator->getCurrentPageNumber());
        $this->assertEquals(1, $paginator->getLastPageNumber());
        $this->assertEquals(1, $paginator->getFrom());
        $this->assertEquals(2, $paginator->getTo());
    }

    public function it_throws_exceptions_if_accessing_pages_that_does_not_exist_for_prev_page()
    {
        // Arrange
        $this->expectException(NoSuchPageException::class);
        $this->seamlessWallet->deposit(100, Uuid::uuid6(), TransactionContext::PAYMENT);

        // Act
        $paginator = $this->seamlessWallet->getPaginatedTransactions(Carbon::now(), Carbon::now(), [TransactionContext::PAYMENT, TransactionContext::PAYOUT], 1, 100);
        $paginator->getPreviousPage();
    }

    public function it_throws_exceptions_if_accessing_pages_that_does_not_exist_for_next_page()
    {
        // Arrange
        $this->expectException(NoSuchPageException::class);
        $this->seamlessWallet->deposit(100, Uuid::uuid6(), TransactionContext::PAYMENT);

        // Act
        $paginator = $this->seamlessWallet->getPaginatedTransactions(Carbon::now(), Carbon::now(), [TransactionContext::PAYMENT, TransactionContext::PAYOUT], 1, 100);
        $paginator->getNextPage();
    }

    /** @test */
    public function it_returns_the_expected_data_for_each_transactions(): void
    {
        // Arrange
        $this->seamlessWallet->deposit(50, $uuid = Uuid::uuid6(), TransactionContext::PAYOUT);

        // Act
        $paginator = $this->seamlessWallet->getPaginatedTransactions(Carbon::now(), Carbon::now(), [], 1, 100);

        // Assert
        $this->assertCount(1, $paginator->getData());
        $this->assertEquals($uuid->toString(), $paginator->getData()[0]->id);
        $this->assertEquals(50, $paginator->getData()[0]->amount);
        $this->assertEquals(TransactionType::DEPOSIT, $paginator->getData()[0]->transaction_type_id);
        $this->assertEquals(TransactionContext::PAYOUT, $paginator->getData()[0]->transaction_context_id);
        $this->assertEquals(50, $paginator->getData()[0]->new_balance);
        $this->assertEquals(null, $paginator->getData()[0]->rolled_back_at);
    }
}
