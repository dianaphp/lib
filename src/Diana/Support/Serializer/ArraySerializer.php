<?php

namespace Diana\Support\Serializer;

use Closure;
use Diana\Support\Helpers\Arr;

class ArraySerializer
{
    protected int $indentation;

    protected string $content;

    public function __construct(protected int $tabSize = 4)
    {
        $this->reset();
    }

    /**
     * Runs a whole file
     *
     * @return static
     */
    public function run(
        mixed $array,
        Closure $transform = null
    ): static {
        return $this
            ->serializeLines($array, $transform)
            ->terminate();
    }

    /**
     * Resets the buffer for a new file
     *
     * @return static
     */
    public function reset(): static
    {
        $this->content = '<?php' . str_repeat(PHP_EOL, 2) . 'return [' . PHP_EOL;
        $this->indentation = 1;
        return $this;
    }

    /**
     * Terminates the buffer
     *
     * @return void
     */
    public function terminate(): static
    {
        $this->content .= '];';
        return $this;
    }

    /**
     * Serializes the array
     *
     * @param mixed $array
     * @param Closure $transform
     */
    public static function serialize(
        mixed $array,
        Closure $transform = null
    ): string {
        return (new ArraySerializer())
            ->run($array, $transform)
            ->getContent();
    }

    public function serializeLines(iterable $array, Closure $transform = null): static
    {
        if (!$transform)
            $transform = fn($value) => is_string($value) ? preg_replace("/([^\\\])'/", "$1\'", $value) : $value;

        $associative = Arr::isAssociative($array);

        foreach ($array as $key => $value) {
            if (is_object($value))
                $value = (array) $value;

            if ($associative)
                $this->content .= str_repeat(' ', $this->indentation * $this->tabSize) . "'{$key}' => ";

            if (is_array($value)) {
                $this->content .= '[';
                $this->indentation++;
                $this->serializeLines($value, $transform);
                $this->indentation--;
                $this->content .= '],';

                continue;
            }

            $value = $transform($value);

            $this->content .= match (true) {
                is_string($value) => "'{$value}'",
                is_bool($value) => $value ? 'true' : 'false',
                default => $value
            } . ',' . PHP_EOL;
        }

        return $this;
    }

    public function getContent(): string
    {
        return $this->content;
    }
}