<?php

namespace Cego\SeamlessWallet\Exceptions;

use Exception;
use Throwable;
use Cego\SeamlessWallet\Paginators\Paginator;

/**
 * Class SeamlessWalletRequestFailedException
 */
class NoSuchPageException extends Exception
{
    /**
     * SeamlessWalletRequestFailedException constructor.
     *
     * @param Paginator $paginator
     * @param int $invalidPage
     * @param Throwable|null $previous
     */
    public function __construct(Paginator $paginator, int $invalidPage, Throwable $previous = null)
    {
        $message = sprintf('No such page: %s - Valid range is 1 to %s - for endpoint: %s', $invalidPage, $paginator->getLastPageNumber(), $paginator->getPath());

        parent::__construct($message, 500, $previous);
    }
}
