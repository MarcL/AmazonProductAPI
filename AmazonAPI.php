<?php
/**
 *  Amazon Product API Library
 *
 *  This a generic PHP class that can hook-in to Amazon's Product API
 * 
 *  Author: Marc Littlemore
 *  http://www.marclittlemore.com
 *  
 *  This code is free, provided AS-IS with no warranty expressed or implied.  Use at your own risk.
 *  If you find errors or bugs in this code, please contact me at interested@zanpo.com
 *  If you enhance this code in any way, please send me an update.  Thank you!
 *
 *  Version: 0.1
 *  Last Updated: 13/12/2009
 * 
 *	NOTE: ENTER YOUR API ID AND SECRET KEY BELOW!!!
 *
 */ 

class AmazonAPI
{

	private $m_CI = NULL;		// The CodeIgniter instance
	private $m_amazonUrl = '';
	private $m_scraperUrl = '';
	private $m_amazonBaseUrl = '';
	private $m_locale = 'uk';

	// The two locale tables should always support the same locales
	// or we'll get into trouble! :)
	private $m_localeTable = array(
		'uk'	=>	'http://ecs.amazonaws.co.uk/onca/xml',
		'de'	=>	'http://ecs.amazonaws.de/onca/xml',
		'us'	=>	'http://ecs.amazonaws.com/onca/xml',
	);

	private $m_scraperLocaleTable = array(
		'uk'	=>	'http://www.amazon.co.uk/exec/obidos/ASIN/',
		'de'	=>	'http://www.amazon.de/exec/obidos/ASIN/',
		'us'	=>	'http://www.amazon.com/exec/obidos/ASIN/',
	);
	
	private $m_amazonBaseUrlLocaleTable = array(
		'uk'	=>	'http://www.amazon.co.uk/',
		'de'	=>	'http://www.amazon.de/',
		'us'	=>	'http://www.amazon.com/',
	);
	// -----------------------------------------
	// -----------------------------------------
	// API key ID
	private $m_keyId		= NULL;

	// API Secret Key
	private $m_secretKey	= NULL;
	private $m_associateTag = NULL;
	// -----------------------------------------
	// -----------------------------------------
	
	private $mValidSearchNames = array(
		'All','Apparel','Appliances','Automotive','Baby','Beauty','Blended','Books','Classical','DVD','Electronics','Grocery','HealthPersonalCare','HomeGarden','HomeImprovement','Jewelry','KindleStore','Kitchen','Lighting','Marketplace','MP3Downloads','Music','MusicTracks','MusicalInstruments','OfficeProducts','OutdoorLiving','Outlet','PetSupplies','PCHardware','Shoes','Software','SoftwareVideoGames','SportingGoods','Tools','Toys','VHS','Video','VideoGames','Watches',
	);

	private $mErrors = array();

	public function __construct( $keyId, $secretKey, $associateTag )
	{
		// Setup the AWS credentials
		$this->m_keyId			= $keyId;
		$this->m_secretKey		= $secretKey;
		$this->m_associateTag	= $associateTag;

		$this->m_CI =& get_instance();

		// Pretend to be Google Chrome for scraping
		ini_set( 'user_agent', 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US) AppleWebKit/532.5 (KHTML, like Gecko) Chrome/4.0.249.64 Safari/532.5' );
		
		// Set UK as locale by default
		$this->SetLocale( 'uk' );
	}

	public function SetLocale( $locale )
	{
		// Check we have a locale in our table
		$foundLocale = false;
		foreach( $this->m_localeTable as $key => $value )
		{
			if ( $key == $locale )
			{
				// Found the locale
				$foundLocale = true;
				break;
			}
		}
		
		if ( $foundLocale == false )
		{
			// If not then just assume it's the UK
			$locale = 'uk';
		}
		
		// Set the URL for this locale
		$this->m_locale = $locale;
		$this->m_amazonUrl = $this->m_localeTable[$locale];
		$this->m_scraperUrl = $this->m_scraperLocaleTable[$locale];
		$this->m_amazonBaseUrl = $this->m_amazonBaseUrlLocaleTable[$locale];
	}
	
	public function GetValidSearchNames()
	{
		return( $this->mValidSearchNames );
	}

