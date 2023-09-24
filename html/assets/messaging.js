document.body.addEventListener( 'load', function()
{
	let t = document.getElementsByTagName( 'a' );
	for( let a = 0; a < t.length; a++ )
	{
		if( t[a].onclick )
			t[a].onclick = null;
		if( t[a].onmousedown )
			t[a].onmousedown = null;
		if( t[a].onmouseup )
			t[a].onmouseup = null;
	}
} );
