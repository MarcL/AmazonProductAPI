<?php

use PHPUnit\Framework\TestCase;
use MarcL\AmazonUrlBuilder;

class AmazonUrlBuilderTest extends TestCase {
    private $defaultKeyId = 'defaultKeyId';
    private $defaultSecretKey = 'defaultSecretKey';
    private $defaultAssociateTag = 'defaultAssociateTag';

    public function testShouldThrowExceptionIfMissingKeyId() {
        $this->expectExceptionMessage('Amazon key ID should be defined');

        $amazonUrlBuilder = new AmazonUrlBuilder(NULL, NULL, NULL);
    }

    public function testShouldThrowExceptionIfMissingSecretKey() {
        $this->expectExceptionMessage('Amazon secret key should be defined');

        $amazonUrlBuilder = new AmazonUrlBuilder($this->defaultKeyId, NULL, NULL);
    }

    public function testShouldThrowExceptionIfMissingAssociateTag() {
        $this->expectExceptionMessage('Amazon associate tag should be defined');

        $amazonUrlBuilder = new AmazonUrlBuilder($this->defaultKeyId, $this->defaultSecretKey, NULL);
    }

    private function createDefaultUrlBuilder($locale = 'us') {
        return new AmazonUrlBuilder(
            $this->defaultKeyId,
            $this->defaultSecretKey,
            $this->defaultAssociateTag,
            $locale
        );
    }

    private function getUrlQueryParameterArray($url) {
	    $uriElements = parse_url($url);
	    $query = $uriElements['query'];

	    parse_str($query, $parameters);

        return($parameters);
    }

    public function testShouldBuildUrlWithExpectedDefaultTld() {
        $amazonUrlBuilder = new AmazonUrlBuilder(
            $this->defaultKeyId,
            $this->defaultSecretKey,
            $this->defaultAssociateTag
        );
        $expectedTld = 'amazon.com';

        $url = $amazonUrlBuilder->generate(array());

        $this->assertContains($expectedTld, $url);
    }

    public function testShouldBuildUrlWithExpectedGivenUKLocale() {
        $amazonUrlBuilder = $this->createDefaultUrlBuilder('uk');
        $expectedTld = 'amazon.co.uk';

        $url = $amazonUrlBuilder->generate(array());

        $this->assertContains($expectedTld, $url);
    }

    public function testShouldBuildUrlWithExpectedGivenJapaneseLocale() {
        $amazonUrlBuilder = $this->createDefaultUrlBuilder('jp');
        $expectedTld = 'amazon.co.jp';

        $url = $amazonUrlBuilder->generate(array());

        $this->assertContains($expectedTld, $url);
    }

    public function testShouldBuildUrlWithExpectedGivenMexicanLocale() {
        $amazonUrlBuilder = $this->createDefaultUrlBuilder('mx');
        $expectedTld = 'amazon.com.mx';

        $url = $amazonUrlBuilder->generate(array());

        $this->assertContains($expectedTld, $url);
    }

    public function testShouldBuildUrlWithExpectedGivenValidLocale() {
        $amazonUrlBuilder = $this->createDefaultUrlBuilder('de');
        $expectedTld = 'amazon.de';

        $url = $amazonUrlBuilder->generate(array());

        $this->assertContains($expectedTld, $url);
    }

    public function testShouldBuildUrlWithDefaultUSLocaleIfGivenLocaleIsUnknown() {
        $amazonUrlBuilder = $this->createDefaultUrlBuilder('unknown');
        $expectedTld = 'amazon.com';

        $url = $amazonUrlBuilder->generate(array());

        $this->assertContains($expectedTld, $url);
    }

    public function testShouldContainExpectedService() {
        $amazonUrlBuilder = $this->createDefaultUrlBuilder();
        $url = $amazonUrlBuilder->generate(array());

        $this->assertContains('Service=AWSECommerceService', $url);
    }

    public function testShouldContainExpectedAssociateTag() {
        $givenAssociateTag = 'given-associate-tag';
        $amazonUrlBuilder = new AmazonUrlBuilder(
            $this->defaultKeyId,
            $this->defaultSecretKey,
            $givenAssociateTag
        );
        $url = $amazonUrlBuilder->generate(array());

        $this->assertContains('AssociateTag=' . $givenAssociateTag, $url);
    }

    public function testShouldContainExpectedAwsAccessKeyId() {
        $givenAwsAccessKeyId = 'givenAwsAccessKeyId';
        $amazonUrlBuilder = new AmazonUrlBuilder(
            $givenAwsAccessKeyId,
            $this->defaultSecretKey,
            $this->defaultAssociateTag
        );
        $url = $amazonUrlBuilder->generate(array());

        $this->assertContains('AWSAccessKeyId=' . $givenAwsAccessKeyId, $url);
    }

    public function testShouldContainExpectedVersion() {
        $amazonUrlBuilder = $this->createDefaultUrlBuilder();
        $url = $amazonUrlBuilder->generate(array());

        $this->assertContains('Version=2011-08-01', $url);
    }

    public function testShouldContainValidTimestamp() {
        $amazonUrlBuilder = $this->createDefaultUrlBuilder();
        $url = $amazonUrlBuilder->generate(array());

        $parameters = $this->getUrlQueryParameterArray($url);

        $this->assertStringMatchesFormat('%d-%d-%dT%d:%d:%dZ', $parameters['Timestamp']);
    }

    public function testShouldContainPassedParameter() {
        $givenParameters = array(
            'Operation' => 'ItemLookup'
        );
        $amazonUrlBuilder = $this->createDefaultUrlBuilder();
        $url = $amazonUrlBuilder->generate($givenParameters);

        $parameters = $this->getUrlQueryParameterArray($url);

        $this->assertContains('Operation=' . $givenParameters['Operation'], $url);
    }

    public function testShouldContainPassedParameterWithCorrectEncoding() {
        $givenParameters = array(
            'ResponseGroup' => 'ItemAttributes,Offers,Reviews,Images,EditorialReview',
        );
        $amazonUrlBuilder = $this->createDefaultUrlBuilder();
        $url = $amazonUrlBuilder->generate($givenParameters);

        $expectedParameter = 'ResponseGroup=' . rawurlencode($givenParameters['ResponseGroup']);

        $parameters = $this->getUrlQueryParameterArray($url);

        $this->assertContains($expectedParameter, $url);
    }
}
?>
