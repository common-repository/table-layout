<?php if ( ! defined( 'ABSPATH' ) ) exit; //exits when accessed directly

class MMTL_Settings
{
	protected $id = '';
	protected $parent_slug = null;
	protected $page_title  = '';
	protected $menu_title  = '';
	protected $capability  = '';
	protected $menu_slug   = '';
	protected $option_name = '';
	protected $page_hook = '';

	public function __construct( $id, $page_title, $args = null )
	{
		$args = wp_parse_args( $args, array
		(
			'parent_slug' => 'options-general.php',
			'page_title'  => $page_title,
			'menu_title'  => $page_title,
			'capability'  => 'manage_options',
			'menu_slug'   => $id,
			'option_name' => $id
		));
		
		extract( $args );

		$this->id = $id;
		$this->parent_slug = $parent_slug;
		$this->page_title  = $page_title;
		$this->menu_title  = $menu_title;
		$this->capability  = $capability;
		$this->menu_slug   = $menu_slug;
		$this->option_name = $option_name;

		add_action( 'admin_init', array( $this, 'register_settings' ) );
		add_action( 'admin_menu', array( $this, 'register_settings_page' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'settings_page_scripts' ) );

		register_deactivation_hook( MMTL_FILE, array( $this, 'unregister_settings' ) );
	}

	public function get_option( $name = null, $default = false )
	{
		$options = get_option( $this->option_name, $this->get_default_options() );

		if ( ! $name )
		{
			return $options;
		}

		if ( isset( $options[ $name ] ) )
		{
			return $options[ $name ];
		}

		return $default;
	}

	public function get_default_options()
	{
		return array();
	}

	public function get_field_id( $field_id )
	{
		return esc_attr( sprintf( '%s-%s', $this->id, $field_id ) );
	}

	public function get_field_name( $field_id )
	{
		return esc_attr( sprintf( '%s[%s]', $this->option_name, $field_id ) );
	}

	public function get_field_value( $field_id, $default = '' )
	{
		return esc_attr( $this->get_option( $field_id, $default ) );
	}

	public function register_settings()
	{
		register_setting( $this->id, $this->option_name, array( $this, 'sanitize_options' ) );
	}

	public function unregister_settings()
	{
		unregister_setting( $this->id, $this->option_name, array( $this, 'sanitize_options' ) );
	}

	public function register_settings_page()
	{
		$this->page_hook = add_submenu_page( $this->parent_slug, $this->page_title, $this->menu_title, $this->capability, $this->menu_slug, array( $this, 'print_settings_page' ) );
	}

	public function print_settings_page()
	{
		?>

		<div class="wrap">

			<h1><?php echo esc_html( $this->page_title ); ?></h1>

			<form action="options.php" method="post">

				<?php settings_fields( $this->id ); ?>
				<?php do_settings_sections( $this->id ); ?>

				<?php submit_button( __( 'Update', 'table-layout' ) ); ?>

			</form>

		</div><!--- .wrap -->

		<?php
	}

	public function sanitize_options( $input )
	{
		return $input;
	}

	public function settings_page_scripts()
	{
		$screen = get_current_screen();

		if ( $screen->id != $this->page_hook )
		{
			return;
		}
	}
}

?>