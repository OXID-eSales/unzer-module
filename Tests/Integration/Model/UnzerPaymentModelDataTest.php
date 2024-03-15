<?php

namespace OxidSolutionCatalysts\Unzer\Tests\Integration\Model;

use OxidEsales\TestingLibrary\UnitTestCase;
use OxidSolutionCatalysts\Unzer\Model\UnzerPaymentData;

class UnzerPaymentModelDataTest extends UnitTestCase
{
    /** @var string */
    private $paymentDataJson;

    public function setUp(): void
    {
        $this->paymentDataJson = file_get_contents(__DIR__ . '/../../fixtures/paymentData.json');
        parent::setUp();
    }

    public function testPaymentDataParse()
    {
        $decodedPaymentDataArray = json_decode($this->paymentDataJson, true);
        $paymentData = new UnzerPaymentData($decodedPaymentDataArray);

        $this->assertEquals($decodedPaymentDataArray['id'], $paymentData->id);
        $this->assertEquals($decodedPaymentDataArray['isSuccess'], $paymentData->isSuccess);
        $this->assertEquals($decodedPaymentDataArray['isPending'], $paymentData->isPending);
        $this->assertEquals($decodedPaymentDataArray['isResumed'], $paymentData->isResumed);
        $this->assertEquals($decodedPaymentDataArray['isError'], $paymentData->isError);
        $this->assertEquals($decodedPaymentDataArray['url'], $paymentData->url);
        $this->assertEquals($decodedPaymentDataArray['timestamp'], $paymentData->timestamp);
        $this->assertEquals($decodedPaymentDataArray['traceId'], $paymentData->traceId);
        $this->assertEquals($decodedPaymentDataArray['paymentId'], $paymentData->paymentId);
        $this->assertEquals($decodedPaymentDataArray['errors'][0]['code'], $paymentData->errors[0]->code);
        $this->assertEquals(
            $decodedPaymentDataArray['errors'][0]['customerMessage'],
            $paymentData->errors[0]->customerMessage
        );
        $this->assertEquals(
            $decodedPaymentDataArray['errors'][0]['status']['successful'],
            $paymentData->errors[0]->status->successful
        );
        $this->assertEquals(
            $decodedPaymentDataArray['errors'][0]['status']['processing'],
            $paymentData->errors[0]->status->processing
        );
        $this->assertEquals(
            $decodedPaymentDataArray['errors'][0]['status']['pending'],
            $paymentData->errors[0]->status->pending
        );
        $this->assertEquals(
            $decodedPaymentDataArray['errors'][0]['processing']['uniqueId'],
            $paymentData->errors[0]->processing->uniqueId
        );
        $this->assertEquals(
            $decodedPaymentDataArray['errors'][0]['processing']['shortId'],
            $paymentData->errors[0]->processing->shortId
        );
    }
}
