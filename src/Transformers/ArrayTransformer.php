<?php

namespace MarcL\Transformers;

use MarcL\Transformers\IDataTransformer;

class ArrayTransformer implements IDataTransformer {
    public function execute($xmlData) {
        $json = json_encode($xmlData);
        return(json_decode($json));
    }
}

?>