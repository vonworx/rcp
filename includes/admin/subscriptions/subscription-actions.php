<?php
/**
 * Subscription Level Actions
 *
 * @package     restrict-content-pro
 * @subpackage  Admin/Subscription Actions
 * @copyright   Copyright (c) 2017, Restrict Content Pro
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       2.9
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Add a new subscription level
 *
 * @since 2.9
 * @return void
 */
function rcp_process_add_subscription_level() {

	if ( ! wp_verify_nonce( $_POST['rcp_add_level_nonce'], 'rcp_add_level_nonce' ) ) {
		wp_die( __( 'Nonce verification failed.', 'rcp' ), __( 'Error', 'rcp' ), array( 'response' => 403 ) );
	}

	if ( ! current_user_can( 'rcp_manage_levels' ) ) {
		wp_die( __( 'You do not have permission to perform this action.', 'rcp' ), __( 'Error', 'rcp' ), array( 'response' => 403 ) );
	}

	if ( empty( $_POST['name'] ) ) {
		rcp_log( 'Failed creating new subscription level: empty subscription name.' );
		$url = admin_url( 'admin.php?page=rcp-member-levels&rcp_message=level_missing_fields' );
		wp_safe_redirect( esc_url_raw( $url ) );
		exit;
	}

	$levels = new RCP_Levels();

	$level_id = $levels->insert( $_POST );

	if ( $level_id ) {
		$url = admin_url( 'admin.php?page=rcp-member-levels&rcp_message=level_added' );
	} else {
		$url = admin_url( 'admin.php?page=rcp-member-levels&rcp_message=level_not_added' );
	}
	wp_safe_redirect( $url );
	exit;

}
add_action( 'rcp_action_add-level', 'rcp_process_add_subscription_level' );

/**
 * Edit an existing subscription level
 *
 * @since 2.9
 * @return void
 */
function rcp_process_edit_subscription_level() {

	if ( ! wp_verify_nonce( $_POST['rcp_edit_level_nonce'], 'rcp_edit_level_nonce' ) ) {
		wp_die( __( 'Nonce verification failed.', 'rcp' ), __( 'Error', 'rcp' ), array( 'response' => 403 ) );
	}

	if ( ! current_user_can( 'rcp_manage_levels' ) ) {
		wp_die( __( 'You do not have permission to perform this action.', 'rcp' ), __( 'Error', 'rcp' ), array( 'response' => 403 ) );
	}

	$levels = new RCP_Levels();
	$update = $levels->update( $_POST['subscription_id'], $_POST );

	if ( $update ) {
		$url = admin_url( 'admin.php?page=rcp-member-levels&rcp_message=level_updated' );
	} else {
		$url = admin_url( 'admin.php?page=rcp-member-levels&rcp_message=level_not_updated' );
	}

	wp_safe_redirect( $url );
	exit;

}
add_action( 'rcp_action_edit-subscription', 'rcp_process_edit_subscription_level' );

/**
 * Delete a subscription level
 *
 * @since 2.9
 * @return void
 */
function rcp_process_delete_subscription_level() {

	if ( ! wp_verify_nonce( $_GET['_wpnonce'], 'rcp-delete-subscription-level' ) ) {
		wp_die( __( 'Nonce verification failed.', 'rcp' ), __( 'Error', 'rcp' ), array( 'response' => 403 ) );
	}

	if ( ! current_user_can( 'rcp_manage_levels' ) ) {
		wp_die( __( 'You do not have permission to perform this action.', 'rcp' ), __( 'Error', 'rcp' ), array( 'response' => 403 ) );
	}

	if ( ! isset( $_GET['level_id'] ) ) {
		wp_die( __( 'Please choose a subscription level.', 'rcp' ), __( 'Error', 'rcp' ), array( 'response' => 400 ) );
	}

	$level_id = absint( $_GET['level_id'] );

	$members_of_subscription = rcp_get_members_of_subscription( $level_id );

	// Cancel all active members of this subscription.
	if ( $members_of_subscription ) {
		foreach ( $members_of_subscription as $member ) {
			rcp_set_status( $member, 'cancelled' );
		}
	}

	$levels = new RCP_Levels();
	$levels->remove( $level_id );
	$levels->remove_all_meta_for_level_id( $level_id );

	wp_safe_redirect( add_query_arg( 'rcp_message', 'level_deleted', 'admin.php?page=rcp-member-levels' ) );
	exit;

}
add_action( 'rcp_action_delete_subscription', 'rcp_process_delete_subscription_level' );

/**
 * Activate a subscription level
 *
 * @since 2.9
 * @return void
 */
function rcp_process_activate_subscription() {

	if ( ! wp_verify_nonce( $_GET['_wpnonce'], 'rcp-activate-subscription-level' ) ) {
		wp_die( __( 'Nonce verification failed.', 'rcp' ), __( 'Error', 'rcp' ), array( 'response' => 403 ) );
	}

	if ( ! current_user_can( 'rcp_manage_levels' ) ) {
		wp_die( __( 'You do not have permission to perform this action.', 'rcp' ), __( 'Error', 'rcp' ), array( 'response' => 403 ) );
	}

	if ( ! isset( $_GET['level_id'] ) ) {
		wp_die( __( 'Please choose a subscription level.', 'rcp' ), __( 'Error', 'rcp' ), array( 'response' => 400 ) );
	}

	$level_id = absint( $_GET['level_id'] );
	$levels   = new RCP_Levels();
	$update   = $levels->update( $level_id, array( 'status' => 'active' ) );
	delete_transient( 'rcp_subscription_levels' );

	rcp_log( sprintf( 'Successfully activated subscription level #%d.', $level_id ) );

	wp_safe_redirect( add_query_arg( 'rcp_message', 'level_activated', 'admin.php?page=rcp-member-levels' ) );
	exit;

}
add_action( 'rcp_action_activate_subscription', 'rcp_process_activate_subscription' );

/**
 * Deactivate a subscription level
 *
 * @since 2.9
 * @return void
 */
function rcp_process_deactivate_subscription() {

	if ( ! wp_verify_nonce( $_GET['_wpnonce'], 'rcp-deactivate-subscription-level' ) ) {
		wp_die( __( 'Nonce verification failed.', 'rcp' ), __( 'Error', 'rcp' ), array( 'response' => 403 ) );
	}

	if ( ! current_user_can( 'rcp_manage_levels' ) ) {
		wp_die( __( 'You do not have permission to perform this action.', 'rcp' ), __( 'Error', 'rcp' ), array( 'response' => 403 ) );
	}

	if ( ! isset( $_GET['level_id'] ) ) {
		wp_die( __( 'Please choose a subscription level.', 'rcp' ), __( 'Error', 'rcp' ), array( 'response' => 400 ) );
	}

	$level_id = absint( $_GET['level_id'] );
	$levels   = new RCP_Levels();
	$update   = $levels->update( $level_id, array( 'status' => 'inactive' ) );
	delete_transient( 'rcp_subscription_levels' );

	rcp_log( sprintf( 'Successfully deactivated subscription level #%d.', $level_id ) );

	wp_safe_redirect( add_query_arg( 'rcp_message', 'level_deactivated', 'admin.php?page=rcp-member-levels' ) );
	exit;

}
add_action( 'rcp_action_deactivate_subscription', 'rcp_process_deactivate_subscription' );