<?php if ( ! defined( 'ABSPATH' ) ) exit; //exits when accessed directly

class MMTL_Common
{
	static public function position_fields( $field_name, $data, $type = null, $args = array() )
	{
		$labels = array
		(
			'top'          => __( 'Top', 'table-layout' ),
			'top_right'    => __( 'Top right', 'table-layout' ),
			'right'        => __( 'Right', 'table-layout' ),
			'bottom_right' => __( 'Bottom right', 'table-layout' ),
			'bottom'       => __( 'Bottom', 'table-layout' ),
			'bottom_left'  => __( 'Bottom left', 'table-layout' ),
			'left'         => __( 'Left', 'table-layout' ),
			'top_left'     => __( 'Top left', 'table-layout' )
		);

		// filters data with keys presented in labels array

		$data = array_intersect_key( $data, $labels );

		if ( empty( $data ) )
		{
			return;
		}

		?>

		<div class="mmtl-row mmtl-position-fields">

			<?php foreach ( $data as $dir => $value ) :

				$label = $labels[ $dir ];

			?>
			<div class="mmtl-col mmtl-col-sm-3">

				<div class="mmtl-form-group">
					<div class="mmtl-input-group">
						<div class="mmtl-input-group-addon"><span class="mmtl-direction mmtl-direction-<?php echo esc_attr( $dir ); ?> dashicons dashicons-arrow-up-alt"></div>
				      		<?php

							$args = array_merge( $args, array
							(
								'name'  => sprintf( $field_name, $dir ),
								'value' => $value
							));

							if ( empty( $args['class'] ) )
							{
								$args['class'] = '';
							}

							$args['class'] = 'mmtl-form-control ' . $args['class'];

							switch ( $type )
							{
								case 'number':
									
									MMTL_Form::number( $args );

									break;

								case 'dropdown':
									
									MMTL_Form::dropdown( $args );

									break;

								default :

									MMTL_Form::textfield( $args );
							}

							?>

						<?php if ( $type == 'number' ) : ?>
						<div class="mmtl-input-group-addon">px</div>
						<?php endif; ?>
				      	
				    </div>
				</div>
					
			</div>
			<?php endforeach; ?>

		</div><!-- .mmtl-row -->

		<?php
	}

	static public function is_post_screen( $post_type = null )
	{
		if ( ! is_admin() || ! function_exists( 'get_current_screen' ) )
		{
			return false;
		}

		$screen = get_current_screen();

		if ( ! $screen->base == 'post' )
		{
			return false;
		}
			
		if ( $post_type && $screen->id != $post_type )
		{
			return false;
		}

		return true;
	}

	static public function get_screen_post_id()
	{
		if ( self::is_post_screen() && ! empty( $_GET['post'] ) )
		{
			return $_GET['post'];
		}

		return false;
	}

	static public function get_post_by_name( $post_name, $post_type = 'post', $output = OBJECT )
	{
		global $wpdb;

        $post = $wpdb->get_var( $wpdb->prepare( "SELECT ID FROM $wpdb->posts WHERE post_name=%s AND post_type=%s;", $post_name, $post_type ) );
       
        if ( $post )
        {
        	return get_post( $post, $output );
        }

    	return null;
	}

	// http://bavotasan.com/2011/convert-hex-color-to-rgb-using-php/
	static public function hex_to_rgb($hex)
	{
	   $hex = str_replace("#", "", $hex);

	   if(strlen($hex) == 3) {
	      $r = hexdec(substr($hex,0,1).substr($hex,0,1));
	      $g = hexdec(substr($hex,1,1).substr($hex,1,1));
	      $b = hexdec(substr($hex,2,1).substr($hex,2,1));
	   } else {
	      $r = hexdec(substr($hex,0,2));
	      $g = hexdec(substr($hex,2,2));
	      $b = hexdec(substr($hex,4,2));
	   }
	   $rgb = array($r, $g, $b);
	  
	   return $rgb;
	}

	static public function removep( $str )
	{
		if ( stripos( $str, '</p>' ) === false )
		{
			return $str;
		}

		$opening_shortcode_tag = '\[mmtl-\w+(?:\s+(?:\w+="[^"]*"))*\]';
		$closing_shortcode_tag = '\[\/mmtl-\w+\]';

		$opening_p_tag = '<p(?:\s+(?:\w+="[^"]*"))*>';
		$closing_p_tag = '<\/p>';

		// removes paragraphs around tl shortcodes
		$str = preg_replace( "/" . $opening_p_tag . "\s*(($opening_shortcode_tag)|($closing_shortcode_tag))/si" , '$1', $str ); // <p>[mmtl-row] <p>[/mmtl-row]
		$str = preg_replace( "/(($opening_shortcode_tag)|($closing_shortcode_tag))\s*" . $closing_p_tag . "/si" , '$1', $str ); // [mmtl-row]</p> [/mmtl-row]</p>

		// replaces paragraphs with newlines
		$str = preg_replace( "/$closing_p_tag\s*$opening_p_tag/si", "\n\n", $str ); // </p><p> => (2 lines)
		$str = preg_replace( "/(<\/[a-z0-9]+>\s*)$opening_p_tag/si", "$1\n\n", $str ); // ><p> => >(2 lines)
		$str = preg_replace( "/($closing_p_tag|$opening_p_tag)/si", "\n\n", $str );

		// replaces breaks
		$str = preg_replace( '/<br\s*\/?>/si', "\n", $str ); // <br> => (1 line)

		return $str;
	}

