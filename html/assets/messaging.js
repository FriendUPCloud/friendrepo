	<script>
		_friendRepo = {
			stripATags: function()
			{
				let t = document.getElementsByTagName( 'a' );
				for( let a = 0; a < t.length; a++ )
				{
					t[a].onclick = function( e ){ e.stopPropagation(); };
					t[a].onmousedown = function( e ){ e.stopPropagation(); };
					t[a].onmouseup = function( e ){ e.stopPropagation(); };
					if( t[a].getAttribute( 'target' ) )
						t[a].removeAttribute( 'target' );
				}
			}
		}
		document.body.addEventListener( 'load', function()
		{
			_friendRepo.stripATags();
		} );
		document.body.addEventListener( 'mouseover', function()
		{
			_friendRepo.stripATags();
		} );
		document.body.addEventListener( 'mouseout', function()
		{
			_friendRepo.stripATags();
		} );
		_friendRepo.stripATags();
	</script>

