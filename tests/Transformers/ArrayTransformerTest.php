<?php

use PHPUnit\Framework\TestCase;
use MarcL\Transformers\ArrayTransformer;
use tests\helpers\AmazonXmlResponse;

class ArrayTransformerTest extends TestCase {
    public function testShouldReturnExpectedXml() {
        $amazonXmlResponse = new AmazonXmlResponse();
        $amazonXmlResponse->addRequestId('test-request-id');

        $transformer = new ArrayTransformer();
        $testXmlData = $amazonXmlResponse->asXml();
        $givenXml = simplexml_load_string($testXmlData);
        $expectedResponse = json_decode(json_encode($givenXml));

        $response = $transformer->execute($givenXml);

        $this->assertEquals($expectedResponse, $response);
    }
}
?>
