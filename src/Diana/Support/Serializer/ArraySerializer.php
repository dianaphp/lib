<?php

namespace Diana\Support\Serializer;

use Closure;
use InvalidArgumentException;

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
        ?Closure $transform = null
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
        $this->content .= "];\n";
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
        ?Closure $transform = null
    ): string {
        return (new ArraySerializer())
            ->run($array, $transform)
            ->getContent();
    }

    public function serializeLines(iterable $array, ?Closure $transform = null): static
    {
        $array = is_array($array) ? $array : iterator_to_array($array);
        $associative = !array_is_list($array);

        foreach ($array as $key => $value) {
            if (is_object($value)) {
                $value = (array) $value;
            }

            $this->content .= str_repeat(' ', $this->indentation * $this->tabSize);
            if ($associative) {
                $this->content .= var_export($key, true) . ' => ';
            }

            if (is_array($value)) {
                $this->content .= '[';
                $this->indentation++;

                if (!empty($value)) {
                    $this->content .= PHP_EOL;
                }
                $this->serializeLines($value, $transform);
                $this->indentation--;
                if (!empty($value)) {
                    $this->content .= str_repeat(' ', $this->indentation * $this->tabSize);
                }
                $this->content .= '],' . PHP_EOL;

                continue;
            }

            $value = $transform ? $transform($value) : $value;
            if ($value !== null && !is_scalar($value)) {
                throw new InvalidArgumentException('ArraySerializer values must be null, scalars, or arrays.');
            }

            $this->content .= var_export($value, true) . ',' . PHP_EOL;
        }

        return $this;
    }

    public function getContent(): string
    {
        return $this->content;
    }
}
