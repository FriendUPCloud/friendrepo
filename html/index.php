<?php

/*******************************************************************************
*  This file is licensed under the MIT license. © 2023 Friend Software Labs AS *
*******************************************************************************/

// Include the config
if( file_exists( '../config.php' ) )
	include( '../config.php' );

// Web actions
if( $_REQUEST[ 'action' ] )
{
	include( '../include/actions.php' );
}

// Get base template or custom template
if( isset( $Config->index_template ) )
	$tpl = file_get_contents( '../' . $Config->index_template );
else $tpl = file_get_contents( '../templates/index.html' );

die( $tpl );

?>
