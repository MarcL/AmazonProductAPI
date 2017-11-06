<?php

use PHPUnit\Framework\TestCase;
use MarcL\Transformers\JsonTransformer;

class JsonTransformerTest extends TestCase {
    public function testShouldReturnExpectedJson() {
        $transformer = new JsonTransformer();
        $testXmlData = "<?xml version=\"1.0\"?><OperationRequest><RequestId>9852889b-383b-4f09-ac23-4448e7ce8a16</RequestId></OperationRequest>";
        $arrayJson = $transformer->execute($testXmlData);

        $this->assertEquals($arrayJson, json_encode($testXmlData));
    }
}
?>
