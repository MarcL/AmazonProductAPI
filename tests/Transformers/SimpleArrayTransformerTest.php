<?php

use PHPUnit\Framework\TestCase;
use MarcL\Transformers\SimpleArrayTransformer;
use tests\helpers\AmazonXmlResponse;

class SimpleArrayTransformerTest extends TestCase {

    private function createValidAmazonXmlRequest() {
        return "<Request><IsValid>True</IsValid></Request>";
    }

    private function createInvalidAmazonXmlResponse($code, $message) {
        $amazonXmlResponse = new AmazonXmlResponse();
        $amazonXmlResponse->addRequestId('test-request-id');
        $amazonXmlResponse->addInvalidRequest($code, $message);
        return $amazonXmlResponse->asXml();
    }

    private function createValidAmazonXmlResponse() {
        $amazonXmlResponse = new AmazonXmlResponse();
        $amazonXmlResponse->addRequestId('test-request-id');
        $amazonXmlResponse->addValidRequest();
        return $amazonXmlResponse->asXml();
    }

    private function createAmazonXmlItems($amazonXmlResponse, $numberOfItems) {
        for($i = 0; $i < $numberOfItems; $i++) {
            $amazonXmlResponse->addItem("ASIN-$i", 'http://detailpage.url', '100', 'Test Title', '50',  'http://largeimage.url', 'http://mediumimage.url', 'http://smallimage.url');
        }
    }

    public function testShouldThrowExpecteExceptionIfNoXmlIsPassed() {
        $transformer = new SimpleArrayTransformer();

        $this->expectExceptionMessage('No XML response');

        $response = $transformer->execute(null);
    }

    public function testShouldThrowExpecteExceptionIfAmazonRequestIsInvalid() {
        $givenCode = 'givenCode';
        $givenMessage = 'given message';
        $testXmlData = $this->createInvalidAmazonXmlResponse($givenCode, $givenMessage);
        $givenXml = simplexml_load_string($testXmlData);

        $this->expectExceptionMessage("API error ($givenCode) : $givenMessage");

        $transformer = new SimpleArrayTransformer();
        $response = $transformer->execute($givenXml);
    }

    public function testShouldReturnEmptyArrayIfNoItemsAreFound() {
        $testXmlData = $this->createValidAmazonXmlResponse();
        $givenXml = simplexml_load_string($testXmlData);

        $transformer = new SimpleArrayTransformer();
        $response = $transformer->execute($givenXml);

        $this->assertEmpty($response);
    }

    public function testShouldReturnExpectedNumberOfItems() {
        $amazonXmlResponse = new AmazonXmlResponse();
        $amazonXmlResponse->addRequestId('test-request-id');
        $amazonXmlResponse->addValidRequest();

        $expectedNumberOfItems = 10;
        $this->createAmazonXmlItems($amazonXmlResponse, $expectedNumberOfItems);
        $testXmlData = $amazonXmlResponse->asXml();
        $givenXml = simplexml_load_string($testXmlData);

        $transformer = new SimpleArrayTransformer();
        $response = $transformer->execute($givenXml);

        $this->assertEquals($expectedNumberOfItems, count($response));
    }
}
?>
