<?php

/*******************************************************************************
*  This file is licensed under the MIT license. © 2023 Friend Software Labs AS *
*******************************************************************************/

// Do some checks
if( !file_exists( '../archive' ) )
	die( '{"message":"Failed to access repository.","response":"404"}' );

header( 'Content-type: application/json; charset=utf-8' );

// List items
if( $_REQUEST[ 'action' ] == 'list' )
{
	// Wallpaper
	if( $_REQUEST[ 'type' ] == 'wallpaper' )
	{
		if( !file_exists( '../archive/wallpaper' ) )
		{
			die( '{"response":"-1","message":"No such asset here."}' );
		}
		$papers = [];
		if( $d = opendir( '../archive/wallpaper' ) )
		{
			while( $f = readdir( $d ) )
			{
				if( $f[0] == '.' ) continue;
				$o = new stdClass();
				$o->type = 'category';
				$o->name = $f;
				$o->wallpapers = [];
				if( is_dir( '../archive/wallpaper/' . $f ) && $i = opendir( '../archive/wallpaper/' . $f ) )
				{
					while( $ff = readdir( $i ) )
					{
						if( $ff[0] == '.' ) continue;
						if( strstr( $ff, '.thumb.' ) ) continue;
						if( substr( $ff, -4, 4 ) == '.jpg' )
						{
							$o->wallpapers[] = $f . '/' . $ff;
						}
					}
					closedir( $i );
				}
				$papers[] = $o;
			}
			closedir( $d );
		}
		if( count( $papers ) )
		{
			die( '{"response":"1","message":"Success.","wallpapers":' . json_encode( $papers ) . '}' );
		}
		die( '{"response":"-1","message":"No wallpapers."}' );
	}
	die( '{"response":"-1","message":"Failed to fetch wallpapers."}' );
}
else if( $_REQUEST[ 'action' ] == 'media' )
{
}
// Get items
else if( $_REQUEST[ 'action' ] == 'get' )
{
	// Wallpaper
	if( $_REQUEST[ 'type' ] == 'wallpaper' )
	{
		$f = stripslashes( urldecode( $_REQUEST[ 'item' ] ) );
		if( !strstr( $f, '..' ) )
		{
			if( file_exists( '../archive/wallpaper/' . $f ) )
			{
				header( 'Content-type: image/jpeg' );
				readfile( '../archive/wallpaper/' . $f );
				die();
			}
		}
	}
	// Wallpaper thumbnail
	else if( $_REQUEST[ 'type' ] == 'wallpaper-thumbnail' )
	{
		$f = stripslashes( urldecode( $_REQUEST[ 'item' ] ) );
		if( !strstr( $f, '..' ) )
		{
			if( file_exists( '../archive/wallpaper/' . $f . '.thumb.jpg' ) )
			{
				header( 'Content-type: image/jpeg' );
				readfile( '../archive/wallpaper/' . $f . '.thumb.jpg' );
				die();
			}
		}
	}
}
die( '{"response":"-1","message":"No such REST query."}' );

?>
