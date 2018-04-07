<?php
/**
 * Subscription Functions
 *
 * Functions for getting non-member specific info about subscription levels.
 *
 * @package     Restrict Content Pro
 * @subpackage  Subscription Functions
 * @copyright   Copyright (c) 2017, Restrict Content Pro
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */

/**
 * Gets an array of all available subscription levels
 *
 * @param string $status The status of subscription levels we want to retrieve: active, inactive, or all
 *
 * @return array|false Array of objects if levels exist, false otherwise
 */
function rcp_get_subscription_levels( $status = 'all' ) {
	global $wpdb, $rcp_db_name;

	$rcp_levels = new RCP_Levels();

	$levels = $rcp_levels->get_levels( array( 'status' => $status ) );

	if( $levels )
		return $levels;
	else
		return array();
}

/**
 * Gets all details of a specified subscription level
 *
 * @param int $id The ID of the subscription level to retrieve.
 *
 * @return object|false Object on success, false otherwise.
 */
function rcp_get_subscription_details( $id ) {
	$levels = new RCP_Levels();
	$level = $levels->get_level( $id );
	if( $level )
		return $level;
	return false;
}

/**
 * Gets all details of a specific subscription level
 *
 * @param string $name The name of the subscription level to retrieve.
 *
 * @return object|false Object on success, false otherwise.
 */
function rcp_get_subscription_details_by_name( $name ) {
	global $wpdb, $rcp_db_name;
	$level = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM " . $rcp_db_name . " WHERE name='%s';", $name ) );
	if( $level )
		return $level[0];
	return false;
}

/**
 * Gets the name of a specified subscription level
 *
 * @param int $id The ID of the subscription level to retrieve
 *
 * @return string Name of subscription, or error message on failure
 */
function rcp_get_subscription_name( $id ) {

	$levels_db = new RCP_Levels;
	return stripslashes( $levels_db->get_level_field( $id, 'name' ) );
}

/**
 * Gets the duration of a subscription
 *
 * @param int $id The ID of the subscription level to retrieve
 *
 * @return object|false Length an unit (m/d/y) of subscription, or false on failure.
 */
function rcp_get_subscription_length( $id ) {
	global $wpdb, $rcp_db_name;
	$level_length = $wpdb->get_results( $wpdb->prepare( "SELECT duration, duration_unit FROM " . $rcp_db_name . " WHERE id='%d';", $id ) );
	if( $level_length )
		return $level_length[0];
	return false;
}

/**
 * Gets the day of expiration of a subscription from the current day
 *
 * @param int $id The ID of the subscription level to retrieve
 *
 * @return string Nicely formatted date of expiration.
 */
function rcp_calculate_subscription_expiration( $id ) {
	$length          = rcp_get_subscription_length( $id );
	$expiration_date = 'none';

	if( $length->duration > 0 ) {

		$current_time       = current_time( 'timestamp' );
		$last_day           = cal_days_in_month( CAL_GREGORIAN, date( 'n', $current_time ), date( 'Y', $current_time ) );

		$expiration_unit    = $length->duration_unit;
		$expiration_length  = $length->duration;
		$expiration_date    = date( 'Y-m-d H:i:s', strtotime( '+' . $expiration_length . ' ' . $expiration_unit . ' 23:59:59', current_time( 'timestamp' ) ) );

		if( date( 'j', $current_time ) == $last_day && 'day' != $expiration_unit ) {
			$expiration_date = date( 'Y-m-d H:i:s', strtotime( $expiration_date . ' +2 days', current_time( 'timestamp' ) ) );
		}

	}

	return $expiration_date;
}

/**
 * Gets the price of a subscription level
 *
 * @param int $id The ID of the subscription level to retrieve
 *
 * @return int|float|false Price of subscription level, false on failure
 */
function rcp_get_subscription_price( $id ) {
	$levels = new RCP_Levels();
	$price = $levels->get_level_field( $id, 'price' );
	if( $price )
		return $price;
	return false;
}

/**
 * Gets the signup fee of a subscription level
 *
 * @param int $id The ID of the subscription level to retrieve
 *
 * @return int|float|false Signup fee if any, false otherwise
 */
function rcp_get_subscription_fee( $id ) {
	$levels = new RCP_Levels();
	$fee = $levels->get_level_field( $id, 'fee' );
	if( $fee )
		return $fee;
	return false;
}

/**
 * Gets the description of a subscription level
 *
 * @param int $id The ID of the subscription level to retrieve
 *
 * @return string Level description.
 */
