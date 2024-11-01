(function()
{
	function isNumeric( value )
	{
  		return ! isNaN( parseFloat( value ) ) && isFinite( value );
	}

	function setColorpickerFieldColor( field )
	{
		var $field = jQuery( field );

		var color = $field.val();

		if ( color.match( /^#[a-f0-9]{6}$/ ) )
		{
			$field
				.css( 'color', getContrast50( color ) )
				.css( 'background-color', color );
		}

		else
		{
			$field
				.css( 'color', '' )
				.css( 'background-color', '' );
		}
	}

	// based on https://24ways.org/2010/calculating-color-contrast/

	function getContrast50( hexcolor )
	{
		if ( hexcolor.indexOf( '#' ) === 0 )
		{
			hexcolor = '0x' + hexcolor.substr( 1 );
		};

    	return (parseInt( hexcolor, 16 ) > 0xffffff / 2 ) ? 'black':'white';
	}

	jQuery( document ).ready(function()
	{	
		// Toggle

		jQuery( '.mmtl-toggle' ).click( function( event )
		{
			event.preventDefault();

			var target = jQuery( this ).attr( 'href' );

			jQuery( target ).toggle();
		});

		// Color Picker

		jQuery( '.mmtl-color-picker' ).iris(
		{
			hide: true, // hides the color picker by default
			change: function( event, ui )
			{
				setColorpickerFieldColor( this );
			}
		})

		.click(function( event )
		{
			event.stopPropagation();

			jQuery( this ).iris( 'show' );
		})

		.change(function( event )
		{
			setColorpickerFieldColor( this );
		})

		.trigger( 'change' );

		jQuery( document ).click(function( event )
		{
			jQuery( '.mmtl-color-picker' ).iris( 'hide' );
		});
		
	});

})();