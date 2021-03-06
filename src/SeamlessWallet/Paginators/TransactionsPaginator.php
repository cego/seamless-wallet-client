<?php

namespace Cego\SeamlessWallet\Paginators;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Cego\SeamlessWallet\SeamlessWallet;
use Cego\SeamlessWallet\Exceptions\NoSuchPageException;
use Cego\SeamlessWallet\PropertyContainers\Transaction;
use Cego\SeamlessWallet\Exceptions\SeamlessWalletRequestFailedException;

class TransactionsPaginator extends Paginator
{
    protected string $playerId;
    protected array $queryParameters;
    protected SeamlessWallet $client;

    /**
     * TransactionsPaginator constructor.
     *
     * @param Collection $data
     * @param SeamlessWallet $client
     * @param array $queryParameters
     */
    public function __construct(Collection $data, SeamlessWallet $client, array $queryParameters)
    {
        parent::__construct($data);

        $this->client = $client;
        $this->playerId = $client->playerId;
        $this->queryParameters = $queryParameters;
    }

    /**
     * Returns the next paginated page
     *
     * @return TransactionsPaginator
     *
     * @throws SeamlessWalletRequestFailedException
     * @throws NoSuchPageException
     */
    public function getNextPage(): TransactionsPaginator
    {
        if ($this->getCurrentPageNumber() == $this->getLastPageNumber()) {
            throw new NoSuchPageException($this, $this->getCurrentPageNumber() + 1);
        }

        return $this->client
            ->forPlayer($this->playerId)
            ->getPaginatedTransactions(
                Carbon::parse($this->queryParameters['from']),
                Carbon::parse($this->queryParameters['to']),
                $this->getContexts(),
                $this->getCurrentPageNumber() + 1,
                $this->queryParameters['per_page'] ?? null,
            );
    }

    /**
     * @return string[]
     */
    private function getContexts(): array
    {
        if ( ! isset($this->queryParameters['contexts'])) {
            return [];
        }

        return explode(",", $this->queryParameters['contexts']);
    }

    /**
     * Returns the next paginated page
     *
     * @return TransactionsPaginator
     *
     * @throws SeamlessWalletRequestFailedException
     * @throws NoSuchPageException
     */
    public function getPreviousPage(): TransactionsPaginator
    {
        if ($this->getCurrentPageNumber() == 1) {
            throw new NoSuchPageException($this, 0);
        }

        return $this->client
            ->forPlayer($this->playerId)
            ->getPaginatedTransactions(
                Carbon::parse($this->queryParameters['from']),
                Carbon::parse($this->queryParameters['to']),
                $this->getContexts(),
                $this->getCurrentPageNumber() - 1,
                $this->queryParameters['per_page'] ?? null,
            );
    }

    /**
     * Returns all transactions for the current page
     *
     * @return Collection|Transaction[]
     */
    public function getData(): Collection
    {
        return parent::getData()
                     ->map(function ($transaction) {
                         return new Transaction($transaction);
                     });
    }
}
