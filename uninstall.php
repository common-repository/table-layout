<?php if ( ! defined( 'ABSPATH' ) ) exit; //exits when accessed directly

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) )
{
	return;
}

delete_option( 'mmtl_version' );
delete_option( 'mmtl_icon_classes' );
delete_option( 'mmtl_update_reports' );
delete_option( 'mmtl_notices' );

delete_post_meta_by_key( 'mmtl_active' );

// deletes component types

$types = get_posts( 'post_type=mmtl_component_type&post_status=any&numberposts=-1' );

foreach ( $types as $type )
{
	wp_delete_post( $type->ID, true );
}

?>