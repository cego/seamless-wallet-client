<?php

namespace Cego\Exceptions;

use Exception;
use Throwable;
use Illuminate\Http\Client\Response;

/**
 * Class SeamlessWalletRequestFailedException
 */
class SeamlessWalletRequestFailedException extends Exception
{
    /**
     * SeamlessWalletRequestFailedException constructor.
     *
     * @param Response $response
     * @param Throwable|null $previous
     */
    public function __construct(Response $response, Throwable $previous = null)
    {
        parent::__construct(sprintf('Seamless Wallet Service [%s]: %s', $response->status(), $response->body()), $response->status(), $previous);
    }
}
