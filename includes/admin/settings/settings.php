<?php
/**
 * Settings
 *
 * @package     Restrict Content Pro
 * @subpackage  Admin/Settings
 * @copyright   Copyright (c) 2017, Restrict Content Pro
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */

/**
 * Register the plugins ettings
 *
 * @return void
 */
function rcp_register_settings() {
	// create whitelist of options
	register_setting( 'rcp_settings_group', 'rcp_settings', 'rcp_sanitize_settings' );
}
add_action( 'admin_init', 'rcp_register_settings' );

/**
 * Render the settings page
 *
 * @return void
 */
function rcp_settings_page() {
	global $rcp_options;

	$defaults = array(
		'currency_position'     => 'before',
		'currency'              => 'USD',
		'registration_page'     => 0,
		'redirect'              => 0,
		'redirect_from_premium' => 0,
		'login_redirect'        => 0,
		'email_header_img'      => '',
		'email_header_text'     => __( 'Hello', 'rcp' )
	);

	$rcp_options = wp_parse_args( $rcp_options, $defaults );

	?>
	<div class="wrap">
		<?php
		if ( ! isset( $_REQUEST['updated'] ) )
			$_REQUEST['updated'] = false;
		?>

		<h1><?php _e( 'Restrict Content Pro', 'rcp' ); ?></h1>
		<h2 class="nav-tab-wrapper">
			<a href="#general" class="nav-tab"><?php _e( 'General', 'rcp' ); ?></a>
			<a href="#payments" class="nav-tab"><?php _e( 'Payments', 'rcp' ); ?></a>
			<a href="#emails" class="nav-tab"><?php _e( 'Emails', 'rcp' ); ?></a>
			<a href="#invoices" class="nav-tab"><?php _e( 'Invoices', 'rcp' ); ?></a>
			<a href="#misc" class="nav-tab"><?php _e( 'Misc', 'rcp' ); ?></a>
		</h2>
		<?php if ( false !== $_REQUEST['updated'] ) : ?>
		<div class="updated fade"><p><strong><?php _e( 'Options saved', 'rcp' ); ?></strong></p></div>
		<?php endif; ?>
		<form method="post" action="options.php" class="rcp_options_form">

			<?php settings_fields( 'rcp_settings_group' ); ?>

			<?php $pages = get_pages(); ?>


			<div id="tab_container">

				<div class="tab_content" id="general">
					<table class="form-table">
						<tr valign="top">
							<th colspan=2>
								<h3><?php _e( 'General', 'rcp' ); ?></h3>
							</th>
						</tr>
						<tr valign="top">
							<th>
								<label for="rcp_settings[license_key]"><?php _e( 'License Key', 'rcp' ); ?></label>
							</th>
							<td>
								<input class="regular-text" id="rcp_settings[license_key]" style="width: 300px;" name="rcp_settings[license_key]" value="<?php if( isset( $rcp_options['license_key'] ) ) { echo $rcp_options['license_key']; } ?>"/>
								<?php $status = get_option( 'rcp_license_status' ); ?>
								<?php if( $status !== false && $status == 'valid' ) { ?>
									<?php wp_nonce_field( 'rcp_deactivate_license', 'rcp_deactivate_license' ); ?>
									<input type="submit" class="button-secondary" name="rcp_license_deactivate" value="<?php _e('Deactivate License', 'rcp'); ?>"/>
									<span style="color:green;"><?php _e('active', 'rcp' ); ?></span>
								<?php } elseif( $status !== 'valid' ) { ?>
									<input type="submit" class="button-secondary" name="rcp_license_activate" value="<?php _e('Activate License', 'rcp' ); ?>"/>
								<?php } ?>
								<p class="description"><?php printf( __( 'Enter license key for Restrict Content Pro. This is required for automatic updates and <a href="%s">support</a>.', 'rcp' ), 'http://restrictcontentpro.com/support' ); ?></p>
							</td>
						</tr>
						<?php do_action( 'rcp_license_settings', $rcp_options ); ?>
						<tr valign="top">
							<th>
								<label for="rcp_settings[registration_page]"><?php _e( 'Registration Page', 'rcp' ); ?></label>
							</th>
							<td>
								<select id="rcp_settings[registration_page]" name="rcp_settings[registration_page]">
									<?php
									if($pages) :
										foreach ( $pages as $page ) {
										  	$option = '<option value="' . $page->ID . '" ' . selected($page->ID, $rcp_options['registration_page'], false) . '>';
											$option .= $page->post_title;
											$option .= ' (ID: ' . $page->ID . ')';
											$option .= '</option>';
											echo $option;
										}
									else :
										echo '<option>' . __('No pages found', 'rcp' ) . '</option>';
									endif;
									?>
								</select>
								<?php if ( ! empty( $rcp_options['registration_page'] ) ) : ?>
									<a href="<?php echo esc_url( get_edit_post_link( $rcp_options['registration_page'] ) ); ?>" class="button-secondary"><?php _e( 'Edit Page', 'rcp' ); ?></a>
									<a href="<?php echo esc_url( get_permalink( $rcp_options['registration_page'] ) ); ?>" class="button-secondary"><?php _e( 'View Page', 'rcp' ); ?></a>
								<?php endif; ?>
								<p class="description"><?php printf( __( 'Choose the primary registration page. This must contain the [register_form] short code. Additional registration forms may be added to other pages with [register_form id="x"]. <a href="%s" target="_blank">See documentation</a>.', 'rcp' ), 'http://docs.restrictcontentpro.com/article/1597-registerform' ); ?></p>
							</td>
						</tr>
						<tr valign="top">
							<th>
								<label for="rcp_settings[redirect]"><?php _e( 'Success Page', 'rcp' ); ?></label>
							</th>
							<td>
								<select id="rcp_settings[redirect]" name="rcp_settings[redirect]">
									<?php
									if($pages) :
										foreach ( $pages as $page ) {
										  	$option = '<option value="' . $page->ID . '" ' . selected($page->ID, $rcp_options['redirect'], false) . '>';
											$option .= $page->post_title;
											$option .= ' (ID: ' . $page->ID . ')';
											$option .= '</option>';
											echo $option;
										}
									else :
										echo '<option>' . __('No pages found', 'rcp' ) . '</option>';
									endif;
									?>
								</select>
								<?php if ( ! empty( $rcp_options['redirect'] ) ) : ?>
									<a href="<?php echo esc_url( get_edit_post_link( $rcp_options['redirect'] ) ); ?>" class="button-secondary"><?php _e( 'Edit Page', 'rcp' ); ?></a>
									<a href="<?php echo esc_url( get_permalink( $rcp_options['redirect'] ) ); ?>" class="button-secondary"><?php _e( 'View Page', 'rcp' ); ?></a>
								<?php endif; ?>
								<p class="description"><?php _e( 'This is the page users are redirected to after a successful registration.', 'rcp' ); ?></p>
							</td>
						</tr>
						<tr valign="top">
							<th>
								<label for="rcp_settings[account_page]"><?php _e( 'Account Page', 'rcp' ); ?></label>
							</th>
							<td>
								<select id="rcp_settings[account_page]" name="rcp_settings[account_page]">
									<?php
									if($pages) :
										$rcp_options['account_page'] = isset( $rcp_options['account_page'] ) ? absint( $rcp_options['account_page'] ) : 0;
										foreach ( $pages as $page ) {
										  	$option = '<option value="' . $page->ID . '" ' . selected($page->ID, $rcp_options['account_page'], false) . '>';
											$option .= $page->post_title;
											$option .= ' (ID: ' . $page->ID . ')';
											$option .= '</option>';
											echo $option;
										}
									else :
										echo '<option>' . __('No pages found', 'rcp' ) . '</option>';
									endif;
									?>
								</select>
								<?php if ( ! empty( $rcp_options['account_page'] ) ) : ?>
									<a href="<?php echo esc_url( get_edit_post_link( $rcp_options['account_page'] ) ); ?>" class="button-secondary"><?php _e( 'Edit Page', 'rcp' ); ?></a>
									<a href="<?php echo esc_url( get_permalink( $rcp_options['account_page'] ) ); ?>" class="button-secondary"><?php _e( 'View Page', 'rcp' ); ?></a>
								<?php endif; ?>
								<p class="description"><?php printf( __( 'This page displays the account and membership information for members. Contains <a href="%s" target="_blank">[subscription_details] short code</a>.', 'rcp' ), 'http://docs.restrictcontentpro.com/article/1600-subscriptiondetails' ); ?></p>
							</td>
						</tr>
						<tr valign="top">
							<th>
								<label for="rcp_settings[edit_profile]"><?php _e( 'Edit Profile Page', 'rcp' ); ?></label>
							</th>
							<td>
								<select id="rcp_settings[edit_profile]" name="rcp_settings[edit_profile]">
									<?php
									if($pages) :
										$rcp_options['edit_profile'] = isset( $rcp_options['edit_profile'] ) ? absint( $rcp_options['edit_profile'] ) : 0;
										foreach ( $pages as $page ) {
										  	$option = '<option value="' . $page->ID . '" ' . selected($page->ID, $rcp_options['edit_profile'], false) . '>';
											$option .= $page->post_title;
											$option .= ' (ID: ' . $page->ID . ')';
											$option .= '</option>';
											echo $option;
										}
									else :
										echo '<option>' . __('No pages found', 'rcp' ) . '</option>';
									endif;
									?>
								</select>
								<?php if ( ! empty( $rcp_options['edit_profile'] ) ) : ?>
									<a href="<?php echo esc_url( get_edit_post_link( $rcp_options['edit_profile'] ) ); ?>" class="button-secondary"><?php _e( 'Edit Page', 'rcp' ); ?></a>
									<a href="<?php echo esc_url( get_permalink( $rcp_options['edit_profile'] ) ); ?>" class="button-secondary"><?php _e( 'View Page', 'rcp' ); ?></a>
								<?php endif; ?>
								<p class="description"><?php printf( __( 'This page displays a profile edit form for logged-in members. Contains <a href="%s" target="_blank">[rcp_profile_editor] shortcode.', 'rcp' ), 'http://docs.restrictcontentpro.com/article/1602-rcpprofileeditor' ); ?></p>
							</td>
						</tr>
						<tr valign="top">
							<th>
								<label for="rcp_settings[update_card]"><?php _e( 'Update Billing Card Page', 'rcp' ); ?></label>
							</th>
							<td>
								<select id="rcp_settings[update_card]" name="rcp_settings[update_card]">
									<?php
									if($pages) :
										$rcp_options['update_card'] = isset( $rcp_options['update_card'] ) ? absint( $rcp_options['update_card'] ) : 0;
										foreach ( $pages as $page ) {
										  	$option = '<option value="' . $page->ID . '" ' . selected($page->ID, $rcp_options['update_card'], false) . '>';
											$option .= $page->post_title;
											$option .= ' (ID: ' . $page->ID . ')';
											$option .= '</option>';
											echo $option;
										}
									else :
										echo '<option>' . __('No pages found', 'rcp' ) . '</option>';
									endif;
									?>
								</select>
								<?php if ( ! empty( $rcp_options['update_card'] ) ) : ?>
									<a href="<?php echo esc_url( get_edit_post_link( $rcp_options['update_card'] ) ); ?>" class="button-secondary"><?php _e( 'Edit Page', 'rcp' ); ?></a>
									<a href="<?php echo esc_url( get_permalink( $rcp_options['update_card'] ) ); ?>" class="button-secondary"><?php _e( 'View Page', 'rcp' ); ?></a>
								<?php endif; ?>
								<p class="description"><?php printf( __( 'This page displays a profile edit form for logged-in members. Contains <a href="%s" target="_blank">[rcp_update_card] short code</a>.', 'rcp' ), 'http://docs.restrictcontentpro.com/article/1608-rcpupdatecard' ); ?></p>
							</td>
						</tr>
						<tr valign="top">
							<th>
								<label for="rcp_settings_auto_renew"><?php _e( 'Auto Renew', 'rcp' ); ?></label>
							</th>
							<td>
								<select name="rcp_settings[auto_renew]" id="rcp_settings_auto_renew">
									<option value="1"<?php selected( '1', rcp_get_auto_renew_behavior() ); ?>><?php _e( 'Always auto renew', 'rcp' ); ?></option>
									<option value="2"<?php selected( '2', rcp_get_auto_renew_behavior() ); ?>><?php _e( 'Never auto renew', 'rcp' ); ?></option>
									<option value="3"<?php selected( '3', rcp_get_auto_renew_behavior() ); ?>><?php _e( 'Let customer choose whether to auto renew', 'rcp' ); ?></option>
								</select>
								<p class="description"><?php _e( 'Select the auto renew behavior you would like subscription levels to have.', 'rcp' ); ?></p>
							</td>
						</tr>
						<tr valign="top"<?php echo ( '3' != rcp_get_auto_renew_behavior() ) ? ' style="display: none;"' : ''; ?>>
							<th>
								<label for="rcp_settings[auto_renew_checked_on]">&nbsp;&mdash;&nbsp;<?php _e( 'Default to Auto Renew', 'rcp' ); ?></label>
							</th>
							<td>
								<input type="checkbox" value="1" name="rcp_settings[auto_renew_checked_on]" id="rcp_settings[auto_renew_checked_on]" <?php checked( true, isset( $rcp_options['auto_renew_checked_on'] ) ); ?>/>
								<span><?php _e( 'Check this to have the auto renew checkbox enabled by default during registration. Customers will be able to change this.', 'rcp' ); ?></span>
							</td>
						</tr>
						<tr valign="top">
							<th>
								<label for="rcp_settings[free_message]"><?php _e( 'Free Content Message', 'rcp' ); ?></label>
							</th>
							<td>
								<?php
								$free_message = isset( $rcp_options['free_message'] ) ? $rcp_options['free_message'] : '';
								wp_editor( $free_message, 'rcp_settings_free_message', array( 'textarea_name' => 'rcp_settings[free_message]', 'teeny' => true ) ); ?>
								<p class="description"><?php _e( 'This is the message shown to users that do not have privilege to view free, user only content.', 'rcp' ); ?></p>
							</td>
						</tr>
						<tr valign="top">
							<th>
								<label for="rcp_settings[paid_message]"><?php _e( 'Premium Content Message', 'rcp' ); ?></label>
							</th>
							<td>
								<?php
								$paid_message = isset( $rcp_options['paid_message'] ) ? $rcp_options['paid_message'] : '';
								wp_editor( $paid_message, 'rcp_settings_paid_message', array( 'textarea_name' => 'rcp_settings[paid_message]', 'teeny' => true ) ); ?>
								<p class="description"><?php _e( 'This is the message shown to users that do not have privilege to view premium content.', 'rcp' ); ?></p>
							</td>
						</tr>
						<?php do_action( 'rcp_messages_settings', $rcp_options ); ?>
					</table>
					<?php do_action( 'rcp_general_settings', $rcp_options ); ?>

				</div><!--end #general-->

				<div class="tab_content" id="payments">
					<table class="form-table">
						<tr>
							<th>
								<label for="rcp_settings[currency]"><?php _e( 'Currency', 'rcp' ); ?></label>
							</th>
							<td>
								<select id="rcp_settings[currency]" name="rcp_settings[currency]">
									<?php
									$currencies = rcp_get_currencies();
									foreach($currencies as $key => $currency) {
										echo '<option value="' . esc_attr( $key ) . '" ' . selected($key, $rcp_options['currency'], false) . '>' . $currency . '</option>';
									}
									?>
								</select>
								<p class="description"><?php _e( 'Choose your currency.', 'rcp' ); ?></p>
							</td>
						</tr>
						<tr valign="top">
							<th>
								<label for="rcp_settings[currency_position]"><?php _e( 'Currency Position', 'rcp' ); ?></label>
							</th>
							<td>
								<select id="rcp_settings[currency_position]" name="rcp_settings[currency_position]">
									<option value="before" <?php selected('before', $rcp_options['currency_position']); ?>><?php _e( 'Before - $10', 'rcp' ); ?></option>
									<option value="after" <?php selected('after', $rcp_options['currency_position']); ?>><?php _e( 'After - 10$', 'rcp' ); ?></option>
								</select>
								<p class="description"><?php _e( 'Show the currency sign before or after the price?', 'rcp' ); ?></p>
							</td>
						</tr>
						<?php $gateways = rcp_get_payment_gateways(); ?>
						<?php if ( count( $gateways ) > 1 ) : ?>
						<tr valign="top">
							<th>
								<h3><?php _e( 'Gateways', 'rcp' ); ?></h3>
							</th>
							<td>
								<?php _e( 'Check each of the payment gateways you would like to enable. Configure the selected gateways below.', 'rcp' ); ?>
							</td>
						</tr>
						<tr valign="top">
							<th><span><?php _e( 'Enabled Gateways', 'rcp' ); ?></span></th>
							<td>
								<?php
									$gateways = rcp_get_payment_gateways();

									foreach( $gateways as $key => $gateway ) :

										$label = $gateway;

										if( is_array( $gateway ) ) {
											$label = $gateway['admin_label'];
										}

										echo '<input name="rcp_settings[gateways][' . $key . ']" id="rcp_settings[gateways][' . $key . ']" type="checkbox" value="1" ' . checked( true, isset( $rcp_options['gateways'][ $key ] ), false) . '/>&nbsp;';
										echo '<label for="rcp_settings[gateways][' . $key . ']">' . $label . '</label><br/>';
									endforeach;
								?>
							</td>
						</tr>
						<?php endif; ?>
						<tr valign="top">
							<th>
								<label for="rcp_settings[sandbox]"><?php _e( 'Sandbox Mode', 'rcp' ); ?></label>
							</th>
							<td>
								<input type="checkbox" value="1" name="rcp_settings[sandbox]" id="rcp_settings[sandbox]" <?php if( isset( $rcp_options['sandbox'] ) ) checked('1', $rcp_options['sandbox']); ?>/>
								<span class="description"><?php _e( 'Use Restrict Content Pro in Sandbox mode. This allows you to test the plugin with test accounts from your payment processor.', 'rcp' ); ?></span>
							</td>
						</tr>
						<?php if( ! function_exists( 'rcp_register_stripe_gateway' ) ) : ?>
						<tr valign="top">
							<th colspan=2>
								<h3><?php _e('Stripe Settings', 'rcp'); ?></h3>
							</th>
						</tr>
						<tr>
							<th>
								<label for="rcp_settings[stripe_test_publishable]"><?php _e( 'Test Publishable Key', 'rcp' ); ?></label>
							</th>
							<td>
								<input class="regular-text" id="rcp_settings[stripe_test_publishable]" style="width: 300px;" name="rcp_settings[stripe_test_publishable]" value="<?php if(isset($rcp_options['stripe_test_publishable'])) { echo $rcp_options['stripe_test_publishable']; } ?>" placeholder="pk_test_xxxxxxxx"/>
								<p class="description"><?php _e('Enter your test publishable key.', 'rcp'); ?></p>
							</td>
						</tr>
						<tr>
							<th>
								<label for="rcp_settings[stripe_test_secret]"><?php _e( 'Test Secret Key', 'rcp' ); ?></label>
							</th>
							<td>
								<input class="regular-text" id="rcp_settings[stripe_test_secret]" style="width: 300px;" name="rcp_settings[stripe_test_secret]" value="<?php if(isset($rcp_options['stripe_test_secret'])) { echo $rcp_options['stripe_test_secret']; } ?>" placeholder="sk_test_xxxxxxxx"/>
								<p class="description"><?php _e('Enter your test secret key. Your API keys can be obtained from your <a href="https://dashboard.stripe.com/account/apikeys" target="_blank">Stripe account settings</a>.', 'rcp'); ?></p>
							</td>
						</tr>
						<tr>
							<th>
								<label for="rcp_settings[stripe_live_publishable]"><?php _e( 'Live Publishable Key', 'rcp' ); ?></label>
							</th>
							<td>
								<input class="regular-text" id="rcp_settings[stripe_live_publishable]" style="width: 300px;" name="rcp_settings[stripe_live_publishable]" value="<?php if(isset($rcp_options['stripe_live_publishable'])) { echo $rcp_options['stripe_live_publishable']; } ?>" placeholder="pk_live_xxxxxxxx"/>
								<p class="description"><?php _e('Enter your live publishable key.', 'rcp'); ?></p>
							</td>
						</tr>
						<tr>
							<th>
								<label for="rcp_settings[stripe_live_secret]"><?php _e( 'Live Secret Key', 'rcp' ); ?></label>
							</th>
							<td>
								<input class="regular-text" id="rcp_settings[stripe_live_secret]" style="width: 300px;" name="rcp_settings[stripe_live_secret]" value="<?php if(isset($rcp_options['stripe_live_secret'])) { echo $rcp_options['stripe_live_secret']; } ?>" placeholder="sk_live_xxxxxxxx"/>
								<p class="description"><?php _e('Enter your live secret key.', 'rcp'); ?></p>
							</td>
						</tr>
						<tr>
							<th colspan=2>
								<p><strong><?php _e('Note', 'rcp'); ?></strong>: <?php _e('in order for subscription payments made through Stripe to be tracked, you must enter the following URL to your <a href="https://dashboard.stripe.com/account/webhooks" target="_blank">Stripe Webhooks</a> under Account Settings:', 'rcp'); ?></p>
								<p><strong><?php echo esc_url( add_query_arg( 'listener', 'stripe', home_url() ) ); ?></strong></p>
							</th>
						</tr>
						<?php endif; ?>
						<tr valign="top">
							<th colspan=2><h3><?php _e( 'PayPal Settings', 'rcp' ); ?></h3></th>
						</tr>
						<tr valign="top">
							<th>
								<label for="rcp_settings[paypal_email]"><?php _e( 'PayPal Address', 'rcp' ); ?></label>
							</th>
							<td>
								<input class="regular-text" id="rcp_settings[paypal_email]" style="width: 300px;" name="rcp_settings[paypal_email]" value="<?php if( isset( $rcp_options['paypal_email'] ) ) { echo $rcp_options['paypal_email']; } ?>"/>
								<p class="description"><?php _e( 'Enter your PayPal email address.', 'rcp' ); ?></p>
							</td>
						</tr>
						<tr>
							<th><?php _e( 'PayPal API Credentials', 'rcp' ); ?></th>
							<td>
								<p><?php _e( 'The PayPal API credentials are required in order to use PayPal Express, PayPal Pro, and to support advanced subscription cancellation options in PayPal Standard. Test API credentials can be obtained at <a href="http://docs.restrictcontentpro.com/article/1548-setting-up-paypal-sandbox-accounts" target="_blank">developer.paypal.com</a>.', 'rcp' ); ?></p>
							</td>
						</tr>
						<?php if( ! function_exists( 'rcp_register_paypal_pro_express_gateway' ) ) : ?>
						<tr>
							<th>
								<label for="rcp_settings[test_paypal_api_username]"><?php _e( 'Test API Username', 'rcp' ); ?></label>
							</th>
							<td>
								<input class="regular-text" id="rcp_settings[test_paypal_api_username]" style="width: 300px;" name="rcp_settings[test_paypal_api_username]" value="<?php if(isset($rcp_options['test_paypal_api_username'])) { echo trim( $rcp_options['test_paypal_api_username'] ); } ?>"/>
								<p class="description"><?php _e('Enter your test API username.', 'rcp'); ?></p>
							</td>
						</tr>
						<tr>
							<th>
								<label for="rcp_settings[test_paypal_api_password]"><?php _e( 'Test API Password', 'rcp' ); ?></label>
							</th>
							<td>
								<input class="regular-text" id="rcp_settings[test_paypal_api_password]" style="width: 300px;" name="rcp_settings[test_paypal_api_password]" value="<?php if(isset($rcp_options['test_paypal_api_password'])) { echo trim( $rcp_options['test_paypal_api_password'] ); } ?>"/>
								<p class="description"><?php _e('Enter your test API password.', 'rcp'); ?></p>
							</td>
						</tr>
						<tr>
							<th>
								<label for="rcp_settings[test_paypal_api_signature]"><?php _e( 'Test API Signature', 'rcp' ); ?></label>
							</th>
							<td>
								<input class="regular-text" id="rcp_settings[test_paypal_api_signature]" style="width: 300px;" name="rcp_settings[test_paypal_api_signature]" value="<?php if(isset($rcp_options['test_paypal_api_signature'])) { echo trim( $rcp_options['test_paypal_api_signature'] ); } ?>"/>
								<p class="description"><?php _e('Enter your test API signature.', 'rcp'); ?></p>
							</td>
						</tr>
						<tr>
							<th>
								<label for="rcp_settings[live_paypal_api_username]"><?php _e( 'Live API Username', 'rcp' ); ?></label>
							</th>
							<td>
								<input class="regular-text" id="rcp_settings[live_paypal_api_username]" style="width: 300px;" name="rcp_settings[live_paypal_api_username]" value="<?php if(isset($rcp_options['live_paypal_api_username'])) { echo trim( $rcp_options['live_paypal_api_username'] ); } ?>"/>
								<p class="description"><?php _e('Enter your live API username.', 'rcp'); ?></p>
							</td>
						</tr>
						<tr>
							<th>
								<label for="rcp_settings[live_paypal_api_password]"><?php _e( 'Live API Password', 'rcp' ); ?></label>
							</th>
							<td>
								<input class="regular-text" id="rcp_settings[live_paypal_api_password]" style="width: 300px;" name="rcp_settings[live_paypal_api_password]" value="<?php if(isset($rcp_options['live_paypal_api_password'])) { echo trim( $rcp_options['live_paypal_api_password'] ); } ?>"/>
								<p class="description"><?php _e('Enter your live API password.', 'rcp'); ?></p>
							</td>
						</tr>
						<tr>
							<th>
								<label for="rcp_settings[live_paypal_api_signature]"><?php _e( 'Live API Signature', 'rcp' ); ?></label>
							</th>
							<td>
								<input class="regular-text" id="rcp_settings[live_paypal_api_signature]" style="width: 300px;" name="rcp_settings[live_paypal_api_signature]" value="<?php if(isset($rcp_options['live_paypal_api_signature'])) { echo trim( $rcp_options['live_paypal_api_signature'] ); } ?>"/>
								<p class="description"><?php _e('Enter your live API signature.', 'rcp'); ?></p>
							</td>
						</tr>
						<?php endif; ?>
						<tr valign="top">
							<th>
								<label for="rcp_settings[paypal_page_style]"><?php _e( 'PayPal Page Style', 'rcp' ); ?></label>
							</th>
							<td>
								<input class="regular-text" id="rcp_settings[paypal_page_style]" style="width: 300px;" name="rcp_settings[paypal_page_style]" value="<?php if( isset( $rcp_options['paypal_page_style'] ) ) { echo trim( $rcp_options['paypal_page_style'] ); } ?>"/>
								<p class="description"><?php _e( 'Enter the PayPal page style name you wish to use, or leave blank for default.', 'rcp' ); ?></p>
							</td>
						</tr>
						<tr valign="top">
							<th>
								<label for="rcp_settings[disable_curl]"><?php _e( 'Disable CURL', 'rcp' ); ?></label>
							</th>
							<td>
								<input type="checkbox" value="1" name="rcp_settings[disable_curl]" id="rcp_settings[disable_curl]" <?php if( isset( $rcp_options['disable_curl'] ) ) checked('1', $rcp_options['disable_curl']); ?>/>
								<span class="description"><?php _e( 'Only check this option if your host does not allow cURL.', 'rcp' ); ?></span>
							</td>
						</tr>
						<tr valign="top">
							<th>
								<label for="rcp_settings[disable_ipn_verify]"><?php _e( 'Disable IPN Verification', 'rcp' ); ?></label>
							</th>
							<td>
								<input type="checkbox" value="1" name="rcp_settings[disable_ipn_verify]" id="rcp_settings[disable_ipn_verify]" <?php if( isset( $rcp_options['disable_ipn_verify'] ) ) checked('1', $rcp_options['disable_ipn_verify']); ?>/>
								<span class="description"><?php _e( 'Only check this option if your members statuses are not getting changed to "active".', 'rcp' ); ?></span>
							</td>
						</tr>
						<tr valign="top">
							<th colspan=2>
								<h3><?php _e('2Checkout Settings', 'rcp'); ?></h3>
							</th>
						</tr>
						<?php // 2checkout Secret Word ?>
						<tr>
							<th>
								<label for="rcp_settings[twocheckout_secret_word]"><?php _e( 'Secret Word', 'rcp' ); ?></label>
							</th>
							<td>
								<input class="regular-text" id="rcp_settings[twocheckout_secret_word]" style="width: 300px;" name="rcp_settings[twocheckout_secret_word]" value="<?php if(isset($rcp_options['twocheckout_secret_word'])) { echo $rcp_options['twocheckout_secret_word']; } ?>"/>
								<p class="description"><?php _e('Enter your secret word. This can be obtained from the <a href="https://sandbox.2checkout.com/sandbox/acct/detail_company_info" target="_blank">2Checkout Sandbox</a>.', 'rcp'); ?></p>
							</td>
						</tr>
						<?php // 2checkout Test Private Key ?>
						<tr>
							<th>
								<label for="rcp_settings[twocheckout_test_private]"><?php _e( 'Test Private Key', 'rcp' ); ?></label>
							</th>
							<td>
								<input class="regular-text" id="rcp_settings[twocheckout_test_private]" style="width: 300px;" name="rcp_settings[twocheckout_test_private]" value="<?php if(isset($rcp_options['twocheckout_test_private'])) { echo $rcp_options['twocheckout_test_private']; } ?>"/>
								<p class="description"><?php _e('Enter your test private key. Your test API keys can be obtained from the <a href="https://sandbox.2checkout.com/sandbox/api" target="_blank">2Checkout Sandbox</a>.', 'rcp'); ?></p>
							</td>
						</tr>
						<?php // 2checkout Test Publishable Key ?>
						<tr>
							<th>
								<label for="rcp_settings[twocheckout_test_publishable]"><?php _e( 'Test Publishable Key', 'rcp' ); ?></label>
							</th>
							<td>
								<input class="regular-text" id="rcp_settings[twocheckout_test_publishable]" style="width: 300px;" name="rcp_settings[twocheckout_test_publishable]" value="<?php if(isset($rcp_options['twocheckout_test_publishable'])) { echo $rcp_options['twocheckout_test_publishable']; } ?>"/>
								<p class="description"><?php _e('Enter your test publishable key.', 'rcp'); ?></p>
							</td>
						</tr>
						<?php // 2checkout Test Seller ID ?>
						<tr>
							<th>
								<label for="rcp_settings[twocheckout_test_seller_id]"><?php _e( 'Test Seller ID', 'rcp' ); ?></label>
							</th>
							<td>
								<input class="regular-text" id="rcp_settings[twocheckout_test_seller_id]" style="width: 300px;" name="rcp_settings[twocheckout_test_seller_id]" value="<?php if(isset($rcp_options['twocheckout_test_seller_id'])) { echo $rcp_options['twocheckout_test_seller_id']; } ?>"/>
								<p class="description"><?php _e('Enter your test Seller ID. <a href="http://help.2checkout.com/articles/FAQ/Where-is-my-Seller-ID" target="_blank">Where is my Seller ID?</a>.', 'rcp'); ?></p>
							</td>
						</tr>
						<?php // 2checkout Live Private Key ?>
						<tr>
							<th>
								<label for="rcp_settings[twocheckout_live_private]"><?php _e( 'Live Private Key', 'rcp' ); ?></label>
							</th>
							<td>
								<input class="regular-text" id="rcp_settings[twocheckout_live_private]" style="width: 300px;" name="rcp_settings[twocheckout_live_private]" value="<?php if(isset($rcp_options['twocheckout_live_private'])) { echo $rcp_options['twocheckout_live_private']; } ?>"/>
								<p class="description"><?php _e('Enter your live secret key. Your API keys can be obtained from the <a href="https://pci.trustwave.com/2checkout" target="_blank">2Checkout PCI Program</a>.', 'rcp'); ?></p>
							</td>
						</tr>
						<?php // 2checkout Live Publishable Key ?>
						<tr>
							<th>
								<label for="rcp_settings[twocheckout_live_publishable]"><?php _e( 'Live Publishable Key', 'rcp' ); ?></label>
							</th>
							<td>
								<input class="regular-text" id="rcp_settings[twocheckout_live_publishable]" style="width: 300px;" name="rcp_settings[twocheckout_live_publishable]" value="<?php if(isset($rcp_options['twocheckout_live_publishable'])) { echo $rcp_options['twocheckout_live_publishable']; } ?>"/>
								<p class="description"><?php _e('Enter your live publishable key.', 'rcp'); ?></p>
							</td>
						</tr>
						<?php // 2checkout Live Seller ID ?>
						<tr>
							<th>
								<label for="rcp_settings[twocheckout_live_seller_id]"><?php _e( 'Live Seller ID', 'rcp' ); ?></label>
							</th>
							<td>
								<input class="regular-text" id="rcp_settings[twocheckout_live_seller_id]" style="width: 300px;" name="rcp_settings[twocheckout_live_seller_id]" value="<?php if(isset($rcp_options['twocheckout_live_seller_id'])) { echo $rcp_options['twocheckout_live_seller_id']; } ?>"/>
								<p class="description"><?php _e('Enter your live Seller ID. <a href="http://help.2checkout.com/articles/FAQ/Where-is-my-Seller-ID" target="_blank">Where is my Seller ID?</a>.', 'rcp'); ?></p>
							</td>
						</tr>

						<tr valign="top">
							<th colspan=2>
								<h3><?php _e('Authorize.net Settings', 'rcp'); ?></h3>
							</th>
						</tr>
						<?php // Authorize.net Test Login ID ?>
						<tr>
							<th>
								<label for="rcp_settings[authorize_test_api_login]"><?php _e( 'Test API Login ID', 'rcp' ); ?></label>
							</th>
							<td>
								<input class="regular-text" id="rcp_settings[authorize_test_api_login]" style="width: 300px;" name="rcp_settings[authorize_test_api_login]" value="<?php if(isset($rcp_options['authorize_test_api_login'])) { echo esc_attr( $rcp_options['authorize_test_api_login'] ); } ?>"/>
								<p class="description"><?php _e('Enter your authorize.net test API login ID.', 'rcp'); ?></p>
							</td>
						</tr>
						<?php // Authorize.net Test Transaction Key ?>
						<tr>
							<th>
								<label for="rcp_settings[authorize_test_txn_key]"><?php _e( 'Test Transaction Key', 'rcp' ); ?></label>
							</th>
							<td>
								<input class="regular-text" id="rcp_settings[authorize_test_txn_key]" style="width: 300px;" name="rcp_settings[authorize_test_txn_key]" value="<?php if(isset($rcp_options['authorize_test_txn_key'])) { echo esc_attr( $rcp_options['authorize_test_txn_key'] ); } ?>"/>
								<p class="description"><?php _e('Enter your authorize.net test transaction key', 'rcp'); ?></p>
							</td>
						</tr>
						<?php // Authorize.net Live Login ID ?>
						<tr>
							<th>
								<label for="rcp_settings[authorize_api_login]"><?php _e( 'Live API Login ID', 'rcp' ); ?></label>
							</th>
							<td>
								<input class="regular-text" id="rcp_settings[authorize_api_login]" style="width: 300px;" name="rcp_settings[authorize_api_login]" value="<?php if(isset($rcp_options['authorize_api_login'])) { echo esc_attr( $rcp_options['authorize_api_login'] ); } ?>"/>
								<p class="description"><?php _e('Enter your authorize.net live API login ID.', 'rcp'); ?></p>
							</td>
						</tr>
						<?php // Authorize.net Live Transaction Key ?>
						<tr>
							<th>
								<label for="rcp_settings[authorize_txn_key]"><?php _e( 'Live Transaction Key', 'rcp' ); ?></label>
							</th>
							<td>
								<input class="regular-text" id="rcp_settings[authorize_txn_key]" style="width: 300px;" name="rcp_settings[authorize_txn_key]" value="<?php if(isset($rcp_options['authorize_txn_key'])) { echo  esc_attr( $rcp_options['authorize_txn_key'] ); } ?>"/>
								<p class="description"><?php _e('Enter your authorize.net live transaction key', 'rcp'); ?></p>
							</td>
						</tr>
						<?php // Authorize.net MD5 Hash ?>
						<tr>
							<th>
								<label for="rcp_settings[authorize_hash_value]"><?php _e( 'MD5-Hash Verification Key', 'rcp' ); ?></label>
							</th>
							<td>
								<input class="regular-text" id="rcp_settings[authorize_hash_value]" style="width: 300px;" name="rcp_settings[authorize_hash_value]" value="<?php if(isset($rcp_options['authorize_hash_value'])) { echo esc_attr( $rcp_options['authorize_hash_value'] ); } ?>"/>
								<p class="description"><?php _e('Enter the MD5 Hash verification key for your Silent Post URL.', 'rcp'); ?></p>
							</td>
						</tr>

						<tr valign="top">
							<th colspan=2>
								<h3><?php _e('Braintree Settings', 'rcp'); ?></h3>
							</th>
						</tr>

						<?php // Braintree Live Merchant ID ?>
						<tr>
							<th>
								<label for="rcp_settings[braintree_live_merchantId]"><?php _e( 'Live Merchant ID', 'rcp' ); ?></label>
							</th>
							<td>
								<input class="regular-text" id="rcp_settings[braintree_live_merchantId]" style="width: 300px;" name="rcp_settings[braintree_live_merchantId]" value="<?php if(isset($rcp_options['braintree_live_merchantId'])) { echo esc_attr( $rcp_options['braintree_live_merchantId'] ); } ?>"/>
								<p class="description"><?php _e('Enter your Braintree live merchant ID.', 'rcp'); ?></p>
							</td>
						</tr>
						<?php // Braintree Live Public Key ?>
						<tr>
							<th>
								<label for="rcp_settings[braintree_live_publicKey]"><?php _e( 'Live Public Key', 'rcp' ); ?></label>
							</th>
							<td>
								<input class="regular-text" id="rcp_settings[braintree_live_publicKey]" style="width: 300px;" name="rcp_settings[braintree_live_publicKey]" value="<?php if(isset($rcp_options['braintree_live_publicKey'])) { echo esc_attr( $rcp_options['braintree_live_publicKey'] ); } ?>"/>
								<p class="description"><?php _e('Enter your Braintree live public key.', 'rcp'); ?></p>
							</td>
						</tr>
						<?php // Braintree Live Private Key ?>
						<tr>
							<th>
								<label for="rcp_settings[braintree_live_privateKey]"><?php _e( 'Live Private Key', 'rcp' ); ?></label>
							</th>
							<td>
								<input class="regular-text" id="rcp_settings[braintree_live_privateKey]" style="width: 300px;" name="rcp_settings[braintree_live_privateKey]" value="<?php if(isset($rcp_options['braintree_live_privateKey'])) { echo esc_attr( $rcp_options['braintree_live_privateKey'] ); } ?>"/>
								<p class="description"><?php _e('Enter your Braintree live private key.', 'rcp'); ?></p>
							</td>
						</tr>
						<?php // Braintree Live Encryption Key ?>
						<tr>
							<th>
								<label for="rcp_settings[braintree_live_encryptionKey]"><?php _e( 'Live Client Side Encryption Key', 'rcp' ); ?></label>
							</th>
							<td>
								<textarea class="regular-text" id="rcp_settings[braintree_live_encryptionKey]" style="width: 300px;height: 100px;" name="rcp_settings[braintree_live_encryptionKey]"/><?php if(isset($rcp_options['braintree_live_encryptionKey'])) { echo  esc_attr( $rcp_options['braintree_live_encryptionKey'] ); } ?></textarea>
								<p class="description"><?php _e('Enter your Braintree live client side encryption key.', 'rcp'); ?></p>
							</td>
						</tr>

						<?php // Braintree Sandbox Merchant ID ?>
						<tr>
							<th>
								<label for="rcp_settings[braintree_sandbox_merchantId]"><?php _e( 'Sandbox Merchant ID', 'rcp' ); ?></label>
							</th>
							<td>
								<input class="regular-text" id="rcp_settings[braintree_sandbox_merchantId]" style="width: 300px;" name="rcp_settings[braintree_sandbox_merchantId]" value="<?php if(isset($rcp_options['braintree_sandbox_merchantId'])) { echo esc_attr( $rcp_options['braintree_sandbox_merchantId'] ); } ?>"/>
								<p class="description"><?php _e('Enter your Braintree sandbox merchant ID.', 'rcp'); ?></p>
							</td>
						</tr>
						<?php // Braintree Sandbox Public Key ?>
						<tr>
							<th>
								<label for="rcp_settings[braintree_sandbox_publicKey]"><?php _e( 'Sandbox Public Key', 'rcp' ); ?></label>
							</th>
							<td>
								<input class="regular-text" id="rcp_settings[braintree_sandbox_publicKey]" style="width: 300px;" name="rcp_settings[braintree_sandbox_publicKey]" value="<?php if(isset($rcp_options['braintree_sandbox_publicKey'])) { echo esc_attr( $rcp_options['braintree_sandbox_publicKey'] ); } ?>"/>
								<p class="description"><?php _e('Enter your Braintree sandbox public key.', 'rcp'); ?></p>
							</td>
						</tr>
						<?php // Braintree Sandbox Private Key ?>
						<tr>
							<th>
								<label for="rcp_settings[braintree_sandbox_privateKey]"><?php _e( 'Sandbox Private Key', 'rcp' ); ?></label>
							</th>
							<td>
								<input class="regular-text" id="rcp_settings[braintree_sandbox_privateKey]" style="width: 300px;" name="rcp_settings[braintree_sandbox_privateKey]" value="<?php if(isset($rcp_options['braintree_sandbox_privateKey'])) { echo esc_attr( $rcp_options['braintree_sandbox_privateKey'] ); } ?>"/>
								<p class="description"><?php _e('Enter your Braintree sandbox private key.', 'rcp'); ?></p>
							</td>
						</tr>
						<?php // Braintree Sandbox Encryption Key ?>
						<tr>
							<th>
								<label for="rcp_settings[braintree_sandbox_encryptionKey]"><?php _e( 'Sandbox Client Side Encryption Key', 'rcp' ); ?></label>
							</th>
							<td>
								<textarea class="regular-text" id="rcp_settings[braintree_sandbox_encryptionKey]" style="width: 300px;height: 100px;" name="rcp_settings[braintree_sandbox_encryptionKey]"/><?php if ( isset( $rcp_options['braintree_sandbox_encryptionKey'] ) ) { echo esc_attr( $rcp_options['braintree_sandbox_encryptionKey'] ); } ?></textarea>
								<p class="description"><?php _e('Enter your Braintree sandbox client side encryption key.', 'rcp'); ?></p>
							</td>
						</tr>

					</table>
					<?php do_action( 'rcp_payments_settings', $rcp_options ); ?>

				</div><!--end #payments-->

				<div class="tab_content" id="emails">
					<div id="rcp_email_options">

						<table class="form-table">
							<tr>
								<th colspan=2><h3><?php _e( 'General', 'rcp' ); ?></h3></th>
							</tr>
							<tr>
								<th>
									<label for="rcp_settings[email_template]"><?php _e( 'Template', 'rcp' ); ?></label>
								</th>
								<td>
									<?php $emails = new RCP_Emails; $selected_template = isset( $rcp_options['email_template'] ) ? $rcp_options['email_template'] : ''; ?>
									<select id="rcp_settings[email_template]" name="rcp_settings[email_template]">
										<?php foreach( $emails->get_templates() as $id => $template ) : ?>
											<option value="<?php echo esc_attr( $id ); ?>"<?php selected( $id, $selected_template ); ?>><?php echo $template; ?></option>
										<?php endforeach; ?>
									</select>
									<p class="description"><?php _e( 'Select the template used for email design.', 'rcp' ); ?></p>
								</td>
							</tr>
							<tr>
								<th>
									<label for="rcp_settings[email_header_text]"><?php _e( 'Email Header', 'rcp' ); ?></label>
								</th>
								<td>
									<input class="regular-text" id="rcp_settings[email_header_text]" style="width: 300px;" name="rcp_settings[email_header_text]" value="<?php echo esc_attr( $rcp_options['email_header_text'] ); ?>"/>
									<p class="description"><?php _e( 'Text shown at top of email notifications.', 'rcp' ); ?></p>
								</td>
							</tr>
							<tr>
								<th>
									<label for="rcp_settings[email_header_img]"><?php _e( 'Email Logo', 'rcp' ); ?></label>
								</th>
								<td>
									<input class="regular-text rcp-upload-field" id="rcp_settings[email_header_img]" style="width: 300px;" name="rcp_settings[email_header_img]" value="<?php echo esc_attr( $rcp_options['email_header_img'] ); ?>"/>
									<button class="rcp-upload button"><?php _e( 'Choose Image', 'rcp' ); ?></button>
									<p class="description"><?php _e( 'Image shown at top of email notifications.', 'rcp' ); ?></p>
								</td>
							</tr>
							<tr>
								<th>
									<label for="rcp_settings[from_name]"><?php _e( 'From Name', 'rcp' ); ?></label>
								</th>
								<td>
									<input class="regular-text" id="rcp_settings[from_name]" style="width: 300px;" name="rcp_settings[from_name]" value="<?php if( isset( $rcp_options['from_name'] ) ) { echo $rcp_options['from_name']; } else { echo get_bloginfo( 'name' ); } ?>"/>
									<p class="description"><?php _e( 'The name that emails come from. This is usually the name of your business.', 'rcp' ); ?></p>
								</td>
							</tr>
							<tr>
								<th>
									<label for="rcp_settings[from_email]"><?php _e( 'From Email', 'rcp' ); ?></label>
								</th>
								<td>
									<input class="regular-text" id="rcp_settings[from_email]" style="width: 300px;" name="rcp_settings[from_email]" value="<?php if( isset( $rcp_options['from_email'] ) ) { echo $rcp_options['from_email']; } else { echo get_bloginfo( 'admin_email' ); } ?>"/>
									<p class="description"><?php _e( 'The email address that emails are sent from.', 'rcp' ); ?></p>
								</td>
							</tr>
							<tr>
								<th>
									<label for="rcp_settings[admin_notice_emails]"><?php _e( 'Admin Notification Email', 'rcp' ); ?></label>
								</th>
								<td>
									<input class="regular-text" id="rcp_settings[admin_notice_emails]" style="width: 300px;" name="rcp_settings[admin_notice_emails]" value="<?php if( isset( $rcp_options['admin_notice_emails'] ) ) { echo $rcp_options['admin_notice_emails']; } else { echo get_bloginfo( 'admin_email' ); } ?>"/>
									<p class="description"><?php _e( 'Admin notices are sent to this email address. Separate multiple emails with a comma.', 'rcp' ); ?></p>
								</td>
							</tr>

							<tr valign="top">
								<th>
									<label for="rcp_settings[email_verification]"><?php _e( 'Email Verification', 'rcp' ); ?></label>
								</th>
								<td>
									<?php $verify = isset( $rcp_options['email_verification'] ) ? $rcp_options['email_verification'] : 'off'; ?>
									<select id="rcp_settings[email_verification]" name="rcp_settings[email_verification]" class="rcp-disable-email">
										<option value="off" <?php selected( $verify, 'off' ); ?>><?php _e( 'Off', 'rcp' ); ?></option>
										<option value="free" <?php selected( $verify, 'free' ); ?>><?php _e( 'On for free subscription levels', 'rcp' ); ?></option>
										<option value="all" <?php selected( $verify, 'all' ); ?>><?php _e( 'On for all subscription levels', 'rcp' ); ?></option>
									</select>
									<span alt="f223" class="rcp-help-tip dashicons dashicons-editor-help" title="<?php esc_attr_e( 'If "On for free subscription levels" is chosen, memberships with a 0 price in the level settings will require email verification. This does not include registrations that have been made free with a discount code or credits.', 'rcp' ); ?>"></span>
									<p class="description"><?php _e( 'Require that new members verify their email address before gaining access to restricted content.', 'rcp' ); ?></p>
								</td>
							</tr>
							<tr valign="top"<?php echo ( ! isset( $rcp_options['email_verification'] ) || 'off' == $rcp_options['email_verification'] ) ? ' style="display: none;"' : ''; ?>>
								<th>
									<label for="rcp_settings[verification_subject]"><?php _e( 'Email Verification Subject', 'rcp' ); ?></label>
								</th>
								<td>
									<input class="regular-text" id="rcp_settings[verification_subject]" style="width: 300px;" name="rcp_settings[verification_subject]" value="<?php echo ! empty( $rcp_options['verification_subject'] ) ? esc_attr( $rcp_options['verification_subject'] ) : esc_attr__( 'Please confirm your email address', 'rcp' ); ?>"/>
									<p class="description"><?php _e( 'The subject line for the email verification message.', 'rcp' ); ?></p>
								</td>
							</tr>
							<tr valign="top"<?php echo ( ! isset( $rcp_options['email_verification'] ) || 'off' == $rcp_options['email_verification'] ) ? ' style="display: none;"' : ''; ?>>
								<th>
									<label for="rcp_settings[verification_email]"><?php _e( 'Email Verification Body', 'rcp' ); ?></label>
								</th>
								<td>
									<?php
									$verification_email = isset( $rcp_options['verification_email'] ) ? wptexturize( $rcp_options['verification_email'] ) : sprintf( __( 'Click here to confirm your email address and activate your account: %s', 'rcp' ), '%verificationlink%' );
									wp_editor( $verification_email, 'rcp_settings_verification_email', array( 'textarea_name' => 'rcp_settings[verification_email]', 'teeny' => true ) );
									?>
									<p class="description"><?php printf( __( 'This is the message for the verification email. Use the %s template tag for the verification URL.', 'rcp' ), '<code>%verificationlink%</code>' ); ?></p>
								</td>
							</tr>

							<tr>
								<th>
									<label><?php _e( 'Available Template Tags', 'rcp' ); ?></label>
								</th>
								<td>
									<p class="description"><?php _e( 'The following template tags are available for use in all of the email settings below.', 'rcp' ); ?></p>
									<?php echo rcp_get_emails_tags_list(); ?>
								</td>
							</tr>
							<tr>
								<th colspan=2><h3><?php _e( 'Active Subscription Email', 'rcp' ); ?></h3></th>
							</tr>
							<tr>
								<th>
									<label for="rcp_settings[disable_active_email]"><?php _e( 'Disable for Member', 'rcp' ); ?></label>
								</th>
								<td>
									<input type="checkbox" value="1" name="rcp_settings[disable_active_email]" id="rcp_settings[disable_active_email]" class="rcp-disable-email" <?php checked( true, isset( $rcp_options['disable_active_email'] ) ); ?>/>
									<span><?php _e( 'Check this to disable the email sent out to the member when their subscription becomes active.', 'rcp' ); ?></span>
								</td>
							</tr>
							<tr<?php echo ( isset( $rcp_options['disable_active_email'] ) ) ? ' style="display: none;"' : ''; ?>>
								<th>
									<label for="rcp_settings[active_subject]"><?php _e( 'Member Subject', 'rcp' ); ?></label>
								</th>
								<td>
									<input class="regular-text" id="rcp_settings[active_subject]" style="width: 300px;" name="rcp_settings[active_subject]" value="<?php if( isset( $rcp_options['active_subject'] ) ) { echo $rcp_options['active_subject']; } ?>"/>
									<p class="description"><?php _e( 'The subject line for the email sent to users when their subscription becomes active.', 'rcp' ); ?></p>
								</td>
							</tr>
							<tr valign="top"<?php echo ( isset( $rcp_options['disable_active_email'] ) ) ? ' style="display: none;"' : ''; ?>>
								<th>
									<label for="rcp_settings[active_email]"><?php _e( 'Member Email Body', 'rcp' ); ?></label>
								</th>
								<td>
									<?php
									$active_email = isset( $rcp_options['active_email'] ) ? wptexturize( $rcp_options['active_email'] ) : '';
									wp_editor( $active_email, 'rcp_settings_active_email', array( 'textarea_name' => 'rcp_settings[active_email]', 'teeny' => true ) );
									?>
									<p class="description"><?php _e( 'This is the email message that is sent to users when their subscription becomes active.', 'rcp' ); ?></p>
								</td>
							</tr>
							<tr>
								<th>
									<label for="rcp_settings[disable_active_email_admin]"><?php _e( 'Disable for Admin', 'rcp' ); ?></label>
								</th>
								<td>
									<input type="checkbox" value="1" name="rcp_settings[disable_active_email_admin]" id="rcp_settings[disable_active_email_admin]" class="rcp-disable-email" <?php checked( true, isset( $rcp_options['disable_active_email_admin'] ) ); ?>/>
									<span><?php _e( 'Check this to disable the email sent out to the administrator when a new member becomes active.', 'rcp' ); ?></span>
								</td>
							</tr>
							<tr<?php echo ( isset( $rcp_options['disable_active_email_admin'] ) ) ? ' style="display: none;"' : ''; ?>>
								<th>
									<label for="rcp_settings[active_subject_admin]"><?php _e( 'Admin Subject', 'rcp' ); ?></label>
								</th>
								<td>
									<input class="regular-text" id="rcp_settings[active_subject_admin]" style="width: 300px;" name="rcp_settings[active_subject_admin]" value="<?php if( isset( $rcp_options['active_subject_admin'] ) ) { echo $rcp_options['active_subject_admin']; } ?>"/>
									<p class="description"><?php _e( 'The subject line for the email sent to the admin when a member\'s subscription becomes active.', 'rcp' ); ?></p>
								</td>
							</tr>
							<tr valign="top"<?php echo ( isset( $rcp_options['disable_active_email_admin'] ) ) ? ' style="display: none;"' : ''; ?>>
								<th>
									<label for="rcp_settings[active_email_admin]"><?php _e( 'Admin Email Body', 'rcp' ); ?></label>
								</th>
								<td>
									<?php
									$active_email = isset( $rcp_options['active_email_admin'] ) ? wptexturize( $rcp_options['active_email_admin'] ) : '';
									wp_editor( $active_email, 'rcp_settings_active_email_admin', array( 'textarea_name' => 'rcp_settings[active_email_admin]', 'teeny' => true ) );
									?>
									<p class="description"><?php _e( 'This is the email message that is sent to the admin when a member\'s subscription becomes active.', 'rcp' ); ?></p>
								</td>
							</tr>
							<tr valign="top">
								<th colspan=2>
									<h3><?php _e( 'Cancelled Subscription Email', 'rcp' ); ?></h3>
								</th>
							</tr>
							<tr>
								<th>
									<label for="rcp_settings[disable_cancelled_email]"><?php _e( 'Disable for Member', 'rcp' ); ?></label>
								</th>
								<td>
									<input type="checkbox" value="1" name="rcp_settings[disable_cancelled_email]" id="rcp_settings[disable_cancelled_email]" class="rcp-disable-email" <?php checked( true, isset( $rcp_options['disable_cancelled_email'] ) ); ?>/>
									<span><?php _e( 'Check this to disable the email sent to a member when their subscription is cancelled.', 'rcp' ); ?></span>
								</td>
							</tr>
							<tr valign="top"<?php echo ( isset( $rcp_options['disable_cancelled_email'] ) ) ? ' style="display: none;"' : ''; ?>>
								<th>
									<label for="rcp_settings[cancelled_subject]"><?php _e( 'Member Subject line', 'rcp' ); ?></label>
								</th>
								<td>
									<input class="regular-text" id="rcp_settings[cancelled_subject]" style="width: 300px;" name="rcp_settings[cancelled_subject]" value="<?php if( isset( $rcp_options['cancelled_subject'] ) ) { echo $rcp_options['cancelled_subject']; } ?>"/>
									<p class="description"><?php _e( 'The subject line for the email sent to users when their subscription is cancelled.', 'rcp' ); ?></p>
								</td>
							</tr>
							<tr valign="top"<?php echo ( isset( $rcp_options['disable_cancelled_email'] ) ) ? ' style="display: none;"' : ''; ?>>
								<th>
									<label for="rcp_settings[cancelled_email]"><?php _e( 'Member Email Body', 'rcp' ); ?></label>
								</th>
								<td>
									<?php
									$cancelled_email = isset( $rcp_options['cancelled_email'] ) ? wptexturize( $rcp_options['cancelled_email'] ) : '';
									wp_editor( $cancelled_email, 'rcp_settings_cancelled_email', array( 'textarea_name' => 'rcp_settings[cancelled_email]', 'teeny' => true ) );
									?>
									<p class="description"><?php _e( 'This is the email message that is sent to users when their subscription is cancelled.', 'rcp' ); ?></p>
								</td>
							</tr>
							<tr>
								<th>
									<label for="rcp_settings[disable_cancelled_email_admin]"><?php _e( 'Disable for Admin', 'rcp' ); ?></label>
								</th>
								<td>
									<input type="checkbox" value="1" name="rcp_settings[disable_cancelled_email_admin]" id="rcp_settings[disable_cancelled_email_admin]" class="rcp-disable-email" <?php checked( true, isset( $rcp_options['disable_cancelled_email_admin'] ) ); ?>/>
									<span><?php _e( 'Check this to disable the email sent to the administrator when a member\'s subscription is cancelled.', 'rcp' ); ?></span>
								</td>
							</tr>
							<tr valign="top"<?php echo ( isset( $rcp_options['disable_cancelled_email_admin'] ) ) ? ' style="display: none;"' : ''; ?>>
								<th>
									<label for="rcp_settings[cancelled_subject_admin]"><?php _e( 'Admin Subject line', 'rcp' ); ?></label>
								</th>
								<td>
									<input class="regular-text" id="rcp_settings[cancelled_subject_admin]" style="width: 300px;" name="rcp_settings[cancelled_subject_admin]" value="<?php if( isset( $rcp_options['cancelled_subject_admin'] ) ) { echo $rcp_options['cancelled_subject_admin']; } ?>"/>
									<p class="description"><?php _e( 'The subject line for the email sent to the admin when a member\'s subscription is cancelled.', 'rcp' ); ?></p>
								</td>
							</tr>
							<tr valign="top"<?php echo ( isset( $rcp_options['disable_cancelled_email_admin'] ) ) ? ' style="display: none;"' : ''; ?>>
								<th>
									<label for="rcp_settings[cancelled_email_admin]"><?php _e( 'Admin Email Body', 'rcp' ); ?></label>
								</th>
								<td>
									<?php
									$cancelled_email = isset( $rcp_options['cancelled_email_admin'] ) ? wptexturize( $rcp_options['cancelled_email_admin'] ) : '';
									wp_editor( $cancelled_email, 'rcp_settings_cancelled_email_admin', array( 'textarea_name' => 'rcp_settings[cancelled_email_admin]', 'teeny' => true ) );
									?>
									<p class="description"><?php _e( 'This is the email message that is sent to the admin when a member\'s subscription is cancelled.', 'rcp' ); ?></p>
								</td>
							</tr>
							<tr valign="top">
								<th colspan=2>
									<h3><?php _e( 'Expired Subscription Email', 'rcp' ); ?></h3>
								</th>
							</tr>
							<tr>
								<th>
									<label for="rcp_settings[disable_expired_email]"><?php _e( 'Disable for Member', 'rcp' ); ?></label>
								</th>
								<td>
									<input type="checkbox" value="1" name="rcp_settings[disable_expired_email]" id="rcp_settings[disable_expired_email]" class="rcp-disable-email" <?php checked( true, isset( $rcp_options['disable_expired_email'] ) ); ?>/>
									<span><?php _e( 'Check this to disable the email sent out to a member when their subscription expires.', 'rcp' ); ?></span>
								</td>
							</tr>
							<tr valign="top"<?php echo ( isset( $rcp_options['disable_expired_email'] ) ) ? ' style="display: none;"' : ''; ?>>
								<th>
									<label for="rcp_settings[expired_subject]"><?php _e( 'Member Subject', 'rcp' ); ?></label>
								</th>
								<td>
									<input class="regular-text" id="rcp_settings[expired_subject]" style="width: 300px;" name="rcp_settings[expired_subject]" value="<?php if( isset( $rcp_options['expired_subject'] ) ) { echo $rcp_options['expired_subject']; } ?>"/>
									<p class="description"><?php _e( 'The subject line for the email sent to users when their subscription is expired.', 'rcp' ); ?></p>
								</td>
							</tr>
							<tr valign="top"<?php echo ( isset( $rcp_options['disable_expired_email'] ) ) ? ' style="display: none;"' : ''; ?>>
								<th>
									<label for="rcp_settings[expired_email]"><?php _e( 'Member Email Body', 'rcp' ); ?></label>
								</th>
								<td>
									<?php
									$expired_email = isset( $rcp_options['expired_email'] ) ? wptexturize( $rcp_options['expired_email'] ) : '';
									wp_editor( $expired_email, 'rcp_settings_expired_email', array( 'textarea_name' => 'rcp_settings[expired_email]', 'teeny' => true ) );
									?>
									<p class="description"><?php _e( 'This is the email message that is sent to users when their subscription is expired.', 'rcp' ); ?></p>
								</td>
							</tr>
							<tr>
								<th>
									<label for="rcp_settings[disable_expired_email_admin]"><?php _e( 'Disable for Admin', 'rcp' ); ?></label>
								</th>
								<td>
									<input type="checkbox" value="1" name="rcp_settings[disable_expired_email_admin]" id="rcp_settings[disable_expired_email_admin]" class="rcp-disable-email" <?php checked( true, isset( $rcp_options['disable_expired_email_admin'] ) ); ?>/>
									<span><?php _e( 'Check this to disable the email sent to the administrator when a member\'s subscription expires.', 'rcp' ); ?></span>
								</td>
							</tr>
							<tr valign="top"<?php echo ( isset( $rcp_options['disable_expired_email_admin'] ) ) ? ' style="display: none;"' : ''; ?>>
								<th>
									<label for="rcp_settings[expired_subject_admin]"><?php _e( 'Admin Subject', 'rcp' ); ?></label>
								</th>
								<td>
									<input class="regular-text" id="rcp_settings[expired_subject_admin]" style="width: 300px;" name="rcp_settings[expired_subject_admin]" value="<?php if( isset( $rcp_options['expired_subject_admin'] ) ) { echo $rcp_options['expired_subject_admin']; } ?>"/>
									<p class="description"><?php _e( 'The subject line for the email sent to the admin when a member\'s subscription is expired.', 'rcp' ); ?></p>
								</td>
							</tr>
							<tr valign="top"<?php echo ( isset( $rcp_options['disable_expired_email_admin'] ) ) ? ' style="display: none;"' : ''; ?>>
								<th>
									<label for="rcp_settings[expired_email_admin]"><?php _e( 'Admin Email Body', 'rcp' ); ?></label>
								</th>
								<td>
									<?php
									$expired_email = isset( $rcp_options['expired_email_admin'] ) ? wptexturize( $rcp_options['expired_email_admin'] ) : '';
									wp_editor( $expired_email, 'rcp_settings_expired_email_admin', array( 'textarea_name' => 'rcp_settings[expired_email_admin]', 'teeny' => true ) );
									?>
									<p class="description"><?php _e( 'This is the email message that is sent to the admin when a member\'s subscription is expired.', 'rcp' ); ?></p>
								</td>
							</tr>
							<tr valign="top">
								<th colspan="2"><h3><?php _e( 'Expiration Reminders', 'rcp' ); ?></h3></th>
							</tr>
							<tr valign="top">
								<th>
									<?php _e( 'Subscription Expiration Reminders', 'rcp' ); ?>
								</th>
								<td>
									<?php rcp_subscription_reminder_table( 'expiration' ); ?>
								</td>
							</tr>
							<tr valign="top">
								<th colspan="2"><h3><?php _e( 'Renewal Reminders', 'rcp' ); ?></h3></th>
							</tr>
							<tr valign="top">
								<th>
									<?php _e( 'Subscription Renewal Reminders', 'rcp' ); ?>
								</th>
								<td>
									<?php rcp_subscription_reminder_table( 'renewal' ); ?>
								</td>
							</tr>
							<tr valign="top">
								<th colspan=2>
									<h3><?php _e( 'Free Subscription Email', 'rcp' ); ?></h3>
								</th>
							</tr>
							<tr>
								<th>
									<label for="rcp_settings[disable_free_email]"><?php _e( 'Disable for Member', 'rcp' ); ?></label>
								</th>
								<td>
									<input type="checkbox" value="1" name="rcp_settings[disable_free_email]" id="rcp_settings[disable_free_email]" class="rcp-disable-email" <?php checked( true, isset( $rcp_options['disable_free_email'] ) ); ?>/>
									<span><?php _e( 'Check this to disable the email sent to a member when they register for a free subscription.', 'rcp' ); ?></span>
								</td>
							</tr>
							<tr valign="top"<?php echo ( isset( $rcp_options['disable_free_email'] ) ) ? ' style="display: none;"' : ''; ?>>
								<th>
									<label for="rcp_settings[free_subject]"><?php _e( 'Member Subject', 'rcp' ); ?></label>
								</th>
								<td>
									<input class="regular-text" id="rcp_settings[free_subject]" style="width: 300px;" name="rcp_settings[free_subject]" value="<?php if( isset( $rcp_options['free_subject'] ) ) { echo $rcp_options['free_subject']; } ?>"/>
									<p class="description"><?php _e( 'The subject line for the email sent to users when they sign up for a free membership.', 'rcp' ); ?></p>
								</td>
							</tr>
							<tr valign="top"<?php echo ( isset( $rcp_options['disable_free_email'] ) ) ? ' style="display: none;"' : ''; ?>>
								<th>
									<label for="rcp_settings[free_email]"><?php _e( 'Member Email Body', 'rcp' ); ?></label>
								</th>
								<td>
									<?php
									$free_email = isset( $rcp_options['free_email'] ) ? wptexturize( $rcp_options['free_email'] ) : '';
									wp_editor( $free_email, 'rcp_settings_free_email', array( 'textarea_name' => 'rcp_settings[free_email]', 'teeny' => true ) );
									?>
									<p class="description"><?php _e( 'This is the email message that is sent to users when they sign up for a free account.', 'rcp' ); ?></p>
								</td>
							</tr>
							<tr>
								<th>
									<label for="rcp_settings[disable_free_email_admin]"><?php _e( 'Disable for Admin', 'rcp' ); ?></label>
								</th>
								<td>
									<input type="checkbox" value="1" name="rcp_settings[disable_free_email_admin]" id="rcp_settings[disable_free_email_admin]" class="rcp-disable-email" <?php checked( true, isset( $rcp_options['disable_free_email_admin'] ) ); ?>/>
									<span><?php _e( 'Check this to disable the email sent to the administrator when a member registers for a free subscription.', 'rcp' ); ?></span>
								</td>
							</tr>
							<tr valign="top"<?php echo ( isset( $rcp_options['disable_free_email_admin'] ) ) ? ' style="display: none;"' : ''; ?>>
								<th>
									<label for="rcp_settings[free_subject_admin]"><?php _e( 'Admin Subject', 'rcp' ); ?></label>
								</th>
								<td>
									<input class="regular-text" id="rcp_settings[free_subject_admin]" style="width: 300px;" name="rcp_settings[free_subject_admin]" value="<?php if( isset( $rcp_options['free_subject_admin'] ) ) { echo $rcp_options['free_subject_admin']; } ?>"/>
									<p class="description"><?php _e( 'The subject line for the email sent to the admin when a user signs up for a free membership.', 'rcp' ); ?></p>
								</td>
							</tr>
							<tr valign="top"<?php echo ( isset( $rcp_options['disable_free_email_admin'] ) ) ? ' style="display: none;"' : ''; ?>>
								<th>
									<label for="rcp_settings[free_email_admin]"><?php _e( 'Admin Email Body', 'rcp' ); ?></label>
								</th>
								<td>
									<?php
									$free_email = isset( $rcp_options['free_email_admin'] ) ? wptexturize( $rcp_options['free_email_admin'] ) : '';
									wp_editor( $free_email, 'rcp_settings_free_email_admin', array( 'textarea_name' => 'rcp_settings[free_email_admin]', 'teeny' => true ) );
									?>
									<p class="description"><?php _e( 'This is the email message that is sent to the admin when a user signs up for a free account.', 'rcp' ); ?></p>
								</td>
							</tr>
							<tr valign="top">
								<th colspan=2>
									<h3><?php _e( 'Trial Subscription Email', 'rcp' ); ?></h3>
								</th>
							</tr>
							<tr>
								<th>
									<label for="rcp_settings[disable_trial_email]"><?php _e( 'Disable for Member', 'rcp' ); ?></label>
								</th>
								<td>
									<input type="checkbox" value="1" name="rcp_settings[disable_trial_email]" id="rcp_settings[disable_trial_email]" class="rcp-disable-email" <?php checked( true, isset( $rcp_options['disable_trial_email'] ) ); ?>/>
									<span><?php _e( 'Check this to disable the email sent to a member when they sign up with a trial.', 'rcp' ); ?></span>
								</td>
							</tr>
							<tr valign="top"<?php echo ( isset( $rcp_options['disable_trial_email'] ) ) ? ' style="display: none;"' : ''; ?>>
								<th>
									<label for="rcp_settings[trial_subject]"><?php _e( 'Member Subject', 'rcp' ); ?></label>
								</th>
								<td>
									<input class="regular-text" id="rcp_settings[trial_subject]" style="width: 300px;" name="rcp_settings[trial_subject]" value="<?php if( isset( $rcp_options['trial_subject'] ) ) { echo $rcp_options['trial_subject']; } ?>"/>
									<p class="description"><?php _e( 'The subject line for the email sent to users when they sign up for a free trial.', 'rcp' ); ?></p>
								</td>
							</tr>
							<tr valign="top"<?php echo ( isset( $rcp_options['disable_trial_email'] ) ) ? ' style="display: none;"' : ''; ?>>
								<th>
									<label for="rcp_settings[trial_email]"><?php _e( 'Member Trial Email Message', 'rcp' ); ?></label>
								</th>
								<td>
									<?php
									$trial_email = isset( $rcp_options['trial_email'] ) ? wptexturize( $rcp_options['trial_email'] ) : '';
									wp_editor( $trial_email, 'rcp_settings_trial_email', array( 'textarea_name' => 'rcp_settings[trial_email]', 'teeny' => true ) );
									?>
									<p class="description"><?php _e( 'This is the email message that is sent to users when they sign up for a free trial.', 'rcp' ); ?></p>
								</td>
							</tr>
							<tr>
								<th>
									<label for="rcp_settings[disable_trial_email_admin]"><?php _e( 'Disable for Admin', 'rcp' ); ?></label>
								</th>
								<td>
									<input type="checkbox" value="1" name="rcp_settings[disable_trial_email_admin]" id="rcp_settings[disable_trial_email_admin]" class="rcp-disable-email" <?php checked( true, isset( $rcp_options['disable_trial_email_admin'] ) ); ?>/>
									<span><?php _e( 'Check this to disable the email sent to the administrator when a member signs up with a trial.', 'rcp' ); ?></span>
								</td>
							</tr>
							<tr valign="top"<?php echo ( isset( $rcp_options['disable_trial_email_admin'] ) ) ? ' style="display: none;"' : ''; ?>>
								<th>
									<label for="rcp_settings[trial_subject_admin]"><?php _e( 'Admin Subject', 'rcp' ); ?></label>
								</th>
								<td>
									<input class="regular-text" id="rcp_settings[trial_subject_admin]" style="width: 300px;" name="rcp_settings[trial_subject_admin]" value="<?php if( isset( $rcp_options['trial_subject_admin'] ) ) { echo $rcp_options['trial_subject_admin']; } ?>"/>
									<p class="description"><?php _e( 'The subject line for the email sent to the admin when a user signs up for a free trial.', 'rcp' ); ?></p>
								</td>
							</tr>
							<tr valign="top"<?php echo ( isset( $rcp_options['disable_trial_email_admin'] ) ) ? ' style="display: none;"' : ''; ?>>
								<th>
									<label for="rcp_settings[trial_email_admin]"><?php _e( 'Admin Trial Email Message', 'rcp' ); ?></label>
								</th>
								<td>
									<?php
									$trial_email = isset( $rcp_options['trial_email_admin'] ) ? wptexturize( $rcp_options['trial_email_admin'] ) : '';
									wp_editor( $trial_email, 'rcp_settings_trial_email_admin', array( 'textarea_name' => 'rcp_settings[trial_email_admin]', 'teeny' => true ) );
									?>
									<p class="description"><?php _e( 'This is the email message that is sent to the admin when a user signs up for a free trial.', 'rcp' ); ?></p>
								</td>
							</tr>
							<tr valign="top">
								<th colspan=2><h3><?php _e( 'Payment Received Email', 'rcp' ); ?></h3></th>
							</tr>
							<tr>
								<th>
									<label for="rcp_settings[disable_payment_received_email]"><?php _e( 'Disable for Member', 'rcp' ); ?></label>
								</th>
								<td>
									<input type="checkbox" value="1" name="rcp_settings[disable_payment_received_email]" id="rcp_settings[disable_payment_received_email]" class="rcp-disable-email" <?php checked( true, isset( $rcp_options['disable_payment_received_email'] ) ); ?>/>
									<span><?php _e( 'Check this to disable the email sent out when a payment is received.', 'rcp' ); ?></span>
								</td>
							</tr>
							<tr valign="top"<?php echo ( isset( $rcp_options['disable_payment_received_email'] ) ) ? ' style="display: none;"' : ''; ?>>
								<th>
									<label for="rcp_settings[payment_received_subject]"><?php _e( 'Member Subject', 'rcp' ); ?></label>
								</th>
								<td>
									<input class="regular-text" id="rcp_settings[payment_received_subject]" style="width: 300px;" name="rcp_settings[payment_received_subject]" value="<?php if( isset( $rcp_options['payment_received_subject'] ) ) { echo $rcp_options['payment_received_subject']; } ?>"/>
									<p class="description"><?php _e( 'The subject line for the email sent to users upon a successful payment being received.', 'rcp' ); ?></p>
								</td>
							</tr>
							<tr valign="top"<?php echo ( isset( $rcp_options['disable_payment_received_email'] ) ) ? ' style="display: none;"' : ''; ?>>
								<th>
									<label for="rcp_settings[payment_received_email]"><?php _e( 'Member Email Body', 'rcp' ); ?></label>
								</th>
								<td>
									<?php
									$payment_received_email = isset( $rcp_options['payment_received_email'] ) ? wptexturize( $rcp_options['payment_received_email'] ) : '';
									wp_editor( $payment_received_email, 'rcp_settings_payment_received_email', array( 'textarea_name' => 'rcp_settings[payment_received_email]', 'teeny' => true ) );
									?>
									<p class="description"><?php _e( 'This is the email message that is sent to users after a payment has been received from them.', 'rcp' ); ?></p>
								</td>
							</tr>
							<tr valign="top">
								<th colspan=2><h3><?php _e( 'Renewal Payment Failed Email', 'rcp' ); ?></h3></th>
							</tr>
							<tr>
								<th>
									<label for="rcp_settings[disable_renewal_payment_failed_email]"><?php _e( 'Disable for Member', 'rcp' ); ?></label>
								</th>
								<td>
									<input type="checkbox" value="1" name="rcp_settings[disable_renewal_payment_failed_email]" id="rcp_settings[disable_renewal_payment_failed_email]" class="rcp-disable-email" <?php checked( true, isset( $rcp_options['disable_renewal_payment_failed_email'] ) ); ?>/>
									<span><?php _e( 'Check this to disable the email sent out when a renewal payment fails.', 'rcp' ); ?></span>
								</td>
							</tr>
							<tr valign="top"<?php echo ( isset( $rcp_options['disable_renewal_payment_failed_email'] ) ) ? ' style="display: none;"' : ''; ?>>
								<th>
									<label for="rcp_settings[renewal_payment_failed_subject]"><?php _e( 'Member Subject', 'rcp' ); ?></label>
								</th>
								<td>
									<input class="regular-text" id="rcp_settings[renewal_payment_failed_subject]" style="width: 300px;" name="rcp_settings[renewal_payment_failed_subject]" value="<?php if( isset( $rcp_options['renewal_payment_failed_subject'] ) ) { echo esc_attr( $rcp_options['renewal_payment_failed_subject'] ); } ?>"/>
									<p class="description"><?php _e( 'The subject line for the email sent to users when a renewal payment fails.', 'rcp' ); ?></p>
								</td>
							</tr>
							<tr valign="top"<?php echo ( isset( $rcp_options['disable_renewal_payment_failed_email'] ) ) ? ' style="display: none;"' : ''; ?>>
								<th>
									<label for="rcp_settings[renewal_payment_failed_email]"><?php _e( 'Member Email Body', 'rcp' ); ?></label>
								</th>
								<td>
									<?php
									$renewal_payment_failed_email = isset( $rcp_options['renewal_payment_failed_email'] ) ? wptexturize( $rcp_options['renewal_payment_failed_email'] ) : '';
									wp_editor( $renewal_payment_failed_email, 'rcp_settings_renewal_payment_failed_email', array( 'textarea_name' => 'rcp_settings[renewal_payment_failed_email]', 'teeny' => true ) );
									?>
									<p class="description"><?php _e( 'This is the email message that is sent to users when a renewal payment fails.', 'rcp' ); ?></p>
								</td>
							</tr>
							<tr valign="top">
								<th colspan=2>
									<h3><?php _e( 'New User Notifications', 'rcp' ); ?></h3>
								</th>
							</tr>
							<tr valign="top">
								<th>
									<label for="rcp_settings[disable_new_user_notices]"><?php _e( 'Disable New User Notifications', 'rcp' ); ?></label>
								</th>
								<td>
									<input type="checkbox" value="1" name="rcp_settings[disable_new_user_notices]" id="rcp_settings[disable_new_user_notices]" <?php if( isset( $rcp_options['disable_new_user_notices'] ) ) checked('1', $rcp_options['disable_new_user_notices']); ?>/>
									<span class="description"><?php _e( 'Check this option if you do NOT want to receive emails when new users signup.', 'rcp' ); ?></span>
								</td>
							</tr>
						</table>
						<?php do_action( 'rcp_email_settings', $rcp_options ); ?>

					</div><!--end #rcp_email_options-->
					<div class="clear"></div>
				</div><!--end #emails-->

				<div class="tab_content" id="invoices">
					<table class="form-table">
						<tr valign="top">
							<th>
								<label for="rcp_settings[invoice_logo]"><?php _e( 'Invoice Logo', 'rcp' ); ?></label>
							</th>
							<td>
								<input class="regular-text rcp-upload-field" id="rcp_settings[invoice_logo]" style="width: 300px;" name="rcp_settings[invoice_logo]" value="<?php if( isset( $rcp_options['invoice_logo'] ) ) { echo $rcp_options['invoice_logo']; } ?>"/>
								<button class="button-secondary rcp-upload"><?php _e( 'Choose Logo', 'rcp' ); ?></button>
								<p class="description"><?php _e( 'Upload a logo to display on the invoices.', 'rcp' ); ?></p>
							</td>
						</tr>
						<tr valign="top">
							<th>
								<label for="rcp_settings[invoice_company]"><?php _e( 'Company Name', 'rcp' ); ?></label>
							</th>
							<td>
								<input class="regular-text" id="rcp_settings[invoice_company]" style="width: 300px;" name="rcp_settings[invoice_company]" value="<?php if( isset( $rcp_options['invoice_company'] ) ) { echo $rcp_options['invoice_company']; } ?>"/>
								<p class="description"><?php _e( 'Enter the company name that will be shown on the invoice.', 'rcp' ); ?></p>
							</td>
						</tr>
						<tr valign="top">
							<th>
								<label for="rcp_settings[invoice_name]"><?php _e( 'Name', 'rcp' ); ?></label>
							</th>
							<td>
								<input class="regular-text" id="rcp_settings[invoice_name]" style="width: 300px;" name="rcp_settings[invoice_name]" value="<?php if( isset( $rcp_options['invoice_name'] ) ) { echo $rcp_options['invoice_name']; } ?>"/>
								<p class="description"><?php _e( 'Enter the personal name that will be shown on the invoice.', 'rcp' ); ?></p>
							</td>
						</tr>
						<tr valign="top">
							<th>
								<label for="rcp_settings[invoice_address]"><?php _e( 'Address Line 1', 'rcp' ); ?></label>
							</th>
							<td>
								<input class="regular-text" id="rcp_settings[invoice_address]" style="width: 300px;" name="rcp_settings[invoice_address]" value="<?php if( isset( $rcp_options['invoice_address'] ) ) { echo $rcp_options['invoice_address']; } ?>"/>
								<p class="description"><?php _e( 'Enter the first address line that will appear on the invoice.', 'rcp' ); ?></p>
							</td>
						</tr>
						<tr valign="top">
							<th>
								<label for="rcp_settings[invoice_address_2]"><?php _e( 'Address Line 2', 'rcp' ); ?></label>
							</th>
							<td>
								<input class="regular-text" id="rcp_settings[invoice_address_2]" style="width: 300px;" name="rcp_settings[invoice_address_2]" value="<?php if( isset( $rcp_options['invoice_address_2'] ) ) { echo $rcp_options['invoice_address_2']; } ?>"/>
								<p class="description"><?php _e( 'Enter the second address line that will appear on the invoice.', 'rcp' ); ?></p>
							</td>
						</tr>
						<tr valign="top">
							<th>
								<label for="rcp_settings[invoice_city_state_zip]"><?php _e( 'City, State, and Zip', 'rcp' ); ?></label>
							</th>
							<td>
								<input class="regular-text" id="rcp_settings[invoice_city_state_zip]" style="width: 300px;" name="rcp_settings[invoice_city_state_zip]" value="<?php if( isset( $rcp_options['invoice_city_state_zip'] ) ) { echo $rcp_options['invoice_city_state_zip']; } ?>"/>
								<p class="description"><?php _e( 'Enter the city, state and zip/postal code that will appear on the invoice.', 'rcp' ); ?></p>
							</td>
						</tr>
						<tr valign="top">
							<th>
								<label for="rcp_settings[invoice_email]"><?php _e( 'Email', 'rcp' ); ?></label>
							</th>
							<td>
								<input class="regular-text" id="rcp_settings[invoice_email]" style="width: 300px;" name="rcp_settings[invoice_email]" value="<?php if( isset( $rcp_options['invoice_email'] ) ) { echo $rcp_options['invoice_email']; } ?>"/>
								<p class="description"><?php _e( 'Enter the email address that will appear on the invoice.', 'rcp' ); ?></p>
							</td>
						</tr>
						<tr valign="top">
							<th>
								<label for="rcp_settings[invoice_header]"><?php _e( 'Header Text', 'rcp' ); ?></label>
							</th>
							<td>
								<input class="regular-text" id="rcp_settings[invoice_header]" style="width: 300px;" name="rcp_settings[invoice_header]" value="<?php if( isset( $rcp_options['invoice_header'] ) ) { echo $rcp_options['invoice_header']; } ?>"/>
								<p class="description"><?php _e( 'Enter the message you would like to be shown on the header of the invoice.', 'rcp' ); ?></p>
							</td>
						</tr>
						<tr valign="top">
							<th>
								<label for="rcp_settings_invoice_notes"><?php _e( 'Notes', 'rcp' ); ?></label>
							</th>
							<td>
								<?php wp_editor( $rcp_options['invoice_notes'], 'rcp_settings_invoice_notes', array( 'textarea_name' => 'rcp_settings[invoice_notes]', 'teeny' => true ) ); ?>
								<p class="description"><?php _e( 'Enter additional notes you would like displayed below the invoice totals.', 'rcp' ); ?></p>
							</td>
						</tr>
						<tr valign="top">
							<th>
								<label for="rcp_settings[invoice_footer]"><?php _e( 'Footer Text', 'rcp' ); ?></label>
							</th>
							<td>
								<input class="regular-text" id="rcp_settings[invoice_footer]" style="width: 300px;" name="rcp_settings[invoice_footer]" value="<?php if( isset( $rcp_options['invoice_footer'] ) ) { echo $rcp_options['invoice_footer']; } ?>"/>
								<p class="description"><?php _e( 'Enter the message you would like to be shown on the footer of the invoice.', 'rcp' ); ?></p>
							</td>
						</tr>
					</table>
					<?php do_action( 'rcp_invoice_settings', $rcp_options ); ?>
				</div><!--end #invoices-->

				<div class="tab_content" id="misc">
					<table class="form-table">
						<tr valign="top">
							<th>
								<label for="rcp_settings[hide_premium]"><?php _e( 'Hide Restricted Posts', 'rcp' ); ?></label>
							</th>
							<td>
								<input type="checkbox" value="1" name="rcp_settings[hide_premium]" id="rcp_settings[hide_premium]" <?php if( isset( $rcp_options['hide_premium'] ) ) checked('1', $rcp_options['hide_premium']); ?>/>
								<span class="description"><?php _e( 'Check this to hide all restricted posts from queries when the user does not have access.', 'rcp' ); ?></span>
							</td>
						</tr>
						<tr valign="top">
							<th>
								<label for="rcp_settings[redirect]">&nbsp;&mdash;&nbsp;<?php _e( 'Redirect Page', 'rcp' ); ?></label>
							</th>
							<td>
								<select id="rcp_settings[redirect_from_premium]" name="rcp_settings[redirect_from_premium]">
									<?php
									if($pages) :
										foreach ( $pages as $page ) {
										  	$option = '<option value="' . $page->ID . '" ' . selected($page->ID, $rcp_options['redirect_from_premium'], false) . '>';
											$option .= $page->post_title;
											$option .= '</option>';
											echo $option;
										}
									else :
										echo '<option>' . __('No pages found', 'rcp' ) . '</option>';
									endif;
									?>
								</select>
								<?php if ( ! empty( $rcp_options['redirect_from_premium'] ) ) : ?>
									<a href="<?php echo esc_url( get_edit_post_link( $rcp_options['redirect_from_premium'] ) ); ?>" class="button-secondary"><?php _e( 'Edit Page', 'rcp' ); ?></a>
									<a href="<?php echo esc_url( get_permalink( $rcp_options['redirect_from_premium'] ) ); ?>" class="button-secondary"><?php _e( 'View Page', 'rcp' ); ?></a>
								<?php endif; ?>
								<p class="description"><?php _e( 'This is the page non-subscribed users are redirected to when attempting to access a premium post or page.', 'rcp' ); ?></p>
							</td>
						</tr>
						<tr valign="top">
							<th>
								<label for="rcp_settings[hijack_login_url]"><?php _e( 'Redirect Default Login URL', 'rcp' ); ?></label>
							</th>
							<td>
								<input type="checkbox" value="1" name="rcp_settings[hijack_login_url]" id="rcp_settings[hijack_login_url]" <?php if( isset( $rcp_options['hijack_login_url'] ) ) checked('1', $rcp_options['hijack_login_url']); ?>/>
								<span class="description"><?php _e( 'Check this to force the default login URL to redirect to the page specified below.', 'rcp' ); ?></span>
							</td>
						</tr>
						<tr valign="top">
							<th>
								<label for="rcp_settings[redirect]">&nbsp;&mdash;&nbsp;<?php _e( 'Login Page', 'rcp' ); ?></label>
							</th>
							<td>
								<select id="rcp_settings[login_redirect]" name="rcp_settings[login_redirect]">
									<?php
									if($pages) :
										foreach ( $pages as $page ) {
										  	$option = '<option value="' . $page->ID . '" ' . selected($page->ID, $rcp_options['login_redirect'], false) . '>';
											$option .= $page->post_title;
											$option .= '</option>';
											echo $option;
										}
									else :
										echo '<option>' . __('No pages found', 'rcp' ) . '</option>';
									endif;
									?>
								</select>
								<?php if ( ! empty( $rcp_options['login_redirect'] ) ) : ?>
									<a href="<?php echo esc_url( get_edit_post_link( $rcp_options['login_redirect'] ) ); ?>" class="button-secondary"><?php _e( 'Edit Page', 'rcp' ); ?></a>
									<a href="<?php echo esc_url( get_permalink( $rcp_options['login_redirect'] ) ); ?>" class="button-secondary"><?php _e( 'View Page', 'rcp' ); ?></a>
								<?php endif; ?>
								<p class="description"><?php _e( 'This is the page the default login URL redirects to, if the option above is checked. This should be the page that contains the [login_form] short code.', 'rcp' ); ?></p>
							</td>
						</tr>
						<tr valign="top">
							<th>
								<label for="rcp_settings[auto_add_users]"><?php _e( 'Auto Add Users to Level', 'rcp' ); ?></label>
							</th>
							<td>
								<input type="checkbox" value="1" name="rcp_settings[auto_add_users]" id="rcp_settings[auto_add_users]" <?php if( isset( $rcp_options['auto_add_users'] ) ) checked('1', $rcp_options['auto_add_users']); ?>/>
								<span class="description"><?php _e( 'Check this to automatically add new WordPress users to a subscription level. This only needs to be turned on if you\'re adding users manually or through some means other than the registration form. This does not automatically take payment so it\'s best used for free levels.', 'rcp' ); ?></span>
							</td>
						</tr>
						<tr valign="top">
							<th>
								<label for="rcp_settings[auto_add_users_level]">&nbsp;&mdash;&nbsp;<?php _e( 'Subscription Level', 'rcp' ); ?></label>
							</th>
							<td>
								<select id="rcp_settings[auto_add_users_level]" name="rcp_settings[auto_add_users_level]">
									<?php
									$selected_level = isset( $rcp_options['auto_add_users_level'] ) ? $rcp_options['auto_add_users_level'] : '';
									foreach( rcp_get_subscription_levels( 'all' ) as $key => $level ) :
										echo '<option value="' . esc_attr( absint( $level->id ) ) . '"' . selected( $level->id, $selected_level, false ) . '>' . esc_html( $level->name ) . '</option>';
									endforeach;
									?>
								</select>
								<p class="description"><?php _e( 'New WordPress users will be automatically added to this subscription level if the above option is checked.', 'rcp' ); ?></p>
							</td>
						</tr>
						<tr valign="top">
							<th>
								<label for="rcp_settings[content_excerpts]"><?php _e( 'Content Excerpts', 'rcp' ); ?></label>
							</th>
							<td>
								<?php $excerpts = isset( $rcp_options['content_excerpts'] ) ? $rcp_options['content_excerpts'] : 'individual'; ?>
								<select id="rcp_settings[content_excerpts]" name="rcp_settings[content_excerpts]">
									<option value="always" <?php selected( $excerpts, 'all' ); ?>><?php _e( 'Always show excerpts', 'rcp' ); ?></option>
									<option value="never" <?php selected( $excerpts, 'never' ); ?>><?php _e( 'Never show excerpts', 'rcp' ); ?></option>
									<option value="individual" <?php selected( $excerpts, 'individual' ); ?>><?php _e( 'Decide for each post individually', 'rcp' ); ?></option>
								</select>
								<p class="description"><?php _e( 'Whether or not to show excerpts to members without access to the content.', 'rcp' ); ?></p>
							</td>
						</tr>
						<tr valign="top">
							<th>
								<label for="rcp_settings[no_login_sharing]"><?php _e( 'Prevent Account Sharing', 'rcp' ); ?></label>
							</th>
							<td>
								<input type="checkbox" value="1" name="rcp_settings[no_login_sharing]" id="rcp_settings[no_login_sharing]"<?php checked( true, isset( $rcp_options['no_login_sharing'] ) ); ?>/>
								<span class="description"><?php _e( 'Check this if you\'d like to prevent multiple users from logging into the same account simultaneously.', 'rcp' ); ?></span>
							</td>
						</tr>
						<tr valign="top">
							<th>
								<label for="rcp_settings[one_time_discounts]"><?php _e( 'One Time Discounts', 'rcp' ); ?></label>
							</th>
							<td>
								<input type="checkbox" value="1" name="rcp_settings[one_time_discounts]" id="rcp_settings[one_time_discounts]" <?php if( isset( $rcp_options['one_time_discounts'] ) ) checked('1', $rcp_options['one_time_discounts']); ?>/>
								<span class="description"><?php _e( 'Check this to enable one time discounts. When this option is not enabled, discount codes will apply to all payments in a subscription instead of just the initial payment.', 'rcp' ); ?></span>
							</td>
						</tr>
						<tr valign="top">
							<th>
								<label for="rcp_settings[disable_toolbar]"><?php _e( 'Disable WordPress Toolbar', 'rcp' ); ?></label>
							</th>
							<td>
								<input type="checkbox" value="1" name="rcp_settings[disable_toolbar]" id="rcp_settings[disable_toolbar]"<?php checked( true, isset( $rcp_options['disable_toolbar'] ) ); ?>/>
								<span class="description"><?php _e( 'Check this if you\'d like to disable the WordPress toolbar for subscribers. Note: will not disable the toolbar for administrators.', 'rcp' ); ?></span>
							</td>
						</tr>
						<tr valign="top">
							<th>
								<label for="rcp_settings[email_ipn_reports]"><?php _e( 'Email IPN reports', 'rcp' ); ?></label>
							</th>
							<td>
								<input type="checkbox" value="1" name="rcp_settings[email_ipn_reports]" id="rcp_settings[email_ipn_reports]" <?php if( isset( $rcp_options['email_ipn_reports'] ) ) checked('1', $rcp_options['email_ipn_reports']); ?>/>
								<span class="description"><?php _e( 'Check this to send an email each time an IPN request is made with PayPal. The email will contain a list of all data sent. This is useful for debugging in the case that something is not working with the PayPal integration.', 'rcp' ); ?></span>
							</td>
						</tr>
						<tr valign="top">
							<th>
								<label for="rcp_settings[disable_css]"><?php _e( 'Disable Form CSS', 'rcp' ); ?></label><br/>
							</th>
							<td>
								<input type="checkbox" value="1" name="rcp_settings[disable_css]" id="rcp_settings[disable_css]" <?php if( isset( $rcp_options['disable_css'] ) ) checked('1', $rcp_options['disable_css']); ?>/>
								<span class="description"><?php _e( 'Check this to disable all included form styling.', 'rcp' ); ?></span>
							</td>
						</tr>
						<tr valign="top">
							<th>
								<label for="rcp_settings[enable_recaptcha]"><?php _e( 'Enable reCaptcha', 'rcp' ); ?></label>
							</th>
							<td>
								<input type="checkbox" value="1" name="rcp_settings[enable_recaptcha]" id="rcp_settings[enable_recaptcha]" <?php if( isset( $rcp_options['enable_recaptcha'] ) ) checked('1', $rcp_options['enable_recaptcha']); ?>/>
								<span class="description"><?php _e( 'Check this to enable reCaptcha on the registration form.', 'rcp' ); ?></span>
							</td>
						</tr>
						<tr valign="top">
							<th>
								<label for="rcp_settings[recaptcha_public_key]"><?php _e( 'reCaptcha Site Key', 'rcp' ); ?></label>
							</th>
							<td>
								<input id="rcp_settings[recaptcha_public_key]" style="width: 300px;" name="rcp_settings[recaptcha_public_key]" type="text" value="<?php if( isset( $rcp_options['recaptcha_public_key'] ) ) echo $rcp_options['recaptcha_public_key']; ?>" />
								<p class="description"><?php _e( 'This your own personal reCaptcha Site key. Go to', 'rcp' ); ?> <a href="https://www.google.com/recaptcha/"><?php _e( 'your account', 'rcp' ); ?></a>, <?php _e( 'then click on your domain (or add a new one) to find your site key.', 'rcp' ); ?></p>
							<td>
						</tr>
						<tr valign="top">
							<th>
								<label for="rcp_settings[recaptcha_private_key]"><?php _e( 'reCaptcha Secret Key', 'rcp' ); ?></label>
							</th>
							<td>
								<input id="rcp_settings[recaptcha_private_key]" style="width: 300px;" name="rcp_settings[recaptcha_private_key]" type="text" value="<?php if( isset( $rcp_options['recaptcha_private_key'] ) ) echo $rcp_options['recaptcha_private_key']; ?>" />
								<p class="description"><?php _e( 'This your own personal reCaptcha Secret key. Go to', 'rcp' ); ?> <a href="https://www.google.com/recaptcha/"><?php _e( 'your account', 'rcp' ); ?></a>, <?php _e( 'then click on your domain (or add a new one) to find your secret key.', 'rcp' ); ?></p>
							</td>
						</tr>
						<tr valign="top">
							<th>
								<label for="rcp_settings[debug_mode]"><?php _e( 'Enable Debug Mode', 'rcp' ); ?></label>
							</th>
							<td>
								<input type="checkbox" value="1" name="rcp_settings[debug_mode]" id="rcp_settings[debug_mode]" <?php checked( true, ! empty( $rcp_options['debug_mode'] ) ); ?>/>
								<span class="description"><?php printf( __( 'Turn on error logging to help identify issues. Logs are kept in <a href="%s">Restrict > Tools</a>.', 'rcp' ), esc_url( admin_url( 'admin.php?page=rcp-tools' ) ) ); ?></span>
							</td>
						</tr>
						<tr valign="top">
							<th>
								<label for="rcp_settings[show_beta_updates]"><?php _e( 'Opt into beta versions?', 'rcp' ); ?></label>
							</th>
							<td>
								<input type="checkbox" value="1" name="rcp_settings[show_beta_updates]" id="rcp_settings[show_beta_updates]" <?php checked( true, ! empty( $rcp_options['show_beta_updates'] ) ); ?>/>
								<span class="description"><?php _e( 'Check this box if you would like to receive update notifications for beta releases. When beta versions are available, an update notification will be shown in your Plugins page.', 'rcp' ); ?></span>
							</td>
						</tr>
						<tr valign="top">
							<th>
								<label for="rcp_settings[remove_data_on_uninstall]"><?php _e( 'Remove Data on Uninstall', 'rcp' ); ?></label>
							</th>
							<td>
								<input type="checkbox" value="1" name="rcp_settings[remove_data_on_uninstall]" id="rcp_settings[remove_data_on_uninstall]" <?php checked( true, ! empty( $rcp_options['remove_data_on_uninstall'] ) ); ?>/>
								<span class="description"><?php _e( 'Remove all saved data for Restrict Content Pro when the plugin is uninstalled.', 'rcp' ); ?></span>
							</td>
						</tr>
					</table>
					<?php do_action( 'rcp_misc_settings', $rcp_options ); ?>
				</div><!--end #misc-->

			</div><!--end #tab_container-->

			<!-- save the options -->
			<p class="submit">
				<input type="submit" class="button-primary" value="<?php _e( 'Save Options', 'rcp' ); ?>" />
			</p>


		</form>
	</div><!--end wrap-->

	<?php
}

