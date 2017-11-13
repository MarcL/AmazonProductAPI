<?php

use PHPUnit\Framework\TestCase;
use MarcL\Transformers\JsonTransformer;
use tests\helpers\AmazonXmlResponse;

class JsonTransformerTest extends TestCase {
    public function testShouldReturnExpectedJson() {
        $amazonXmlResponse = new AmazonXmlResponse();
        $amazonXmlResponse->addRequestId('test-request-id');

        $transformer = new JsonTransformer();
        $testXmlData = $amazonXmlResponse->asXml();
        $givenXml = simplexml_load_string($testXmlData);
        $expectedResponse = json_encode($givenXml);

        $response = $transformer->execute($givenXml);

        $this->assertEquals($expectedResponse, $response);
    }
}
?>
