<?php

namespace Diana\Support\Wrapper;

class ArrayWrapper
{
    protected array $array;

    final public function __construct(mixed ...$input)
    {
        if (count($input) === 1) {
            $input = $input[0];
        }

        $this->array = (array)$input;
    }

    public function diff(array ...$array): static
    {
        return new static(array_diff($this->array, ...$array));
    }

    public function filter(?callable $callback = null, int $mode = 0): static
    {
        return new static(array_filter($this->array, $callback, $mode));
    }

    public function map(callable $callback): static
    {
        return new static(array_map($callback, $this->array));
    }

    public function merge(array ...$array): static
    {
        return new static(array_merge($this->array, ...$array));
    }

    public function push(mixed ...$values): static
    {
        array_push($this->array, ...$values);
        return $this;
    }

    public function shift(): mixed
    {
        return array_shift($this->array);
    }

    public function unshift(mixed ...$values): static
    {
        array_unshift($this->array, ...$values);
        return $this;
    }

    public function pop(): mixed
    {
        return array_pop($this->array);
    }

    public function fill(int $start, int $length, mixed $value): static
    {
        return new static(array_fill($start, $length, $value));
    }

    public function flip(): static
    {
        return new static(array_flip($this->array));
    }

    public function reverse(bool $preserveKeys = false): static
    {
        return new static(array_reverse($this->array, $preserveKeys));
    }

    public function search(mixed $needle, bool $strict = false): string|int|false
    {
        return array_search($needle, $this->array, $strict);
    }

    public function slice(int $offset, ?int $length = null, bool $preserveKeys = false): ArrayWrapper
    {
        $this->array = array_slice($this->array, $offset, $length, $preserveKeys);
        return $this;
    }

    public function splice(int $offset, ?int $length = null, mixed $replacement = []): ArrayWrapper
    {
        return new ArrayWrapper(array_splice($this->array, $offset, $length, $replacement));
    }

    public function unique(int $sortFlags = SORT_STRING): ArrayWrapper
    {
        $this->array = array_unique($this->array, $sortFlags);
        return $this;
    }

    public function values(): ArrayWrapper
    {
        $this->array = array_values($this->array);
        return $this;
    }

    public function keys(): ArrayWrapper
    {
        return new static(array_keys($this->array));
    }

    public function sort(int $sortFlags = SORT_REGULAR): static
    {
        sort($this->array, $sortFlags);
        return $this;
    }

    public function rsort(int $sortFlags = SORT_REGULAR): static
    {
        rsort($this->array, $sortFlags);
        return $this;
    }

    public function asort(int $sortFlags = SORT_REGULAR): static
    {
        asort($this->array, $sortFlags);
        return $this;
    }

    public function arsort(int $sortFlags = SORT_REGULAR): static
    {
        arsort($this->array, $sortFlags);
        return $this;
    }

    public function ksort(int $sortFlags = SORT_REGULAR): static
    {
        ksort($this->array, $sortFlags);
        return $this;
    }

    public function krsort(int $sortFlags = SORT_REGULAR): static
    {
        krsort($this->array, $sortFlags);
        return $this;
    }

    public function shuffle(): static
    {
        shuffle($this->array);
        return $this;
    }

    public function chunk(int $size, bool $preserveKeys = false): static
    {
        return new static(array_chunk($this->array, $size, $preserveKeys));
    }

    public function combine(array $values): static
    {
        return new static(array_combine($this->array, $values));
    }

    public function countValues(): static
    {
        return new static(array_count_values($this->array));
    }

    public function intersect(array ...$arrays): static
    {
        return new static(array_intersect($this->array, ...$arrays));
    }

    public function intersectAssoc(array ...$arrays): static
    {
        return new static(array_intersect_assoc($this->array, ...$arrays));
    }

    public function intersectKey(array ...$arrays): static
    {
        return new static(array_intersect_key($this->array, ...$arrays));
    }

    public function each(callable $callback): static
    {
        foreach ($this->array as $key => $value) {
            $callback($value, $key);
        }
        return $this;
    }

    public function filterKeys(callable $callback): static
    {
        return $this->filter($callback, ARRAY_FILTER_USE_KEY);
    }

    public function first(): mixed
    {
        return reset($this->array);
    }

    public function last(): mixed
    {
        return end($this->array);
    }

    public function get(int $index): mixed
    {
        return $this->array[$index] ?? null;
    }

    public function set(int $index, mixed $value): static
    {
        $this->array[$index] = $value;
        return $this;
    }

    public function unset(int $index): static
    {
        unset($this->array[$index]);
        return $this;
    }

    public function has(int $index): bool
    {
        return isset($this->array[$index]);
    }

    public function toArray(): array
    {
        return $this->array;
    }

    public function count(): int
    {
        return count($this->array);
    }

    public function __toString(): string
    {
        return json_encode($this->array);
    }
}
