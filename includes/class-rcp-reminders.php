<?php

/**
 * Renewal and Expiration Reminders Class
 *
 * @package     Restrict Content Pro
 * @subpackage  Admin/Reminders
 * @copyright   Copyright (c) 2017, Restrict Content Pro
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       2.9
 */
final class RCP_Reminders {

	/**
	 * Get things started
	 *
	 * @access public
	 * @since  2.9
	 * @return void
	 */
	public function __construct() {

	}

	/**
	 * Retrieve reminder notices periods
	 *
	 * @access public
	 * @since  2.9
	 * @return array reminder notice periods
	 */
	public function get_notice_periods() {

		$periods = array(
			'today'    => __( 'The day of the renewal/expiration', 'rcp' ),
			'+1day'    => __( 'One day before renewal/expiration', 'rcp' ),
			'+2days'   => __( 'Two days before renewal/expiration', 'rcp' ),
			'+3days'   => __( 'Three days before renewal/expiration', 'rcp' ),
			'+4days'   => __( 'Four days before renewal/expiration', 'rcp' ),
			'+5days'   => __( 'Five days before renewal/expiration', 'rcp' ),
			'+6days'   => __( 'Six days before renewal/expiration', 'rcp' ),
			'+1week'   => __( 'One week before renewal/expiration', 'rcp' ),
			'+2weeks'  => __( 'Two weeks before renewal/expiration', 'rcp' ),
			'+3weeks'  => __( 'Three weeks before renewal/expiration', 'rcp' ),
			'+1month'  => __( 'One month before renewal/expiration', 'rcp' ),
			'+2months' => __( 'Two months before renewal/expiration', 'rcp' ),
			'+3months' => __( 'Three months before renewal/expiration', 'rcp' ),
			'-1day'    => __( 'One day after expiration', 'rcp' ),
			'-2days'   => __( 'Two days after expiration', 'rcp' ),
			'-3days'   => __( 'Three days after expiration', 'rcp' ),
			'-1week'   => __( 'One week after expiration', 'rcp' ),
			'-2weeks'  => __( 'Two weeks after expiration', 'rcp' ),
			'-1month'  => __( 'One month after expiration', 'rcp' ),
			'-2months' => __( 'Two months after expiration', 'rcp' ),
			'-3months' => __( 'Three months after expiration', 'rcp' )
		);

		return $periods;

		//return apply_filters( 'rcp_reminder_notice_periods', $periods );

	}

	/**
	 * Retrieve the reminder label for a notice
	 *
	 * @param int $notice_id ID of the notice to get the label for.
	 *
	 * @access public
	 * @since  2.9
	 * @return String
	 */
	public function get_notice_period_label( $notice_id = 0 ) {

		$notice  = $this->get_notice( $notice_id );
		$periods = $this->get_notice_periods();
		$label   = $periods[ $notice['send_period'] ];

		return $label;

		//return apply_filters( 'rcp_reminder_notice_period_label', $label, $notice_id );

	}

	/**
	 * Retrieve reminder notices types
	 *
	 * @access public
	 * @since  2.9
	 * @return array reminder notice types
	 */
	public function get_notice_types() {

		$types = array(
			'renewal'    => __( 'Renewal', 'rcp' ),
			'expiration' => __( 'Expiration', 'rcp' ),
		);

		return $types;

		//return apply_filters( 'rcp_reminder_notice_types', $types );

	}

	/**
	 * Retrieve the reminder type label for a notice
	 *
	 * @param int $notice_id ID of the notice to get the label for.
	 *
	 * @access public
	 * @since  2.9
	 * @return String
	 */
	public function get_notice_type_label( $notice_id = 0 ) {

		$notice = $this->get_notice( $notice_id );
		$types  = $this->get_notice_types();
		$label  = $types[ $notice['type'] ];

		return $label;

		//return apply_filters( 'rcp_reminder_notice_type_label', $label, $notice_id );

	}

	/**
	 * Retrieve a reminder notice
	 *
	 * @param int $notice_id ID of the notice to retrieve.
	 *
	 * @access public
	 * @since  2.9
	 * @return array|false Reminder notice details or false if notice ID is invalid.
	 */
	public function get_notice( $notice_id = 0 ) {

		$notices = $this->get_notices();
		$notice  = isset( $notices[ $notice_id ] ) ? $notices[ $notice_id ] : false;

		return $notice;

		//return apply_filters( 'rcp_reminder_notice', $notice, $notice_id );

	}

