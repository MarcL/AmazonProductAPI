<?php

require('vendor/autoload.php');

use MarcL\AmazonAPI;
use MarcL\AmazonUrlBuilder;

// Should load these from environment variables
include_once('./secretKeys.php');

// Setup a new instance of the AmazonUrlBuilder with your keys
$urlBuilder = new AmazonUrlBuilder(
    $keyId,
    $secretKey,
    $associateId,
    'uk'
);

// Setup a new instance of the AmazonAPI with your keys
$amazonAPI = new AmazonAPI($urlBuilder, 'simple');

// Need to avoid triggering Amazon API throttling
$sleepTime = 1.5;

// Item Search:
// Harry Potter in Books, sort by featured
$items = $amazonAPI->ItemSearch('harry potter', 'Books');
print('>> Harry Potter in Books, sort by featured');
var_dump($items);

sleep($sleepTime);

// Harry Potter in Books, sort by price low to high
$items = $amazonAPI->ItemSearch('harry potter', 'Books', 'price');
print('>> Harry Potter in Books, sort by price low to high');
var_dump($items);

sleep($sleepTime);

// Harry Potter in Books, sort by price high to low
$items = $amazonAPI->ItemSearch('harry potter', 'Books', '-price');
print('>> Harry Potter in Books, sort by price high to low');
var_dump($items);

sleep($sleepTime);

// Amazon echo, lookup only with Amazon as a seller
$items = $amazonAPI->ItemLookUp('B01GAGVIE4', true);
print('>> Look up specific ASIN\n');
var_dump($items);
?>
