<?php

namespace MarcL;

class AmazonUrlBuilder {
    private $secretKey = NULL;
    private $keyId = NULL;
    private $associateTag = NULL;
    private $amazonEndpoint = NULL;

	private $localeTable = array(
		'br' => 'webservices.amazon.br/onca/xml',
		'ca' =>	'webservices.amazon.ca/onca/xml',
		'cn' =>	'webservices.amazon.cn/onca/xml',
		'fr' =>	'webservices.amazon.fr/onca/xml',
		'de' =>	'webservices.amazon.de/onca/xml',
		'in' =>	'webservices.amazon.in/onca/xml',
		'it' =>	'webservices.amazon.it/onca/xml',
		'jp' =>	'webservices.amazon.co.jp/onca/xml',
		'mx' =>	'webservices.amazon.com.mx/onca/xml',
		'es' =>	'webservices.amazon.es/onca/xml',
		'uk' =>	'webservices.amazon.co.uk/onca/xml',
		'us' =>	'webservices.amazon.com/onca/xml'
	);

	private function throwIfNull($parameterValue, $parameterName) {
		if ($parameterValue == NULL) {
			throw new \Exception($parameterName . ' should be defined');
		}
	}

    public function __construct($keyId, $secretKey, $associateTag, $locale = 'us') {
		$this->throwIfNull($keyId, 'Amazon key ID');
		$this->throwIfNull($secretKey, 'Amazon secret key');
		$this->throwIfNull($associateTag, 'Amazon associate tag');

        $this->secretKey = $secretKey;
        $this->amazonEndpoint = $this->GetAmazonApiEndpoint($locale);
        $this->associateTag = $associateTag;
        $this->keyId = $keyId;
    }

    private function GetAmazonApiEndpoint($locale) {
		if (!array_key_exists($locale, $this->localeTable)) {
			$locale = 'us';
		}

        return('https://' . $this->localeTable[$locale]);
    }

	private function CreateUnsignedAmazonUrl($params) {
		$baseParams = array(
			'Service' => 'AWSECommerceService',
			'AssociateTag' => $this->associateTag,
			'AWSAccessKeyId' => $this->keyId
		);

		$buildParams = array_merge($baseParams, $params);

		$request = $this->amazonEndpoint . '?' .http_build_query($buildParams);

		return($request);
	}

	/**
	  * This function will take an existing Amazon request and change it so that it will be usable
	  * with the new authentication.
	  *
	  * @param string $request - your existing request URI
	  * @param string $version - (optional) the version of the service you are using
	  *
	  * @link http://www.ilovebonnie.net/2009/07/27/amazon-aws-api-rest-authentication-for-php-5/
	  */
	private function GetSignedRequest($request, $version = '2011-08-01') {
	    // Get a nice array of elements to work with
	    $uri_elements = parse_url($request);

	    // Grab our request elements
	    $request = $uri_elements['query'];

	    // Throw them into an array
	    parse_str($request, $parameters);

	    // Add the new required paramters
	    $parameters['Timestamp'] = gmdate("Y-m-d\TH:i:s\Z");
	    $parameters['Version'] = $version;

	    // The new authentication requirements need the keys to be sorted
	    ksort($parameters);

	    // Create our new request
	    foreach ($parameters as $parameter => $value) {
	        // We need to be sure we properly encode the value of our parameter
	        $parameter = str_replace("%7E", "~", rawurlencode($parameter));
	        $value = str_replace("%7E", "~", rawurlencode($value));
	        $request_array[] = $parameter . '=' . $value;
	    }

	    // Put our & symbol at the beginning of each of our request variables and put it in a string
	    $new_request = implode('&', $request_array);

	    // Create our signature string
	    $signature_string = "GET\n{$uri_elements['host']}\n{$uri_elements['path']}\n{$new_request}";

	    // Create our signature using hash_hmac
	    $signature = urlencode(
			base64_encode(
				hash_hmac(
					'sha256',
					$signature_string,
					$this->secretKey,
					true
				)
			)
		);

	    // Return our new request
		$newUrl = "http://{$uri_elements['host']}{$uri_elements['path']}?{$new_request}&Signature={$signature}";
	    return $newUrl;
	}

    public function generate($params) {
        $unsignedRequest = $this->CreateUnsignedAmazonUrl($params);

        return $this->GetSignedRequest($unsignedRequest);
    }
}

?>