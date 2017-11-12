<?php

namespace tests\helpers;

class AmazonXmlResponse {
	public function __construct() {
        $this->document = new \SimpleXMLElement('<?xml version="1.0" encoding="utf-8"?><OperationRequest></OperationRequest>');
    }

    public function asXml() {
        return $this->document->asXML();
    }

    public function addRequestId($requestId = 'test-request-id') {
        $this->document->addChild('RequestId', $requestId);
    }

    public function addInvalidRequest($errorCode, $errorMessage) {
        $items = $this->document->addChild('Items');
        $request = $items->addChild('Request');
        $request->addChild('IsValid', 'False');
        $errors = $request->addChild('Errors');
        $error = $errors->addChild('Error');
        $error->addChild('Code', $errorCode);
        $error->addChild('Message', $errorMessage);
    }

    public function addValidRequest() {
        $items = $this->document->addChild('Items');
        $request = $items->addChild('Request');
        $request->addChild('IsValid', 'True');
    }
}

?>
