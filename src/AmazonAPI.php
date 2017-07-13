<?php
/**
 *  Amazon Product API Library
 *
 *  @author Marc Littlemore
 *  @link 	http://www.marclittlemore.com
 *
 */

namespace MarcL;

use MarcL\CurlHttpRequest;
use MarcL\AmazonUrlBuilder;

class AmazonAPI
{
	private $m_amazonUrl = '';
	private $m_locale = 'uk';
	private $m_retrieveArray = false;
	private $m_useSSL = true;

	// AWS endpoint for each locale
	private $m_localeTable = array(
		'br' => 'webservices.amazon.br/onca/xml',
		'ca' =>	'webservices.amazon.ca/onca/xml',
		'cn' =>	'webservices.amazon.cn/onca/xml',
		'fr' =>	'webservices.amazon.fr/onca/xml',
		'de' =>	'webservices.amazon.de/onca/xml',
		'in' =>	'webservices.amazon.in/onca/xml',
		'it' =>	'webservices.amazon.it/onca/xml',
		'jp' =>	'webservices.amazon.jp/onca/xml',
		'mx' =>	'webservices.amazon.mx/onca/xml',
		'es' =>	'webservices.amazon.es/onca/xml',
		'uk' =>	'webservices.amazon.co.uk/onca/xml',
		'us' =>	'webservices.amazon.com/onca/xml'
	);

	// API key ID
	private $m_keyId		= NULL;

	// API Secret Key
	private $m_secretKey	= NULL;

	// AWS associate tag
	private $m_associateTag = NULL;

	// Valid names that can be used for search
	private $mValidSearchNames = array(
		'All',
		'Apparel',
		'Appliances',
		'Automotive',
		'Baby',
		'Beauty',
		'Blended',
		'Books',
		'Classical',
		'DVD',
		'Electronics',
		'Grocery',
		'HealthPersonalCare',
		'HomeGarden',
		'HomeImprovement',
		'Jewelry',
		'KindleStore',
		'Kitchen',
		'Lighting',
		'Marketplace',
		'MP3Downloads',
		'Music',
		'MusicTracks',
		'MusicalInstruments',
		'OfficeProducts',
		'OutdoorLiving',
		'Outlet',
		'PetSupplies',
		'PCHardware',
		'Shoes',
		'Software',
		'SoftwareVideoGames',
		'SportingGoods',
		'Tools',
		'Toys',
		'VHS',
		'Video',
		'VideoGames',
		'Watches'
	);

	private $mErrors = array();

	private function throwIfNull($parameterValue, $parameterName) {
		if ($parameterValue == NULL) {
			throw new \Exception($parameterName . ' should be defined');
		}
	}

	public function __construct($keyId, $secretKey, $associateTag) {
		$this->throwIfNull($keyId, 'Amazon key ID');
		$this->throwIfNull($secretKey, 'Amazon secret key');
		$this->throwIfNull($associateTag, 'Amazon associate tag');

		// Setup the AWS credentials
		$this->m_keyId			= $keyId;
		$this->m_secretKey		= $secretKey;
		$this->m_associateTag	= $associateTag;

		// Set UK as locale by default
		$this->SetLocale('uk');
	}

	public function SetSSL($useSSL = true) {
		$this->m_useSSL = $useSSL;
	}

	public function SetRetrieveAsArray($retrieveArray = true) {
		$this->m_retrieveArray	= $retrieveArray;
	}

	public function SetLocale($locale) {
		// Check we have a locale in our table
		if (!array_key_exists($locale, $this->m_localeTable))
		{
			// If not then just assume it's US
			$locale = 'us';
		}

		// Set the URL for this locale
		$this->m_locale = $locale;

		// Check for SSL
		if ($this->m_useSSL)
			$this->m_amazonUrl = 'https://' . $this->m_localeTable[$locale];
		else
			$this->m_amazonUrl = 'http://' . $this->m_localeTable[$locale];
	}

	public function GetValidSearchNames() {
		return($this->mValidSearchNames);
	}

	private function MakeSignedRequest($url) {
		$urlBuilder = new AmazonUrlBuilder($url, $this->m_secretKey);
		$signedUrl = $urlBuilder->generate();

		try {
			$request = new CurlHttpRequest();
			$response = $request->execute($signedUrl);

			$parsedXml = simplexml_load_string($response);

			return($parsedXml);
		} catch(\Exception $error) {
			$this->AddError("Error downloading data : $signedUrl : " . $error->getMessage());
		}

		return(false);
	}