	static public function write_to_file( $file, $data, $append = false )
	{
		if ( $append )
		{
			$mode = 'w+';
		}

		else
		{
			$mode = 'w';
		}

		$handle = fopen( $file, $mode );

		if ( ! $handle )
		{
			return new WP_Error( __( 'Cannot open file for writing', 'table-layout' ) );
		}

		if ( fwrite( $handle, $data ) === false )
		{
			return new WP_Error( __( 'Cannot write to file', 'table-layout' ) );
		}

		fclose( $handle );

		return true;
	}

	// https://codex.wordpress.org/Plugin_API/Action_Reference/admin_notices
	static public function notice( $message = '', $args = '' )
	{
		$args = wp_parse_args( $args, array
		(
			'type' => 'info',
			'class' => '',
			'dismissable' => false
		));

		extract( $args );

		if ( $class )
		{
			$class = ' ' . $class;
		}

		if ( $type )
		{
			$class .= ' notice-' . $type;
		}

		if ( $dismissable )
		{
			$class .= ' is-dismissible';
		}

		printf( '<div class="mmtl-notice notice%s"><p>%s</p></div>', esc_attr( $class ), $message );
	}

	static public function ajax_loader()
	{
		return '<span class="mmtl-loader"><span class="mmtl-spin dashicons dashicons-image-rotate"></span></span>';
	}

	static public function parse_html_attributes( $attributes, $extra = '' )
	{
		$extra = trim( $extra );
	 
		$str = '';
	 
		foreach ( $attributes as $key => $value )
		{
			if ( (string) $value === '' )
			{
				continue;
			}
	 
			$str .= sprintf( ' %s="%s"', esc_attr( $key ), esc_attr( $value ) );
		}
	 
		if ( $extra )
		{
			$str .= ' ' . $extra;
		}
	 
		return $str;
	}
	
	static public function load_template( $path, $data = array(), $return = false )
	{
		if ( $return )
		{
			ob_start();
		}

		if ( is_array( $data ) )
		{
			extract( $data );
		}

		require_once plugin_dir_path( MMTL_FILE ) . 'templates/' . $path . '.php';

		if ( $return )
		{
			return ob_get_clean();
		}
	}

	static public function get_attachment_sizes( $attachment_id, $abs = false )
	{
		$data = wp_get_attachment_metadata( $attachment_id );

		if ( ! $data )
		{
			return false;
		}
		
		$sizes = $data['sizes'];

		// adds original size

		$sizes['full'] = array
		(
			'file'   => basename( $data['file'] ),
			'width'  => $data['width'],
			'height' => $data['height']
		);

		// sets dir

		$upload_dir = wp_upload_dir();

		$base = trailingslashit( dirname( $data['file'] ) );

		if ( $abs == 'path' )
		{
			$base = trailingslashit( $upload_dir['basedir'] ) . $base;
		}

		else if ( $abs == 'url' )
		{
			$base = trailingslashit( $upload_dir['baseurl'] ) . $base;
		}

		foreach ( $sizes as &$size )
		{
			$size['file'] = $base . ltrim( $size['file'], '/' );
		}

		return $sizes;
	}

	static public function get_attachment_id_by_url( $url )
	{
		// removes size suffix

		$guid = preg_replace( '/-\d+x\d+(\.[a-z0-9]+)$/i', '$1', $url );

		// gets attachment id

		global $wpdb;

		$attachment = $wpdb->get_row( sprintf( 'SELECT ID FROM %sposts WHERE guid="%s"', esc_sql( $wpdb->prefix ), esc_sql( $guid ) ) );

		if ( $attachment )
		{
			return $attachment->ID;
		}

		return false;
	}

	static public function html_class_to_array( $class )
	{
		$class = trim( preg_replace( '/\s+/' , ' ', $class ) );

		if ( $class )
		{
			$classes = explode( ' ', $class );
		}

		else
		{
			$classes = array();
		}

		return $classes;
	}

	static public function is_shortcode_used( $tag )
	{
		global $wp_query;

	    $posts   = $wp_query->posts;
	    $pattern = get_shortcode_regex();
	    
	    if ( is_array( $posts ) )
	   	{
	    	foreach ( $posts as $post )
		    {
		    	$pattern = '\[' . preg_quote( $tag ) . '(?:\s+(?:\w+="[^"]*"))*\]';

		    	if ( preg_match_all( '/' . $pattern . '/s', $post->post_content ) )
		    	{
		    		return true;
		    	}

		    	/*
				if ( preg_match_all( '/'. $pattern .'/s', $post->post_content, $matches )
					&& array_key_exists( 2, $matches )
					&& in_array( $tag, $matches[2] ) )
				{
					return true;
				} 
				*/   
		    }
	    }

	    return false;
	}

	static public function get_column_span( $width )
	{
		switch ( $width )
		{
			case '1/12'  : return 1;
			case '1/6'   : return 2;
			case '1/4'   : return 3;
			case '1/3'   : return 4;
			case '5/12'  : return 5;
			case '1/2'   : return 6;
			case '7/12'  : return 7;
			case '2/3'   : return 8;
			case '3/4'   : return 9;
			case '5/6'   : return 10;
			case '11/12' : return 11;
		}

		return 12;
	}
}

?>