<?php

namespace MarcL\Transformers;

use MarcL\Transformers\IDataTransformer;

class ArrayTransformer implements IDataTransformer {
    private $xmlData = NULL;

    public function __construct($xmlData) {
        $this->xmlData = $xmlData;
    }

    public function execute() {
        $json = json_encode($this->xmlData);
        return(json_decode($json));
    }
}

?>