/**
 * Sanitize settings.
 *
 * @param array $data
 *
 * @return array Sanitized data.
 */
function rcp_sanitize_settings( $data ) {

	if( empty( $data['license_key'] ) ) {
		delete_option( 'rcp_license_status' );
	}

	if( ! empty( $_POST['rcp_license_deactivate'] ) ) {
		rcp_deactivate_license();
	} elseif( ! empty( $data['license_key'] ) ) {
		rcp_activate_license();
	}

	// Trim API key fields.
	$api_key_fields = array(
		'stripe_test_secret', 'stripe_test_publishable',
		'stripe_live_secret', 'stripe_live_publishable',
		'twocheckout_test_private', 'twocheckout_test_publishable',
		'twocheckout_live_private', 'twocheckout_live_publishable'
	);

	foreach ( $api_key_fields as $field ) {
		if ( ! empty( $data[ $field ] ) ) {
			$data[ $field ] = trim( $data[ $field ] );
		}
	}

	delete_transient( 'rcp_login_redirect_invalid' );

	// Make sure the [login_form] short code is on the redirect page. Users get locked out if it is not
	if( isset( $data['hijack_login_url'] ) ) {

		$page_id = absint( $data['login_redirect'] );
		$page    = get_post( $page_id );

		if( ! $page || 'page' != $page->post_type ) {
			unset( $data['hijack_login_url'] );
		}

		if(
			// Check for various login form short codes
			false === strpos( $page->post_content, '[login_form' ) &&
			false === strpos( $page->post_content, '[edd_login' ) &&
			false === strpos( $page->post_content, '[subscription_details' ) &&
			false === strpos( $page->post_content, '[login' )
		) {
			unset( $data['hijack_login_url'] );
			set_transient( 'rcp_login_redirect_invalid', 1, MINUTE_IN_SECONDS );
		}

	}

	// Sanitize email bodies
	$email_bodies = array( 'active_email', 'cancelled_email', 'expired_email', 'renew_notice_email', 'free_email', 'trial_email', 'payment_received_email' );
	foreach ( $email_bodies as $email_body ) {
		if ( ! empty( $data[$email_body] ) ) {
			$data[$email_body] = wp_kses_post( $data[$email_body] );
		}
	}

	do_action( 'rcp_save_settings', $data );

	return apply_filters( 'rcp_save_settings', $data );
}

