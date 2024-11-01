<?php if ( ! defined( 'ABSPATH' ) ) exit; //exits when accessed directly

class MMTL_Updates
{
	private static $instance = null;

	protected $page_hook = null;

	protected $actions = array();

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
		add_action( 'admin_notices', array( $this, 'notices' ) );
		add_action( 'admin_menu', array( $this, 'register_page' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );

		add_action( 'wp_ajax_mmtl_updater_do_action', array( $this, 'do_action' ) );
		
		add_filter( 'mmtl_updater_actions', array( $this, 'register_actions' ), 5, 2 );
	}

	public function get_actions()
	{
		$from_version = get_option( 'mmtl_version' );
		$to_version = MMTL_VERSION;

		$all_actions = apply_filters( 'mmtl_updater_actions', array() );

		if ( empty( $all_actions ) || ! is_array( $all_actions ) )
		{
			return array();
		}

		if ( $from_version || $to_version )
		{
			$actions = array();
		
			foreach ( $all_actions as $action_id => $action )
			{
				// skips when version is less or aqual than from verion

				if ( $from_version !== false && version_compare( $action['version'], $from_version , '<=' ) )
				{
					continue;
				}

				// skips when version is greater than to verion

				if ( $to_version && version_compare( $action['version'], $to_version , '>' ) )
				{
					continue;
				}

				$actions[ $action_id ] = $action;
			}
		}

		else
		{
			$actions = $all_actions;
		}
		
		if ( count( $actions ) > 0 )
		{
			// sorts actions on version number

			uasort( $actions, array( $this, 'sort_actions' ) );

			$actions['_finish'] = array
			(
				'title'       => __( 'Finish', 'table-layout' ),
				'description' => __( 'Finishes the update process.', 'table-layout' ),
				'version'     => '',
				'callback'    => array( $this, 'finish_update' )
			);
		}

		return $actions;
	}

	public function register_actions( $actions )
	{
		$actions['1.5.0'] = array
		(
			'title'       =>  __( 'Text component', 'table-layout' ),
			'description' => __( 'The text component is added in version 1.5.0. It manages the textual content inside a column (previously handled by the column itself). This action writes a shortcode that wraps around the contents of a column.', 'table-layout' ),
			'version'     => '1.5.0', // action applied for older versions than this version  
			'callback'    => array( $this, 'process_update_1_5_0' )
		);

		$actions['1.5.5'] = array
		(
			'title'       => esc_html__( "No <p>'s" ),
			'description' => esc_html__( 'As cause of conflicts with shortcodes of other plugins html paragraphs <p> and breaks <br/> are replaced with newlines. (WordPress adds them automatically when a post or page is displayed in the front-end.)', 'table-layout' ),
			'version'     => '1.5.5', // action applied for older versions than this version  
			'callback'    => array( $this, 'process_update_1_5_5' )
		);

		return $actions;
	}

	public function do_action()
	{
		if ( ! defined( 'DOING_AJAX' ) || ! DOING_AJAX )
		{
			return;
		}

		check_admin_referer( 'mmtl_updater', MMTL_NONCE_NAME );

		$action_id = ! empty( $_POST[ 'action_id' ] ) ? $_POST[ 'action_id' ] : null;

		$actions = $this->get_actions();

		if ( empty( $actions ) )
		{
			wp_send_json_error( __( 'No actions found.', 'table-layout' ) );
		}

		if ( ! $action_id || empty( $actions[ $action_id ] ) )
		{
			wp_send_json_error( __( 'Invalid action.', 'table-layout' ) );
		}

		$action = $actions[ $action_id ];

		$results = call_user_func( $action['callback'] );

		$report = $this->create_report( $results, $action_id );

		$this->save_report( $report );

		if ( ! empty( $report['errors'] ) )
		{
			wp_send_json_error( $report );
		}

		wp_send_json_success( $report );
	}

	public function save_report( $report )
	{
		$reports = get_option( 'mmtl_update_reports', array() );

		if ( ! is_array( $reports ) )
		{
			$reports = array();
		}

		$reports[] = $report;

		return update_option( 'mmtl_update_reports', $reports );
	}

	public function register_page()
	{
		$this->page_hook = add_submenu_page( null, __( 'Table Layout Updater', 'table-layout' ), __( 'Table Layout Updater', 'table-layout' ), 'update_plugins', 'mmtl_updater', array( $this, 'print_page' ) );
	}

