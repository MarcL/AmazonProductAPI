<?php
/**
 *  Amazon Product API Library
 *
 * @author Marc Littlemore
 * @link    http://www.marclittlemore.com
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

    // Valid ResponseGroups that can be used
    private $mValidResponseGroups = array(
        'Accessories',
        'AlternateVersions',
        'BrowseNodeInfo',
        'BrowseNodes',
        'Cart',
        'CartNewReleases',
        'CartTopSellers',
        'CartSimilarities',
        'EditorialReview',
        'Images',
        'ItemAttributes',
        'ItemIds',
        'Large',
        'Medium',
        'MostGifted',
        'MostWishedFor',
        'NewReleases',
        'OfferFull',
        'OfferListings',
        'Offers',
        'OfferSummary',
        'PromotionSummary',
        'RelatedItems',
        'Request',
        'Reviews',
        'SalesRank',
        'SearchBins',
        'Similarities',
        'Small',
        'TopSellers',
        'Tracks',
        'Variations',
        'VariationImages',
        'VariationMatrix',
        'VariationOffers',
        'VariationSummary'
    );

    private $mErrors = array();

    public function __construct($urlBuilder, $outputType)
    {
        $this->urlBuilder = $urlBuilder;
        $this->dataTransformer = DataTransformerFactory::create($outputType);
    }

    public function GetValidSearchNames()
    {
        return $this->mValidSearchNames;
    }

    public function GetValidResponseGroups()
    {
        return $this->mValidResponseGroups;
    }

    /**
     * Search for items
     *
     * @param    keywords            Keywords which we're requesting
     * @param    responseGroup        ResponseGroups to be requested
     * @param    searchIndex            Name of search index (category) requested. NULL if searching all.
     * @param    sortBy                Category to sort by, only used if searchIndex is not 'All'
     * @param    condition            Condition of item. Valid conditions : Used, Collectible, Refurbished, All
     *
     * @return    mixed                SimpleXML object, array of data or false if failure.
     */
    public function ItemSearch($keywords, Array $responseGroups = [], $searchIndex = NULL, $sortBy = NULL, $condition = 'New')
    {
        $params = array(
            'Operation' => 'ItemSearch',
            'ResponseGroup' => $this->CheckAndHandleResponseGroups($responseGroups, 'ItemSearch'),
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
     * @param    asinList            Either a single ASIN or an array of ASINs
     * @param    responseGroup        ResponseGroups to be requested
     * @param    onlyFromAmazon        True if only requesting items from Amazon and not 3rd party vendors
     *
     * @return    mixed                SimpleXML object, array of data or false if failure.
     */
    public function ItemLookup($asinList, Array $responseGroups = [], $onlyFromAmazon = false)
    {
        if (is_array($asinList)) {
            $asinList = implode(',', $asinList);
        }

        $params = array(
            'Operation' => 'ItemLookup',
            'ResponseGroup' => $this->CheckAndHandleResponseGroups($responseGroups, 'ItemLookup'),
            'ReviewSort' => '-OverallRating',
            'ItemId' => $asinList,
            'MerchantId' => ($onlyFromAmazon == true) ? 'Amazon' : 'All'
        );

        return $this->MakeAndParseRequest($params);
    }

    public function GetErrors()
    {
        return $this->mErrors;
    }

    private function AddError($error)
    {
        array_push($this->mErrors, $error);
    }

    private function MakeAndParseRequest($params)
    {
        $signedUrl = $this->urlBuilder->generate($params);

        try {
            $request = new CurlHttpRequest();
            $response = $request->execute($signedUrl);

            $parsedXml = simplexml_load_string($response);

            if ($parsedXml === false) {
                return false;
            }

            return $this->dataTransformer->execute($parsedXml);
        } catch (\Exception $error) {
            $this->AddError("Error downloading data : $signedUrl : " . $error->getMessage());
            return false;
        }
    }

    private function CheckAndHandleResponseGroups(Array $responseGroups, $requestType = null)
    {
        foreach ($responseGroups as &$value) {
            $value = ucfirst($value);

            if (!in_array($value, $responseGroups)) {
                $this->AddError($value . ' is not a valid ResponseGroup.');
                unset($value);
            }
        }

        if (empty($responseGroups)) {

            switch ($requestType) {
                default:
                    return 'Small';
                case 'ItemSearch':
                    return 'ItemAttributes,Offers,Images';
                case 'ItemLookup':
                    return 'ItemAttributes,Offers,Reviews,Images,EditorialReview';
            }
        }

        return implode($responseGroups, ',');
    }
}

?>
