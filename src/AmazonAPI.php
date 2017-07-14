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
	private $m_locale = 'uk';
	private $m_retrieveArray = false;

	private $m_keyId		= NULL;
	private $m_secretKey	= NULL;
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

	public function SetRetrieveAsArray($retrieveArray = true) {
		$this->m_retrieveArray	= $retrieveArray;
	}

	public function SetLocale($locale) {
		$this->m_locale = $locale;
	}

	public function GetValidSearchNames() {
		return($this->mValidSearchNames);
	}

	private function MakeSignedRequest($params) {
		$urlBuilder = new AmazonUrlBuilder(
			$this->m_keyId,
			$this->m_secretKey,
			$this->m_associateTag,
			$this->m_locale
		);
		$signedUrl = $urlBuilder->generate($params);

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

	private function MakeAndParseRequest($params) {
		$parsedXml = $this->MakeSignedRequest($params);
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

		return($this->MakeAndParseRequest($params));
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

		return($this->MakeAndParseRequest($params));
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
