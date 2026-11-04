<?php

namespace Diana\Tests\Serializer;

use Diana\Support\Serializer\ArraySerializer;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class ArraySerializerTest extends TestCase
{
    public static function arrays(): iterable
    {
        yield 'empty' => [[]];
        yield 'types' => [[null, true, false, 0, -42, PHP_INT_MAX, 1.0, -1.25, INF, -INF]];
        yield 'strings' => [["O'Reilly", "'leading", "trailing\\", "\\'", "\\\\'", '', "line\nnext\r\t", "\x00\xff", 'こんにちは', '<?php throw new Exception();', "'); throw new Exception(); //"]];
        yield 'keys' => [["O'Reilly" => null, "trailing\\" => 'value', "\\'" => false, "line\nnext" => true, '' => []]];
        yield 'sparse and mixed keys' => [[3 => 'three', -2 => 'negative', 'nested' => [4 => null, "a'b" => ['end\\']]]];
    }

    #[DataProvider('arrays')]
    public function testPhpRoundtripPreservesValuesAndKeys(array $values): void
    {
        $source = ArraySerializer::serialize($values);
        token_get_all($source, TOKEN_PARSE);
        $path = tempnam(sys_get_temp_dir(), 'diana-serializer-');
        try {
            file_put_contents($path, $source);
            self::assertSame($values, require $path);
        } finally {
            unlink($path);
        }
    }

    public function testTransformIsAppliedToNestedValuesBeforeEscaping(): void
    {
        $source = ArraySerializer::serialize(['nested' => ['original']], fn ($value) => "'$value\\");
        self::assertSame(['nested' => ["'original\\"]], eval(substr($source, 5)));
    }

    public function testIterableAndReset(): void
    {
        $serializer = new ArraySerializer(2);
        $source = $serializer->run((static function () { yield "a'b" => null; })())->getContent();
        self::assertSame(["a'b" => null], eval(substr($source, 5)));
        self::assertSame([], eval(substr($serializer->reset()->run([])->getContent(), 5)));
    }

    public function testResourcesAreRejected(): void
    {
        $resource = fopen('php://memory', 'r');
        try {
            $this->expectException(InvalidArgumentException::class);
            ArraySerializer::serialize([$resource]);
        } finally {
            fclose($resource);
        }
    }
}