/**
 * Activate license key
 *
 * @return bool|void
 */
function rcp_activate_license() {
	if( ! isset( $_POST['rcp_license_activate'] ) )
		return;

	if( ! isset( $_POST['rcp_settings']['license_key'] ) )
		return;

	if( ! current_user_can( 'rcp_manage_settings' ) ) {
		return;
	}

	// retrieve the license from the database
	$status  = get_option( 'rcp_license_status' );
	$license = trim( $_POST['rcp_settings']['license_key'] );

	if( 'valid' == $status )
		return; // license already activated

	// data to send in our API request
	$api_params = array(
		'edd_action'=> 'activate_license',
		'license' 	=> $license,
		'item_name' => 'Restrict Content Pro', // the name of our product in EDD
		'url'       => home_url()
	);

	// Call the custom API.
	$response = wp_remote_post( 'https://restrictcontentpro.com', array( 'timeout' => 15, 'sslverify' => false, 'body' => $api_params ) );

	// make sure the response came back okay
	if ( is_wp_error( $response ) )
		return false;

	// decode the license data
	$license_data = json_decode( wp_remote_retrieve_body( $response ) );

	update_option( 'rcp_license_status', $license_data->license );
	delete_transient( 'rcp_license_check' );

	if( 'valid' !== $license_data->license ) {
		wp_die( sprintf( __( 'Your license key could not be activated. Error: %s', 'rcp' ), $license_data->error ), __( 'Error', 'rcp' ), array( 'response' => 401, 'back_link' => true ) );
	}

}

