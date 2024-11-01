<?php if ( ! defined( 'ABSPATH' ) ) exit; //exits when accessed directly

class MMTL_Components
{
	private static $instance = null;

	static public function get_instance()
	{
		if ( ! self::$instance )
		{
			self::$instance = new self();
		}

		return self::$instance;
	}

	public function __construct()
	{
		
	}

	public function init()
	{
		add_filter( 'the_content', array( $this, 'the_content' ), 5 );
		add_filter( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ), 15 );

		add_filter( 'mmtl_component', array( $this, 'add_common_controls' ), 1, 15 );
	}

	public function add_common_controls( $component )
	{
		if ( ! in_array( 'edit', $component['controls'] ) )
		{
			$component['controls'][] = 'edit';
		}

		if ( ! in_array( 'copy', $component['controls'] ) )
		{
			$component['controls'][] = 'copy';
		}

		if ( ! in_array( 'delete', $component['controls'] ) )
		{
			$component['controls'][] = 'delete';
		}

		return $component;
	}

	public function enqueue_scripts()
	{
		if ( ! MMTL_Common::is_shortcode_used( 'mmtl-row' ) )
		{
			return;
		}

		wp_enqueue_style( 'table-layout' );
		wp_enqueue_style( 'table-layout-types' );
	}

	public function the_content( $the_content )
 	{
 		if ( has_shortcode( $the_content, 'mmtl-row' ) )
 		{
 			$the_content = sprintf( '<div class="mmtl-wrap">%s</div>', $the_content );
 		}

 		return $the_content;
 	}
}

MMTL_Components::get_instance()->init();

?>