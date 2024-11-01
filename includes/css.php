<?php if ( ! defined( 'ABSPATH' ) ) exit; //exits when accessed directly

class MMTL_CSS
{
	static public function get_css_from_file( $file )
	{
		$contents = file_get_contents( $file );

		// checks if contents could be fetched

		if ( $contents === false )
		{
			return false;
		}

		return self::css_to_array( $contents );
	}

	static public function add_declaration( $property, $value, &$declarations )
	{
		$valid = self::is_valid_declaration( $property, $value );

		MMTL_API::log( sprintf( 'MMTL_CSS::add_declaration() %s', var_export( array
		(
			'property' => $property,
			'value'    => $value,
			'valid'    => $valid
		), true ) ) );

		if ( $valid )
		{
			$declarations[ $property ] = $value;

			return true;
		}

		return false;
	}

	static public function sanitize_selector( $selector )
	{
		// removes unneeded whitespace characters from selector

		return preg_replace( '/\s*(\s|>|:)\s*/', '$1', trim( $selector ) ); 
	}

	static public function is_valid_declaration( $property, $value )
	{
		$valid = true;

		switch ( $property )
		{
			case 'width':
			case 'height':
			case 'font-size':

			case 'margin-top':
			case 'margin-right':
			case 'margin-bottom':
			case 'margin-left':

			case 'padding-top':
			case 'padding-right':
			case 'padding-bottom':
			case 'padding-left':

			case 'border-top-width':
			case 'border-right-width':
			case 'border-bottom-width':
			case 'border-left-width':

			case '-webkit-border-top-right-radius':
			case '-webkit-border-bottom-right-radius':
			case '-webkit-border-bottom-left-radius':
			case '-webkit-border-top-left-radius':

			case '-moz-border-topright-radius':
			case '-moz-border-bottomright-radius':
			case '-moz-border-bottomleft-radius':
			case '-moz-border-topleft-radius':

			case 'border-top-right-radius':
			case 'border-bottom-right-radius':
			case 'border-bottom-left-radius':
			case 'border-top-left-radius':

				$valid = preg_match( '/^(auto|\d+(px|em|%)|initial|inherit)$/', $value ) ? true : false;

				break;

			case 'line-height':

				$valid = preg_match( '/^(normal|\d+(px|em|%)?|initial|inherit)$/', $value ) ? true : false;

				break;

			case 'max-width' :
			case 'max-height' :

				$valid = preg_match( '/^(none|\d+(px|em|%)|initial|inherit)$/', $value ) ? true : false;

				break;

			case 'min-width' :
			case 'min-height' :

				$valid = preg_match( '/^(\d+(px|em|%)|initial|inherit)$/', $value ) ? true : false;

				break;

			case 'border-top-style' :
			case 'border-right-style' :
			case 'border-bottom-style' :
			case 'border-left-style' :

				$valid = preg_match( '/^(none|hidden|dotted|dashed|solid|double|groove|ridge|inset|outset|initial|inherit)$/', $value ) ? true : false;

				break;

			case 'color':
			case 'background-color' :
			case 'border-top-color' :
			case 'border-right-color' :
			case 'border-bottom-color' :
			case 'border-left-color' :
			
				$valid = preg_match( '/^(#[a-f0-9]{3,6}|transparent|initial|inherit)$/', $value ) ? true : false;

				break;
		}

		return apply_filters( 'mmtl_css_validate_declaration', $valid, $property, $value );
	}

	static public function css_to_string( $selector, $declarations = array() )
	{
		// recursive

		if ( is_array( $selector ) )
		{
			$css = '';

			foreach ( $selector as $key => $value )
			{
				$css .= self::css_to_string( $key, $value );
			}

			return $css;
		}

		$selector = self::sanitize_selector( $selector );

		if ( ! $selector )
		{
			return '';
		}

		$declarations = self::declarations_to_string( $declarations );

		if ( empty( $declarations ) )
		{
			return '';
		}

		$css = sprintf( '%s{%s}', $selector, $declarations );

		return $css;
	}

	static public function css_to_array( $css_string )
	{
		$pattern =  '('
			   .	'(?:'
			   .		'(?:\.[a-z-]+)'
			   .		'(?:'
			   .			'(?:\s*)'
			   .			'|(?:\s*>\s*)'
			   .		')?'
			   . 	')+'
			   . ')'
			   . '\s*\{'
			   . '(.+?)'
			   . '\}';

		preg_match_all( '/' . $pattern . '/s', $css_string, $matches, PREG_SET_ORDER );

		$css_array = array();

		foreach ( $matches as $match )
		{
			list( , $selector, $declarations ) = $match;

			// sanitizes selector

			$selector = self::sanitize_selector( $selector );

			// parses declarations

			$declarations = self::declarations_to_array( $declarations );

			// merges declarations if selector is already added

			if ( isset( $css_array[ $selector ] ) )
			{
				$css_array[ $selector ] = array_merge( $css_array[ $selector ]['declarations'], $declarations );

				continue;
			}

			$css_array[ $selector ] = $declarations;
		}

		return $css_array;
	}

	static public function declarations_to_string( $declarations )
	{
		$str = '';

		foreach ( $declarations as $property => $value )
		{
			$value = trim( $value );

			$valid = self::is_valid_declaration( $property, $value );

			if ( ! $valid )
			{
				continue;
			}

			$str .= sprintf( '%s:%s;', $property, $value );
		}

		return $str;
	}

	static public function declarations_to_array( $declarations )
	{
		preg_match_all( '/\s*([a-z-]+)\s*:\s*(.+?)\s*;/s', $declarations, $matches, PREG_SET_ORDER );

		$array = array();

		foreach ( $matches as $data )
		{
			list( , $property, $value ) = $data;

			$array[ $property ] = $value;
		}

		return $array;
	}

	static public function write_css( $css_array, $file )
	{
		MMTL_API::log( 'write_css: ' . var_export( $css_array, true ) );

		if ( ! file_exists( $file ) )
		{
			return new WP_Error( 'file_exists', __( 'file does not exist.', 'table-layout' ) );
		}

		$handle = fopen( $file, 'w' );

		if ( ! $handle )
		{
			return new WP_Error( __( 'Cannot open file for writing', 'table-layout' ) );
		}

		$css = self::css_to_string( $css_array );

		if ( fwrite( $handle, $css ) === false )
		{
			return new WP_Error( __( 'Cannot write to file', 'table-layout' ) );
		}

		fclose( $handle );

		return true;
	}
}

?>