	function GetScraperUrl()
	{
		return( $this->m_scraperUrl );
	}
	
	private function GetUrl( $url )
	{
		// The use of `file_get_contents` may not work on all servers because it relies on the ability to open remote
		// URLs using the file manipulation functions. 
		// PHP gives you the ability to disable this functionality in your php.ini file and many administrators do so for security reasons.
		// If your administrator has not done so, you can comment out the following 5 lines of code and uncomment the 6th.  
		$session = curl_init( $url );
		curl_setopt( $session, CURLOPT_HEADER, false );
		curl_setopt( $session, CURLOPT_RETURNTRANSFER, true );
		$response = curl_exec( $session );
		curl_close( $session ); 
		//$response = file_get_contents($request);
		$parsedXml = simplexml_load_string( $response );
		
		return( $parsedXml );
	}
	
	//Set up the operation in the request
	public function ItemSearch( $keywords, $searchIndex = NULL, $sortBySalesRank = true, $condition = 'New' )
	{
		// Set the values for some of the parameters.
		$operation = "ItemSearch";
		$responseGroup = "ItemAttributes,Offers,Images";
		
		//Define the request
		$request= $this->GetBaseUrl()
		   . "&Operation=" . $operation
		   . "&Keywords=" . $keywords
		   . "&ResponseGroup=" . $responseGroup
		   . "&Condition=" . $condition;

		// Assume we're searching in all if an index isn't passed
		if ( empty( $searchIndex ) )
		{
			// Search for all
			$request .= "&SearchIndex=All";
		}
		else
		{
			// Searching for specific index
			$request .= "&SearchIndex=" . $searchIndex;

			// If we're sorting by sales rank
			if ( $sortBySalesRank && ( $searchIndex != 'All' ) )
				$request .= "&Sort=salesrank";
		}

		// Need to sign the request now
		$signedUrl = $this->GetSignedRequest( $this->m_secretKey, $request );
		
		// Get the response from the signed URL
		$parsedXml = $this->GetUrl( $signedUrl );
		
		//$this->PrintErrors( $parsedXml );
		//var_dump( $parsedXml );

		$items = $this->RetrieveItems( $parsedXml );
		return( $items );
	}
	
	// Pass the ASIN id
	public function ItemLookup( $asinList, $onlyFromAmazon = false )
	{
		// Check if it's an array
		if ( is_array( $asinList ) )
		{
			$asinList = implode( ',', $asinList );
		}
		
		// Set the values for some of the parameters.
		$operation = "ItemLookup";
		$responseGroup = "ItemAttributes,Offers,Reviews,Images,EditorialReview";
		
		// Determine whether we just want Amazon results only or not
		$merchantId = ( $onlyFromAmazon == true ) ? 'Amazon' : 'All';
		
		$reviewSort = '-OverallRating';
		//Define the request
		$request = $this->GetBaseUrl()
		   . "&ItemId=" . $asinList
		   . "&Operation=" . $operation
		   . "&ResponseGroup=" . $responseGroup
		   . "&ReviewSort=" . $reviewSort
		   . "&MerchantId=" . $merchantId;
		   
		// Need to sign the request now
		$signedUrl = $this->GetSignedRequest( $this->m_secretKey, $request );
		
		// Get the response from the signed URL
		$parsedXml = $this->GetUrl( $signedUrl );
		
		//$this->PrintErrors( $parsedXml );
//		var_dump( $parsedXml ); die();
		
		var_dump( $parsedXml->Items );
		$items = $this->RetrieveItems( $parsedXml );
		return( $items );
	}

