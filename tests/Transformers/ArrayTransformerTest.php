<?php

use PHPUnit\Framework\TestCase;
use MarcL\Transformers\ArrayTransformer;

class ArrayTransformerTest extends TestCase {
    public function testShouldThrowExceptionIfMissingKeyId() {
        $transformer = new ArrayTransformer();
        $testXmlData = '<?xml version="1.0"?><OperationRequest></OperationRequest>';
        $arrayJson = $transformer->execute($testXmlData);

        $this->assertEquals($arrayJson, $testXmlData);
    }
}
?>
