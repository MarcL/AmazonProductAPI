<?php

use PHPUnit\Framework\TestCase;
use MarcL\Transformers\SimpleArrayTransformer;

class SimpleArrayTransformerTest extends TestCase {
    private function createInvalidAmazonXmlRequest($code, $message) {
        return "<Request><IsValid>False</IsValid><Errors><Error><Code>$code</Code><Message>$message</Message></Error></Errors></Request>";
    }

    private function createValidAmazonXmlRequest() {
        return "<Request><IsValid>True</IsValid></Request>";
    }

    private function createInvalidAmazonXmlResponse($code, $message) {
        $request = $this->createInvalidAmazonXmlRequest($code, $message);
        return "<?xml version=\"1.0\"?><OperationRequest><RequestId>9852889b-383b-4f09-ac23-4448e7ce8a16</RequestId><Items>$request</Items></OperationRequest>";
    }

    private function createValidAmazonXmlResponse() {
        $request = $this->createValidAmazonXmlRequest();
        return "<?xml version=\"1.0\"?><OperationRequest><RequestId>9852889b-383b-4f09-ac23-4448e7ce8a16</RequestId><Items>$request</Items></OperationRequest>";
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
}
?>