	/**
	 * Retrieve reminder notice periods
	 *
	 * @param string $type Type of notices to get (renewal, expiration, or all).
	 *
	 * @access public
	 * @since  2.9
	 * @return array Reminder notices defined in settings
	 */
	public function get_notices( $type = 'all' ) {
		$notices = get_option( 'rcp_reminder_notices', array() );

		if ( $type != 'all' ) {

			$notices_hold = array();

			foreach ( $notices as $key => $notice ) {

				if ( $notice['type'] == $type ) {
					$notices_hold[ $key ] = $notice;
				}

			}

			$notices = $notices_hold;

		}

		return $notices;

		//return apply_filters( 'rcp_reminder_notices', $notices, $type );
	}

	/**
	 * Retrieve the default notice settings
	 *
	 * @param string $type Notice type to retrieve, either 'renewal' or 'expiration'.
	 *
	 * @access public
	 * @since  2.9
	 * @return array
	 */
	public function get_default_notice( $type = 'renewal' ) {

		$settings = array(
			'send_period' => '+1month',
			'enabled'     => false
		);

		if ( 'expiration' == $type ) {

			// Expiration notice
			$settings['type']    = 'expiration';
			$settings['subject'] = __( 'Your Subscription is About to Expire', 'rcp' );

			$settings['message'] = 'Hello %name%,

Your subscription for %subscription_name% will expire on %expiration%.';

		} else {

			// Renewal notice
			$settings['type']    = 'renewal';
			$settings['subject'] = __( 'Your Subscription is About to Renew', 'rcp' );

			$settings['message'] = 'Hello %name%,

Your subscription for %subscription_name% will renew on %expiration%.';

		}

		return $settings;

	}

	/**
	 * Send reminder emails
	 *
	 * @access public
	 * @since  2.9
	 * @return void
	 */
	public function send_reminders() {

		$rcp_email      = new RCP_Emails;
		$reminder_types = $this->get_notice_types();

		foreach ( $reminder_types as $type => $name ) {

			$notices = $this->get_notices( $type );

			foreach ( $notices as $notice_id => $notice ) {

				// Skip if this reminder isn't enabled.
				if ( empty( $notice['enabled'] ) ) {
					continue;
				}

				// Skip if subject or message isn't filled out.
				if ( empty( $notice['subject'] ) || empty( $notice['message'] ) ) {
					continue;
				}

				$members = $this->get_reminder_subscriptions( $notice['send_period'], $type );

				if ( ! $members ) {
					continue;
				}

				foreach ( $members as $user ) {

					$member = new RCP_Member( $user );

					// Ensure an expiration notice isn't sent to an auto-renew subscription
					if ( $type == 'expiration' && $member->is_recurring() && $member->is_active() ) {
						continue;
					}

					// Ensure an expiration notice isn't sent to a still-trialling subscription
					if ( $type == 'expiration' && $member->is_trialing() ) {
						continue;
					}

					$sent_time = get_user_meta( $member->ID, sanitize_key( '_rcp_reminder_sent_' . $member->get_subscription_id() . '_' . $notice_id ), true );

					if ( $sent_time ) {
						continue;
					}

					$rcp_email->member_id = $member->ID;
					$rcp_email->send( $member->user_email, stripslashes( $notice['subject'] ), $notice['message'] );

					$member->add_note( sprintf( __( '%s notice was emailed to the member - %s.', 'rcp' ), ucwords( $type ), $this->get_notice_period_label( $notice_id ) ) );

					// Prevents reminder notices from being sent more than once.
					add_user_meta( $member->ID, sanitize_key( '_rcp_reminder_sent_' . $member->get_subscription_id() . '_' . $notice_id ), time() );

				}

			}
		}

	}