/**
 * Deactivate license key
 *
 * @return bool|void
 */
function rcp_deactivate_license() {

	// listen for our activate button to be clicked
	if( isset( $_POST['rcp_license_deactivate'] ) ) {

		global $rcp_options;

		// run a quick security check
	 	if( ! check_admin_referer( 'rcp_deactivate_license', 'rcp_deactivate_license' ) )
			return; // get out if we didn't click the Activate button

		if( ! current_user_can( 'rcp_manage_settings' ) ) {
			return;
		}

		// retrieve the license from the database
		$license = trim( $rcp_options['license_key'] );

		// data to send in our API request
		$api_params = array(
			'edd_action'=> 'deactivate_license',
			'license' 	=> $license,
			'item_name' => urlencode( 'Restrict Content Pro' ), // the name of our product in EDD
			'url'       => home_url()
		);

		// Call the custom API.
		$response = wp_remote_post( 'https://restrictcontentpro.com', array( 'timeout' => 15, 'sslverify' => false, 'body' => $api_params ) );

		// make sure the response came back okay
		if ( is_wp_error( $response ) )
			return false;

		// decode the license data
		$license_data = json_decode( wp_remote_retrieve_body( $response ) );

		// $license_data->license will be either "deactivated" or "failed"
		if( $license_data->license == 'deactivated' ) {
			delete_option( 'rcp_license_status' );
			delete_transient( 'rcp_license_check' );
		}

	}
}

