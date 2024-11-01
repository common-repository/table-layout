<?php if ( ! defined( 'ABSPATH' ) ) exit; //exits when accessed directly

class MMTL_Types
{
	private static $instance = null;

	protected $types = array();
	protected $data = array();

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
		$this->data = array
		(
			'margin_top'       => array( 'pattern' => '\d+', 'format' => 'margin-top:%dpx;' ),
			'margin_right'     => array( 'pattern' => '\d+', 'format' => 'margin-right:%dpx;' ),
			'margin_bottom'    => array( 'pattern' => '\d+', 'format' => 'margin-bottom:%dpx;' ),
			'margin_left'      => array( 'pattern' => '\d+', 'format' => 'margin-left:%dpx;' ),
			'padding_top'      => array( 'pattern' => '\d+', 'format' => 'padding-top:%dpx;' ),
			'padding_right'    => array( 'pattern' => '\d+', 'format' => 'padding-right:%dpx;' ),
			'padding_bottom'   => array( 'pattern' => '\d+', 'format' => 'padding-bottom:%dpx;' ),
			'padding_left'     => array( 'pattern' => '\d+', 'format' => 'padding-left:%dpx;' ),
			'border_top_width'       => array( 'pattern' => '\d+', 'format' => 'border-top-width:%dpx;' ),
			'border_right_width'     => array( 'pattern' => '\d+', 'format' => 'border-right-width:%dpx;' ),
			'border_bottom_width'    => array( 'pattern' => '\d+', 'format' => 'border-bottom-width:%dpx;' ),
			'border_left_width'      => array( 'pattern' => '\d+', 'format' => 'border-left-width:%dpx;' ),
			'border_top_right_radius'    => array( 'pattern' => '\d+', 'format' => 'border-top-right-radius:%dpx;' ),
			'border_bottom_right_radius' => array( 'pattern' => '\d+', 'format' => 'border-bottom-right-radius:%dpx;' ),
			'border_bottom_left_radius'  => array( 'pattern' => '\d+', 'format' => 'border-bottom-left-radius:%dpx;' ),
			'border_top_left_radius'     => array( 'pattern' => '\d+', 'format' => 'border-top-left-radius:%dpx;' ),
			'border_style'               => array( 'pattern' => '(none|solid|dotted|dashed)', 'format' => 'border-style:%s;' ),
			'border_color'     => array( 'pattern' => '#[a-f0-9]', 'format' => 'border-color:%s;' ),
			'background_color' => array( 'pattern' => '#[a-f0-9]', 'format' => 'background-color:%s;' )
		);

		add_action( 'init', array( $this, 'register_post_type' ) );
		
