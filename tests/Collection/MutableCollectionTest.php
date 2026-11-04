<?php

namespace Diana\Tests\Collection;

use Diana\Support\Collection\MutableCollection;
use PHPUnit\Framework\TestCase;

class MutableCollectionTest extends TestCase
{
    public function testNullableReferenceAndAllowedMutation(): void
    {
        $reference = null;
        $collection = new MutableCollection(['name' => 'before'], $reference, fillable: ['name']);
        $collection->set('name', 'after');
        self::assertSame(['name' => 'after'], $collection->getAll());
        self::assertNull($reference);
    }

    public function testMutationsCanUseAnArrayReference(): void
    {
        $reference = ['name' => 'before'];
        $collection = new MutableCollection(byReference: $reference, fillable: ['name']);
        $collection->set('name', 'after');
        self::assertSame(['name' => 'after'], $reference);
    }
}
