<?php

/*******************************************************************************
*  This file is licensed under the MIT license. © 2023 Friend Software Labs AS *
*******************************************************************************/

/*
	This is a network browsing proxy for Friend OS
*/

// Checks if the url contains HTML
function checkHTML( $url )
{
	// Function to check if a string contains HTML
	function isHTML( $string )
	{
		return preg_match( "/<html/i", $string );
	}

	// Function to fetch a small portion of the URL content
	function fetchPartialContent( $url, $length = 1000 )
	{
		$content = file_get_contents( $url, false, null, 0, $length );
		return $content;
	}

	// Fetch a small portion of the URL content
	$partialContent = fetchPartialContent( $url );

	// Check if the fetched content contains HTML
	if( isHTML( $partialContent ) )
		return true;
	return false;
}

function getBaseUrl() 
{
    $protocol = isset( $_SERVER[ 'HTTPS' ] ) && $_SERVER[ 'HTTPS' ] === 'on' ? 'https://' : 'http://';
    $host = $_SERVER[ 'HTTP_HOST' ];
    return $protocol . $host;
}

function outputProxyHTML( $htmlUrl )
{

	// Function to modify URLs in the HTML content
	function modifyUrls( $htmlContent, $htmlUrl )
	{
		// Regular expression to match existing base href tag
		$basePattern = '/<base[^>]*href=["\'](https?:\/\/[^"\']+)["\'][^>]*>/i';
		$baseUrl = getBaseUrl();

		// Extract the host from the base URL
		$baseHost = parse_url( $htmlUrl, PHP_URL_HOST );
		
		// Check if an existing base href tag is present in the HTML
		if( preg_match($basePattern, $htmlContent, $matches ) ) 
		{
			// Replace the existing base href tag with the specified format
			$htmlContent = preg_replace($basePattern, '<base href="' . $baseUrl . '/network/' . $baseHost . '/$1">', $htmlContent);
		}
		else 
		{
			// If no existing base href tag is found, add a new one in the <head> section
			$headPattern = '/<head[^>]*>/i';
			$replacement = '<head><base href="' . $baseUrl . '/network/' . $baseHost . '/">';
			$htmlContent = preg_replace( $headPattern, $replacement, $htmlContent );
		}
		
		 // Remove target="_blank" attributes from links
		$htmlContent = preg_replace( '/ target=["\']?_blank["\']?/i', '', $htmlContent );
		$htmlContent = preg_replace( '/ target\=\"\_blank\"/i', '', $htmlContent );
		
		// Regular expression to match form action attributes in HTML
		$formPattern = '/<form[^>]*action=["\']([^"\']+)["\'][^>]*>/i';

		// Replace form action attributes with the specified format
		$htmlContent = preg_replace( $formPattern, '<form action="' . $baseUrl . '/network.php?url=' . urlencode('$1') . '"', $htmlContent );

		// Regular expression to match <a> tag href links and replace them with the specified format
		$aTagPattern = '/<a\s+[^>]*href=["\']([^"\']+)["\'][^>]*>/i';
		$htmlContent = preg_replace( $aTagPattern, '<a href="' . $baseUrl . '/network.php?url=$1">', $htmlContent );
		
		// Add our listener
		$script = file_get_contents( 'assets/messaging.js' );
		$htmlContent = preg_replace( '/\<\/body/i', $script . '</body', $htmlContent );
		
		return $htmlContent;
		//return $modifiedContent;
	}

	// Function to load HTML content from a URL and retrieve the Content-Type header
	function loadHtmlWithContentTypeFromUrl( $url, &$contentType, &$redirectCode )
	{
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HEADER, true);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); // Disable automatic redirection

		$response = curl_exec( $ch );
		$httpCode = curl_getinfo( $ch, CURLINFO_HTTP_CODE ); // Get the HTTP status code
		$headerSize = curl_getinfo( $ch, CURLINFO_HEADER_SIZE );
		$contentType = curl_getinfo( $ch, CURLINFO_CONTENT_TYPE );
		
		// Set the User-Agent header to mimic a Firefox request
	    curl_setopt( $ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:90.0) Gecko/20100101 Firefox/90.0' );

		curl_close( $ch );

		// Check if the HTTP status code is 302 (temporary redirect)
		$isRedirect = ($httpCode == 302);
		$redirectCode = $httpCode;

		// Extract the HTML content from the response
		$htmlContent = substr($response, $headerSize);

		return $htmlContent;
	}

	// Load the HTML content from the URL and retrieve the Content-Type header
	$htmlContent = loadHtmlWithContentTypeFromUrl( $htmlUrl, $contentType, $redirectCode );
	if( $redirectCode != 200 )
	{
		die( str_replace( '%code%', $redirectCode, file_get_contents( 'assets/error.html' ) ) );
	}
	
	// Modify the URLs in the HTML content
	$modifiedHtml = modifyUrls( $htmlContent, $htmlUrl );
	
	// Output the modified HTML content and the Content-Type header
	header( 'Content-type: ' . $contentType );
	die( $modifiedHtml );
}

if( isset( $_REQUEST[ 'query' ] ) )
{
	$uricomponent = explode( '/', $_SERVER[ 'SCRIPT_URL' ] );
	$uricomponent = $uricomponent[2];
	die( $uricomponent . '/' . $_REQUEST[ 'query' ] );
	$_REQUEST[ 'url' ] = $_REQUEST[ 'query' ];
}

if( isset( $_REQUEST[ 'url' ] ) )
{
	$url = urldecode( $_REQUEST[ 'url' ] );
	if( checkHTML( $url ) )
	{
		// Dies here
		outputProxyHTML( $url );
	}
	else
	{
		header( 'Location: ' . $_REQUEST[ 'url' ] );
	}
}

die( file_get_contents( 'assets/404.html' ) );

?>