function rcp_get_subscription_description( $id ) {
	$levels = new RCP_Levels();
	$desc = $levels->get_level_field( $id, 'description' );
	return apply_filters( 'rcp_get_subscription_description', stripslashes( $desc ), $id );
}

/**
 * Gets the access level of a subscription package
 *
 * @param int $id The ID of the subscription level to retrieve
 *
 * @return int|false The numerical access level the subscription gives, or false if none.
 */
function rcp_get_subscription_access_level( $id ) {
	$levels = new RCP_Levels();
	$level = $levels->get_level_field( $id, 'level' );
	if( $level )
		return $level;
	return false;
}

/**
 * Retrieve the number of active subscribers on a subscription level
 *
 * @param int    $id     ID of the subscription level to check.
 * @param string $status Membership status to check. Default is 'active'.
 *
 * @since       2.6
 * @access      public
 * @return      int Number of subscribers.
*/
function rcp_get_subscription_member_count( $id, $status = 'active' ) {

	global $rcp_levels_db;

	$key   = $id . '_' . $status . '_member_count';
	$count = $rcp_levels_db->get_meta( $id, $key, true );

	if( '' === $count ) {

		$count = rcp_count_members( $id, $status );
		$rcp_levels_db->update_meta( $id, $key, (int) $count );

	}

	$count = (int) max( $count, 0 );

	return apply_filters( 'rcp_get_subscription_member_count', $count, $id, $status );
}

/**
 * Increments the number of active subscribers on a subscription level
 *
 * @param int    $id     ID of the subscription level to increment the count of.
 * @param string $status Membership status to increment count for. Default is 'active'.
 *
 * @since       2.6
 * @access      public
 * @return      void
*/
function rcp_increment_subscription_member_count( $id, $status = 'active' ) {

	global $rcp_levels_db;

	$key    = $id . '_' . $status . '_member_count';
	$count  = rcp_get_subscription_member_count( $id, $status );
	$count += 1;

	$rcp_levels_db->update_meta( $id, $key, (int) $count );

	do_action( 'rcp_increment_subscription_member_count', $id, $count, $status );
}

/**
 * Decrements the number of active subscribers on a subscription level
 *
 * @param int    $id     ID of the subscription level to decrement the count of.
 * @param string $status Membership status to decrement count for. Default is 'active'.
 *
 * @since       2.6
 * @access      public
 * @return      void
*/
function rcp_decrement_subscription_member_count( $id, $status = 'active' ) {

	global $rcp_levels_db;

	$key    = $id . '_' . $status . '_member_count';
	$count  = rcp_get_subscription_member_count( $id, $status );
	$count -= 1;
	$count  = max( $count, 0 );

	$rcp_levels_db->update_meta( $id, $key, (int) $count );

	do_action( 'rcp_decrement_subscription_member_count', $id, $count, $status );
}

/**
 * Get a formatted duration unit name for subscription lengths
 *
 * @param string $unit   The duration unit to return a formatted string for.
 * @param int    $length The duration of the subscription level.
 *
 * @return string A formatted unit display. Example "days" becomes "Days". Return is localized.
 */
function rcp_filter_duration_unit( $unit, $length ) {
	$new_unit = '';
	switch ( $unit ) :
		case 'day' :
			if( $length > 1 )
				$new_unit = __( 'Days', 'rcp' );
			else
				$new_unit = __( 'Day', 'rcp' );
		break;
		case 'month' :
			if( $length > 1 )
				$new_unit = __( 'Months', 'rcp' );
			else
				$new_unit = __( 'Month', 'rcp' );
		break;
		case 'year' :
			if( $length > 1 )
				$new_unit = __( 'Years', 'rcp' );
			else
				$new_unit = __( 'Year', 'rcp' );
		break;
	endswitch;
	return $new_unit;
}

/**
 * Checks to see if there are any paid subscription levels created
 *
 * @since 1.1.0
 * @return bool True if paid levels exist, false if only free.
 */
function rcp_has_paid_levels() {
	return ( bool ) rcp_get_paid_levels();
}

/**
 * Return the paid levels
 *
 * @since 2.5
 * @return array
 */
function rcp_get_paid_levels() {

	$paid_levels = array();

	foreach( rcp_get_subscription_levels() as $level ) {
		if( $level->price > 0 && $level->status == 'active' ) {
			$paid_levels[] = $level;
		}
	}

	return apply_filters( 'rcp_get_paid_levels', $paid_levels );

}

/**
 * Retrieves available access levels
 *
 * @since 1.3.2
 * @return array
 */
