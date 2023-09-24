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

function outputProxyHTML( $htmlUrl )
{

	// Function to modify URLs in the HTML content
	function modifyUrls( $htmlContent )
	{
		// Regular expression to match URLs in HTML attributes (src, href, etc.)
		$pattern = '/(src|href)=["\'](https?:\/\/[^"\']+)["\']/i';
		
		// Callback function to replace URLs with the modified format
		$replacement = '$1="network.php?url=' . urlencode( '$2' ) . '"';
		
		// Use preg_replace_callback to modify the URLs
		$modifiedContent = preg_replace_callback( $pattern, function( $matches ) use ( $replacement )
		{
		    return preg_replace( '/\$(\d+)/', '$', preg_replace( '/\\/', '\\\\', $replacement ) );
		}, $htmlContent );
		
		return $modifiedContent;
	}

	// Function to load HTML content from a URL and retrieve the Content-Type header
	function loadHtmlWithContentTypeFromUrl( $url, &$contentType, &$redirectCode )
	{
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HEADER, true);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false); // Disable automatic redirection

		$response = curl_exec($ch);
		$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE); // Get the HTTP status code
		$headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
		$contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);

		curl_close($ch);

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
	$modifiedHtml = modifyUrls( $htmlContent );
	
	// Output the modified HTML content and the Content-Type header
	header( 'Content-type: ' . $contentType );
	die( $modifiedHtml );
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
