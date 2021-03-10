<?php

namespace Cego\SeamlessWallet\RequestDrivers;

use ArrayAccess;
use Illuminate\Support\Collection;

/**
 * Class Response
 *
 * @implements ArrayAccess<string, mixed>
 *
 * @package Cego\SeamlessWallet\RequestDrivers
 */
class Response implements ArrayAccess
{
    public int $code;
    public Collection $data;
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
        $this->data = new Collection($data);
        $this->isSynchronous = $isSynchronous;
    }

    /**
     * @inheritDoc
     */
    public function offsetExists($offset): bool
    {
        return isset($this->data[$offset]);
    }

    /**
     * @inheritDoc
     */
    public function offsetGet($offset)
    {
        return $this->data[$offset];
    }

    /**
     * @inheritDoc
     */
    public function offsetSet($offset, $value): void
    {
        $this->data[$offset] = $value;
    }

    /**
     * @inheritDoc
     */
    public function offsetUnset($offset): void
    {
        unset($this->data[$offset]);
    }
}
