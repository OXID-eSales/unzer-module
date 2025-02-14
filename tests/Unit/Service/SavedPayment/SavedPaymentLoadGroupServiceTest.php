<?php

namespace OxidSolutionCatalysts\Unzer\Tests\Unit\Service\SavedPayment;

use OxidSolutionCatalysts\Unzer\Service\SavedPayment\SavedPaymentLoadGroupService;
use PHPUnit\Framework\TestCase;

class SavedPaymentLoadGroupServiceTest extends TestCase
{
    /** @var SavedPaymentLoadGroupService */
    private $groupService;

    protected function setUp(): void
    {
        $this->groupService = new SavedPaymentLoadGroupService();
    }

    /**
     * @covers \OxidSolutionCatalysts\Unzer\Service\SavedPayment\SavedPaymentLoadGroupService::groupByPaymentTypeId
     */
    public function testGroupByPaymentTypeId()
    {
        // Sample data
        $ungroupedTransactions = [
            ['PAYMENTTYPEID' => 'card-123', 'OXID' => 'tx1', 'amount' => 100],
            ['PAYMENTTYPEID' => 'card-123', 'OXID' => 'tx2', 'amount' => 200],
            ['PAYMENTTYPEID' => 'paypal-456', 'OXID' => 'tx3', 'amount' => 300],
            ['PAYMENTTYPEID' => 'sepa-789', 'OXID' => 'tx4', 'amount' => 400],
        ];

        // Expected output
        $expectedGroupedTransactions = [
            ['PAYMENTTYPEID' => 'card-123', 'OXID' => 'tx2', 'amount' => 200],
            ['PAYMENTTYPEID' => 'paypal-456', 'OXID' => 'tx3', 'amount' => 300],
            ['PAYMENTTYPEID' => 'sepa-789', 'OXID' => 'tx4', 'amount' => 400],
        ];

        $result = $this->groupService->groupByPaymentTypeId($ungroupedTransactions);

        $this->assertEquals($expectedGroupedTransactions, $result);
    }

    /**
     * @covers \OxidSolutionCatalysts\Unzer\Service\SavedPayment\SavedPaymentLoadGroupService::groupByPaymentTypeId
     */
    public function testGroupByPaymentTypeIdEmptyArray()
    {
        $ungroupedTransactions = [];

        $expectedGroupedTransactions = [];

        $result = $this->groupService->groupByPaymentTypeId($ungroupedTransactions);

        $this->assertEquals($expectedGroupedTransactions, $result);
    }

    /**
     * @covers \OxidSolutionCatalysts\Unzer\Service\SavedPayment\SavedPaymentLoadGroupService::groupByPaymentTypeId
     */
    public function testGroupByPaymentTypeIdSingleGroup()
    {
        $ungroupedTransactions = [
            ['PAYMENTTYPEID' => 'card-123', 'OXID' => 'tx1', 'amount' => 100],
        ];

        $expectedGroupedTransactions = [
            ['PAYMENTTYPEID' => 'card-123', 'OXID' => 'tx1', 'amount' => 100],
        ];

        $result = $this->groupService->groupByPaymentTypeId($ungroupedTransactions);

        $this->assertEquals($expectedGroupedTransactions, $result);
    }

    /**
     * @covers \OxidSolutionCatalysts\Unzer\Service\SavedPayment\SavedPaymentLoadGroupService::groupByPaymentTypeId
     */
    public function testGroupByPaymentTypeIdMultipleGroupsWithSameID()
    {
        $ungroupedTransactions = [
            ['PAYMENTTYPEID' => 'card-123', 'OXID' => 'tx1', 'amount' => 100],
            ['PAYMENTTYPEID' => 'card-123', 'OXID' => 'tx2', 'amount' => 200],
            ['PAYMENTTYPEID' => 'card-123', 'OXID' => 'tx3', 'amount' => 300],
        ];

        $expectedGroupedTransactions = [
            ['PAYMENTTYPEID' => 'card-123', 'OXID' => 'tx3', 'amount' => 300],
        ];

        $result = $this->groupService->groupByPaymentTypeId($ungroupedTransactions);

        $this->assertEquals($expectedGroupedTransactions, $result);
    }
}
