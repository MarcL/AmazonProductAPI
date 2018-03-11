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
use MarcL\Transformers\DataTransformerFactory;

class AmazonAPI
{
	private $urlBuilder = NULL;
	private $dataTransformer = NULL;

	// Valid names that can be used for search
	// This variable can be set to NULL
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


	private $mValidResponseGroups = NULL;
	private $mValidSortValues = NULL;

	private $mErrors = array();

	public function __construct($urlBuilder, $outputType, $searchNames = NULL, $responseGroups = NULL, $sortValues = NULL) {
		$this->urlBuilder = $urlBuilder;
		$this->dataTransformer = DataTransformerFactory::create($outputType);

		if ( ! empty($searchNames) )
		{
			$this->mValidSearchNames = $searchNames;
		}

		$this->mValidResponseGroups = $responseGroups;
		$this->mValidSortValues = $sortValues;
	}

	public function GetValidSearchNames() {
		return $this->mValidSearchNames;
	}

	public function GetValidResponseGroups() {
		return $this->mValidResponseGroups;
	}

	public function GetValidSortValues() {
		return $this->mValidSortValues;
	}

	/**
	 * Search for items
	 *
	 * @param	keywords			Keywords which we're requesting
	 * @param	searchIndex			Name of search index (category) requested. NULL if searching all.
	 * @param	sortBy				Category to sort by, only used if searchIndex is not 'All'
	 * @param	condition			Condition of item. Valid conditions : Used, Collectible, Refurbished, All
	 * @param	responseGroups			Response group's for results. Valid conditions : See mValidResponseGroups
	 *
	 * @return	mixed				SimpleXML object, array of data or false if failure.
	 */
	public function ItemSearch($keywords, $searchIndex = NULL, $sortBy = NULL, $condition = 'New', $responseGroups = NULL) {

		if (empty($responseGroups))
		{
			// if empty, set default
			$responseGroups = 'ItemAttributes,Offers,Images';
		}

		$params = array(
			'Operation' => 'ItemSearch',
			'ResponseGroup' => $responseGroups,
			'Keywords' => $keywords,
			'Condition' => $condition,
			'SearchIndex' => empty($searchIndex) ? 'All' : $searchIndex,
			'Sort' => $sortBy && ($searchIndex != 'All') ? $sortBy : NULL
		);

		return $this->MakeAndParseRequest($params);
	}

	/**
	 * Lookup items from ASINs
	 *
	 * @param	asinList			Either a single ASIN or an array of ASINs
	 * @param	onlyFromAmazon		True if only requesting items from Amazon and not 3rd party vendors
	 * @param	responseGroup		Response group for results. Valid conditions : See mValidResponseGroups
	 * @param	reviewSort			Review sort order for results. Valid conditions : See mValidReviewSorts
	 *
	 * @return	mixed				SimpleXML object, array of data or false if failure.
	 */
	public function ItemLookup($asinList, $onlyFromAmazon = false, $responseGroup = NULL, $reviewSort = NULL) {
		if (is_array($asinList)) {
			$asinList = implode(',', $asinList);
		}
		if (empty($responseGroup))
		{
			// if empty, set default
			$responseGroup = 'ItemAttributes,Offers,Reviews,Images,EditorialReview';
		}
		if (empty($reviewSort))
		{
			// if empty, set default
			$reviewSort = '-OverallRating';
		}

		$params = array(
			'Operation' => 'ItemLookup',
			'ResponseGroup' => $responseGroup,
			'ReviewSort' => $reviewSort,
			'ItemId' => $asinList,
			'MerchantId' => ($onlyFromAmazon == true) ? 'Amazon' : 'All'
		);

		return $this->MakeAndParseRequest($params);
	}

	public function GetErrors() {
		return $this->mErrors;
	}

	private function AddError($error) {
		array_push($this->mErrors, $error);
	}

	private function MakeAndParseRequest($params) {
		$signedUrl = $this->urlBuilder->generate($params);

		try {
			$request = new CurlHttpRequest();
			$response = $request->execute($signedUrl);

			$parsedXml = simplexml_load_string($response);

			if ($parsedXml === false) {
				return false;
			}

			return $this->dataTransformer->execute($parsedXml);
		} catch(\Exception $error) {
			$this->AddError("Error downloading data : $signedUrl : " . $error->getMessage());
			return false;
		}
	}
}
?>
