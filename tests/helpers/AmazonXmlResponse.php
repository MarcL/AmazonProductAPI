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

    // TODO : Refactor
    public function addItem($asin, $detailpageUrl, $rrp, $title, $lowestNewPrice,  $largeImageUrl, $mediumImageUrl, $smallImageUrl) {
        $item = $this->items->addChild('Item');
        $item->addChild('ASIN', $asin);
        $item->addChild('DetailPageUrl', $detailpageUrl);

        $itemAttributes = $item->addChild('ItemAttributes');
        $listPrice = $itemAttributes->addChild('ListPrice');
        $listPrice->addChild('Amount', $rrp);
        $itemAttributes->addChild('Title', $title);

        $offerSummary = $item->addChild('OfferSummary');
        $lowestNewPrice = $offerSummary->addChild('LowestNewPrice');
        $lowestNewPrice->addChild('Amount', $lowestNewPrice);

        $this->addItemImage($item, 'LargeImage', $largeImageUrl);
        $this->addItemImage($item, 'MediumImage', $mediumImageUrl);
        $this->addItemImage($item, 'SmallImage', $smallImageUrl);

        return $item;
    }

    public function addItemImage($item, $imageType, $imageUrl) {
        $image = $item->addChild($imageType);
        $image->addChild('URL', $imageUrl);
    }
}

?>
