<?php

namespace Diana\Support\Collection;

use ArrayAccess;
use Countable;
use Diana\Support\Helpers\Arr;
use JsonSerializable;
use RecursiveArrayIterator;
use RecursiveIteratorIterator;
use Stringable;
use Iterator;

class Collection implements ArrayAccess, JsonSerializable, Stringable, Iterator, Countable
{
    protected array $items = [];

    public function __construct(self|array|null $byValue = [], self|array &$byReference = null, $preserveClass = true, protected array $hidden = [], protected array $visible = [])
    {
        if ($byReference == null) {
            $class = $preserveClass ? static::class : self::class;

            foreach ($byValue as &$item) {
                if ($item instanceof self) {
                    $item = new $class(null, $item, $preserveClass);
                }
            }

            $this->items = $byValue;
        } else
            $this->items = &$byReference;
    }

    // finalizers

    public function wrap(string $pre, string $post): string
    {
        $result = '';
        foreach ($this->attributes as $attribute)
            $result .= $pre . $attribute . $post;
        return $result;
    }

    public function join(string $separator): string
    {
        return join($separator, $this->attributes);
    }

    public function flat(): static
    {
        return new static(iterator_to_array(new RecursiveIteratorIterator(new RecursiveArrayIterator($this->items)), true));
    }

    public function indexOf(string $value = null): bool|int|string
    {
        return array_search($value, $this->attributes);
    }

    //

    public function get(mixed $item = null): mixed
    {
        if ($item)
            return $this->items[$item];

        return $this->getAll();
    }

    public function getHiddenKeys()
    {
        return $this->hidden;
    }

    public function getHidden()
    {
        return Arr::only($this->items, $this->getHiddenKeys());
    }

    public function setHidden(string|array ...$items)
    {
        $this->hidden = array_merge($this->hidden, (array) $items);

        return $this;
    }

    public function unsetHidden(string|array ...$items)
    {
        $this->hidden = array_diff($this->hidden, (array) $items);

        return $this;
    }

    public function setVisible(string|array ...$items)
    {
        $this->visible = array_diff($this->visible, (array) $items);

        return $this;
    }

    public function unsetVisible(string|array ...$items)
    {
        $this->visible = array_merge($this->visible, (array) $items);

        return $this;
    }

    public function getVisibleKeys(): array
    {
        if (empty($this->visible))
            return array_diff(array_keys($this->items), $this->getHiddenKeys());
        else
            return $this->visible;
    }

    public function getVisible(): array
    {
        return Arr::only($this->items, $this->getVisibleKeys());
    }

    public function getAll(): array
    {
        return $this->items;
    }

    public function jsonSerialize(): mixed
    {
        return $this->getVisible();
    }

    public function count(): int
    {
        return count($this->items);
    }

    public function has(mixed $item): bool
    {
        return isset($this->items[$item]);
    }

    public function remove(mixed $item): void
    {
        unset($this->items[$item]);
    }

    public function set(mixed $item, mixed $value): void
    {
        if ($item)
            $this->items[$item] = $value;
        else
            $this->push($value);
    }

    public function push(mixed $value): void
    {
        $this->items[] = $value;
    }

    public function firstKey(): mixed
    {
        return array_key_first($this->items);
    }

    public function first(): mixed
    {
        return $this->items[$this->firstKey()];
    }

    public function __toString(): string
    {
        return json_encode($this);
    }

    public function __set(string $name, mixed $value): void
    {
        $this->set($name, $value);
    }

    public function __get(mixed $name): mixed
    {
        return $this->get($name);
    }

    public function __isset(mixed $name): bool
    {
        return $this->has($name);
    }

    public function __unset(mixed $name): void
    {
        $this->remove($name);
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        $this->set($offset, $value);
    }

    public function offsetGet(mixed $offset): mixed
    {
        return $this->get($offset);
    }

    public function offsetExists(mixed $offset): bool
    {
        return $this->has($offset);
    }

    public function offsetUnset(mixed $offset): void
    {
        $this->remove($offset);
    }

    public function rewind(): void
    {
        reset($this->items);
    }

    public function valid(): bool
    {
        return key($this->items) !== null;
    }

    public function key(): mixed
    {
        return key($this->items);
    }

    public function next(): void
    {
        next($this->items);
    }

    public function current(): mixed
    {
        return current($this->items);
    }

    public function isAssociative(): bool
    {
        return array_keys($this->items) !== range(0, $this->count() - 1);
    }

    public function isIterable(): bool
    {
        return !$this->isAssociative();
    }
}