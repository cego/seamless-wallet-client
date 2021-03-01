<?php

namespace Cego\SeamlessWallet\Paginators;

use Illuminate\Support\Collection;

class Paginator
{
    protected Collection $data;

    /**
     * Paginator constructor.
     *
     * @param Collection $data
     */
    public function __construct(Collection $data)
    {
        $this->data = $data;
    }

    /**
     * Returns the current page of the paginator
     *
     * @return int
     */
    public function getCurrentPageNumber(): int
    {
        return $this->data->get('current_page');
    }

    /**
     * Returns all rows of the current page of the paginator
     *
     * @return Collection
     */
    public function getData(): Collection
    {
        return collect($this->data->get('data'));
    }

    /**
     * Returns the url endpoint for the first page of the paginator
     *
     * @return string
     */
    public function getFirstPageUrl(): string
    {
        return $this->data->get('first_page_url');
    }

    /**
     * Get the index of the first item in the slice.
     *
     * @return int|null
     */
    public function getFrom(): ?int
    {
        return $this->data->get('from');
    }

    /**
     * Get the index of the last item in the slice.
     *
     * @return int|null
     */
    public function getTo(): ?int
    {
        return $this->data->get('to');
    }

    /**
     * Returns the total number of entries for all pages
     *
     * @return int
     */
    public function getTotalEntries(): ?int
    {
        return $this->data->get('total');
    }

    /**
     * Get the last available page.
     *
     * @return int
     */
    public function getLastPageNumber(): int
    {
        return $this->data->get('last_page');
    }

    /**
     * Get the url of the last available page.
     *
     * @return int
     */
    public function getLastPageUrl(): int
    {
        return $this->data->get('last_page');
    }

    /**
     * Returns paginator ui links for forward, and backward link buttons
     *
     * @return Collection|PaginatorLink[]
     */
    public function getLinks(): Collection
    {
        return collect($this->data->get('links'))
            ->map(function ($linkData) {
                return new PaginatorLink($linkData);
            });
    }

    /**
     * Returns the url for the next page
     *
     * @return string|null
     */
    public function getNextPageUrl(): ?string
    {
        return $this->data->get('next_page_url');
    }

    /**
     * Returns the url for the previous page
     *
     * @return string|null
     */
    public function getPrevPageUrl(): ?string
    {
        return $this->data->get('prev_page_url');
    }

    /**
     * Returns the base path of the paginator endpoint
     *
     * @return string
     */
    public function getPath(): string
    {
        return $this->data->get('path');
    }

    /**
     * Returns the number of entries per page
     *
     * @return int
     */
    public function getEntriesPerPage(): int
    {
        return $this->data->get('per_page');
    }
}
