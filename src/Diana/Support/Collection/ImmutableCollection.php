<?php

namespace Diana\Support\Collection;

use ArrayAccess;
use Countable;
use Diana\Support\Exceptions\MutationException;
use JsonSerializable;
use Stringable;
use Iterator;

class ImmutableCollection extends Collection implements ArrayAccess, JsonSerializable, Stringable, Iterator, Countable
{
    public function set(mixed $item, mixed $value): void
    {
        throw new MutationException("Attempted to set [{$item}] => [{$value}] of an immutable collection.");
    }

    public function push(mixed $value): void
    {
        throw new MutationException("Attempted to push [{$value}] into an immutable collection.");
    }

    public function remove(mixed $item): void
    {
        throw new MutationException("Attempted to remove [{$item}] of an immutable collection.");
    }
}