	/**
	 * Retrieve all members to send notices to
	 *
	 * @param string $period Reminder period.
	 * @param string $type   Type of notice to get the subscriptions for (renewal or expiration).
	 *
	 * @access public
	 * @since  2.9
	 * @return array|false Subscribers whose subscriptions are renewing or expiring within the defined period. False if
	 *                     none are found.
	 */
	public function get_reminder_subscriptions( $period = '+1month', $type = false ) {

		if ( ! $type ) {
			return false;
		}

		$args = array(
			'number'      => 9999,
			'count_total' => false,
			'fields'      => 'ids',
			'meta_query'  => array(
				'relation' => 'AND'
			)
		);

		switch ( $type ) {

			case 'renewal' :
				$args['meta_query'][] = array(
					'key'     => 'rcp_status',
					'compare' => '=',
					'value'   => 'active'
				);
				$args['meta_query'][] = array(
					'key'   => 'rcp_recurring',
					'value' => 'yes'
				);
				$args['meta_query'][] = array(
					'key'     => 'rcp_expiration',
					'value'   => array(
						date( 'Y-m-d H:i:s', strtotime( $period . ' midnight', current_time( 'timestamp' ) ) ),
						date( 'Y-m-d H:i:s', strtotime( $period . ' midnight', current_time( 'timestamp' ) ) + ( DAY_IN_SECONDS - 1 ) )
					),
					'type'    => 'DATETIME',
					'compare' => 'between'
				);
				break;

			case 'expiration' :
				$args['meta_query'][] = array(
					'key'     => 'rcp_recurring',
					'compare' => 'NOT EXISTS'
				);

				if ( 0 === strpos( $period, '-' ) ) {
					$status = array( 'expired', 'cancelled' ); // If after expiration, their status may be expired.
				} else {
					$status = array( 'active', 'cancelled' );
				}

				$args['meta_query'][] = array(
					'key'     => 'rcp_expiration',
					'value'   => array(
						date( 'Y-m-d H:i:s', strtotime( $period . ' midnight', current_time( 'timestamp' ) ) ),
						date( 'Y-m-d H:i:s', strtotime( $period . ' midnight', current_time( 'timestamp' ) ) + ( DAY_IN_SECONDS - 1 ) )
					),
					'type'    => 'DATETIME',
					'compare' => 'between'
				);
				$args['meta_query'][] = array(
					'key'     => 'rcp_status',
					'compare' => 'IN',
					'value'   => $status
				);
				break;

		}

		/**
		 * Filters the WP_User_Query arguments for getting relevant subscriptions.
		 *
		 * @param array  $args   Query arguments.
		 * @param string $period Reminder period.
		 * @param string $type   Type of notice to get the subscriptions for (renewal or expiration).
		 *
		 * @since 2.9.2
		 */
		$args = apply_filters( 'rcp_reminder_subscription_args', $args, $period, $type );

		$subscriptions = get_users( $args );

		if ( ! empty( $subscriptions ) ) {
			return $subscriptions;
		}

		return false;

	}

	/**
	 * Setup and send test email for a reminder
	 *
	 * @param int $notice_id ID of the notice to test.
	 *
	 * @access public
	 * @since  2.9
	 * @return void
	 */
	public function send_test_notice( $notice_id = 0 ) {

		$notice = $this->get_notice( $notice_id );
		$emails = new RCP_Emails;

		$current_user = wp_get_current_user();
		$email_to     = $current_user->user_email;
		$message      = ! empty( $notice['message'] ) ? $notice['message'] : __( "**THIS IS A DEFAULT TEST MESSAGE - Notice message was not retrieved.**\n\nHello %name%,\n\nYour subscription for %subscription_name% will renew or expire on %expiration%.", 'rcp' );
		$message      = $this->filter_test_notice( $message );
		$subject      = ! empty( $notice['subject'] ) ? $notice['subject'] : __( 'Default Subject Message - Your Subscription is About to Renew or Expire', 'rcp' );
		$subject      = $this->filter_test_notice( $subject );

		$emails->member_id = $current_user->ID;
		$emails->send( $email_to, stripslashes( $subject ), $message );

	}

	/**
	 * Filter template tags for test email.
	 *
	 * @param string $text
	 *
	 * @access public
	 * @since  2.9
	 * @return string
	 */
	public function filter_test_notice( $text = '' ) {

		$text = str_replace( '%name%', 'NAME GOES HERE', $text );
		$text = str_replace( '%subscription_name%', 'SUBSCRIPTION NAME', $text );
		$text = str_replace( '%expiration%', date( 'F j, Y', strtotime( 'today' ) ), $text );

		return $text;

	}

}