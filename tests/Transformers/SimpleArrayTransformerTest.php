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

    private function createAmazonItem($asin, $pageUrl) {
        return "<Item><ASIN>$asin</ASIN><DetailPageURL>$pageUrl</DetailPageURL></Item>";
    }

    private function createAmazonItemList($numItems) {
        $itemListString = '';
        for($i = 0; $i < $numItems; $i++) {
            $itemListString .= $this->createAmazonItem("ASIN-$i", "https://amazon.com/ASIN-$i");
        }

        return $itemListString;
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

    // public function testShouldReturnExpectedNumberOfItems() {
    //     $expectedNumberOfItems = 10;
    //     $itemList = $this->createAmazonItemList($expectedNumberOfItems);
    //     $testXmlData = $this->createValidAmazonXmlResponse($itemList);
    //     $givenXml = simplexml_load_string($testXmlData);

    //     $transformer = new SimpleArrayTransformer();
    //     $response = $transformer->execute($givenXml);
    //     var_dump($response);

    //     $this->assertEquals($expectedNumberOfItems, count($response));
    // }
}
?>
