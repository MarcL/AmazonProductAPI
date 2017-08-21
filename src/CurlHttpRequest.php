<?php

namespace MarcL;

interface IHttpRequest {
	public function execute($url);
}

class CurlHttpRequest implements IHttpRequest {
	private $url = NULL;
	private $error = NULL;

	public function __construct() {
		if (!function_exists('curl_init'))
		{
			throw new \Exception('Curl not found');
		}
	}

	public function execute($url) {
		$session = curl_init($url);
		curl_setopt($session, CURLOPT_HEADER, false);
		curl_setopt($session, CURLOPT_RETURNTRANSFER, true);
		$response = curl_exec($session);

		if ($response === false) {
			$this->error = curl_error($session);
		}

		curl_close($session);

		if (!empty($error)) {
			throw new \Exception($error);
		}

		return($response);
	}
}

?>
