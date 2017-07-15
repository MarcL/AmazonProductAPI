<?php

namespace MarcL\Transformers;

use MarcL\Transformers\IDataTransformer;

class JsonTransformer implements IDataTransformer {
    private $xmlData = NULL;

    public function __construct($xmlData) {
        $this->xmlData = $xmlData;
    }

    public function execute() {
        return json_encode($this->xmlData);
    }
}

?>