<?php
/**
 * Example code for Shoprenter App
 * DO NOT USE IN PRODUCTION
 *
 *  This is the app's redirectURi
 *
 */

$shopname = $_GET['shopname'];
$code = $_GET['code'];
$timestamp = $_GET['timestamp'];
$hmac = $_GET['hmac'];
$clientId = '4f48c5c2b698028c7e9f164c';
$clientSecret = '13afd849ec2781ca3fb2dd1a';
$appId = 12;

/**
 * @param string $clientSecret
 * @param string $shopName
 * @param int $code
 * @param int $timestamp
 * @return string
 */
function generateHmac($clientSecret, $shopName, $code, $timestamp) {
    $queryString = sprintf('shopname=%s&code=%s&timestamp=%s', $shopName, $code, $timestamp);

    return hash_hmac('sha256', $queryString, $clientSecret);
}

/**
 * @param string $generatedHmac
 * @param string $hmac
 * @return bool
 */
function isValidHmac($generatedHmac, $hmac) {
    return $generatedHmac === $hmac;
}

$generatedHmac = generateHmac($clientSecret, $shopname, $code, $timestamp);

if (!isValidHmac($generatedHmac, $hmac)) {
    echo 'Validation failed!';
    exit;
}

$options = [
    CURLOPT_RETURNTRANSFER => true,     // return web page
    CURLOPT_HEADER         => false,    // don't return headers
    CURLOPT_FOLLOWLOCATION => true,     // follow redirects
    CURLOPT_ENCODING       => '',       // handle all encodings
    CURLOPT_USERAGENT      => 'spider', // who am i
    CURLOPT_AUTOREFERER    => true,     // set referer on redirect
    CURLOPT_CONNECTTIMEOUT => 120,      // timeout on connect
    CURLOPT_TIMEOUT        => 120,      // timeout on response
    CURLOPT_MAXREDIRS      => 10,       // stop after 10 redirects
    CURLOPT_POST           => 1,
    CURLOPT_SSL_VERIFYPEER => false,    // Disabled SSL Cert checks
    CURLOPT_SSL_VERIFYHOST => false,    // Disabled SSL Cert checks
    CURLOPT_POSTFIELDS     => sprintf('client_id=%s&client_secret=%s&code=%s&timestamp=%s&hmac=%s', $clientId, $clientSecret, $code, $timestamp, $hmac)
];

// Send request for API credentials
$ch      = curl_init( 'https://'.$shopname.'.myshoprenter.hu/admin/oauth/access_credential' );
curl_setopt_array( $ch, $options );
$content = curl_exec( $ch );
$err     = curl_errno( $ch );
$errmsg  = curl_error( $ch );
$header  = curl_getinfo( $ch );
curl_close( $ch );

// Store credentials for this shop
file_put_contents($shopname.'auth.txt', $content);

// redirect to the app's interface 
header(sprintf('Location: https://%s.shoprenter.hu/admin/app/%d', $shopname, $appId));
