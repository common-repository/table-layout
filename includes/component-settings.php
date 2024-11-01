<?php if ( ! defined( 'ABSPATH' ) ) exit; //exits when accessed directly

class MMTL_Component_Settings
{
	private static $instance = null;

	protected $pages = array();

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
		add_action( 'admin_init', array( $this, 'register_settings' ), 20 );
	}

	public function register_settings()
	{
		$components = MMTL_API::get_components();

		foreach ( $components as $component )
		{
			$options = array
			(
				'' => __( 'Default', 'table-layout' )
			);

			$types = get_posts(array
			(
				'post_type'   => 'mmtl_component_type',
				'meta_key'    => 'component',
				'meta_value'  => $component['id'],
				'numberposts' => 999
			));

			foreach ( $types as $type )
			{
				$options[ $type->post_name ] = $type->post_title;
			}

			$this->add_settings_section( 'type', __( 'Type', 'table-layout' ), '', $component['id'] );

			$this->register_setting( $component['id'], 'type', '' );
			$this->add_settings_field( 'type', __( 'Type' ), array( 'MMTL_Form', 'dropdown' ), $component['id'], 'type', array
			(
				'id'        => 'mmtl-type',
				'label_for' => 'mmtl-type',
				'class'     => '',
				'name'      => 'type',
				'value'     => '',
				'options'   => $options
			));
		}
	}

	public function get_field_id( $field_id )
	{
		return esc_attr( $field_id );
	}

	public function get_field_name( $field_id )
	{
		return esc_attr( $field_id );
	}

	public function get_field_value( $field_id )
	{
		return '';
	}

	public function add_settings_page( $id, $title, $callback )
	{
		$page = array
		(
			'id'       => $id,
			'title'    => $title,
			'callback' => $callback
		);

		$page = apply_filters( 'mmtl_component_settings_page', $page );

		$this->pages[ $page['id'] ] = $page;
	}

	public function register_setting( $option_group, $option_name, $sanitize_callback = null, $rules = array() )
	{
		$setting = array
		(
			'option_group'      => $option_group,
			'option_name'       => $option_name,
			'sanitize_callback' => $sanitize_callback,
			'rules'            	=> $rules
		);

		$settings = apply_filters( 'mmtl_component_settings_setting', $setting );

		$this->settings[] = $settings;
	}

	public function add_settings_section( $id, $title, $description, $page )
	{
		$section = array
		(
			'id'          => $id,
			'title'       => $title,
			'description' => $description,
			'page'        => $page
		);

		$section = apply_filters( 'mmtl_component_settings_section', $section );

		$this->sections[] = $section;
	}

	public function add_settings_field( $id, $title, $callback, $page, $section = 'default', $args = null )
	{
		$field = array
		(
			'id'       => $id,
			'title'    => $title,
			'callback' => $callback,
			'page'     => $page,
			'section'  => $section,
			'args'     => $args
		);

		$field = apply_filters( 'mmtl_component_settings_field', $field );

		$this->fields[] = $field;
	}

	public function add_settings_error( $setting, $code, $message, $type )
	{
		$errors = get_option( 'mmtl_settings_errors', array() );

		$errors[ $setting ][ $code ] = array
		(
			'message' => $message,
			'type' => $type
		);

		update_option( 'mmtl_settings_errors', $errors );
	}

	public function settings_errors( $setting )
	{
		$errors = get_option( 'mmtl_settings_errors', array() );

		if ( empty( $errors[ $setting ] ) )
		{
			return;
		}

		foreach ( $errors[ $setting ] as $error )
		{
			MMTL_Common::notice( $error['message'], array
			(
				'type' => $error['type']
			));
		}

		unset( $errors[ $setting ] );

		update_option( 'mmtl_settings_errors', $errors );
	}

	public function do_settings_sections( $page )
	{
		$sections = wp_filter_object_list( $this->sections, array( 'page' => $page ) );

		foreach ( $sections as $section )
		{
			if ( ! empty( $section['title'] ) )
			{
				printf( '<h3>%s</h3>', $section['title'] );
			}

			if ( ! empty( $section['description'] ) )
			{
				echo $section['description'];
			}

			$this->do_settings_fields( $page, $section['id'] );
		}
	}

	public function do_settings_fields( $page, $section )
	{
		$fields = wp_filter_object_list( $this->fields, array( 'page' => $page, 'section' => $section ) );

		foreach ( $fields as $field )
		{
			printf( '<div class="mmtl-field mmtl-field-%s">', esc_attr( $field['id'] ) );

			if ( $field['title'] )
			{
				if ( ! empty( $field['args']['label_for'] ) )
				{
					$label_id = $field['args']['label_for'];
				}

				else
				{
					$label_id = $field['id'];
				}

				printf( '<label for="%s">%s</label><br>', esc_attr( $label_id ), $field['title'] );
			}
			
			// value

			if ( ! empty( $field['args']['name'] ) )
			{
				$field_name = $field['args']['name'];

				// checks for setting

				$settings = wp_filter_object_list( $this->settings, array
				(
					'option_group' => $field['page'],
					'option_name'  => $field['args']['name']
				));

				$setting = reset( $settings );

				if ( $setting && isset( $_POST[ $setting['option_name'] ] ) )
				{
					$field['args']['value'] = $_POST[ $setting['option_name'] ];
				}
			}

			// field

			call_user_func( $field['callback'], $field['args'] );

			// description

			if ( ! empty( $field['args']['description'] ) )
			{
				printf( '<p class="description">%s</p>', $field['args']['description'] );
			}
		
			echo '</div>'; // .mmtl-field
		}
	}

	public function settings_fields( $option_group )
	{
		wp_nonce_field( 'editor', MMTL_NONCE_NAME );

		?>
		
		<input type="hidden" name="action" value="mmtl_sanitize_options">
		<input type="hidden" name="_mmtl_option_group" value="<?php echo esc_attr( $option_group ); ?>">

		<?php
	}

	public function sanitize_options()
	{
		$option_group = ! empty( $_POST['_mmtl_option_group'] ) ? $_POST['_mmtl_option_group'] : null;

		$options = array();

		if ( $option_group )
		{
			$settings = wp_filter_object_list( $this->settings, array( 'option_group' => $option_group ) );

			foreach ( $settings as $setting )
			{
				if ( isset( $_POST[ $setting['option_name'] ] ) )
				{
					$option_value = $_POST[ $setting['option_name'] ];
				}

				else
				{
					$option_value = ''; // for checkboxes
				}

				// sanitize

				if ( $setting['sanitize_callback'] )
				{
					if ( is_array( $setting['sanitize_callback'] ) )
					{
						$callbacks = array( $setting['sanitize_callback'] );
					}

					else
					{
						$callbacks = explode( '|', $setting['sanitize_callback'] );
					}

					foreach ( $callbacks as $callback )
					{
						$option_value = call_user_func( $callback, $option_value );
					}

					if ( $option_value === null )
					{
						continue;
					}
				}

				// rules

				$fields = wp_filter_object_list( $this->fields, array( 'page' => $setting['option_group'], 'id' => $setting['option_name'] ) );

				$field = reset( $fields );

				foreach ( $setting['rules'] as $rule )
				{
					$message = apply_filters( 'mmtl_settings_rule_' . $rule, '', $option_value, $field );

					if ( ! $message )
					{
						continue;
					}

					$this->add_settings_error( $setting['option_group'], $setting['option_name'], $message, 'error' );

					$option_value = '';
				}

				$options[ $setting['option_name'] ] = $option_value;
			}
		}

		return $options;
	}

	public function get_settings_page( $page )
	{
		$p = $this->pages[ $page ];

		ob_start();
		
		call_user_func( $p['callback'] );

		$content = ob_get_clean();

		$data = array
		(
			'title'   => $p['title'],
			'content' => $content
		);

		$html = MMTL_Common::load_template( 'modal', $data, true );

		return $html;
	}
}

MMTL_Component_Settings::get_instance()->init();

?>