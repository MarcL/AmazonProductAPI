<?php

namespace MarcL\Transformers;

use MarcL\Transformers\IDataTransformer;

class XmlTransformer implements IDataTransformer {
    private $xmlData = NULL;

    public function __construct($xmlData) {
        $this->xmlData = $xmlData;
    }

    public function execute() {
        return $this->xmlData;
    }
}

?>