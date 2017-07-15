<?php

namespace MarcL\Transformers;

use MarcL\Transformers\IDataTransformer;

class XmlTransformer implements IDataTransformer {
    public function execute($xmlData) {
        return $xmlData;
    }
}

?>