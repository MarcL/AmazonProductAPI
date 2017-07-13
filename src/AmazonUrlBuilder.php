<?php

namespace MarcL;

class AmazonUrlBuilder {
    private $url = NULL;
    private $secretKey = NULL;

    public function __construct($url, $secretKey) {
        $this->secretKey = $secretKey;
        $this->url = $this->GetSignedRequest($url);
    }

	/**
	  * This function will take an existing Amazon request and change it so that it will be usable
	  * with the new authentication.
	  *
	  * @param string $request - your existing request URI
	  * @param string $access_key - your Amazon AWS access key
	  * @param string $version - (optional) the version of the service you are using
	  *
	  * @link http://www.ilovebonnie.net/2009/07/27/amazon-aws-api-rest-authentication-for-php-5/
	  */
	private function GetSignedRequest($request, $access_key = false, $version = '2011-08-01') {
	    // Get a nice array of elements to work with
	    $uri_elements = parse_url($request);

	    // Grab our request elements
	    $request = $uri_elements['query'];

	    // Throw them into an array
	    parse_str($request, $parameters);

	    // Add the new required paramters
	    $parameters['Timestamp'] = gmdate("Y-m-d\TH:i:s\Z");
	    $parameters['Version'] = $version;
	    if (strlen($access_key) > 0) {
	        $parameters['AWSAccessKeyId'] = $access_key;
	    }

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
	    $signature = urlencode(base64_encode(hash_hmac('sha256', $signature_string, $this->secretKey, true)));

	    // Return our new request
		$newUrl = "http://{$uri_elements['host']}{$uri_elements['path']}?{$new_request}&Signature={$signature}";
	    return $newUrl;
	}

    public function get() {
        return($this->url);
    }
}

?>