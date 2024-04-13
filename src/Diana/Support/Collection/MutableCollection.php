<?php

namespace Diana\Support\Collection;

use ArrayAccess;
use Countable;
use Diana\Support\Exceptions\MutationException;
use JsonSerializable;
use Stringable;
use Iterator;

class MutableCollection extends Collection implements ArrayAccess, JsonSerializable, Stringable, Iterator, Countable
{
    protected bool $pushable = false;

    public function __construct(self|array|null $byValue = [], self|array &$byReference = null, $preserveClass = true, protected array $visible = [], protected array $hidden = [], protected array $fillable = [])
    {
        parent::__construct($byValue, $byReference, $preserveClass, $visible, $hidden);
    }

    public function allow(string|array ...$items)
    {
        $this->fillable = array_merge($this->fillable, (array) $items);

        return $this;
    }

    public function setPushable(bool $pushable = true)
    {
        $this->pushable = $pushable;

        return $this;
    }

    public function deny(string|array ...$items)
    {
        $this->fillable = array_diff($this->fillable, (array) $items);

        return $this;
    }

    public function getFillable(): array
    {
        return $this->fillable;
    }

    public function set(mixed $item, mixed $value): void
    {
        if (!in_array($item, $this->fillable))
            throw new MutationException("Attempted to set an immutable item [{$item}] => [{$value}] of a mutable collection.");

        parent::set($item, $value);
    }

    public function push(mixed $value): void
    {
        if (!$this->pushable)
            throw new MutationException("Attempted to push a value [{$value}] into an unpushable mutable collection.");

        parent::push($value);
    }

    public function remove(mixed $item): void
    {
        if (!in_array($item, $this->fillable))
            throw new MutationException("Attempted to remove an immutable item [{$item}] of a mutable collection.");

        parent::remove($item);
    }
}