		add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ) );

		add_action( 'save_post', array( $this, 'save_margin_settings' ) );
		add_action( 'admin_notices', array( $this, 'admin_notices' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
	}

	public function get_type( $post_id )
	{
		if ( is_numeric( $post_id ) )
		{
			$post = get_post( $post_id );
		}
		
		else
		{
			$post = $post_id;
		}

		if ( get_post_type( $post ) != 'mmtl_type' )
		{
			return null;
		}

		return array
		(
			'id'               => $post->ID,
			'title'            => $post->post_title,
			'class'            => $post->post_name,
			'component'        => get_post_meta( $post->ID, 'component', true ),
			'margin_top'       => get_post_meta( $post->ID, 'margin_top', true ),
			'margin_right'     => get_post_meta( $post->ID, 'margin_right', true ),
			'margin_bottom'    => get_post_meta( $post->ID, 'margin_bottom', true ),
			'margin_left'      => get_post_meta( $post->ID, 'margin_left', true ),
			'padding_top'      => get_post_meta( $post->ID, 'padding_top', true ),
			'padding_right'    => get_post_meta( $post->ID, 'padding_right', true ),
			'padding_bottom'   => get_post_meta( $post->ID, 'padding_bottom', true ),
			'padding_left'     => get_post_meta( $post->ID, 'padding_left', true ),
			'border_top_width'    => get_post_meta( $post->ID, 'border_top_width', true ),
			'border_right_width'  => get_post_meta( $post->ID, 'border_right_width', true ),
			'border_bottom_width' => get_post_meta( $post->ID, 'border_bottom_width', true ),
			'border_left_width'   => get_post_meta( $post->ID, 'border_left_width', true ),
			'border_top_right_radius'    => get_post_meta( $post->ID, 'border_top_right_radius', true ),
			'border_bottom_right_radius' => get_post_meta( $post->ID, 'border_bottom_right_radius', true ),
			'border_bottom_left_radius'  => get_post_meta( $post->ID, 'border_bottom_left_radius', true ),
			'border_top_left_radius'     => get_post_meta( $post->ID, 'border_top_left_radius', true ),
			'border_style'      => get_post_meta( $post->ID, 'border_style', true ),
			'border_color'      => get_post_meta( $post->ID, 'border_color', true ),
			'background_color' => get_post_meta( $post->ID, 'background_color', true )
		);
	}

	public function get_types( $args = '' )
	{
		$args = wp_parse_args( $args );

		$args[ 'post_type' ] = 'mmtl_type';

		$posts = get_posts( $args );

		$types = array();

		foreach ( $posts as $post )
		{
			$types[] = $this->get_type( $post->ID );
		}

		return $types;
	}

	public function get_type_class( $type_id )
	{
		$type = $this->get_type( $type_id );

		if ( ! $type )
		{
			return '';
		}

		return sprintf( '%s-type-%s', $type['component'], $type['class'] );
	}

	public function get_type_options( $args = '' )
	{
		$types = $this->get_types( $args );

		$options = array
		(
			'' => __( 'Default' )
		);

		foreach ( $types as $type )
		{
			$options[ $type['id'] ] = $type['title'];
		}

		return $options;
	}

	public function get_type_css( $type_id )
	{
		$valid = $this->validate_type( $type_id );

		if ( is_wp_error( $valid ) )
		{
			return $valid;
		}

		$type = $this->get_type( $type_id );

		$css = sprintf( '/*%s*/.%s{', $type['id'], $this->get_type_class( $type['id'] ) );

		foreach ( $type as $key => $value )
		{
			if ( ! isset( $this->data[ $key ] ) )
			{
				continue;
			}

			if ( $value === '' )
			{
				continue;
			}

			$style = $this->data[ $key ];

			$css .= sprintf( $style['format'], $value );
		}

		$css .= '}';

		return $css;
	}

	public function validate_type( $type_id )
	{
		$type = $this->get_type( $type_id );

		$errors = new WP_Error();

		foreach ( $type as $key => $value )
		{
			if ( ! isset( $this->data[ $key ] ) )
			{
				continue;
			}

			if ( $value === '' )
			{
				continue;
			}

			$style = $this->data[ $key ];

			if ( ! preg_match( '/' . $style['pattern'] . '/', $value ) )
			{
				$readable = str_replace( '_' , ' ', $key );

				$errors->add( $key, sprintf( __( 'Invalid value %s for %s', 'table-layout' ), $value, $readable ) );
				
				continue;
			}
		}

		if ( count( $errors->get_error_codes() ) > 0 )
		{
			return $errors;
		}

		return true;
	}

	public function write_type_css( $type_id )
	{
		$valid = $this->validate_type( $type_id );

		if ( is_wp_error( $valid ) )
		{
			return $valid;
		}

		$file = plugin_dir_path( MMTL_FILE ) . 'css/table-layout-types.css';

		if ( ! file_exists( $file ) )
		{
			return new WP_Error( 'file_exists', sprintf( __( 'file %s does not exist.' ), $file ) );
		}

		$css = file_get_contents( $file );

		if ( $css === false )
		{
			return new WP_Error( 'file_get_contents', sprintf( __( 'Unable to get contents of file %s.' ), $file ) );
		}

		$token = sprintf( '/*%s*/', $type_id );

		$index = -1;

		if ( $css )
		{
			$lines = explode( "\n", $css );

			for ( $i = 0; $i < count( $lines ); $i++ )
			{
				$line = $lines[ $i ];

				if ( strpos( $line, $token ) === 0 )
				{
					$index = $i;

					break;
				}
			}

			if ( $index != -1 )
			{
				array_splice( $lines, $index, 1 );

				$css = implode( "\n", $lines );
			}
		}

		$css .= $this->get_type_css( $type_id ) . "\n";

		$written = MMTL_Common::write_to_file( $file, $css );

		if ( ! $written )
		{
			return new WP_Error( 'file_get_contents', sprintf( __( 'Unable to write to file %s.' ), $file ) );
		}

		return true;
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
			'all_items'          => __( 'All Types', 'table-layout' ),
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
			'show_in_menu'       => true,
			'query_var'          => false,
			'rewrite'            => array( 'slug' => 'mmtl-type' ),
			'capability_type'    => 'post',
			'has_archive'        => false,
			'hierarchical'       => false,
			'menu_position'      => null,
			'supports'           => array( 'title' )
		);

		register_post_type( 'mmtl_type', $args );
	}

	public function add_meta_boxes()
	{
		add_meta_box ( 'mmtl-type-component', __( 'Component', 'table-layout' ), array( $this, 'print_component_settings' ), 'mmtl_type', 'side', 'default', null );
		add_meta_box ( 'mmtl-type-box-model', __( 'Box Model', 'table-layout' ), array( $this, 'print_box_model_settings' ), 'mmtl_type', 'advanced', 'default', null );
		//add_meta_box ( 'mmtl-type-margin', __( 'Margin', 'table-layout' ), array( $this, 'print_margin_settings' ), 'mmtl_type', 'advanced', 'default', null );
		//add_meta_box ( 'mmtl-type-padding', __( 'Padding', 'table-layout' ), array( $this, 'print_padding_settings' ), 'mmtl_type', 'advanced', 'default', null );
		add_meta_box ( 'mmtl-type-border', __( 'Border', 'table-layout' ), array( $this, 'print_border_settings' ), 'mmtl_type', 'advanced', 'default', null );
		add_meta_box ( 'mmtl-type-background', __( 'Background', 'table-layout' ), array( $this, 'print_background_settings' ), 'mmtl_type', 'advanced', 'default', null );
	}

	public function print_component_settings( $post )
	{
		wp_nonce_field( 'types_save', MMTL_NONCE_NAME );

		$type = $this->get_type( $post );

		?>

		<ul>
			<?php foreach ( MMTL_API::get_components() as $component ) : ?>
			<li><label><input type="radio" name="mmtl_type[component]" value="<?php echo esc_attr( $component['id'] ); ?>"<?php checked( $component['id'], $type['component'] ) ?>> <?php echo esc_html( $component['title'] ); ?></label></li>
			<?php endforeach; ?>
		</ul>
		
		<?php
	}

	public function print_box_model_settings( $post )
	{
		wp_nonce_field( 'types_save', MMTL_NONCE_NAME );

		$type = $this->get_type( $post );

		?>

		<div class="mmtl-box-model">

				<ul class="mmtl-box-model-margin" title="<?php esc_attr_e( 'margin', 'table-layout' ); ?>">
					<li class="mmtl-box-model-title"><?php _e( 'margin', 'table-layout' ); ?></li>
					<li class="mmtl-top"><input type="text" name="mmtl_type[margin_top]" value="<?php echo esc_attr( $type['margin_top'] ); ?>" placeholder="-"></li>
					<li class="mmtl-right"><input type="text" name="mmtl_type[margin_right]" value="<?php echo esc_attr( $type['margin_right'] ); ?>" placeholder="-"></li>
					<li class="mmtl-bottom"><input type="text" name="mmtl_type[margin_bottom]" value="<?php echo esc_attr( $type['margin_bottom'] ); ?>" placeholder="-"></li>
					<li class="mmtl-left"><input type="text" name="mmtl_type[margin_left]" value="<?php echo esc_attr( $type['margin_left'] ); ?>" placeholder="-"></li>
				</ul>

				<ul class="mmtl-box-model-border" title="<?php esc_attr_e( 'border', 'table-layout' ); ?>">
					<li class="mmtl-box-model-title"><?php _e( 'border', 'table-layout' ); ?></li>
					<li class="mmtl-top"><input type="text" name="mmtl_type[border_top_width]" value="<?php echo esc_attr( $type['border_top_width'] ); ?>" placeholder="-"></li>
					<li class="mmtl-right"><input type="text" name="mmtl_type[border_right_width]" value="<?php echo esc_attr( $type['border_right_width'] ); ?>" placeholder="-"></li>
					<li class="mmtl-bottom"><input type="text" name="mmtl_type[border_bottom_width]" value="<?php echo esc_attr( $type['border_bottom_width'] ); ?>" placeholder="-"></li>
					<li class="mmtl-left"><input type="text" name="mmtl_type[border_left_width]" value="<?php echo esc_attr( $type['border_left_width'] ); ?>" placeholder="-"></li>
				</ul>

				<ul class="mmtl-box-model-padding" title="<?php esc_attr_e( 'padding', 'table-layout' ); ?>">
					<li class="mmtl-box-model-title"><?php _e( 'padding', 'table-layout' ); ?></li>
					<li class="mmtl-top"><input type="text" name="mmtl_type[padding_top]" value="<?php echo esc_attr( $type['padding_top'] ); ?>" placeholder="-"></li>
					<li class="mmtl-right"><input type="text" name="mmtl_type[padding_right]" value="<?php echo esc_attr( $type['padding_right'] ); ?>" placeholder="-"></li>
					<li class="mmtl-bottom"><input type="text" name="mmtl_type[padding_bottom]" value="<?php echo esc_attr( $type['padding_bottom'] ); ?>" placeholder="-"></li>
					<li class="mmtl-left"><input type="text" name="mmtl_type[padding_left]" value="<?php echo esc_attr( $type['padding_left'] ); ?>" placeholder="-"></li>
				</ul>

				<ul class="mmtl-box-model-inner" title="<?php esc_attr_e( 'inner', 'table-layout' ); ?>">
					<li></li>
				</ul>

			</div><!-- .mmtl-box-model -->

		<?php
	}

	public function print_margin_settings( $post )
	{
		wp_nonce_field( 'types_save', MMTL_NONCE_NAME );

		$type = $this->get_type( $post );

		?>

		<div class="mmtl-field-group">
			<label><?php _e( 'Top', 'table-layout' ); ?></label>
			<input type="number" class="tiny-text" name="mmtl_type[margin_top]" data-min="-99" data-max="99" value="<?php echo esc_attr( $type['margin_top'] ); ?>">
		</div><!-- mmtl-field-group -->

		<div class="mmtl-field-group">
			<label><?php _e( 'Right', 'table-layout' ); ?></label>
			<input type="number" class="tiny-text" name="mmtl_type[margin_right]" data-min="-99" data-max="99" value="<?php echo esc_attr( $type['margin_right'] ); ?>">
		</div><!-- mmtl-field-group -->
		
		<div class="mmtl-field-group">
			<label><?php _e( 'Bottom', 'table-layout' ); ?></label>
			<input type="number" class="tiny-text" name="mmtl_type[margin_bottom]" data-min="-99" data-max="99" value="<?php echo esc_attr( $type['margin_bottom'] ); ?>">
		</div><!-- mmtl-field-group -->
		
		<div class="mmtl-field-group">
			<label><?php _e( 'Left', 'table-layout' ); ?></label>
			<input type="number" class="tiny-text" name="mmtl_type[margin_left]" data-min="-99" data-max="99" value="<?php echo esc_attr( $type['margin_left'] ); ?>">
		</div><!-- mmtl-field-group -->
		
		<?php
	}

	public function print_padding_settings( $post )
	{
		wp_nonce_field( 'types_save', MMTL_NONCE_NAME );

		$type = $this->get_type( $post );

		?>

		<div class="mmtl-field-group">
			<label><?php _e( 'Top', 'table-layout' ); ?></label>
			<input type="number" class="tiny-text" name="mmtl_type[padding_top]" data-min="0" data-max="99" value="<?php echo esc_attr( $type['padding_top'] ); ?>">
		</div><!-- mmtl-field-group -->

		<div class="mmtl-field-group">
			<label><?php _e( 'Right', 'table-layout' ); ?></label>
			<input type="number" class="tiny-text" name="mmtl_type[padding_right]" data-min="0" data-max="99" value="<?php echo esc_attr( $type['padding_right'] ); ?>">
		</div><!-- mmtl-field-group -->
		
		<div class="mmtl-field-group">
			<label><?php _e( 'Bottom', 'table-layout' ); ?></label>
			<input type="number" class="tiny-text" name="mmtl_type[padding_bottom]" data-min="0" data-max="99" value="<?php echo esc_attr( $type['padding_bottom'] ); ?>">
		</div><!-- mmtl-field-group -->
		
		<div class="mmtl-field-group">
			<label><?php _e( 'Left', 'table-layout' ); ?></label>
			<input type="number" class="tiny-text" name="mmtl_type[padding_left]" data-min="0" data-max="99" value="<?php echo esc_attr( $type['padding_left'] ); ?>">
		</div><!-- mmtl-field-group -->

		<?php
	}

	public function print_border_settings( $post )
	{
		wp_nonce_field( 'types_save', MMTL_NONCE_NAME );

		$type = $this->get_type( $post );

		/*
		<div class="mmtl-field-group">
			<label><?php _e( 'Top', 'table-layout' ); ?></label>
			<input type="number" class="tiny-text" name="mmtl_type[border_top_width]" data-min="0" data-max="99" value="<?php echo esc_attr( $type['border_top_width'] ); ?>">
		</div><!-- mmtl-field-group -->

		<div class="mmtl-field-group">
			<label><?php _e( 'Right', 'table-layout' ); ?></label>
			<input type="number" class="tiny-text" name="mmtl_type[border_right_width]" data-min="0" data-max="99" value="<?php echo esc_attr( $type['border_right_width'] ); ?>">
		</div><!-- mmtl-field-group -->
		
		<div class="mmtl-field-group">
			<label><?php _e( 'Bottom', 'table-layout' ); ?></label>
			<input type="number" class="tiny-text" name="mmtl_type[border_bottom_width]" data-min="0" data-max="99" value="<?php echo esc_attr( $type['border_bottom_width'] ); ?>">
		</div><!-- mmtl-field-group -->
		
		<div class="mmtl-field-group">
			<label><?php _e( 'Left', 'table-layout' ); ?></label>
			<input type="number" class="tiny-text" name="mmtl_type[border_left_width]" data-min="0" data-max="99" value="<?php echo esc_attr( $type['border_left_width'] ); ?>">
		</div><!-- mmtl-field-group -->
		*/

		?>

		<div class="mmtl-field-group">
			<label><?php _e( 'Type', 'table-layout' ); ?></label><br>
			<input type="text" class="regular-text" name="mmtl_type[border_style]" value="<?php echo esc_attr( $type['border_style'] ); ?>">
		</div><!-- mmtl-field-group -->

		<div class="mmtl-field-group">
			<label><?php _e( 'Color', 'table-layout' ); ?></label><br>
			<input type="text" class="mmtl-color-picker" name="mmtl_type[border_color]" value="<?php echo esc_attr( $type['border_color'] ); ?>">
		</div><!-- mmtl-field-group -->
		
		<?php
	}

	public function print_background_settings( $post )
	{
		wp_nonce_field( 'types_save', MMTL_NONCE_NAME );

		$type = $this->get_type( $post );

		?>

		<div class="mmtl-field-group">
			<label><?php _e( 'Color', 'table-layout' ); ?></label><br>
			<input type="text" class="mmtl-color-picker" name="mmtl_type[background_color]" value="<?php echo esc_attr( $type['background_color'] ); ?>">
		</div><!-- mmtl-field-group -->
		
		<?php
	}

	public function check( $action, $post_id )
	{
		/*
         * We need to verify this came from the our screen and with proper authorization,
         * because save_post can be triggered at other times.
         */

		$nonce_name = MMTL_NONCE_NAME;
 
        // Check if our nonce is set.
        if ( ! isset( $_POST[ $nonce_name ] ) )
        {
            return false;
        }
 
        $nonce = $_POST[ $nonce_name ];
 
        // Verify that the nonce is valid.
        if ( ! wp_verify_nonce( $nonce, $action ) )
        {
            return false;
        }
 
        /*
         * If this is an autosave, our form has not been submitted,
         * so we don't want to do anything.
         */
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
        {
            return false;
        }
 
        // Check the user's permissions.
        if ( $_POST['post_type'] == 'page' )
        {
            if ( ! current_user_can( 'edit_page', $post_id ) )
            {
                return false;
            }
        }

        else
        {
            if ( ! current_user_can( 'edit_post', $post_id ) )
            {
                return false;
            }
        }
 
        /* OK, it's safe for us to save the data now. */
 	
        if ( ! isset( $_POST['mmtl_type'] ) || ! is_array( $_POST['mmtl_type'] ) )
        {
        	 return false;
        }

        return true;
	}

	public function save_margin_settings( $post_id )
	{
		if ( ! $this->check( 'types_save', $post_id ) )
		{
			return $post_id;
		}

        $input = $_POST['mmtl_type'];

        update_post_meta( $post_id, 'component', $input['component'] );
        update_post_meta( $post_id, 'margin_top', sanitize_text_field( $input['margin_top'] ) );
        update_post_meta( $post_id, 'margin_right', sanitize_text_field( $input['margin_right'] ) );
        update_post_meta( $post_id, 'margin_bottom', sanitize_text_field( $input['margin_bottom'] ) );
        update_post_meta( $post_id, 'margin_left', sanitize_text_field( $input['margin_left'] ) );
        update_post_meta( $post_id, 'padding_top', sanitize_text_field( $input['padding_top'] ) );
        update_post_meta( $post_id, 'padding_right', sanitize_text_field( $input['padding_right'] ) );
        update_post_meta( $post_id, 'padding_bottom', sanitize_text_field( $input['padding_bottom'] ) );
        update_post_meta( $post_id, 'padding_left', sanitize_text_field( $input['padding_left'] ) );
        update_post_meta( $post_id, 'border_top_width', sanitize_text_field( $input['border_top_width'] ) );
        update_post_meta( $post_id, 'border_right_width', sanitize_text_field( $input['border_right_width'] ) );
        update_post_meta( $post_id, 'border_bottom_width', sanitize_text_field( $input['border_bottom_width'] ) );
        update_post_meta( $post_id, 'border_left_width', sanitize_text_field( $input['border_left_width'] ) );

        update_post_meta( $post_id, 'border_top_right_radius', sanitize_text_field( $input['border_top_right_radius'] ) );
        update_post_meta( $post_id, 'border_bottom_right_radius', sanitize_text_field( $input['border_bottom_right_radius'] ) );
        update_post_meta( $post_id, 'border_bottom_left_radius', sanitize_text_field( $input['border_bottom_left_radius'] ) );
        update_post_meta( $post_id, 'border_top_left_radius', sanitize_text_field( $input['border_top_left_radius'] ) );

        update_post_meta( $post_id, 'border_style', sanitize_text_field( $input['border_style'] ) );
        update_post_meta( $post_id, 'border_color', sanitize_text_field( $input['border_color'] ) );
        update_post_meta( $post_id, 'background_color', sanitize_text_field( $input['background_color'] ) );

        $errors = $this->write_type_css( $post_id );

        if ( is_wp_error( $errors ) )
        {
        	$notices = array();

        	foreach ( $errors->get_error_codes() as $code )
        	{
        		$notices[ $code ] = $errors->get_error_message( $code );
        	}

        	update_option( 'mmtl_notices', $notices );
        }
    }

    public function admin_notices()
    {
    	$errors = get_option( 'mmtl_notices' );

    	if ( empty( $errors ) || ! is_array( $errors ) )
    	{
    		return;
    	}

    	delete_option( 'mmtl_notices' );

    	foreach ( $errors as $code => $message )
    	{
    		MMTL_Common::notice( $message, 'type=warning' );
    	}
    }

    public function enqueue_scripts()
	{
		$screen = get_current_screen();

		/*
		if ( $screen->id != $this->page_hook )
		{
			return;
		}
		*/

		//wp_enqueue_style( 'jquery-ui', 'https://code.jquery.com/ui/1.11.4/themes/black-tie/jquery-ui.css' );

		wp_enqueue_style( 'wp-color-picker' );
	    wp_enqueue_script( 'wp-color-picker' );

		wp_enqueue_style( 'table-layout' );

		wp_enqueue_style( 'table-layout-admin' );
		wp_enqueue_script( 'table-layout-admin' );
	}
}

MMTL_Types::get_instance()->init();

?>