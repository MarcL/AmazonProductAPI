<?php

namespace MarcL\Transformers;

use MarcL\Transformers\IDataTransformer;

class JsonTransformer implements IDataTransformer {
    public function execute($xmlData) {
        return json_encode($xmlData);
    }
}

?>