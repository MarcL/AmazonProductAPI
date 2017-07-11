<?php
use PHPUnit\Framework\TestCase;

use MarcL\AmazonAPI;

class AmazonAPITest extends TestCase {
    public function testShouldThrowExceptionIfMissingKeyId() {
        $amazonAPI = new AmazonApi(NULL, NULL, NULL);

        $this->assertEquals(0, 1);
    }
}
?>