function rcp_get_access_levels() {
	$levels = array(
		0 => 'None',
		1 => '1',
		2 => '2',
		3 => '3',
		4 => '4',
		5 => '5',
		6 => '6',
		7 => '7',
		8 => '8',
		9 => '9',
		10 => '10'
	);
	return apply_filters( 'rcp_access_levels', $levels );
}

/**
 * Generates a new subscription key
 *
 * @since 1.3.2
 * @return string
 */
function rcp_generate_subscription_key() {
	return apply_filters( 'rcp_subscription_key', urlencode( strtolower( md5( uniqid() ) ) ) );
}

/**
 * Determines if a subscription level should be shown
 *
 * @param int $level_id ID of the subscription level to check.
 * @param int $user_id  ID of the user, or 0 to use currently logged in user.
 *
 * @since 1.3.2.3
 * @return bool
 */
function rcp_show_subscription_level( $level_id = 0, $user_id = 0 ) {

	global $rcp_levels_db, $rcp_register_form_atts;

	if( empty( $user_id ) ) {
		$user_id = get_current_user_id();
	}

	$ret = true;

	$user_level = rcp_get_subscription_id( $user_id );
	$sub_length = rcp_get_subscription_length( $level_id );
	$sub_price 	= rcp_get_subscription_price( $level_id );
	$used_trial = rcp_has_used_trial( $user_id );
	$trial_duration = $rcp_levels_db->trial_duration( $level_id );

	// Don't show free trial if user has already used it. Don't show if sub is free and user already has it. Don't show if sub is unlimited and user already has it.
	if (
		is_user_logged_in()
		&&
		( $sub_price == '0' && $sub_length->duration > 0 && $used_trial )
		||
		( $sub_price == '0' && $user_level == $level_id )
		||
		( empty( $sub_length->duration ) && $user_level == $level_id )
		||
		( ! empty( $trial_duration ) && $used_trial && ( $user_level == $level_id && ! rcp_is_expired( $user_id ) ) )
	) {
		$ret = false;
	}

	// If multiple levels are specified in shortcode, like [register_form ids="1,2"]
	if ( ! empty( $rcp_register_form_atts['ids'] ) ) {

		$levels_to_show = array_map( 'absint', explode( ',', $rcp_register_form_atts['ids'] ) );

		if ( ! in_array( $level_id, $levels_to_show ) ) {
			$ret = false;
		}

	}

	return apply_filters( 'rcp_show_subscription_level', $ret, $level_id, $user_id );
}


/**
 * Retrieve the subscription levels a post/page is restricted to
 *
 * @param int $post_id The ID of the post to retrieve levels for
 *
 * @since       1.6
 * @access      public
 * @return      array
*/
function rcp_get_content_subscription_levels( $post_id = 0 ) {
	$levels = get_post_meta( $post_id, 'rcp_subscription_level', true );

	if( 'all' == $levels ) {
		// This is for backwards compatibility from when RCP didn't allow content to be restricted to multiple levels
		return false;
	}

	if( 'any' !== $levels && 'any-paid' !== $levels && ! empty( $levels ) && ! is_array( $levels ) ) {
		$levels = array( $levels );
	}
	return apply_filters( 'rcp_get_content_subscription_levels', $levels, $post_id );
}

/**
 * Get taxonomies that can be restricted
 *
 * @param string $output The type of output to return in the array. Accepts either taxonomy 'names'
 *                       or 'objects'. Default 'names'.
 *
 * @since 2.5
 * @return array
 */
function rcp_get_restricted_taxonomies( $output = 'names' ) {
	return apply_filters( 'rcp_get_restricted_taxonomies', get_taxonomies( array( 'public' => true, 'show_ui' => true ), $output ) );
}

/**
 * Get restrictions for the provided term_id
 *
 * @param int $term_id
 *
 * @since 2.5
 * @return array
 */
function rcp_get_term_restrictions( $term_id ) {

	// fallback to older method of handling term meta if term meta does not exist
	if ( ( ! function_exists( 'get_term_meta' ) ) || ! $restrictions = get_term_meta( $term_id, 'rcp_restricted_meta', true ) ) {
		$restrictions = get_option( "rcp_category_meta_$term_id" );
	}

	return apply_filters( 'rcp_get_term_restrictions', $restrictions, $term_id );
}

/**
 * Gets the IDs of subscription levels with trial periods.
 *
 * @since 2.7
 * @return array An array of numeric subscription level IDs. An empty array if none are found.
 */
function rcp_get_trial_level_ids() {

	$ids = array();

	foreach( rcp_get_subscription_levels() as $level ) {
		if( ! empty( $level->trial_duration ) && $level->trial_duration > 0 ) {
			$ids[] = $level->id;
		}
	}

	return $ids;
}