	public function RetrieveItems( $responseXml )
	{
		$items = array();
		if ( empty( $responseXml ) )
		{
			$this->AddError( "No XML response found from AWS." );
			return( $items );
		}

		if ( empty( $responseXml->Items ) )
		{
			$this->AddError( "No items found." );
			return( $items );
		}

		if ( $responseXml->Items->Request->IsValid != 'True' )
		{
			$errorCode = $responseXml->Items->Request->Errors->Error->Code;
			$errorMessage = $responseXml->Items->Request->Errors->Error->Message;
			$error = "API ERROR ($errorCode) : $errorMessage";
			$this->AddError( $error );
			return( $items );
		}

		// Get each item
		foreach( $responseXml->Items->Item as $responseItem )
		{
			$item = array();
			$item['asin'] = (string) $responseItem->ASIN;
			$item['url'] = (string) $responseItem->DetailPageURL;
			$item['rrp'] = ( (float) $responseItem->ItemAttributes->ListPrice->Amount ) / 100.0;
			$item['title'] = (string) $responseItem->ItemAttributes->Title;
			
			if ( $responseItem->OfferSummary )
			{
				$item['lowestPrice'] = ( (float) $responseItem->OfferSummary->LowestNewPrice->Amount ) / 100.0;
			}
			else
			{
				$item['lowestPrice'] = 0.0;
			}

			// Images
			$item['largeImage'] = (string) $responseItem->LargeImage->URL;
			$item['mediumImage'] = (string) $responseItem->MediumImage->URL;
			$item['smallImage'] = (string) $responseItem->SmallImage->URL;

			// var_dump( $responseItem->EditorialReviews );
			//var_dump( $item );
			array_push( $items, $item );
		}

		return( $items );		
	}


	private function GetBaseUrl()
	{
		//Define the request
		$request=
		     $this->m_amazonUrl
		   . "?Service=AWSECommerceService"
		   . "&AssociateTag=" . $this->m_associateTag
		   . "&AWSAccessKeyId=" . $this->m_keyId;
		   
		return( $request );
	}
	
	/**
	  * This function will take an existing Amazon request and change it so that it will be usable 
	  * with the new authentication.
	  *
	  * @param string $secret_key - your Amazon AWS secret key
	  * @param string $request - your existing request URI
	  * @param string $access_key - your Amazon AWS access key
	  * @param string $version - (optional) the version of the service you are using
	  */
	// Code from here http://www.ilovebonnie.net/2009/07/27/amazon-aws-api-rest-authentication-for-php-5/
	private function GetSignedRequest( $secret_key, $request, $access_key = false, $version = '2009-03-01')
	{
	    // Get a nice array of elements to work with
	    $uri_elements = parse_url($request);
	 
	    // Grab our request elements
	    $request = $uri_elements['query'];
	 
	    // Throw them into an array
	    parse_str($request, $parameters);
	 
	    // Add the new required paramters
	    $parameters['Timestamp'] = gmdate( "Y-m-d\TH:i:s\Z" );
	    $parameters['Version'] = $version;
	    if ( strlen($access_key) > 0 )
	    {
	        $parameters['AWSAccessKeyId'] = $access_key;
	    }   
	 
	    // The new authentication requirements need the keys to be sorted
	    ksort( $parameters );
	 
	    // Create our new request
	    foreach ( $parameters as $parameter => $value )
	    {
	        // We need to be sure we properly encode the value of our parameter
	        $parameter = str_replace( "%7E", "~", rawurlencode( $parameter ) );
	        $value = str_replace( "%7E", "~", rawurlencode( $value ) );
	        $request_array[] = $parameter . '=' . $value;
	    }   
	 
	    // Put our & symbol at the beginning of each of our request variables and put it in a string
	    $new_request = implode( '&', $request_array );
	 
	    // Create our signature string
	    $signature_string = "GET\n{$uri_elements['host']}\n{$uri_elements['path']}\n{$new_request}";
	 
	    // Create our signature using hash_hmac
	    $signature = urlencode( base64_encode( hash_hmac( 'sha256', $signature_string, $secret_key, true ) ) );
	 
	    // Return our new request
	    return "http://{$uri_elements['host']}{$uri_elements['path']}?{$new_request}&Signature={$signature}";
	}

	private function AddError( $error )
	{
		array_push( $this->mErrors, $error );
	}

	public function GetErrors()
	{
		return( $this->mErrors );
	}

	// Get all errors as a single error string
	public function GetErrorString()
	{
		$errors = $this->GetErrors();
		$errorString = '<ul>';
		foreach( $errors as $error )
		{
			$errorString .= "<li>$error</li>";
		}
		$errorString .= "</ul>";

		return( $errorString );
	}
}

?>