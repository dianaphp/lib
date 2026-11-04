<?php

namespace Diana\Tests\Helpers;

use DateTime;
use DateTimeImmutable;
use Diana\Support\Helpers\Data;
use PHPUnit\Framework\TestCase;
use ReflectionFunction;
use ReflectionMethod;

class DataTest extends TestCase
{
    public function testUnionTypesReturnClassNamesAndIgnoreBuiltins(): void
    {
        $parameter = (new ReflectionFunction(static function (DateTime|DateTimeImmutable|string|null $value): void {}))->getParameters()[0];
        self::assertSame([DateTime::class, DateTimeImmutable::class], Data::getParameterClassNames($parameter));
        self::assertNull(Data::getParameterClassName($parameter));
    }

    public function testSelfAndParentAreResolvedWithinUnions(): void
    {
        $parameter = (new ReflectionMethod(TypeChild::class, 'union'))->getParameters()[0];
        self::assertSame([TypeChild::class, TypeParent::class], Data::getParameterClassNames($parameter));
    }

    public function testSingleNullableClassAndBuiltinParameters(): void
    {
        $parameters = (new ReflectionFunction(static function (?DateTime $date, int $number, $untyped): void {}))->getParameters();
        self::assertSame([DateTime::class], Data::getParameterClassNames($parameters[0]));
        self::assertSame([], Data::getParameterClassNames($parameters[1]));
        self::assertSame([], Data::getParameterClassNames($parameters[2]));
    }
}

class TypeParent {}
class TypeChild extends TypeParent
{
    public function union(self|parent $value): void {}
}
