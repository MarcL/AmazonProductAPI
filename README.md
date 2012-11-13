<<<<<<< HEAD
# AmazonProductAPI
PHP library to perform product lookup and searches using the Amazon Product API.

## Dependencies
Requires the [SimpleXML](http://php.net/manual/en/book.simplexml.php) PHP and [Curl](http://php.net/manual/en/book.curl.php) extensions to be installed.
It also assumes that you have some knowledge of Amazon's Product API and have set up an Amazon Associate and [Amazon Web Services](http://docs.amazonwebservices.com/AWSECommerceService/2011-08-01/GSG/GettingSetUp.html) account in order to retrieve your access keys.

## Installation
Clone the git repository:
	
	git clone https://github.com/MarcL/AmazonProductAPI.git
	

## Usage
Include the library in your code
	
	require_once( 'AmazonAPI' )
	
Instantiate the class using your secret keys

	$keyId 		= 'YOUR-AWS-KEY';
	$secretKey 	= 'YOUR-AWS-SECRET-KEY';
	$associateId	= 'YOUR-AMAZON-ASSOCIATE-ID';
	
	$amazonAPI = new AmazonAPI( $keyId, $secretKey, $associateId );
	
It currently supports three Amazon regions - United Kingdom, United States and Germany. The default is UK but to set the locale call:

	$amazonAPI->SetLocale( 'us' );
	
### Item Search
To search for an item use the ItemSearch method:

	// Search for Harry Potter items in all categories
	$items = $amazonAPI->ItemSearch( 'harry potter' );
	
	// Search for Harry Potter items in Books category only
	$items = $amazonAPI->ItemSearch( 'harry potter', 'Books' );

To determine valid categories for search call GetValidSearchNames() :

	// Get an array of valid search categories we can use
	$searchCategories = $amazonAPI->GetValidSearchNames();
	
### Item Lookup
To look up product using the product ASIN number use ItemLookup:

	// Retrieve specific item by id
	$items = $amazonAPI->ItemLookUp( 'B003U6I396' );
	
	// Retrieve a list of items by ids
	$asinIds = array( 'B003U6I396', 'B003U6I397', 'B003U6I398' );
	$items = $amazonAPI->ItemLookUp( $asinIds );	

## Returned data
By default the data will be returned as SimpleXML nodes. However if you pass a 4th parameter as 'true' to the constructor then a simplified array of items will be returned. For example:

	// Return XML data
	$amazonAPI = new AmazonAPI( $keyId, $secretKey, $associateId );
	$items = $amazonAPI->ItemSearch( 'harry potter' );
	var_dump( $items );

This will output:
	
	class SimpleXMLElement#2 (2) {
		public $OperationRequest =>
			class SimpleXMLElement#3 (3) {
			    public $RequestId =>
			    string(36) "de58449e-0c1a-47ac-9823-00fd049c52df"
			    public $Arguments =>
			    class SimpleXMLElement#5 (1) {
			      public $Argument =>
			      array(11) {
        ...

	// Return simplified data
	$amazonAPI = new AmazonAPI( $keyId, $secretKey, $associateId, true );
	$items = $amazonAPI->ItemSearch( 'harry potter' );
	var_dump( $items );
	
Returning simplified data gives:

	array(10) {
	  [0] =>
	  array(8) {
	    'asin' =>
	    string(10) "B00543R3WG"
	    'url' =>
	    string(212) "http://www.amazon.co.uk/Harry-Potter-Complete-8-Film-Collection/dp/B00543R3WG%3FSubscriptionId%3D1BM0B8TXM1YSZ1M0XDR2%26tag%3Ddjcr-21%26linkCode%3Dxm2%26camp%3D2025%26creative%3D165953%26creativeASIN%3DB00543R3WG"
	    'rrp' =>
	    double(44.99)
	    'title' =>
	    string(58) "Harry Potter - The Complete 8-Film Collection [DVD] [2011]"
	    'lowestPrice' =>
	    double(23.4)
	    'largeImage' =>
	    string(53) "http://ecx.images-amazon.com/images/I/51qa9nTUsEL.jpg"
	    'mediumImage' =>
	    string(61) "http://ecx.images-amazon.com/images/I/51qa9nTUsEL._SL160_.jpg"
	    'smallImage' =>
	    string(60) "http://ecx.images-amazon.com/images/I/51qa9nTUsEL._SL75_.jpg"
	  }
	  [1] =>
	  array(8) {
	    'asin' =>
	    string(10) "0747558191"
	    'url' =>
	    string(212) "http://www.amazon.co.uk/Harry-Potter-Philosophers-Stone-Rowling/dp/0747558191%3FSubscriptionId%3D1BM0B8TXM1YSZ1M0XDR2%26tag%3Ddjcr-21%26linkCode%3Dxm2%26camp%3D2025%26creative%3D165953%26creativeASIN%3D0747558191"
	    'rrp' =>
	    double(6.99)
    	'title' =>
    	string(40) "Harry Potter and the Philosopher\'s Stone"
    	…
    	
## TODO
* Need to make the simplified data less hardcoded!
	
	
## Thanks
This library uses code based on [AWS API authentication For PHP](http://randomdrake.com/2009/07/27/amazon-aws-api-rest-authentication-for-php-5/) by [David Drake](https://github.com/randomdrake).
=======
AmazonProductAPI
================

PHP library to retrieve products from the Amazon Product API
>>>>>>> 8eac234496a5a0245c61d95931beec800f0cf132
