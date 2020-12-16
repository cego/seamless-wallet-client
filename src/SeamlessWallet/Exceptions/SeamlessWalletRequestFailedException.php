<?php

namespace Cego\SeamlessWallet\Exceptions;

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
        $message = sprintf('Seamless Wallet Service [%s]: %s', $response->status(), $response->body());

        parent::__construct($message, 500, $previous);
    }
}
