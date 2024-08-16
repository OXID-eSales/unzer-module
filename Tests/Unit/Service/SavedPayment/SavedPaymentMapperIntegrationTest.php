<?php

namespace OxidSolutionCatalysts\Unzer\Tests\Unit\Service\SavedPayment;

use OxidSolutionCatalysts\Unzer\Service\SavedPayment\SavedPaymentMapper;
use OxidSolutionCatalysts\Unzer\Service\SavedPayment\SavedPaymentMethodValidator;
use PHPUnit\Framework\TestCase;

class SavedPaymentMapperIntegrationTest extends TestCase
{
    /** @var SavedPaymentMapper */
    private $mapper;

    /** @var SavedPaymentMethodValidator|\PHPUnit\Framework\MockObject\MockObject */
    private $validator;

    protected function setUp(): void
    {
        $this->validator = $this->createMock(SavedPaymentMethodValidator::class);
        $this->mapper = new SavedPaymentMapper($this->validator);
    }

    /**
     * Test the grouping of payment types with various methods
     *
     * @dataProvider getTestData
     * @covers       \OxidSolutionCatalysts\Unzer\Service\SavedPayment\SavedPaymentMapper::groupPaymentTypes
     */
    public function testGroupPaymentTypes($given, $expected)
    {
        // Mock the validator to always return true
        $this->validator->method('validate')->willReturn(true);

        $result = $this->mapper->groupPaymentTypes($given);

        $this->assertEquals($expected, $result);
    }

    public function getTestData(): array
    {
        return [
            $this->getTestData1(),
            $this->getTestData2(),
            $this->getTestData3(),
            $this->getTestData4(),
        ];
    }

    private function getTestData1(): array
    {
        return [
            [//given
                'paypal' => [
                    'user1@example.com' => ['email' => 'user1@example.com', 'other' => 'data'],
                    'user2@example.com' => ['email' => 'user2@example.com', 'other' => 'data'],
                ],
                'card' => [
                    '1234567812345678|03/40' => [
                        'number' => '1234567812345678',
                        'expiryDate' => '03/40',
                        'other' => 'data'
                    ],
                    '8765432187654321|03/40' => [
                        'number' => '8765432187654321',
                        'expiryDate' => '03/40',
                        'other' => 'data'
                    ],
                ],
                'sepa' => [
                    'DE89370400440532013000' => ['iban' => 'DE89370400440532013000', 'other' => 'data'],
                    'DE89370400440532013001' => ['iban' => 'DE89370400440532013001', 'other' => 'data'],
                ],
            ],
            [//result
                'paypal' => [
                    'user1@example.com' => ['email' => 'user1@example.com', 'other' => 'data'],
                    'user2@example.com' => ['email' => 'user2@example.com', 'other' => 'data'],
                ],
                'card' => [
                    '1234567812345678|03/40' => [
                        'number' => '1234567812345678',
                        'expiryDate' => '03/40',
                        'other' => 'data'
                    ],
                    '8765432187654321|03/40' => [
                        'number' => '8765432187654321',
                        'expiryDate' => '03/40',
                        'other' => 'data'
                    ],
                ],
                'sepa' => [
                    'DE89370400440532013000' => ['iban' => 'DE89370400440532013000', 'other' => 'data'],
                    'DE89370400440532013001' => ['iban' => 'DE89370400440532013001', 'other' => 'data'],
                ],
            ]
        ];
    }

