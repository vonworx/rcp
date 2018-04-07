<?php
/**
 * Admin Notices
 *
 * @package     Restrict Content Pro
 * @subpackage  Admin/Notices
 * @copyright   Copyright (c) 2017, Restrict Content Pro
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */

/**
 * Display admin notices
 *
 * @return void
 */
function rcp_admin_notices() {
	global $rcp_options;

	$message = ! empty( $_GET['rcp_message'] ) ? urldecode( $_GET['rcp_message'] ) : false;
	$class   = 'updated';
	$text    = '';

	if( current_user_can( 'rcp_manage_settings' ) ) {
		// only show notice if settings have never been saved
		if ( ! is_array( $rcp_options ) || empty( $rcp_options ) ) {
			echo '<div class="notice notice-info"><p><a href="' . admin_url( "admin.php?page=rcp-settings" ) . '">' . __( 'You should now configure your Restrict Content Pro settings', 'rcp' ) . '</a></p></div>';
		}

		if ( rcp_check_if_upgrade_needed() ) {
			echo '<div class="error"><p>' . __( 'The Restrict Content Pro database needs to be updated: ', 'rcp' ) . ' ' . '<a href="' . esc_url( add_query_arg( 'rcp-action', 'upgrade', admin_url() ) ) . '">' . __( 'upgrade now', 'rcp' ) . '</a></p></div>';
		}

		if ( isset( $_GET['rcp-db'] ) && $_GET['rcp-db'] == 'updated' ) {
			echo '<div class="updated fade"><p>' . __( 'The Restrict Content Pro database has been updated', 'rcp' ) . '</p></div>';
		}

		if ( false !== get_transient( 'rcp_login_redirect_invalid' ) ) {
			echo '<div class="error"><p>' . __( 'The page selected for log in redirect does not appear to contain a log in form. Please add [login_form] to the page then re-enable the log in redirect option.', 'rcp' ) . '</p></div>';
		}

		if ( 'expired' === rcp_check_license() && ! get_user_meta( get_current_user_id(), '_rcp_expired_license_dismissed', true ) ) {
			echo '<div class="error info">';
			echo '<p>' . __( 'Your license key for Restrict Content Pro has expired. Please renew your license to re-enable automatic updates.', 'rcp' ) . '</p>';
			echo '<p><a href="' . wp_nonce_url( add_query_arg( array( 'rcp_notice' => 'expired_license' ) ), 'rcp_dismiss_notice', 'rcp_dismiss_notice_nonce' ) . '">' . _x( 'Dismiss Notice', 'License', 'rcp' ) . '</a></p>';
			echo '</div>';
		} elseif ( 'valid' !== rcp_check_license() && ! get_user_meta( get_current_user_id(), '_rcp_missing_license_dismissed', true ) ) {
			echo '<div class="notice notice-info">';
			echo '<p>' . sprintf( __( 'Please <a href="%s">enter and activate</a> your license key for Restrict Content Pro to enable automatic updates.', 'rcp' ), esc_url( admin_url( 'admin.php?page=rcp-settings' ) ) ) . '</p>';
			echo '<p><a href="' . wp_nonce_url( add_query_arg( array( 'rcp_notice' => 'missing_license' ) ), 'rcp_dismiss_notice', 'rcp_dismiss_notice_nonce' ) . '">' . _x( 'Dismiss Notice', 'License', 'rcp' ) . '</a></p>';
			echo '</div>';
		}
	}

	if( current_user_can( 'activate_plugins' ) ) {
		if ( function_exists( 'rcp_register_stripe_gateway' ) ) {
			$deactivate_url = add_query_arg( array( 's' => 'restrict+content+pro+-+stripe' ), admin_url( 'plugins.php' ) );
			echo '<div class="error"><p>' . sprintf( __( 'You are using an outdated version of the Stripe integration for Restrict Content Pro. Please <a href="%s">deactivate</a> the add-on version to configure the new version.', 'rcp' ), $deactivate_url ) . '</p></div>';
		}

		if ( function_exists( 'rcp_register_paypal_pro_express_gateway' ) ) {
			$deactivate_url = add_query_arg( array( 's' => 'restrict+content+pro+-+paypal+pro' ), admin_url( 'plugins.php' ) );
			echo '<div class="error"><p>' . sprintf( __( 'You are using an outdated version of the PayPal Pro / Express integration for Restrict Content Pro. Please <a href="%s">deactivate</a> the add-on version to configure the new version.', 'rcp' ), $deactivate_url ) . '</p></div>';
		}
	}

	// Payment messages.
	if( current_user_can( 'rcp_manage_payments' ) ) {
		switch( $message ) {
			case 'payment_deleted' :

				$text = __( 'Payment deleted', 'rcp' );
				break;

			case 'payment_added' :

				$text = __( 'Payment added', 'rcp' );
				break;

			case 'payment_not_added' :

				$text = __( 'Payment creation failed', 'rcp' );
				$class = 'error';
				break;

			case 'payment_updated' :

				$text = __( 'Payment updated', 'rcp' );
				break;

			case 'payment_not_updated' :

				$text = __( 'Payment update failed', 'rcp' );
				break;
		}
	}

	// Upgrade messages.
	if( current_user_can( 'rcp_manage_settings' ) ) {
		switch( $message ) {
			case 'upgrade-complete' :

				$text =  __( 'Database upgrade complete', 'rcp' );
				break;
		}
	}

	// Member messages.
	if( current_user_can( 'rcp_manage_members' ) ) {
		switch( $message ) {
			case 'user_added' :

				$text = __( 'The user\'s subscription has been added', 'rcp' );
				break;

			case 'user_not_added' :

				$text = __( 'The user\'s subscription could not be added', 'rcp' );
				$class = 'error';
				break;

			case 'user_updated' :

				$text = __( 'Member updated', 'rcp' );
				break;

			case 'members_updated' :

				$text = __( 'Member accounts updated', 'rcp' );
				break;

			case 'member_cancelled' :

				$text = __( 'Member\'s payment profile cancelled successfully', 'rcp' );
				break;

			case 'verification_sent' :

				$text = __( 'Verification email sent successfully.', 'rcp' );
				break;

			case 'email_verified' :

				$text = __( 'The user\'s email has been verified successfully', 'rcp' );
				break;
		}
	}

	// Level messages.
	if( current_user_can( 'rcp_manage_levels' ) ) {
		switch( $message ) {
			case 'level_added' :

				$text = __( 'Subscription level added', 'rcp' );
				break;

			case 'level_updated' :

				$text = __( 'Subscription level updated', 'rcp' );
				break;

			case 'level_not_added' :

				$text = __( 'Subscription level could not be added', 'rcp' );
				$class = 'error';
				break;

			case 'level_not_updated' :

				$text = __( 'Subscription level could not be updated', 'rcp' );
				$class = 'error';
				break;

			case 'level_missing_fields' :

				$text = __( 'Subscription level fields are required', 'rcp' );
				$class = 'error';
				break;

			case 'level_deleted' :

				$text = __( 'Subscription level deleted', 'rcp' );
				break;

			case 'level_activated' :

				$text = __( 'Subscription level activated', 'rcp' );
				break;

			case 'level_deactivated' :

				$text = __( 'Subscription level deactivated', 'rcp' );
				break;
		}
	}

	// Discount messages.
	if( current_user_can( 'rcp_manage_discounts' ) ) {
		switch ( $message ) {
			case 'discount_added' :

				$text = __( 'Discount code created', 'rcp' );
				break;

			case 'discount_not_added' :

				$text  = __( 'The discount code could not be created due to an error', 'rcp' );
				$class = 'error';
				break;

			case 'discount_deleted' :

				$text = __( 'Discount code successfully deleted', 'rcp' );
				break;

			case 'discount_activated' :

				$text = __( 'Discount code activated', 'rcp' );
				break;

			case 'discount_deactivated' :

				$text = __( 'Discount code deactivated', 'rcp' );
				break;
		}
	}

	// Post type restriction messages.
	if( current_user_can( 'edit_posts' ) || current_user_can( 'edit_pages' ) ) {
		switch ( $message ) {
			case 'post-type-updated' :
				$text = __( 'Post type restrictions updated.', 'rcp' );
				break;
		}
	}

	// Subscription reminder messages.
	if( current_user_can( 'rcp_manage_settings' ) ) {
		switch( $message ) {
			case 'reminder_added' :

				$text = __( 'Subscription reminder added', 'rcp' );
				break;

			case 'reminder_updated' :

				$text = __( 'Subscription reminder updated', 'rcp' );
				break;

			case 'reminder_deleted' :

				$text = __( 'Subscription reminder deleted', 'rcp' );
				break;

			case 'test_reminder_sent' :

				$text = __( 'Test reminder sent successfully', 'rcp' );
				break;
		}

		if( $message ) {
			echo '<div class="' . $class . '"><p>' . $text . '</p></div>';
		}
	}
}
add_action( 'admin_notices', 'rcp_admin_notices' );

/**
 * Dismiss an admin notice for current user
 *
 * @access      private
 * @return      void
*/
function rcp_dismiss_notices() {

	if( empty( $_GET['rcp_dismiss_notice_nonce'] ) || empty( $_GET['rcp_notice'] ) ) {
		return;
	}

	if( ! wp_verify_nonce( $_GET['rcp_dismiss_notice_nonce'], 'rcp_dismiss_notice') ) {
		wp_die( __( 'Security check failed', 'rcp' ), __( 'Error', 'rcp' ), array( 'response' => 403 ) );
	}

	$notice = sanitize_key( $_GET['rcp_notice'] );

	update_user_meta( get_current_user_id(), "_rcp_{$notice}_dismissed", 1 );

	do_action( 'rcp_dismiss_notices', $notice );

	wp_redirect( remove_query_arg( array( 'rcp_notice' ) ) );
	exit;

}
add_action( 'admin_init', 'rcp_dismiss_notices' );