/**
 * Check license key to see if it's valid
 *
 * @return string|false|void
 */
function rcp_check_license() {

	if( ! empty( $_POST['rcp_settings'] ) ) {
		return; // Don't fire when saving settings
	}

	global $rcp_options;

	$status = get_transient( 'rcp_license_check' );

	// Run the license check a maximum of once per day
	if( false === $status && ! empty( $rcp_options['license_key'] ) ) {

		// data to send in our API request
		$api_params = array(
			'edd_action'=> 'check_license',
			'license' 	=> trim( $rcp_options['license_key'] ),
			'item_name' => urlencode( 'Restrict Content Pro' ), // the name of our product in EDD
			'url'       => home_url()
		);

		// Send check-ins once per week.
		$last_checked = get_option( 'rcp_last_checkin', false );
		if( ! is_numeric( $last_checked ) || $last_checked < strtotime( '-1 week', current_time( 'timestamp' ) ) ) {
			$api_params['site_data'] = rcp_get_site_tracking_data();
		}

		// Call the custom API.
		$response = wp_remote_post( 'https://restrictcontentpro.com', array( 'timeout' => 35, 'sslverify' => false, 'body' => $api_params ) );

		// make sure the response came back okay
		if ( is_wp_error( $response ) )
			return false;

		$license_data = json_decode( wp_remote_retrieve_body( $response ) );

		$rcp_options['license_status'] = $license_data->license;

		update_option( 'rcp_settings', $rcp_options );

		set_transient( 'rcp_license_check', $license_data->license, DAY_IN_SECONDS );

		if( ! empty( $api_params['site_data'] ) ) {
			update_option( 'rcp_last_checkin', current_time( 'timestamp' ), false );
		}

		$status = $license_data->license;

		if( 'valid' !== $status ) {
			delete_option( 'rcp_license_status' );
		}

	}

	return $status;

}
add_action( 'admin_init', 'rcp_check_license' );

