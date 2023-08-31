<?php

/*******************************************************************************
*  This file is licensed under the MIT license. © 2023 Friend Software Labs AS *
*******************************************************************************/

// Do some checks
if( !file_exists( '../archive' ) )
	die( '{"message":"Failed to access repository.","response":404"}' );

if( $_REQUEST[ 'action' ] == 'list' )
{
	
}
else if( $_REQUEST[ 'action' ] == 'media' )
{
}
else if( $_REQUEST[ 'action' ] == 'get' )
{
}

?>