	public function print_page()
	{
		$actions = $this->get_actions();

		add_thickbox();

		?>

		<div id="mmtl-updater-screen" class="wrap">

			<h2><?php _e( 'Table Layout Updater', 'table-layout' ) ?></h2>

			<?php if ( ! empty( $actions ) ): ?>

			<p><?php _e( 'Database data needs to be updated. Click the update button below to update.' , 'table-layout'); ?></p>
			
			<h3><?php _e( 'Actions', 'table-layout' ); ?></h3>

			<p><?php _e( 'Following actions will be processed during the update.' , 'table-layout'); ?></p>

			<form action="" method="post">

				<table class="widefat">
					<thead>
						<tr>
							<th class="mmtl-title-col"><?php _e( 'Title', 'table-layout' ); ?></th>
							<th class="mmtl-description-col"><?php _e( 'Description', 'table-layout' ); ?></th>
							<th class="mmtl-version-col"><?php _e( 'Plugin version', 'table-layout' ); ?></th>
							<th class="mmtl-button-col"></th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ( $actions as $action_id => $action ) : ?>
						<tr id="mmtl-action-<?php echo esc_attr( $action_id ); ?>" class="mmtl-action">
							<td class="mmtl-title-col"><strong><?php echo $action['title']; ?></strong></td>
							<td class="mmtl-description-col"><?php echo $action['description']; ?></td>
							<td class="mmtl-version-col"><?php echo $action['version']; ?></td>
							<td class="mmtl-button-col">
								
								<p class="mmtl-success mmtl-hide"><?php _e( 'Done', 'table-layout' ) ?></p>
								<p class="mmtl-error mmtl-hide"><?php _e( 'Done. (errors encountered)', 'table-layout' ); ?></p>

								<div id="mmtl-action-<?php echo sanitize_title( $action_id ); ?>-log" class="mmtl-log mmtl-hide">

									<h3><?php _e( 'Log', 'table-layout' ); ?></h3>

									<p>
										<textarea class="mmtl-log-text large-text" rows="10" cols="60" readonly></textarea>
									</p>

								</div>

								<a class="mmtl-hide mmtl-show-log-button thickbox" href="#TB_inline?width=600&height=350&inlineId=mmtl-action-<?php echo sanitize_title( $action_id ); ?>-log"><?php _e( 'Show log' ); ?></a>
							
								<?php echo MMTL_Common::ajax_loader(); ?>
								
							</td>
						</tr>
					<?php endforeach; ?>
					</tbody>
				</table>

				<?php submit_button( __( 'Update', 'table-layout' ) ); ?>

				<p class="mmtl-update-complete mmtl-hide"><?php _e( 'Update complete', 'table-layout' ); ?></p>
				<p class="mmtl-update-ajax-error mmtl-hide"><?php printf( __( 'Unable to update: %s', 'table-layout' ), '<span class="mmtl-ajax-error-text"></span>' ); ?></p>

			</form>

			<?php else : ?>
			<p><?php _e( 'No updates available.', 'table-layout' ); ?></p>
			<?php endif; ?>

		</div><!-- . wrap -->

		<?php
	}

	public function is_updateable()
	{
		$actions = $this->get_actions();
		
		return ! empty( $actions );
	}

	public function notices()
	{
		if ( ! $this->is_updateable() )
		{
			return;
		}

		$screen = get_current_screen();

		if ( $screen->id == $this->page_hook )
		{
			return;
		}

	    ?>
	    <div class="notice notice-error">
	        <p>
	        	<strong><?php _e( 'Table Layout', 'table-layout' ); ?></strong>:
	        	<?php _e( 'Database data needs to be updated.', 'table-layout' ); ?>
	        	<a href="<?php echo admin_url( 'admin.php?page=mmtl_updater' ); ?>"><?php _e( 'Go to the update page', 'table-layout' ); ?></a>
	    	</p>
	    </div>
	    <?php
	}

	public function contains_error( $obj )
	{
		foreach ( $obj as $key => $value )
		{
			if ( is_wp_error( $value ) )
			{
				return true;
			}
		}

		return false;
	}