    private function getTestData2(): array
    {
        return [
            [//given
                'paypal' => [
                    'user1@example.com' => ['email' => 'user1@example.com', 'other' => 'data'],
                    'user1@example.com' => ['email' => 'user1@example.com', 'other' => 'data'],
                ],
                'card' => [
                    '1234567812345678|03/40' => [
                        'number' => '1234567812345678',
                        'expiryDate' => '03/40',
                        'other' => 'data'
                    ],
                    '8765432187654321|03/40' => [
                        'number' => '8765432187654321',
                        'expiryDate' => '03/40',
                        'other' => 'data'
                    ],
                ],
                'sepa' => [
                    'DE89370400440532013000' => ['iban' => 'DE89370400440532013000', 'other' => 'data'],
                    'DE89370400440532013001' => ['iban' => 'DE89370400440532013001', 'other' => 'data'],
                ],
            ],
            [//result
                'paypal' => [
                    'user1@example.com' => ['email' => 'user1@example.com', 'other' => 'data'],
                ],
                'card' => [
                    '1234567812345678|03/40' => [
                        'number' => '1234567812345678',
                        'expiryDate' => '03/40',
                        'other' => 'data'
                    ],
                    '8765432187654321|03/40' => [
                        'number' => '8765432187654321',
                        'expiryDate' => '03/40',
                        'other' => 'data'
                    ],
                ],
                'sepa' => [
                    'DE89370400440532013000' => ['iban' => 'DE89370400440532013000', 'other' => 'data'],
                    'DE89370400440532013001' => ['iban' => 'DE89370400440532013001', 'other' => 'data'],
                ],
            ]
        ];
    }

    private function getTestData3(): array
    {
        return [
            [//given
                'paypal' => [
                    'user1@example.com' => ['email' => 'user1@example.com', 'other' => 'data'],
                    'user2@example.com' => ['email' => 'user2@example.com', 'other' => 'data'],
                ],
                'card' => [
                    '1234567812345678|03/40' => [
                        'number' => '1234567812345678',
                        'expiryDate' => '03/40',
                        'other' => 'data'
                    ],
                    '1234567812345678|03/40' => [
                        'number' => '1234567812345678',
                        'expiryDate' => '03/40',
                        'other' => 'data'
                    ],
                ],
                'sepa' => [
                    'DE89370400440532013000' => ['iban' => 'DE89370400440532013000', 'other' => 'data'],
                    'DE89370400440532013001' => ['iban' => 'DE89370400440532013001', 'other' => 'data'],
                ],
            ],
            [//result
                'paypal' => [
                    'user1@example.com' => ['email' => 'user1@example.com', 'other' => 'data'],
                    'user2@example.com' => ['email' => 'user2@example.com', 'other' => 'data'],
                ],
                'card' => [
                    '1234567812345678|03/40' => [
                        'number' => '1234567812345678',
                        'expiryDate' => '03/40',
                        'other' => 'data'
                    ],
                ],
                'sepa' => [
                    'DE89370400440532013000' => ['iban' => 'DE89370400440532013000', 'other' => 'data'],
                    'DE89370400440532013001' => ['iban' => 'DE89370400440532013001', 'other' => 'data'],
                ],
            ]
        ];
    }

    private function getTestData4(): array
    {
        return [
            [//given
                'paypal' => [
                    'user1@example.com' => ['email' => 'user1@example.com', 'other' => 'data'],
                    'user2@example.com' => ['email' => 'user2@example.com', 'other' => 'data'],
                ],
                'card' => [
                    '1234567812345678|03/40' => [
                        'number' => '1234567812345678',
                        'expiryDate' => '03/40',
                        'other' => 'data'
                    ],
                    '8765432187654321|03/40' => [
                        'number' => '8765432187654321',
                        'expiryDate' => '03/40',
                        'other' => 'data'
                    ],
                ],
                'sepa' => [
                    'DE89370400440532013000' => ['iban' => 'DE89370400440532013000', 'other' => 'data'],
                    'DE89370400440532013000' => ['iban' => 'DE89370400440532013000', 'other' => 'data'],
                ],
            ],
            [//result
                'paypal' => [
                    'user1@example.com' => ['email' => 'user1@example.com', 'other' => 'data'],
                    'user2@example.com' => ['email' => 'user2@example.com', 'other' => 'data'],
                ],
                'card' => [
                    '1234567812345678|03/40' => [
                        'number' => '1234567812345678',
                        'expiryDate' => '03/40',
                        'other' => 'data'
                    ],
                    '8765432187654321|03/40' => [
                        'number' => '8765432187654321',
                        'expiryDate' => '03/40',
                        'other' => 'data'
                    ],
                ],
                'sepa' => [
                    'DE89370400440532013000' => ['iban' => 'DE89370400440532013000', 'other' => 'data'],
                ],
            ]
        ];
    }
}
