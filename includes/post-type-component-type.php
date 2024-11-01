<?php if ( ! defined( 'ABSPATH' ) ) exit; //exits when accessed directly

define( 'MMTL_COMPONENT_TYPES_STYLESHEET', plugin_dir_path( MMTL_FILE ) . 'css/table-layout-types.css' );

class MMTL_Post_Type_Component_Type
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
		add_action( 'init', array( $this, 'register_post_type' ) );
		add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ), 5 );
		add_action( 'save_post', array( $this, 'save_post' ), 15 );
		add_action( 'admin_notices', array( $this, 'admin_notices' ) );
	}

	public function init()
	{
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
	}

	public function get_class( $type )
	{
		// post name

		if ( is_string( $type ) )
		{
			$type = MMTL_Common::get_post_by_name( $type, 'mmtl_component_type' );
		}

		// ID

		else if ( is_numeric( $type ) )
		{
			$type = get_post( $type );
		}

		if ( ! $type || get_post_type( $type ) !== 'mmtl_component_type' )
		{
			return false;
		}

		$component_id = get_post_meta( $type->ID, 'component', true );

		if ( ! $component_id )
		{
			return false;
		}

		return sprintf( '%s-type-%s', $component_id, $type->post_name );
	}

	public function register_post_type()
	{
		$labels = array
		(
			'name'               => _x( 'Types', 'post type general name', 'table-layout' ),
			'singular_name'      => _x( 'Type', 'post type singular name', 'table-layout' ),
			'menu_name'          => _x( 'Types', 'admin menu', 'table-layout' ),
			'name_admin_bar'     => _x( 'Type', 'add new on admin bar', 'table-layout' ),
			'add_new'            => _x( 'Add New', 'type', 'table-layout' ),
			'add_new_item'       => __( 'Add New Type', 'table-layout' ),
			'new_item'           => __( 'New Type', 'table-layout' ),
			'edit_item'          => __( 'Edit Type', 'table-layout' ),
			'view_item'          => __( 'View Type', 'table-layout' ),
			'all_items'          => __( 'Table Layout Types', 'table-layout' ),
			'search_items'       => __( 'Search Types', 'table-layout' ),
			'parent_item_colon'  => __( 'Parent Types:', 'table-layout' ),
			'not_found'          => __( 'No types found.', 'table-layout' ),
			'not_found_in_trash' => __( 'No types found in Trash.', 'table-layout' )
		);

		$args = array
		(
			'labels'             => $labels,
	        'description'        => __( 'Description.', 'table-layout' ),
			'public'             => false,
			'publicly_queryable' => false,
			'show_ui'            => true,
			'show_in_menu'       => 'themes.php',
			'query_var'          => false,
			'rewrite'            => array( 'slug' => 'mmtl-component-type' ),
			'capability_type'    => 'post',
			'has_archive'        => false,
			'hierarchical'       => false,
			'menu_position'      => null,
			'supports'           => array( 'title' )
		);

		register_post_type( 'mmtl_component_type', $args );
	}

	public function add_meta_boxes()
	{
		$component_id = get_post_meta( MMTL_Common::get_screen_post_id(), 'component', true );

		add_meta_box( 'mmtl-component-type-component', __( 'Component', 'table-layout' ), array( $this, 'print_component_settings' ), 'mmtl_component_type', 'side', 'default', null );
	}

	public function print_component_settings( $post )
	{
		wp_nonce_field( 'mmtl_component_type_save', MMTL_NONCE_NAME );

		$components = MMTL_API::get_components();

		$selected_component_id = get_post_meta( $post->ID, 'component', true );

		if ( $selected_component_id )
		{
			$component = MMTL_API::get_component( $selected_component_id );

			printf( '<p>%s</p><input type="hidden" name="mmtl_component" value="%s">', esc_html( $component['title'] ), esc_attr( $component['id'] ) );

			return;
		}

		?>

		<p><?php _e( 'Select a component and click the "publish" button to show the settings.' ) ?></p>

		<ul>
			<?php foreach ( $components as $component ) : ?>
			<li><label><input type="radio" name="mmtl_component" value="<?php echo esc_attr( $component['id'] ); ?>"<?php checked( $component['id'], $selected_component_id ); ?>> <?php echo esc_html( $component['title'] ); ?></label></li>
			<?php endforeach; ?>
		</ul>

		<?php
	}


	public function save_post( $post_id )
	{
		 /*
         * We need to verify this came from the our screen and with proper authorization,
         * because save_post can be triggered at other times.
         */
 
        // Check if our nonce is set.

        if ( ! isset( $_POST[ MMTL_NONCE_NAME ] ) )
        {
            return $post_id;
        }
 
        $nonce = $_POST[ MMTL_NONCE_NAME ];
 		 
        // Verify that the nonce is valid.

        if ( ! wp_verify_nonce( $nonce, 'mmtl_component_type_save' ) )
        {
            return $post_id;
        }

        /*
         * If this is an autosave, our form has not been submitted,
         * so we don't want to do anything.
         */

        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
        {
            return $post_id;
        }
 
        // Check the user's permissions.

        if ( $_POST['post_type'] == 'page' )
        {
            if ( ! current_user_can( 'edit_page', $post_id ) )
            {
                return $post_id;
            }
        }

        else
        {
            if ( ! current_user_can( 'edit_post', $post_id ) )
            {
                return $post_id;
            }
        }
 
        /* OK, it's safe for us to save the data now. */

		// component

		if ( isset( $_POST['mmtl_component'] ) )
		{
			update_post_meta( $post_id, 'component', $_POST['mmtl_component'] );
		}

		// writes css

		$component_id = get_post_meta( $post_id, 'component', true ); 

		if ( $component_id )
		{
			$result = $this->write_css( $post_id );
		}
	}

	public function write_css( $type_id )
	{
		$selector = $this->get_class( $type_id );

		if ( ! $selector )
		{
			return false;
		}

		$component_id = get_post_meta( $type_id, 'component', true );

		$selector = '.' . $selector;

		// writes styles to css file

		$css = apply_filters( 'mmtl_component_type_css', array(), $type_id, $component_id, $selector );

		$all_css = MMTL_CSS::get_css_from_file( MMTL_COMPONENT_TYPES_STYLESHEET );

		if ( ! is_array( $css ) )
		{
			$css = array();
		}

		$css = array_merge( $all_css, $css );

		$written = MMTL_CSS::write_css( $css, MMTL_COMPONENT_TYPES_STYLESHEET );
	}

	public function enqueue_scripts( $post )
	{
		if ( ! MMTL_Common::is_post_screen( 'mmtl_component_type' ) )
		{
			return;
		}

		wp_enqueue_style( 'table-layout' );
		wp_enqueue_style( 'table-layout-admin' );
		wp_enqueue_script( 'table-layout-admin' );
	}

	public function admin_notices()
	{
		if ( ! MMTL_Common::is_post_screen( 'mmtl_component_type' ) )
		{
			return;
		}

		if ( file_exists( MMTL_COMPONENT_TYPES_STYLESHEET ) )
		{
			$suffix = __( 'Contact the hosting provider to solve the issue.' );

			if ( ! is_readable( MMTL_COMPONENT_TYPES_STYLESHEET ) )
			{
				MMTL_Common::notice( sprintf( 'stylesheet <code>%s</code> is not readable. %s', MMTL_COMPONENT_TYPES_STYLESHEET, $suffix ), 'type=error' );
			}

			if ( ! is_writable( MMTL_COMPONENT_TYPES_STYLESHEET ) )
			{
				MMTL_Common::notice( sprintf( 'stylesheet <code>%s</code> is not writable. %s', MMTL_COMPONENT_TYPES_STYLESHEET, $suffix ), 'type=error' );
			}
		}

		else
		{
			MMTL_Common::notice( sprintf( 'stylesheet <code>%s</code> does not exist.', MMTL_COMPONENT_TYPES_STYLESHEET ), 'type=error' );
		}
	}
}

MMTL_Post_Type_Component_Type::get_instance()->init();

?>