<?php

declare(strict_types=1);

namespace Netgen\Layouts\Block;

use ArrayAccess;
use Closure;
use Countable;

/**
 * @implements \ArrayAccess<string, mixed>
 */
final class DynamicParameters implements ArrayAccess, Countable
{
    /**
     * @var array<string, mixed>
     */
    private $dynamicParameters = [];

    public function count(): int
    {
        return count($this->dynamicParameters);
    }

    /**
     * @param mixed $offset
     *
     * @return bool
     */
    public function offsetExists($offset): bool
    {
        return array_key_exists($offset, $this->dynamicParameters);
    }

    /**
     * @param mixed $offset
     *
     * @return mixed
     */
    public function offsetGet($offset)
    {
        if (!$this->offsetExists($offset)) {
            return null;
        }

        if (!$this->dynamicParameters[$offset] instanceof Closure) {
            return $this->dynamicParameters[$offset];
        }

        return $this->dynamicParameters[$offset]();
    }

    /**
     * @param mixed $offset
     * @param mixed $value
     */
    public function offsetSet($offset, $value): void
    {
        $this->dynamicParameters[$offset] = $value;
    }

    /**
     * @param mixed $offset
     */
    public function offsetUnset($offset): void
    {
        if (!$this->offsetExists($offset)) {
            return;
        }

        unset($this->dynamicParameters[$offset]);
    }
}
