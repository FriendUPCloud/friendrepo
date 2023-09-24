let _friendRepo = {
	stripATags: function()
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
			if( t[a].getAttribute( 'target' ) )
				t[a].setAttribute( 'target', '' );
		}
	}
}
document.body.addEventListener( 'load', function()
{
	_friendRepo.stripATags();
} );
_friendRepo.stripATags();
