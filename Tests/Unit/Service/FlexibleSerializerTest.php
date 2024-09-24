<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidSolutionCatalysts\Unzer\Tests\Unit\Service;

use OxidSolutionCatalysts\Unzer\Service\FlexibleSerializer;
use OxidSolutionCatalysts\Unzer\Service\Translator;
use PHPUnit\Framework\TestCase;
use OxidSolutionCatalysts\Unzer\Model\Order;

class FlexibleSerializerTest extends TestCase
{
    private FlexibleSerializer $flexibleSerializer;

    protected function setUp(): void
    {
        $this->translatorMock = $this->createMock(Translator::class);
        $this->translatorMock->method('translate')
            ->with('NOT_SERIALIZABLE')
            ->willReturn('NOT_SERIALIZABLE: ');

        $this->flexibleSerializer = new FlexibleSerializer($this->translatorMock);

        // Define mock classes
        if (!class_exists('OxidEsales\Eshop\Application\Model\Order', false)) {
            class_alias(new class {
                public int $id;
                public string $customerName;
            }, 'OxidEsales\Eshop\Application\Model\Order');
        }

        if (!class_exists('OxidSolutionCatalysts\Unzer\Model\Order', false)) {
            class_alias(new class extends \OxidEsales\Eshop\Application\Model\Order {
                public string $extraField;
            }, 'OxidSolutionCatalysts\Unzer\Model\Order');
        }
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
        $order = new \OxidEsales\Eshop\Application\Model\Order();
        $order->id = 1;
        $order->customerName = 'John Doe';

        $serialized = serialize($order);
        $unserialized = $this->flexibleSerializer->safeUnserialize(
            $serialized,
            [\OxidEsales\Eshop\Application\Model\Order::class]
        );

        $this->assertInstanceOf(\OxidEsales\Eshop\Application\Model\Order::class, $unserialized);
        $this->assertEquals(1, $unserialized->id);
        $this->assertEquals('John Doe', $unserialized->customerName);
    }

    public function testSafeUnserializeWithoutAllowedClasses(): void
    {
        $order = new \OxidEsales\Eshop\Application\Model\Order();
        $order->id = 1;
        $order->customerName = 'John Doe';

        $serialized = serialize($order);
        $unserialized = $this->flexibleSerializer->safeUnserialize($serialized);

        $this->assertInstanceOf(\stdClass::class, $unserialized);
        $this->assertEquals(1, $unserialized->id);
        $this->assertEquals('John Doe', $unserialized->customerName);
    }

    public function testSafeSerializeAndUnserializeExtendedObject(): void
    {
        $extendedOrder = new Order();
        $extendedOrder->id = 1;
        $extendedOrder->customerName = 'John Doe';
        $extendedOrder->extraField = 'Extra Info';

        $serialized = $this->flexibleSerializer->safeSerialize($extendedOrder);
        $unserialized = $this->flexibleSerializer->safeUnserialize(
            $serialized,
            [\OxidEsales\Eshop\Application\Model\Order::class]
        );

        $this->assertInstanceOf(Order::class, $unserialized);
        $this->assertEquals(1, $unserialized->id);
        $this->assertEquals('John Doe', $unserialized->customerName);
        $this->assertEquals('Extra Info', $unserialized->extraField);
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

    public function testSafeSerializeAndUnserializeCustomObject(): void
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
}
