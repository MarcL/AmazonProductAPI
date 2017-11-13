<?php

use PHPUnit\Framework\TestCase;
use MarcL\Transformers\SimpleArrayTransformer;
use tests\helpers\AmazonXmlResponse;

class SimpleArrayTransformerTest extends TestCase {

    private function createInvalidAmazonXmlResponse($code, $message) {
        $amazonXmlResponse = new AmazonXmlResponse();
        $amazonXmlResponse->addRequestId('testRequestId');
        $amazonXmlResponse->addInvalidRequest($code, $message);

        return $amazonXmlResponse;
    }

    private function createDefaultAmazonXmlResponse() {
        $amazonXmlResponse = new AmazonXmlResponse();
        $amazonXmlResponse->addRequestId('testRequestId');
        $amazonXmlResponse->addValidRequest();

        return $amazonXmlResponse;
    }

    private function createDefaultItem($amazonXmlResponse, $asin = 'defaultAsin') {
        $item = $amazonXmlResponse->addItem($asin);

        $amazonXmlResponse->addItemDetailPageUrl($item, 'https://detailpage.url');
        $amazonXmlResponse->addItemItemAttributes($item, '100', 'Test Title');
        $amazonXmlResponse->addItemOfferSummary($item, '50');
        $amazonXmlResponse->addItemLargeImage($item, 'https://defaultimage.url');
        $amazonXmlResponse->addItemMediumImage($item, 'https://defaultimage.url');
        $amazonXmlResponse->addItemSmallImage($item, 'https://defaultimage.url');

        return $item;
    }