	private function MakeAndParseRequest($url) {
		$parsedXml = $this->MakeSignedRequest($url);
		if ($parsedXml === false) {
			return(false);
		}

		if ($this->m_retrieveArray) {
			$items = $this->RetrieveItems($parsedXml);
		}
		else {
			$items = $parsedXml;
		}

		return($items);
	}

	private function CreateUnsignedAmazonUrl($params) {
		$baseParams = array(
			'Service' => 'AWSECommerceService',
			'AssociateTag' => $this->m_associateTag,
			'AWSAccessKeyId' => $this->m_keyId
		);

		$buildParams = array_merge($baseParams, $params);

		$request = $this->m_amazonUrl . '?' .http_build_query($buildParams);

		return($request);

	}

	/**
	 * Search for items
	 *
	 * @param	keywords			Keywords which we're requesting
	 * @param	searchIndex			Name of search index (category) requested. NULL if searching all.
	 * @param	sortBy				Category to sort by, only used if searchIndex is not 'All'
	 * @param	condition			Condition of item. Valid conditions : Used, Collectible, Refurbished, All
	 *
	 * @return	mixed				SimpleXML object, array of data or false if failure.
	 */
	public function ItemSearch($keywords, $searchIndex = NULL, $sortBy = NULL, $condition = 'New') {
		$params = array(
			'Operation' => 'ItemSearch',
			'ResponseGroup' => 'ItemAttributes,Offers,Images',
			'Keywords' => $keywords,
			'Condition' => $condition,
			'SearchIndex' => empty($searchIndex) ? 'All' : $searchIndex,
			'Sort' => $sortBy && ($searchIndex != 'All') ? $sortBy : NULL
		);

		$request = $this->CreateUnsignedAmazonUrl($params);

		return($this->MakeAndParseRequest($request));
	}

	/**
	 * Lookup items from ASINs
	 *
	 * @param	asinList			Either a single ASIN or an array of ASINs
	 * @param	onlyFromAmazon		True if only requesting items from Amazon and not 3rd party vendors
	 *
	 * @return	mixed				SimpleXML object, array of data or false if failure.
	 */
	public function ItemLookup($asinList, $onlyFromAmazon = false) {
		if (is_array($asinList)) {
			$asinList = implode(',', $asinList);
		}

		$params = array(
			'Operation' => 'ItemLookup',
			'ResponseGroup' => 'ItemAttributes,Offers,Reviews,Images,EditorialReview',
			'ReviewSort' => '-OverallRating',
			'ItemId' => $asinList,
			'MerchantId' => ($onlyFromAmazon == true) ? 'Amazon' : 'All'
		);

		$request = $this->CreateUnsignedAmazonUrl($params);

		return($this->MakeAndParseRequest($request));
	}

	/**
	 * Basic method to retrieve only requested item data as an array
	 *
	 * @param	responseXML		XML data to be passed
	 *
	 * @return	Array			Array of item data. Empty array if not found
	 */
	private function RetrieveItems($responseXml) {
		$items = array();
		if (empty($responseXml)) {
			$this->AddError("No XML response found from AWS.");
			return($items);
		}

		if (empty($responseXml->Items)) {
			$this->AddError("No items found.");
			return($items);
		}

		if ($responseXml->Items->Request->IsValid != 'True') {
			$errorCode = $responseXml->Items->Request->Errors->Error->Code;
			$errorMessage = $responseXml->Items->Request->Errors->Error->Message;
			$error = "API ERROR ($errorCode) : $errorMessage";
			$this->AddError($error);
			return($items);
		}

		// Get each item
		foreach($responseXml->Items->Item as $responseItem) {
			$item = array();
			$item['asin'] = (string) $responseItem->ASIN;
			$item['url'] = (string) $responseItem->DetailPageURL;
			$item['rrp'] = ((float) $responseItem->ItemAttributes->ListPrice->Amount) / 100.0;
			$item['title'] = (string) $responseItem->ItemAttributes->Title;

			if ($responseItem->OfferSummary) {
				$item['lowestPrice'] = ((float) $responseItem->OfferSummary->LowestNewPrice->Amount) / 100.0;
			}
			else {
				$item['lowestPrice'] = 0.0;
			}

			// Images
			$item['largeImage'] = (string) $responseItem->LargeImage->URL;
			$item['mediumImage'] = (string) $responseItem->MediumImage->URL;
			$item['smallImage'] = (string) $responseItem->SmallImage->URL;

			array_push($items, $item);
		}

		return($items);
	}

	private function AddError($error) {
		array_push($this->mErrors, $error);
	}

	public function GetErrors() {
		return($this->mErrors);
	}
}
?>
