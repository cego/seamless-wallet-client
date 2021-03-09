<?php

namespace Cego\SeamlessWallet\RequestDrivers;

class Response
{
    public int $code;
    public array $data;
    public bool $isSynchronous;

    /**
     * Response constructor.
     *
     * @param int $code
     * @param array $data
     * @param bool $isSynchronous
     */
    public function __construct(int $code, array $data, bool $isSynchronous)
    {
        $this->code = $code;
        $this->data = $data;
        $this->isSynchronous = $isSynchronous;
    }
}
