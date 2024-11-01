(function()
{
	var MMTL_Updater =
	{
		_elem : null,
		_index : -1,
		_stop : false,

		init : function( elem, options )
		{
			var me = this;

			this._elem = jQuery( elem );
			this._options = jQuery.extend( {}, options );

			this._elem.on( 'mmtl_before', function( event )
			{
				me._elem.find( 'p.submit' )
					.hide();

				me.doAction( 0 );
			});

			this._elem.on( 'mmtl_action_before', function( event, action, index )
			{
				var $action = me.getActionElem( index );

				$action.find( '.mmtl-loader' ).show();
			});

			this._elem.on( 'mmtl_action_success', function( event, action, index )
			{
				var $action = me.getActionElem( index );

				$action
					.addClass( 'mmtl-action-success' )
						.find( '.mmtl-success' ).show();
			});

			this._elem.on( 'mmtl_action_error', function( event, action, index )
			{
				var $action = me.getActionElem( index );

				$action
					.addClass( 'mmtl-action-error' )
						.find( '.mmtl-error' ).show();
			});

			this._elem.on( 'mmtl_action_complete', function( event, action, index, data, success )
			{
				var $action = me.getActionElem( index );

				if ( typeof data === 'object' )
				{
					var report = data;

					if ( ! report.log )
					{
						return;
					};
					
					$action.find( '.mmtl-log-text' )
						.val( report.log )
						.focus(function()
						{
							jQuery(this).select();
						});

					$action.find( '.mmtl-show-log-button' ).show();
				};
			});

			this._elem.on( ' ', function( event, action, index, error )
			{
				var $action = me.getActionElem( index );

				me._stop = true;

				me._elem.find( '.mmtl-update-ajax-error' )
					.show()
					.find( '.mmtl-ajax-error-text' )
						.text( error );

				$action.find( '.mmtl-error' ).show();
			});

			this._elem.on( 'mmtl_action_after', function( event, action, index )
			{
				var $action = me.getActionElem( index );

				$action.find( '.mmtl-loader' ).hide();

				if ( index + 1 >= me._options.actions.length )
				{
					me._elem.trigger( 'mmtl_after' );
					
					return;
				}

				if ( me._stop )
				{
					return;
				};

				me._index = index + 1;

				setTimeout( function()
				{
					me.doAction( me._index );
				}, 1000 );
			});

			this._elem.on( 'mmtl_after', function( event )
			{
				me._elem.find( '.mmtl-update-complete' ).show();			
			});

			this._elem.find( 'form' ).on( 'submit', function( event )
			{
				event.preventDefault();

				me._elem.trigger( 'mmtl_before' );
			});
		},

		getActionElem : function( index )
		{
			return this._elem.find( '.mmtl-action' ).eq( index );
		},

		doAction : function( index )
		{
			var me = this;

			var action = this._options.actions[ index ];

			me._elem.trigger( 'mmtl_action_before', [ action, index ] );

			var args =
			{
				action : 'mmtl_updater_do_action',
				action_id : action,
				[ this._options.noncename ] : this._options.nonce
			};

			return jQuery.post( this._options.ajaxurl, args )

				.done(function( response )
				{
					if ( response.success )
					{
						me._elem.trigger( 'mmtl_action_success', [ action, index, response.data ] );
					}

					else
					{
						me._elem.trigger( 'mmtl_action_error', [ action, index, response.data ] );
					};

					me._elem.trigger( 'mmtl_action_complete', [ action, index, response.data, response.success ] );
				})

				.fail(function( a, b, error )
				{
					me._elem.trigger( 'mmtl_action_ajax_error', [ action, index, error ] );
				})

				.always(function()
				{	
					me._elem.trigger( 'mmtl_action_after', [ action, index ] );
				});
		}
	};

	jQuery( document ).ready(function()
	{
		MMTL_Updater.init( '#mmtl-updater-screen', MMTL_Updater_Options );
	});

})();