<?php

use PHPUnit\Framework\TestCase;
use MarcL\AmazonAPI;

class AmazonAPITest extends TestCase {
    private $defaultKeyId = 'defaultKeyId';
    private $defaultSecretKey = 'defaultSecretKey';

    public function testShouldThrowExceptionIfMissingKeyId() {
        $this->expectExceptionMessage('Amazon key ID should be defined');

        $amazonAPI = new AmazonApi(NULL, NULL, NULL);
    }

    public function testShouldThrowExceptionIfMissingSecretKey() {
        $this->expectExceptionMessage('Amazon secret key should be defined');

        $amazonAPI = new AmazonApi($this->defaultKeyId, NULL, NULL);
    }

    public function testShouldThrowExceptionIfMissingAssociateTag() {
        $this->expectExceptionMessage('Amazon associate tag should be defined');

        $amazonAPI = new AmazonApi($this->defaultKeyId, $this->defaultSecretKey, NULL);
    }
}
?>