<?php

namespace Diana\Tests\Collection;

use Diana\Support\Collection\Collection;
use Diana\Support\Collection\ImmutableCollection;
use Diana\Support\Collection\MutableCollection;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class CollectionVisibilityTest extends TestCase
{
    private const ITEMS = ['name' => 'Ada', 'email' => null, 'password' => 'secret'];

    public static function collectionClasses(): iterable
    {
        yield 'collection' => [Collection::class];
        yield 'mutable' => [MutableCollection::class];
        yield 'immutable' => [ImmutableCollection::class];
    }

    private function assertOutput(array $expected, Collection $collection): void
    {
        self::assertSame($expected, $collection->getVisible());
        self::assertSame($expected, $collection->jsonSerialize());
        self::assertSame(json_encode($expected, JSON_THROW_ON_ERROR), json_encode($collection, JSON_THROW_ON_ERROR));
        self::assertSame(json_encode($expected, JSON_THROW_ON_ERROR), (string) $collection);
        self::assertSame(self::ITEMS, $collection->getAll());
    }

    #[DataProvider('collectionClasses')]
    public function testDefaultVisibilityAndHiddenConstructorKeys(string $class): void
    {
        $this->assertOutput(self::ITEMS, new $class(self::ITEMS));
        $this->assertOutput(['name' => 'Ada', 'email' => null], new $class(self::ITEMS, hidden: ['password']));
    }

    #[DataProvider('collectionClasses')]
    public function testExplicitVisibleConstructorKeysAndHiddenPrecedence(string $class): void
    {
        $collection = new $class(self::ITEMS, hidden: ['password'], visible: ['name', 'password', 'missing']);
        $this->assertOutput(['name' => 'Ada'], $collection);
    }

    #[DataProvider('collectionClasses')]
    public function testExplicitEmptyVisibilityNeverFallsBackToAllItems(string $class): void
    {
        $this->assertOutput([], new $class(self::ITEMS, visible: []));
        $this->assertOutput(self::ITEMS, new $class(self::ITEMS, visible: null));
        $collection = new $class(self::ITEMS);
        self::assertSame($collection, $collection->setVisible([]));
        $this->assertOutput([], $collection);
    }

    #[DataProvider('collectionClasses')]
    public function testVisibleMutatorsAcceptStringsArraysAndMixedArguments(string $class): void
    {
        $collection = new $class(self::ITEMS, hidden: ['password']);
        self::assertSame($collection, $collection->setVisible('name', ['email'], ['password', 'missing']));
        self::assertSame(['name', 'email', 'missing'], array_values($collection->getVisibleKeys()));
        $this->assertOutput(['name' => 'Ada', 'email' => null], $collection);
        self::assertSame($collection, $collection->unsetVisible(['email', 'password'], 'missing'));
        $this->assertOutput(['name' => 'Ada'], $collection);
        $collection->unsetVisible('name');
        self::assertSame([], $collection->getVisibleKeys());
        $this->assertOutput([], $collection);
        $collection->setVisible(['email']);
        $this->assertOutput(['email' => null], $collection);
    }

    #[DataProvider('collectionClasses')]
    public function testUnsetVisibleRestrictsDefaultVisibility(string $class): void
    {
        $collection = new $class(self::ITEMS);
        $collection->unsetVisible(['password'], 'email');
        $this->assertOutput(['name' => 'Ada'], $collection);
        $collection->unsetVisible(['name']);
        $this->assertOutput([], $collection);
    }

    #[DataProvider('collectionClasses')]
    public function testHiddenMutatorsAcceptStringsArraysAndMixedArguments(string $class): void
    {
        $collection = new $class(self::ITEMS);
        self::assertSame($collection, $collection->setHidden('password', ['email']));
        self::assertSame(['password', 'email'], $collection->getHiddenKeys());
        self::assertSame(['email' => null, 'password' => 'secret'], $collection->getHidden());
        $this->assertOutput(['name' => 'Ada'], $collection);
        self::assertSame($collection, $collection->unsetHidden(['password'], 'email'));
        $this->assertOutput(self::ITEMS, $collection);
        $collection->setHidden(['name', 'email', 'password']);
        $this->assertOutput([], $collection);
    }

    #[DataProvider('collectionClasses')]
    public function testHiddenFieldsCannotBeExposedBySetVisible(string $class): void
    {
        $collection = new $class(self::ITEMS, hidden: ['password']);
        $collection->setVisible('password');
        $this->assertOutput([], $collection);
        $collection->unsetHidden('password');
        $this->assertOutput(['password' => 'secret'], $collection);
    }

    public function testMutableConstructorPreservesPositionalParameterMeaning(): void
    {
        $reference = null;
        $collection = new MutableCollection(self::ITEMS, $reference, true, ['name', 'password'], ['password'], ['name']);
        $this->assertOutput(['name' => 'Ada'], $collection);
        self::assertSame(['name'], $collection->getFillable());
        $collection->set('name', 'Grace');
        self::assertSame('{"name":"Grace"}', json_encode($collection, JSON_THROW_ON_ERROR));
    }

    public function testMutableVisibilityAlsoAppliesToReferencedItems(): void
    {
        $reference = self::ITEMS;
        $collection = new MutableCollection(byReference: $reference, visible: ['name', 'password'], hidden: ['password']);
        $reference['name'] = 'Grace';
        self::assertSame('{"name":"Grace"}', json_encode($collection, JSON_THROW_ON_ERROR));
        self::assertSame('secret', $reference['password']);
    }
}
