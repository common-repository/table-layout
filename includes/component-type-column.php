<?php if ( ! defined( 'ABSPATH' ) ) exit; //exits when accessed directly

class MMTL_Component_Type_Column
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
		add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ) );
		add_action( 'save_post', array( $this, 'save_post' ) );

		add_filter( 'mmtl_component_type_css', array( $this, 'css' ), 5, 4 );
	}

	public function add_meta_boxes()
	{
		// checks if component is set and is our component

		if ( ! MMTL_Common::is_post_screen( 'mmtl_component_type' ) )
		{
			return;
		}

		$post_id = MMTL_Common::get_screen_post_id();

		$component_id = get_post_meta( $post_id, 'component', true );

		if ( ! $component_id || $component_id != 'mmtl-col' )
		{
			return;
		}

		add_meta_box( 'mmtl-component-type-col', __( 'Settings', 'table-layout' ), array( $this, 'print_meta_box_inner' ), 'mmtl_component_type' );
	}

	public function print_meta_box_inner( $post )
	{
		$margin_top    = get_post_meta( $post->ID, 'margin_top', true );
		$margin_right  = get_post_meta( $post->ID, 'margin_right', true );
		$margin_bottom = get_post_meta( $post->ID, 'margin_bottom', true );
		$margin_left   = get_post_meta( $post->ID, 'margin_left', true );

		$border_top_width    = get_post_meta( $post->ID, 'border_top_width', true );
		$border_right_width  = get_post_meta( $post->ID, 'border_right_width', true );
		$border_bottom_width = get_post_meta( $post->ID, 'border_bottom_width', true );
		$border_left_width   = get_post_meta( $post->ID, 'border_left_width', true );

		$border_top_style    = get_post_meta( $post->ID, 'border_top_style', true );
		$border_right_style  = get_post_meta( $post->ID, 'border_right_style', true );
		$border_bottom_style = get_post_meta( $post->ID, 'border_bottom_style', true );
		$border_left_style   = get_post_meta( $post->ID, 'border_left_style', true );

		$border_top_color    = get_post_meta( $post->ID, 'border_top_color', true );
		$border_right_color  = get_post_meta( $post->ID, 'border_right_color', true );
		$border_bottom_color = get_post_meta( $post->ID, 'border_bottom_color', true );
		$border_left_color   = get_post_meta( $post->ID, 'border_left_color', true );

		$padding_top    = get_post_meta( $post->ID, 'padding_top', true );
		$padding_right  = get_post_meta( $post->ID, 'padding_right', true );
		$padding_bottom = get_post_meta( $post->ID, 'padding_bottom', true );
		$padding_left   = get_post_meta( $post->ID, 'padding_left', true );

		$background_color  = get_post_meta( $post->ID, 'background_color', true );

		$border_style_options = apply_filters( 'mmtl_border_styles', array
		(
			'' => '',
			'none'   => __( 'none', 'table-layout' ),
			'solid'  => __( 'solid', 'table-layout' ),
			'dashed' => __( 'dashed', 'table-layout' ),
			'dotted' => __( 'dotted', 'table-layout' )
		));

		?>

		<h4><?php _e( 'Margin', 'table-layout' ); ?></h4>

		<?php 

		MMTL_Common::position_fields( 'mmtl_margin_%s', array
		(
			'top'    => get_post_meta( $post->ID, 'margin_top', true ),
			'right'  => get_post_meta( $post->ID, 'margin_right', true ),
			'bottom' => get_post_meta( $post->ID, 'margin_bottom', true ),
			'left'   => get_post_meta( $post->ID, 'margin_left', true )
		), 'number');

		?>

		<h4><?php _e( 'Padding', 'table-layout' ); ?></h4>

		<?php 

		MMTL_Common::position_fields( 'mmtl_padding_%s', array
		(
			'top'    => get_post_meta( $post->ID, 'padding_top', true ),
			'right'  => get_post_meta( $post->ID, 'padding_right', true ),
			'bottom' => get_post_meta( $post->ID, 'padding_bottom', true ),
			'left'   => get_post_meta( $post->ID, 'padding_left', true )
		), 'number');

		?>

		<h4><?php _e( 'Border', 'table-layout' ); ?></h4>

		<p><?php _e( 'Width', 'table-layout' ); ?></p>

		<?php 

		MMTL_Common::position_fields( 'mmtl_border_%s_width', array
		(
			'top'    => get_post_meta( $post->ID, 'border_top_width', true ),
			'right'  => get_post_meta( $post->ID, 'border_right_width', true ),
			'bottom' => get_post_meta( $post->ID, 'border_bottom_width', true ),
			'left'   => get_post_meta( $post->ID, 'border_left_width', true )
		), 'number');

		?>

		<p><?php _e( 'Style', 'table-layout' ); ?></p>

		<?php 

		MMTL_Common::position_fields( 'mmtl_border_%s_style', array
		(
			'top'    => get_post_meta( $post->ID, 'border_top_style', true ),
			'right'  => get_post_meta( $post->ID, 'border_right_style', true ),
			'bottom' => get_post_meta( $post->ID, 'border_bottom_style', true ),
			'left'   => get_post_meta( $post->ID, 'border_left_style', true )
		), 'dropdown', array( 'options' => $border_style_options ) );

		?>

		<p><?php _e( 'Color', 'table-layout' ); ?></p>

		<?php 

		MMTL_Common::position_fields( 'mmtl_border_%s_color', array
		(
			'top'    => get_post_meta( $post->ID, 'border_top_color', true ),
			'right'  => get_post_meta( $post->ID, 'border_right_color', true ),
			'bottom' => get_post_meta( $post->ID, 'border_bottom_color', true ),
			'left'   => get_post_meta( $post->ID, 'border_left_color', true )
		), 'text', array( 'class' => 'mmtl-color-picker' ) );

		?>

		<p><?php _e( 'Radius', 'table-layout' ); ?></p>

		<?php 

		MMTL_Common::position_fields( 'mmtl_border_%s_radius', array
		(
			'top_right'    => get_post_meta( $post->ID, 'border_top_right_radius', true ),
			'bottom_right' => get_post_meta( $post->ID, 'border_bottom_right_radius', true ),
			'bottom_left'  => get_post_meta( $post->ID, 'border_bottom_left_radius', true ),
			'top_left'     => get_post_meta( $post->ID, 'border_top_left_radius', true )
		), 'number');

		?>

		<h4><?php _e( 'Background', 'table-layout' ); ?></h4>

		<p>
			<label for="mmtl-background-color"><?php _e( 'Color', 'table-layout' ); ?></label><br>
			<input type="text" id="mmtl-background-color" class="mmtl-color-picker" name="mmtl_background_color" value="<?php echo esc_attr( $background_color ); ?>">
		</p>

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

		// general

		if ( isset( $_POST['mmtl_background_color'] ) )
		{
			update_post_meta( $post_id, 'background_color', sanitize_text_field( $_POST['mmtl_background_color'] ) );
		}

		// margin

		if ( isset( $_POST['mmtl_margin_top'] ) )
		{
			update_post_meta( $post_id, 'margin_top', sanitize_text_field( $_POST['mmtl_margin_top'] ) );
		}

		if ( isset( $_POST['mmtl_margin_right'] ) )
		{
			update_post_meta( $post_id, 'margin_right', sanitize_text_field( $_POST['mmtl_margin_right'] ) );
		}

		if ( isset( $_POST['mmtl_margin_bottom'] ) )
		{
			update_post_meta( $post_id, 'margin_bottom', sanitize_text_field( $_POST['mmtl_margin_bottom'] ) );
		}

		if ( isset( $_POST['mmtl_margin_left'] ) )
		{
			update_post_meta( $post_id, 'margin_left', sanitize_text_field( $_POST['mmtl_margin_left'] ) );
		}

		// border width
		
		if ( isset( $_POST['mmtl_border_top_width'] ) )
		{
			update_post_meta( $post_id, 'border_top_width', sanitize_text_field( $_POST['mmtl_border_top_width'] ) );
		}

		if ( isset( $_POST['mmtl_border_right_width'] ) )
		{
			update_post_meta( $post_id, 'border_right_width', sanitize_text_field( $_POST['mmtl_border_right_width'] ) );
		}

		if ( isset( $_POST['mmtl_border_bottom_width'] ) )
		{
			update_post_meta( $post_id, 'border_bottom_width', sanitize_text_field( $_POST['mmtl_border_bottom_width'] ) );
		}

		if ( isset( $_POST['mmtl_border_left_width'] ) )
		{
			update_post_meta( $post_id, 'border_left_width', sanitize_text_field( $_POST['mmtl_border_left_width'] ) );
		}

		// border style

		if ( isset( $_POST['mmtl_border_top_style'] ) )
		{
			update_post_meta( $post_id, 'border_top_style', sanitize_text_field( $_POST['mmtl_border_top_style'] ) );
		}

		if ( isset( $_POST['mmtl_border_right_style'] ) )
		{
			update_post_meta( $post_id, 'border_right_style', sanitize_text_field( $_POST['mmtl_border_right_style'] ) );
		}

		if ( isset( $_POST['mmtl_border_bottom_style'] ) )
		{
			update_post_meta( $post_id, 'border_bottom_style', sanitize_text_field( $_POST['mmtl_border_bottom_style'] ) );
		}

		if ( isset( $_POST['mmtl_border_left_style'] ) )
		{
			update_post_meta( $post_id, 'border_left_style', sanitize_text_field( $_POST['mmtl_border_left_style'] ) );
		}

		// border color

		if ( isset( $_POST['mmtl_border_top_color'] ) )
		{
			update_post_meta( $post_id, 'border_top_color', sanitize_text_field( $_POST['mmtl_border_top_color'] ) );
		}

		if ( isset( $_POST['mmtl_border_right_color'] ) )
		{
			update_post_meta( $post_id, 'border_right_color', sanitize_text_field( $_POST['mmtl_border_right_color'] ) );
		}

		if ( isset( $_POST['mmtl_border_bottom_color'] ) )
		{
			update_post_meta( $post_id, 'border_bottom_color', sanitize_text_field( $_POST['mmtl_border_bottom_color'] ) );
		}

		if ( isset( $_POST['mmtl_border_left_color'] ) )
		{
			update_post_meta( $post_id, 'border_left_color', sanitize_text_field( $_POST['mmtl_border_left_color'] ) );
		}

		// border radius

		if ( isset( $_POST['mmtl_border_top_right_radius'] ) )
		{
			update_post_meta( $post_id, 'border_top_right_radius', sanitize_text_field( $_POST['mmtl_border_top_right_radius'] ) );
		}

		if ( isset( $_POST['mmtl_border_bottom_right_radius'] ) )
		{
			update_post_meta( $post_id, 'border_bottom_right_radius', sanitize_text_field( $_POST['mmtl_border_bottom_right_radius'] ) );
		}

		if ( isset( $_POST['mmtl_border_bottom_left_radius'] ) )
		{
			update_post_meta( $post_id, 'border_bottom_left_radius', sanitize_text_field( $_POST['mmtl_border_bottom_left_radius'] ) );
		}

		if ( isset( $_POST['mmtl_border_top_left_radius'] ) )
		{
			update_post_meta( $post_id, 'border_top_left_radius', sanitize_text_field( $_POST['mmtl_border_top_left_radius'] ) );
		}

		// padding
		
		if ( isset( $_POST['mmtl_padding_top'] ) )
		{
			update_post_meta( $post_id, 'padding_top', sanitize_text_field( $_POST['mmtl_padding_top'] ) );
		}

		if ( isset( $_POST['mmtl_padding_right'] ) )
		{
			update_post_meta( $post_id, 'padding_right', sanitize_text_field( $_POST['mmtl_padding_right'] ) );
		}

		if ( isset( $_POST['mmtl_padding_bottom'] ) )
		{
			update_post_meta( $post_id, 'padding_bottom', sanitize_text_field( $_POST['mmtl_padding_bottom'] ) );
		}

		if ( isset( $_POST['mmtl_padding_left'] ) )
		{
			update_post_meta( $post_id, 'padding_left', sanitize_text_field( $_POST['mmtl_padding_left'] ) );
		}
	}

	public function css( $css, $post_id, $component_id, $selector )
	{
		if ( $component_id == 'mmtl-col' )
		{
			$background_color = get_post_meta( $post_id, 'background_color', true );

			$margin_top    = get_post_meta( $post_id, 'margin_top', true );
			$margin_right  = get_post_meta( $post_id, 'margin_right', true );
			$margin_bottom = get_post_meta( $post_id, 'margin_bottom', true );
			$margin_left   = get_post_meta( $post_id, 'margin_left', true );

			$border_top_width    = get_post_meta( $post_id, 'border_top_width', true );
			$border_right_width  = get_post_meta( $post_id, 'border_right_width', true );
			$border_bottom_width = get_post_meta( $post_id, 'border_bottom_width', true );
			$border_left_width   = get_post_meta( $post_id, 'border_left_width', true );

			$border_top_style    = get_post_meta( $post_id, 'border_top_style', true );
			$border_right_style  = get_post_meta( $post_id, 'border_right_style', true );
			$border_bottom_style = get_post_meta( $post_id, 'border_bottom_style', true );
			$border_left_style   = get_post_meta( $post_id, 'border_left_style', true );

			$border_top_color    = get_post_meta( $post_id, 'border_top_color', true );
			$border_right_color  = get_post_meta( $post_id, 'border_right_color', true );
			$border_bottom_color = get_post_meta( $post_id, 'border_bottom_color', true );
			$border_left_color   = get_post_meta( $post_id, 'border_left_color', true );
			
			$border_top_right_radius    = get_post_meta( $post_id, 'border_top_radius', true );
			$border_bottom_right_radius = get_post_meta( $post_id, 'border_right_radius', true );
			$border_bottom_left_radius  = get_post_meta( $post_id, 'border_bottom_radius', true );
			$border_top_left_radius     = get_post_meta( $post_id, 'border_left_radius', true );

			$padding_top    = get_post_meta( $post_id, 'padding_top', true );
			$padding_right  = get_post_meta( $post_id, 'padding_right', true );
			$padding_bottom = get_post_meta( $post_id, 'padding_bottom', true );
			$padding_left   = get_post_meta( $post_id, 'padding_left', true );

			$declarations = array();

			// general			

			MMTL_CSS::add_declaration( 'background-color', $background_color, $declarations );

			// margin

			MMTL_CSS::add_declaration( 'margin-top', $margin_top . 'px', $declarations );
			MMTL_CSS::add_declaration( 'margin-right', $margin_right . 'px', $declarations );
			MMTL_CSS::add_declaration( 'margin-bottom', $margin_bottom . 'px', $declarations );
			MMTL_CSS::add_declaration( 'margin-left', $margin_left . 'px', $declarations );

			// border width
			
			MMTL_CSS::add_declaration( 'border-top-width', $border_top_width . 'px', $declarations );
			MMTL_CSS::add_declaration( 'border-right-width', $border_right_width . 'px', $declarations );
			MMTL_CSS::add_declaration( 'border-bottom-width', $border_bottom_width . 'px', $declarations );
			MMTL_CSS::add_declaration( 'border-left-width', $border_left_width . 'px', $declarations );
			
			// border style

			MMTL_CSS::add_declaration( 'border-top-style', $border_top_style, $declarations );
			MMTL_CSS::add_declaration( 'border-right-style', $border_right_style, $declarations );
			MMTL_CSS::add_declaration( 'border-bottom-style', $border_bottom_style, $declarations );
			MMTL_CSS::add_declaration( 'border-left-style', $border_left_style, $declarations );

			// border color

			MMTL_CSS::add_declaration( 'border-top-color', $border_top_color, $declarations );
			MMTL_CSS::add_declaration( 'border-right-color', $border_right_color, $declarations );
			MMTL_CSS::add_declaration( 'border-bottom-color', $border_bottom_color, $declarations );
			MMTL_CSS::add_declaration( 'border-left-color', $border_left_color, $declarations );

			// border radius

			MMTL_CSS::add_declaration( '-webkit-border-top-right-radius', $border_top_right_radius . 'px', $declarations );
			MMTL_CSS::add_declaration( '-webkit-border-bottom-right-radius', $border_bottom_right_radius . 'px', $declarations );
			MMTL_CSS::add_declaration( '-webkit-border-bottom-left-radius', $border_bottom_left_radius . 'px', $declarations );
			MMTL_CSS::add_declaration( '-webkit-border-top-left-radius', $border_top_left_radius . 'px', $declarations );

			MMTL_CSS::add_declaration( '-moz-border-topright-radius', $border_top_right_radius . 'px', $declarations );
			MMTL_CSS::add_declaration( '-moz-border-bottomright-radius', $border_bottom_right_radius . 'px', $declarations );
			MMTL_CSS::add_declaration( '-moz-border-bottomleft-radius', $border_bottom_left_radius . 'px', $declarations );
			MMTL_CSS::add_declaration( '-moz-border-topleft-radius', $border_top_left_radius . 'px', $declarations );

			MMTL_CSS::add_declaration( 'border-top-right-radius', $border_top_right_radius . 'px', $declarations );
			MMTL_CSS::add_declaration( 'border-bottom-right-radius', $border_bottom_right_radius . 'px', $declarations );
			MMTL_CSS::add_declaration( 'border-bottom-left-radius', $border_bottom_left_radius . 'px', $declarations );
			MMTL_CSS::add_declaration( 'border-top-left-radius', $border_top_left_radius . 'px', $declarations );

			// padding

			MMTL_CSS::add_declaration( 'padding-top', $padding_top . 'px', $declarations );
			MMTL_CSS::add_declaration( 'padding-right', $padding_right . 'px', $declarations );
			MMTL_CSS::add_declaration( 'padding-bottom', $padding_bottom . 'px', $declarations );
			MMTL_CSS::add_declaration( 'padding-left', $padding_left . 'px', $declarations );

			$css[ $selector ] = $declarations;
		}

		return $css;
	}
}

MMTL_Component_Type_Column::get_instance()->init();

?>