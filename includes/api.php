<?php if ( ! defined( 'ABSPATH' ) ) exit; //exits when accessed directly

class MMTL_API
{
	static public function get_option( $name = null, $default = false )
	{
		$settings = MMTL_Settings::get_instance();

		return $settings->get_option( $name, $default );
	}

	static public function get_components()
	{
		$editor = MMTL_Editor::get_instance();

		return $editor->get_components();
	}

	static public function get_component( $component_id )
	{
		$editor = MMTL_Editor::get_instance();

		return $editor->get_component( $component_id );
	}

	static public function get_component_type_class( $type_name )
	{
		return MMTL_Post_Type_Component_Type::get_instance()->get_class( $type_name );
	}

	static public function is_editor_screen()
	{
		$editor = MMTL_Editor::get_instance();

		return $editor->is_editor_screen();
	}

	static public function is_editor_active( $post_id = 0 )
	{
		$editor = MMTL_Editor::get_instance();

		return $editor->is_editor_active( $post_id );
	}

	static public function is_table_layout()
	{
		return MMTL_Common::is_shortcode_used( 'mmtl-row' );
	}

	static public function is_debug_active()
	{
		$debugger = MMTL_Debug::get_instance();

		return $debugger->is_active();
	}

	static public function register_setting( $option_group, $option_name, $sanitize_callback = null )
	{
		$settings = MMTL_Component_Settings::get_instance();

		$settings->register_setting( $option_group, $option_name, $sanitize_callback );
	}

	static public function add_settings_page( $id, $title, $callback )
	{
		$settings = MMTL_Component_Settings::get_instance();

		$settings->add_settings_page( $id, $title, $callback );
	}

	static public function add_settings_section( $id, $title, $description, $page )
	{
		$settings = MMTL_Component_Settings::get_instance();

		$settings->add_settings_section( $id, $title, $description, $page );
	}

	static public function add_settings_field( $id, $title, $callback, $page, $section = 'default', $args = null )
	{
		$settings = MMTL_Component_Settings::get_instance();

		$settings->add_settings_field( $id, $title, $callback, $page, $section, $args );
	}

	static public function do_settings_sections( $page )
	{
		$settings = MMTL_Component_Settings::get_instance();

		$settings->do_settings_sections( $page );
	}

	static public function do_settings_fields( $page, $section )
	{
		$settings = MMTL_Component_Settings::get_instance();

		$settings->do_settings_fields( $page, $section );
	}

	static public function settings_fields( $page )
	{
		$settings = MMTL_Component_Settings::get_instance();

		return $settings->settings_fields( $page );
	}

	static public function sanitize_options( $input, $page = null )
	{
		$settings = MMTL_Component_Settings::get_instance();

		return $settings->sanitize_options( $input, $page );
	}

	static public function log( $message )
	{
		$debugger = MMTL_Debug::get_instance();

		return $debugger->log( $message );
	}
}

?>