	public function create_report( $results, $action_id = false )
	{
		$time = time();

		$report = array
		(
			'results'       => $results,
			'action'        => $action_id,
			'time'          => $time,
			'readable_time' => date('Y-m-d H:i:s P', $time ),
			'processed'     => 0,
			'success'       => 0,
			'errors'        => 0,
			'log'           => ''
		);

		if ( ! empty( $results ) && is_array( $results ) )
		{
			foreach ( $results as $post_id => $result )
			{
				$report['processed']++;

				if ( is_wp_error( $result ) || ! $result )
				{
					$report['errors']++;
				}

				else
				{
					$report['success']++;
				}

				// result is post id

				if ( $result && is_numeric( $result ) )
				{
					$message = __( 'updated', 'table-layout' );
				}

				// result is WP_Error

				else if ( is_wp_error( $result ) )
				{
					$message = sprintf( __( 'Error: %s', 'table-layout' ), $result->get_error_message() );
				}

				// result is text

				else if ( $result )
				{
					$message = $result;
				}

				// no result

				else
				{
					$message = '';
				}

				$report['log'] .= sprintf( "#%d – %s (%s)\n\t%s\n\n", 
					$post_id, get_the_title( $post_id ), get_post_type( $post_id ), $message );
			}

			$overview = '';
			$overview .= sprintf( __( 'processed: %d', 'table-layout' ), $report['processed'] );
			$overview .= " - ";
			$overview .= sprintf( __( 'success: %d', 'table-layout' ), $report['success'] );
			$overview .= " - ";
			$overview .= sprintf( __( 'errors: %d', 'table-layout' ), $report['errors'] );
			$overview .= "\n\n";

			$report['log'] = $overview . $report['log'];
		}

		return $report;
	}

	public function enqueue_scripts()
	{
		$screen = get_current_screen();

		if ( $screen->id != $this->page_hook )
		{
			return;
		}

		wp_enqueue_style( 'table-layout-admin' );

		wp_enqueue_script( 'table-layout-updater', plugins_url( 'js/updater.js', MMTL_FILE ), array( 'jquery' ), false, true );

		wp_localize_script( 'table-layout-updater', 'MMTL_Updater_Options', array
		(
			'ajaxurl'   => admin_url( 'admin-ajax.php' ),
			'noncename' => MMTL_NONCE_NAME,
			'nonce'     => wp_create_nonce( 'mmtl_updater' ),
			'actions'  => array_keys( $this->get_actions() )
		));
	}

	public function finish_update()
	{
		return update_option( 'mmtl_version', MMTL_VERSION );
	}

	public function sort_actions( $a, $b )
	{
		return version_compare( $a['version'], $b['version'] );
	}

	public function process_update_1_5_0()
	{
		// replaces [mmtl-col]{content}[/mmtl-col] with [mmtl-col][mmtl-text]{content}[/mmtl-text][/mmtl-col]

		global $wpdb;

		$results = array();

		$post_types = get_post_types( array( 'public' => true ) );

		foreach ( $post_types as $post_type )
		{
			$sql = sprintf( "SELECT ID, post_content FROM %s WHERE post_type='%s' AND post_content LIKE '%%[mmtl-col%%';", $wpdb->posts, $post_type );
			
			$posts = $wpdb->get_results( $sql, OBJECT );

			if ( empty( $posts ) )
			{
				continue;
			}

			foreach ( $posts as $post )
			{
				// skips when text shortcode is in content

				if ( stripos( $post->post_content, '[/mmtl-text]' ) !== false )
				{
					$results[ $post->ID ] = __( 'shortcode [mmtl-text] already exists.' );

					continue;
				}

				$post_content = preg_replace( '/(\[mmtl-col.*?\])(.*?)(\[\/mmtl-col\])/s', '$1[mmtl-text]$2[/mmtl-text]$3', $post->post_content );
				
				$post_id = wp_update_post( array
				(
					'ID' => $post->ID,
					'post_content' => $post_content
				), true );

				$results[ $post->ID ] = $post_id;
			}
		}

		return $results;
	}

	public function process_update_1_5_5()
	{
		global $wpdb;

		$results = array();

		$post_types = get_post_types( array( 'public' => true ) );

		foreach ( $post_types as $post_type )
		{
			$sql = sprintf( "SELECT ID, post_content FROM %s WHERE post_type='%s' AND post_content LIKE '%%[mmtl-col%%';", $wpdb->posts, $post_type );
			
			$posts = $wpdb->get_results( $sql, OBJECT );

			if ( empty( $posts ) )
			{
				continue;
			}

			foreach ( $posts as $post )
			{
				$post_content = MMTL_Common::removep( $post->post_content );
				
				$post_id = wp_update_post( array
				(
					'ID' => $post->ID,
					'post_content' => $post_content
				), true );

				$results[ $post->ID ] = $post_id;
			}
		}

		return $results;
	}
}

MMTL_Updates::get_instance()->init();

?>