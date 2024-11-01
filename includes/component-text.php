<?php if ( ! defined( 'ABSPATH' ) ) exit; //exits when accessed directly

class MMTL_Component_HTML
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
		add_filter( 'mmtl_editor_components', array( $this, 'register_component' ) );
		add_filter( 'mmtl_editor_column_accepts', array( $this, 'mmtl_editor_column_accepts' ) );

		add_action( 'admin_footer', array( $this, 'admin_print_scripts' ) );
		add_action( 'admin_init', array( $this, 'register_component_settings' ) );

		add_shortcode( 'mmtl-text', array( $this, 'parse' ) );
	}

	public function mmtl_editor_column_accepts( $accepts )
	{
		$accepts['mmtl-text'] = true;

		return $accepts;
	}

	public function register_component( $components )
	{
		$components[ 'mmtl-text' ] = array
		(
			'title'       => __( 'Text block', 'table-layout' ),
			'description' => __( '', 'table-layout' ),
			'icon' => '<span class="glyphicons glyphicons-text-resize"></span>'
		);

		return $components;
	}

	public function register_component_settings()
	{
		MMTL_API::add_settings_page( 'mmtl-text', __( 'Text Block Settings', 'table-layout' ), array( $this, 'print_settings_page' ) );

		MMTL_API::add_settings_section( 'general', __( 'Content', 'table-layout' ), '', 'mmtl-text' );

		MMTL_API::register_setting( 'mmtl-text', 'content', array( $this, 'add_newlines' ) );
		MMTL_API::add_settings_field( 'content', '', array( 'MMTL_Form', 'editor' ), 'mmtl-text', 'general', array
		(
			'name'  => 'content',
			'value' => ''
		));

		// Attributes

		MMTL_API::add_settings_section( 'attributes', __( 'Attributes', 'table-layout' ), '', 'mmtl-text' );

		MMTL_API::register_setting( 'mmtl-text', 'id', 'trim|sanitize_title' );
		MMTL_API::add_settings_field( 'id', __( 'ID' ), array( 'MMTL_Form', 'textfield' ), 'mmtl-text', 'attributes', array
		(
			'id'        => 'mmtl-id',
			'label_for' => 'mmtl-id',
			'class'     => '',
			'name'      => 'id',
			'value'     => ''
		));

		MMTL_API::register_setting( 'mmtl-text', 'class' );
		MMTL_API::add_settings_field( 'class', __( 'Class', 'table-layout' ), array( 'MMTL_Form', 'textfield' ), 'mmtl-text', 'attributes', array
		(
			'id'        => 'mmtl-class',
			'label_for' => 'mmtl-class',
			'class'     => '',
			'name'      => 'class',
			'value'     => ''
		));
	}

	public function add_newlines( $content )
	{
		return "\n\n" . $content . "\n\n";
	}

	public function parse( $atts, $content = '' )
	{
		extract( shortcode_atts( array
		(
			'id'    => '',
			'class' => '',
			'type'  => ''
		), $atts ) );

		$class .= ' mmtl-text';

		if ( $type )
		{
			$class .= ' ' . MMTL_API::get_component_type_class( $type );
		}

		$str = sprintf( '<div%s>', MMTL_Common::parse_html_attributes( array
		(
			'id'    => $id,
			'class' => trim( $class )
		)));

		$str .= do_shortcode( $content );

		$str .= '</div>';

		return $str;
	}

	public function admin_print_scripts()
	{
		if ( ! MMTL_API::is_editor_screen() )
		{
			return;
		}

		$component = MMTL_API::get_component( 'mmtl-text' );

		?>

		<script type="text/html" id="tmpl-mmtl-shortcode-html">
			
			<div class="mmtl-preview">
				
				<div class="mmtl-preview-header">
					<div class="mmtl-preview-icon"><?php echo $component['icon']; ?></div>
					<div class="mmtl-preview-title"><?php echo $component['title']; ?></div>
					<ul class="mmtl-preview-meta">
						<# if ( data.id ) { #><li><?php _e( 'id:', 'table-layout' ); ?> {{ data.id }}</li><# } #>
						<# if ( data.class ) { #><li><?php _e( 'class:', 'table-layout' ); ?> {{ data.class }}</li><# } #>
						<# if ( data.type ) { #><li><?php _e( 'type:', 'table-layout' ); ?> {{ data.type }}</li><# } #>
					</ul>
				</div>

				<# if ( data.content ) { #>
				<div class="mmtl-preview-content mmtl-editor-style">
					{{{ data.content }}}
				</div>
				<# } #>

			</div>

		</script>

		<script type="text/html">
			<?php wp_editor( '', 'mmtl_content', array( 'textarea_name' => 'content', 'wpautop' => true ) ); ?>
		</script>

		<?php
	}

	public function print_settings_page()
	{
		?>

		<form method="post">

			<?php MMTL_API::settings_fields( 'mmtl-text' ); ?>

			<?php MMTL_API::do_settings_sections( 'mmtl-text' ); ?>

			<?php submit_button( __( 'Update', 'table-layout' ) ); ?>

		</form>

		<?php
	}
}

MMTL_Component_HTML::get_instance()->init();

?>