/**
 * Retrieves site data (plugin versions, etc.) to be sent along with the license check.
 *
 * @since 2.9
 * @return array
 */
function rcp_get_site_tracking_data() {

	global $rcp_options;

	/**
	 * @var RCP_Levels $rcp_levels_db
	 */
	global $rcp_levels_db;

	/**
	 * @var RCP_Payments $rcp_payments_db
	 */
	global $rcp_payments_db;

	$data = array();

	$theme_data = wp_get_theme();
	$theme      = $theme_data->Name . ' ' . $theme_data->Version;

	$data['php_version']  = phpversion();
	$data['rcp_version']  = RCP_PLUGIN_VERSION;
	$data['wp_version']   = get_bloginfo( 'version' );
	$data['server']       = isset( $_SERVER['SERVER_SOFTWARE'] ) ? $_SERVER['SERVER_SOFTWARE'] : '';
	$data['install_date'] = get_post_field( 'post_date', $rcp_options['registration_page'] );
	$data['multisite']    = is_multisite();
	$data['url']          = home_url();
	$data['theme']        = $theme;

	// Retrieve current plugin information
	if( ! function_exists( 'get_plugins' ) ) {
		include ABSPATH . '/wp-admin/includes/plugin.php';
	}

	$plugins        = array_keys( get_plugins() );
	$active_plugins = get_option( 'active_plugins', array() );

	foreach ( $plugins as $key => $plugin ) {
		if ( in_array( $plugin, $active_plugins ) ) {
			// Remove active plugins from list so we can show active and inactive separately
			unset( $plugins[ $key ] );
		}
	}

	$enabled_gateways = array();
	$gateways         = new RCP_Payment_Gateways;

	foreach( $gateways->enabled_gateways  as $key => $gateway ) {
		if( is_array( $gateway ) ) {
			$enabled_gateways[ $key ] = $gateway['admin_label'];
		}
	}

	$plugins_str = implode( ',', array_keys( get_plugins() ) );
	$upgraded    = strpos( $plugins_str, 'restrictcontent.php' );

	$data['active_plugins']      = $active_plugins;
	$data['inactive_plugins']    = $plugins;
	$data['locale']              = get_locale();
	$data['auto_renew']          = $rcp_options['auto_renew'];
	$data['currency']            = $rcp_options['currency'];
	$data['gateways']            = $enabled_gateways;
	$data['active_members']      = rcp_get_member_count( 'active' );
	$data['free_members']        = rcp_get_member_count( 'free' );
	$data['expired_members']     = rcp_get_member_count( 'expired' );
	$data['cancelled_members']   = rcp_get_member_count( 'cancelled' );
	$data['subscription_levels'] = $rcp_levels_db->count();
	$data['payments']            = $rcp_payments_db->count();
	$data['upgraded_to_pro']     = ! empty( $upgraded );

	return $data;

}


/**
 * Set rcp_manage_settings as the cap required to save RCP settings pages
 *
 * @since 2.0
 * @return string capability required
 */
function rcp_set_settings_cap() {
	return 'rcp_manage_settings';
}
add_filter( 'option_page_capability_rcp_settings_group', 'rcp_set_settings_cap' );
