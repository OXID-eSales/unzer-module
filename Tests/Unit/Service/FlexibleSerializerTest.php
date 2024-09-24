<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidSolutionCatalysts\Unzer\Tests\Unit\Service;

use OxidSolutionCatalysts\Unzer\Service\FlexibleSerializer;
use OxidSolutionCatalysts\Unzer\Service\Translator;
use PHPUnit\Framework\TestCase;

class FlexibleSerializerTest extends TestCase
{
    private FlexibleSerializer $flexibleSerializer;

    protected function setUp(): void
    {
        $translatorMock = $this->createMock(Translator::class);
        $translatorMock->method('translate')
            ->with('OSCUNZER_NOT_SERIALIZABLE')
            ->willReturn('NOT SERIALIZABLE: ');

        $this->flexibleSerializer = new FlexibleSerializer($translatorMock);
    }

    public function testSafeSerializeAndUnserializeSimpleObject(): void
    {
        $testObject = new \stdClass();
        $testObject->name = 'Test';
        $testObject->value = 42;

        $serialized = $this->flexibleSerializer->safeSerialize($testObject);
        $unserialized = $this->flexibleSerializer->safeUnserialize($serialized);

        $this->assertEquals($testObject, $unserialized);
    }

    public function testSafeSerializeAndUnserializeWithNonSerializableProperty(): void
    {
        $testObject = new \stdClass();
        $testObject->name = 'Test';
        $testObject->resource = fopen('php://memory', 'r');

        $serialized = $this->flexibleSerializer->safeSerialize($testObject);
        $unserialized = $this->flexibleSerializer->safeUnserialize($serialized);

        $this->assertEquals('Test', $unserialized->name);
        $this->assertNull($unserialized->resource);
    }

    public function testSafeUnserializeWithAllowedClasses(): void
    {
        $testObject = new TestSerializableClass();
        $testObject->name = 'Test';

        $serialized = serialize($testObject);
        $unserialized = $this->flexibleSerializer->safeUnserialize($serialized, [TestSerializableClass::class]);

        $this->assertInstanceOf(TestSerializableClass::class, $unserialized);
        $this->assertEquals('Test', $unserialized->name);
    }

    public function testSafeUnserializeWithoutAllowedClasses(): void
    {
        $testObject = new TestSerializableClass();
        $testObject->name = 'Test';

        $serialized = serialize($testObject);
        $unserialized = $this->flexibleSerializer->safeUnserialize($serialized);

        $this->assertInstanceOf(\stdClass::class, $unserialized);
        $this->assertEquals('Test', $unserialized->name);
    }

    public function testSafeSerializeAndUnserializeNestedObject(): void
    {
        $testObject = new \stdClass();
        $testObject->name = 'Test';
        $testObject->nested = new \stdClass();
        $testObject->nested->value = 42;

        $serialized = $this->flexibleSerializer->safeSerialize($testObject);
        $unserialized = $this->flexibleSerializer->safeUnserialize($serialized);

        $this->assertInstanceOf(\stdClass::class, $unserialized);
        $this->assertEquals('Test', $unserialized->name);
        $this->assertInstanceOf(\stdClass::class, $unserialized->nested);
        $this->assertEquals(42, $unserialized->nested->value);
    }

    public function testSafeSerializeAndUnserializeCustomObject()
    {
        $order = new Order();
        $order->id = 1;
        $order->customerName = 'John Doe';

        $serialized = $this->flexibleSerializer->safeSerialize($order);
        $unserialized = $this->flexibleSerializer->safeUnserialize($serialized, [Order::class]);

        $this->assertInstanceOf(Order::class, $unserialized);
        $this->assertEquals(1, $unserialized->id);
        $this->assertEquals('John Doe', $unserialized->customerName);
    }

    public function testSafeSerializeAndUnserializeExtendedObject()
    {
        $extendedOrder = new ExtendedOrder();
        $extendedOrder->id = 1;
        $extendedOrder->customerName = 'John Doe';
        $extendedOrder->extraField = 'Extra Info';

        $serialized = $this->flexibleSerializer->safeSerialize($extendedOrder);
        $unserialized = $this->flexibleSerializer->safeUnserialize($serialized, [Order::class]);

        $this->assertInstanceOf(ExtendedOrder::class, $unserialized);
        $this->assertEquals(1, $unserialized->id);
        $this->assertEquals('John Doe', $unserialized->customerName);
        $this->assertEquals('Extra Info', $unserialized->extraField);
    }
}

class TestSerializableClass
{
    public string $name;
}

class Order
{
    public int $id;
    public string $customerName;
}

class ExtendedOrder extends Order
{
    public string $extraField;
}