(function()
{
	function isNumeric( value )
	{
  		return ! isNaN( parseFloat( value ) ) && isFinite( value );
	}

	jQuery( document ).ready(function()
	{	
		// Tabs

		//jQuery( '.mmtl-tabs' ).tabs();

		// Slider

		jQuery( '.mmtl-slider' ).each(function()
		{
			var $field = jQuery( this );

			var $slider = jQuery( '<div></div>' );

			$slider.slider(
			{
				range: $field.data( 'range' ),
				value: $field.val(),
				min: $field.data( 'min' ),
				max: $field.data( 'max' ),
				slide : function( event, ui )
				{
					$field.val( ui.value );
				}
			});

			$field.change(function()
			{
				$slider.slider( 'value', this.value );
			});

			$slider.insertAfter( $field );
		});

		// Toggle

		jQuery( '.mmtl-toggle' ).click( function( event )
		{
			event.preventDefault();

			var target = jQuery( this ).attr( 'href' );

			jQuery( target ).toggle();
		});

		// Color Picker

		jQuery( '.mmtl-color-picker' ).wpColorPicker();

		// selects all text on focus

		jQuery('.mmtl-box-model :input')
			
			.focus(function( event )
			{
				jQuery( this ).select();

			});

		// border radius preview

		jQuery('.mmtl-box-model-border-radius :input')
			
			.change(function( event )
			{
				var $field = jQuery( this );

				var pos = $field.data( 'position' );
				var radius = $field.val();

				if ( ! isNumeric( radius ) )
				{
					radius = 0;
				};

				radius = radius + 'px';

				jQuery('.mmtl-box-model-border')
					.css( '-webkit-border-' + pos + '-radius', radius )
					.css( '-moz-border-' + pos + '-radius', radius )
					.css( 'border-' + pos + '-radius', radius )

			}).trigger( 'change' );
	});

})();