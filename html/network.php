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
		die( ( ( preg_match( "/<html/i", $string ) && preg_match( "/<\/html>/i", $string ) ) ? 'true' : 'false' ) . $string );
		return preg_match( "/<html/i", $string ) && preg_match( "/<\/html>/i", $string );
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
		$context = stream_context_create( [ 'http' => [ 'header' => 'Content-Type' ] ] );
		$htmlContent = file_get_contents( $url, false, $context, -1, 2048 );

		if($http_response_header)
		{
		    foreach( $http_response_header as $header )
		    {
		        if( stripos( $header, 'Content-Type:' ) === 0 )
		        {
		            $contentType = trim( substr( $header, 13 ) );
		            break;
		        }
		    }
		}

		return $htmlContent;
	}

	// Load the HTML content from the URL and retrieve the Content-Type header
	$htmlContent = loadHtmlWithContentTypeFromUrl($htmlUrl, $contentType);

	// Modify the URLs in the HTML content
	$modifiedHtml = modifyUrls($htmlContent);
	
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
		die( 'redirected..' );
		header( 'Location: ' . $_REQUEST[ 'url' ] );
	}
}

die( file_get_Contents( 'assets/404.html' ) );

?>
