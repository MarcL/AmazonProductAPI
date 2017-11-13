<?php

use PHPUnit\Framework\TestCase;
use MarcL\Transformers\XmlTransformer;
use tests\helpers\AmazonXmlResponse;

class XmlTransformerTest extends TestCase {
    public function testShouldReturnExpectedXml() {
        $amazonXmlResponse = new AmazonXmlResponse();
        $amazonXmlResponse->addRequestId('test-request-id');

        $transformer = new XmlTransformer();
        $testXmlData = $amazonXmlResponse->asXml();
        $givenXml = simplexml_load_string($testXmlData);

        $response = $transformer->execute($givenXml);

        $this->assertSame($givenXml, $response);
    }
}
?>