    private function createAmazonXmlItems($amazonXmlResponse, $numberOfItems) {
        for($i = 0; $i < $numberOfItems; $i++) {
            $item = $this->createDefaultItem($amazonXmlResponse, "ASIN-$i");
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
        $amazonXmlResponse = $this->createInvalidAmazonXmlResponse($givenCode, $givenMessage);
        $givenXml = simplexml_load_string($amazonXmlResponse->asXml());

        $this->expectExceptionMessage("API error ($givenCode) : $givenMessage");

        $transformer = new SimpleArrayTransformer();
        $response = $transformer->execute($givenXml);
    }

    public function testShouldReturnEmptyArrayIfNoItemsAreFound() {
        $amazonXmlResponse = $this->createDefaultAmazonXmlResponse();
        $givenXml = simplexml_load_string($amazonXmlResponse->asXml());

        $transformer = new SimpleArrayTransformer();
        $response = $transformer->execute($givenXml);

        $this->assertEmpty($response);
    }

    public function testShouldReturnExpectedNumberOfItems() {
        $amazonXmlResponse = $this->createDefaultAmazonXmlResponse();

        $expectedNumberOfItems = 10;
        $this->createAmazonXmlItems($amazonXmlResponse, $expectedNumberOfItems);
        $testXmlData = $amazonXmlResponse->asXml();
        $givenXml = simplexml_load_string($testXmlData);

        $transformer = new SimpleArrayTransformer();
        $response = $transformer->execute($givenXml);

        $this->assertEquals($expectedNumberOfItems, count($response));
    }

    public function testShouldReturnItemWithExpectedAsin() {
        $givenAsin = 'givenAsin';
        $amazonXmlResponse = $this->createDefaultAmazonXmlResponse();
        $this->createDefaultItem($amazonXmlResponse, $givenAsin);

        $testXmlData = $amazonXmlResponse->asXml();
        $givenXml = simplexml_load_string($testXmlData);

        $transformer = new SimpleArrayTransformer();
        $response = $transformer->execute($givenXml);

        $this->assertEquals($givenAsin, $response[0]['asin']);
    }

    public function testShouldReturnItemWithExpectedUrl() {
        $givenUrl = 'https://given.url';
        $amazonXmlResponse = $this->createDefaultAmazonXmlResponse();

        $item = $amazonXmlResponse->addItem('defaultAsin');
        $amazonXmlResponse->addItemDetailPageUrl($item, $givenUrl);
        $amazonXmlResponse->addItemItemAttributes($item, '100', 'Test Title');
        $amazonXmlResponse->addItemOfferSummary($item, '50');
        $amazonXmlResponse->addItemLargeImage($item, 'http://largeimage.url');
        $amazonXmlResponse->addItemMediumImage($item, 'http://mediumimage.url');
        $amazonXmlResponse->addItemSmallImage($item, 'http://smallimage.url');

        $testXmlData = $amazonXmlResponse->asXml();
        $givenXml = simplexml_load_string($testXmlData);

        $transformer = new SimpleArrayTransformer();
        $response = $transformer->execute($givenXml);

        $this->assertEquals($givenUrl, $response[0]['url']);
    }

    public function testShouldReturnItemWithExpectedRrp() {
        $givenAmount = '1000';
        $expectedRrp = ((float) $givenAmount) / 100;
        $amazonXmlResponse = $this->createDefaultAmazonXmlResponse();

        $item = $amazonXmlResponse->addItem('defaultAsin');
        $amazonXmlResponse->addItemDetailPageUrl($item, 'https://detailpage.url');
        $amazonXmlResponse->addItemItemAttributes($item, $givenAmount, 'Test Title');
        $amazonXmlResponse->addItemOfferSummary($item, '50');
        $amazonXmlResponse->addItemLargeImage($item, 'http://largeimage.url');
        $amazonXmlResponse->addItemMediumImage($item, 'http://mediumimage.url');
        $amazonXmlResponse->addItemSmallImage($item, 'http://smallimage.url');

        $testXmlData = $amazonXmlResponse->asXml();
        $givenXml = simplexml_load_string($testXmlData);

        $transformer = new SimpleArrayTransformer();
        $response = $transformer->execute($givenXml);

        $this->assertEquals($expectedRrp, $response[0]['rrp']);
    }

    public function testShouldReturnItemWithExpectedTitle() {
        $givenTitle = 'givenTitle';
        $amazonXmlResponse = $this->createDefaultAmazonXmlResponse();

        $item = $amazonXmlResponse->addItem('defaultAsin');
        $amazonXmlResponse->addItemDetailPageUrl($item, 'https://detailpage.url');
        $amazonXmlResponse->addItemItemAttributes($item, '100', $givenTitle);
        $amazonXmlResponse->addItemOfferSummary($item, '50');
        $amazonXmlResponse->addItemLargeImage($item, 'http://largeimage.url');
        $amazonXmlResponse->addItemMediumImage($item, 'http://mediumimage.url');
        $amazonXmlResponse->addItemSmallImage($item, 'http://smallimage.url');

        $testXmlData = $amazonXmlResponse->asXml();
        $givenXml = simplexml_load_string($testXmlData);

        $transformer = new SimpleArrayTransformer();
        $response = $transformer->execute($givenXml);

        $this->assertEquals($givenTitle, $response[0]['title']);
    }

    public function testShouldReturnItemWithExpectedLowestPriceIfPresent() {
        $givenAmount = '1000';
        $expectedLowestPrice = ((float) $givenAmount) / 100;
        $amazonXmlResponse = $this->createDefaultAmazonXmlResponse();

        $item = $amazonXmlResponse->addItem('defaultAsin');
        $amazonXmlResponse->addItemDetailPageUrl($item, 'https://detailpage.url');
        $amazonXmlResponse->addItemItemAttributes($item, '100', 'default title');
        $amazonXmlResponse->addItemOfferSummary($item, $givenAmount);
        $amazonXmlResponse->addItemLargeImage($item, 'http://largeimage.url');
        $amazonXmlResponse->addItemMediumImage($item, 'http://mediumimage.url');
        $amazonXmlResponse->addItemSmallImage($item, 'http://smallimage.url');

        $testXmlData = $amazonXmlResponse->asXml();
        $givenXml = simplexml_load_string($testXmlData);

        $transformer = new SimpleArrayTransformer();
        $response = $transformer->execute($givenXml);

        $this->assertEquals($expectedLowestPrice, $response[0]['lowestPrice']);
    }

    public function testShouldReturnItemWithExpectedLowestPriceIfNotPresent() {
        $amazonXmlResponse = $this->createDefaultAmazonXmlResponse();

        $item = $amazonXmlResponse->addItem('defaultAsin');
        $amazonXmlResponse->addItemDetailPageUrl($item, 'https://detailpage.url');
        $amazonXmlResponse->addItemItemAttributes($item, '100', 'default title');
        $amazonXmlResponse->addItemLargeImage($item, 'http://largeimage.url');
        $amazonXmlResponse->addItemMediumImage($item, 'http://mediumimage.url');
        $amazonXmlResponse->addItemSmallImage($item, 'http://smallimage.url');

        $testXmlData = $amazonXmlResponse->asXml();
        $givenXml = simplexml_load_string($testXmlData);

        $transformer = new SimpleArrayTransformer();
        $response = $transformer->execute($givenXml);

        $this->assertEquals(0, $response[0]['lowestPrice']);
    }

    public function testShouldReturnItemWithExpectedLargeImage() {
        $givenUrl = 'https://given.url';
        $amazonXmlResponse = $this->createDefaultAmazonXmlResponse();

        $item = $amazonXmlResponse->addItem('defaultAsin');
        $amazonXmlResponse->addItemDetailPageUrl($item, 'https://detailpage.url');
        $amazonXmlResponse->addItemItemAttributes($item, '100', 'default title');
        $amazonXmlResponse->addItemLargeImage($item, $givenUrl);
        $amazonXmlResponse->addItemMediumImage($item, 'http://mediumimage.url');
        $amazonXmlResponse->addItemSmallImage($item, 'http://smallimage.url');

        $testXmlData = $amazonXmlResponse->asXml();
        $givenXml = simplexml_load_string($testXmlData);

        $transformer = new SimpleArrayTransformer();
        $response = $transformer->execute($givenXml);

        $this->assertEquals($givenUrl, $response[0]['largeImage']);
    }

    public function testShouldReturnItemWithExpectedMediumImage() {
        $givenUrl = 'https://given.url';
        $amazonXmlResponse = $this->createDefaultAmazonXmlResponse();

        $item = $amazonXmlResponse->addItem('defaultAsin');
        $amazonXmlResponse->addItemDetailPageUrl($item, 'https://detailpage.url');
        $amazonXmlResponse->addItemItemAttributes($item, '100', 'default title');
        $amazonXmlResponse->addItemLargeImage($item, 'http://defaultimage.url');
        $amazonXmlResponse->addItemMediumImage($item, $givenUrl);
        $amazonXmlResponse->addItemSmallImage($item, 'http://smallimage.url');

        $testXmlData = $amazonXmlResponse->asXml();
        $givenXml = simplexml_load_string($testXmlData);

        $transformer = new SimpleArrayTransformer();
        $response = $transformer->execute($givenXml);

        $this->assertEquals($givenUrl, $response[0]['mediumImage']);
    }

    public function testShouldReturnItemWithExpectedSmallImage() {
        $givenUrl = 'https://given.url';
        $amazonXmlResponse = $this->createDefaultAmazonXmlResponse();

        $item = $amazonXmlResponse->addItem('defaultAsin');
        $amazonXmlResponse->addItemDetailPageUrl($item, 'https://detailpage.url');
        $amazonXmlResponse->addItemItemAttributes($item, '100', 'default title');
        $amazonXmlResponse->addItemLargeImage($item, 'http://defaultimage.url');
        $amazonXmlResponse->addItemMediumImage($item, 'http://defaultimage.url');
        $amazonXmlResponse->addItemSmallImage($item, $givenUrl);

        $testXmlData = $amazonXmlResponse->asXml();
        $givenXml = simplexml_load_string($testXmlData);

        $transformer = new SimpleArrayTransformer();
        $response = $transformer->execute($givenXml);

        $this->assertEquals($givenUrl, $response[0]['smallImage']);
    }
}
?>
