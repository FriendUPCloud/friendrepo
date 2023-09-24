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
	function loadHtmlWithContentTypeFromUrl( $url, &$contentType )
	{
		$ch = curl_init( $url );
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
		curl_setopt( $ch, CURLOPT_HEADER, true );

		$response = curl_exec( $ch );
		$headerSize = curl_getinfo( $ch, CURLINFO_HEADER_SIZE );
		$contentType = curl_getinfo( $ch, CURLINFO_CONTENT_TYPE );

		curl_close($ch);

		// Extract the HTML content from the response
		$htmlContent = substr( $response, $headerSize );

		return $htmlContent;
	}

	// Load the HTML content from the URL and retrieve the Content-Type header
	$htmlContent = loadHtmlWithContentTypeFromUrl( $htmlUrl, $contentType );

	die( $htmlContent );
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

die( file_get_Contents( 'assets/404.html' ) );

?>
