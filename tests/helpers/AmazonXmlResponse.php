<?php

namespace tests\helpers;

class AmazonXmlResponse {
	public function __construct() {
        $this->document = new \SimpleXMLElement('<?xml version="1.0" encoding="utf-8"?><OperationRequest></OperationRequest>');
        $this->items = $this->document->addChild('Items');
    }

    public function asXml() {
        return $this->document->asXML();
    }

    public function addRequestId($requestId = 'test-request-id') {
        $this->document->addChild('RequestId', $requestId);
    }

    public function addInvalidRequest($errorCode, $errorMessage) {
        $request = $this->items->addChild('Request');
        $request->addChild('IsValid', 'False');
        $errors = $request->addChild('Errors');
        $error = $errors->addChild('Error');
        $error->addChild('Code', $errorCode);
        $error->addChild('Message', $errorMessage);
    }

    public function addValidRequest() {
        $request = $this->items->addChild('Request');
        $request->addChild('IsValid', 'True');
    }

    public function addItem($asin) {
        $item = $this->items->addChild('Item');
        $item->addChild('ASIN', $asin);

        return $item;
    }

    public function addItemDetailPageUrl($item, $url) {
        $item->addChild('DetailPageURL', $url);
    }

    private function addItemImage($item, $imageType, $imageUrl) {
        $image = $item->addChild($imageType);
        $image->addChild('URL', $imageUrl);
    }

    public function addItemLargeImage($item, $url) {
        $this->addItemImage($item, 'LargeImage', $url);
    }

    public function addItemMediumImage($item, $url) {
        $this->addItemImage($item, 'MediumImage', $url);
    }

    public function addItemSmallImage($item, $url) {
        $this->addItemImage($item, 'SmallImage', $url);
    }

    public function addItemItemAttributes($item, $amount, $title) {
        $itemAttributes = $item->addChild('ItemAttributes');
        $listPrice = $itemAttributes->addChild('ListPrice');
        $listPrice->addChild('Amount', $amount);
        $itemAttributes->addChild('Title', $title);
    }

    public function addItemOfferSummary($item, $amount) {
        $offerSummary = $item->addChild('OfferSummary');
        $lowestNewPrice = $offerSummary->addChild('LowestNewPrice');
        $lowestNewPrice->addChild('Amount', $amount);
    }
}

?>
