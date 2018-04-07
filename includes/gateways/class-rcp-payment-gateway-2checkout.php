<?php
/**
 * 2Checkout Payment Gateway
 *
 * @package     Restrict Content Pro
 * @subpackage  Classes/Gateways/2Checkout
 * @copyright   Copyright (c) 2017, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       2.3
 */

class RCP_Payment_Gateway_2Checkout extends RCP_Payment_Gateway {

	private $secret_word;
	private $secret_key;
	private $publishable_key;
	private $seller_id;
	private $environment;

	/**
	 * Get things going
	 *
	 * @access public
	 * @since  2.3
	 * @return void
	 */
	public function init() {
		global $rcp_options;

		$this->supports[]  = 'one-time';
		$this->supports[]  = 'recurring';
		$this->supports[]  = 'fees';
		$this->supports[]  = 'gateway-submits-form';

		$this->secret_word = isset( $rcp_options['twocheckout_secret_word'] ) ? trim( $rcp_options['twocheckout_secret_word'] ) : '';

		if( $this->test_mode ) {

			$this->secret_key      = isset( $rcp_options['twocheckout_test_private'] )     ? trim( $rcp_options['twocheckout_test_private'] )     : '';
			$this->publishable_key = isset( $rcp_options['twocheckout_test_publishable'] ) ? trim( $rcp_options['twocheckout_test_publishable'] ) : '';
			$this->seller_id       = isset( $rcp_options['twocheckout_test_seller_id'] )   ? trim( $rcp_options['twocheckout_test_seller_id'] )   : '';
			$this->environment     = 'sandbox';

		} else {

			$this->secret_key      = isset( $rcp_options['twocheckout_live_private'] )     ? trim( $rcp_options['twocheckout_live_private'] )     : '';
			$this->publishable_key = isset( $rcp_options['twocheckout_live_publishable'] ) ? trim( $rcp_options['twocheckout_live_publishable'] ) : '';
			$this->seller_id       = isset( $rcp_options['twocheckout_live_seller_id'] )   ? trim( $rcp_options['twocheckout_live_seller_id'] )   : '';
			$this->environment     = 'production';

		}

		if( ! class_exists( 'Twocheckout' ) ) {
			require_once RCP_PLUGIN_DIR . 'includes/libraries/twocheckout/Twocheckout.php';
		}

	} // end init

	/**
	 * Process registration
	 *
	 * @access public
	 * @since  2.3
	 * @return void
	 */
	public function process_signup() {

		Twocheckout::privateKey( $this->secret_key );
		Twocheckout::sellerId( $this->seller_id );
		Twocheckout::sandbox( $this->test_mode );

		/**
		 * @var RCP_Payments $rcp_payments_db
		 */
		global $rcp_payments_db;

		$member = new RCP_Member( $this->user_id );

		if( empty( $_POST['twoCheckoutToken'] ) ) {
			rcp_errors()->add( 'missing_card_token', __( 'Missing 2Checkout token, please try again or contact support if the issue persists.', 'rcp' ), 'register' );
			return;
		}

		$paid = false;

		if ( $this->auto_renew ) {

			$payment_type = 'Credit Card';
			$line_items   = array( array(
				"recurrence"  => $this->length . ' ' . ucfirst( $this->length_unit ),
				"type"        => 'product',
				"price"       => $this->amount,
				"productId"   => $this->subscription_id,
				"name"        => $this->subscription_name,
				"quantity"    => '1',
				"tangible"    => 'N',
				"startupFee"  => $this->initial_amount - $this->amount
			) );

		} else {

			$payment_type = 'Credit Card One Time';
			$line_items   = array( array(
				"recurrence"  => 0,
				"type"        => 'product',
				"price"       => $this->initial_amount,
				"productId"   => $this->subscription_id,
				"name"        => $this->subscription_name,
				"quantity"    => '1',
				"tangible"    => 'N'
			) );

		}

		try {

			$charge = Twocheckout_Charge::auth( array(
				'merchantOrderId' => $this->subscription_key,
				'token'           => $_POST['twoCheckoutToken'],
				'currency'        => strtolower( $this->currency ),
				'billingAddr'     => array(
					'name'        => sanitize_text_field( $_POST['rcp_card_name'] ),
					'addrLine1'   => sanitize_text_field( $_POST['rcp_card_address'] ),
					'city'        => sanitize_text_field( $_POST['rcp_card_city'] ),
					'state'       => sanitize_text_field( $_POST['rcp_card_state'] ),
					'zipCode'     => sanitize_text_field( $_POST['rcp_card_zip'] ),
					'country'     => sanitize_text_field( $_POST['rcp_card_country'] ),
					'email'       => $this->email,
				),
				"lineItems"       => $line_items,
			));

			if( $charge['response']['responseCode'] == 'APPROVED' ) {

				// Look to see if we have an existing subscription to cancel
				if( $member->just_upgraded() && $member->can_cancel() ) {
					$member->cancel_payment_profile( false );
				}

				$member->set_recurring( $this->auto_renew );

				// This activates the user's account.
				$rcp_payments_db->update( $this->payment->id, array(
					'date'             => date( 'Y-m-d H:i:s', current_time( 'timestamp' ) ),
					'payment_type'     => $payment_type,
					'transaction_id'   => $charge['response']['orderNumber'],
					'status'           => 'complete'
				) );

				do_action( 'rcp_gateway_payment_processed', $member, $this->payment->id, $this );

				$paid = true;
			}

		} catch ( Twocheckout_Error $e ) {

			$this->error_message = $e->getMessage();
			do_action( 'rcp_registration_failed', $this );
			wp_die( $e->getMessage(), __( 'Error', 'rcp' ), array( 'response' => '401' ) );

		}

		if ( $paid ) {

			$member->add_note( __( 'Subscription started in 2Checkout', 'rcp' ) );

			$member->set_payment_profile_id( '2co_' . $charge['response']['orderNumber'] );

			if ( ! is_user_logged_in() ) {

				// log the new user in
				rcp_login_user_in( $this->user_id, $this->user_name, $_POST['rcp_user_pass'] );

			}

			do_action( 'rcp_2co_signup', $this->user_id, $this );

		}

		// redirect to the success page, or error page if something went wrong
		wp_redirect( $this->return_url ); exit;
	}

	/**
	 * Proccess webhooks
	 *
	 * @access public
	 * @since  2.3
	 * @return void
	 */
	public function process_webhooks() {

		if ( isset( $_GET['listener'] ) && $_GET['listener'] == '2checkout' ) {

			rcp_log( 'Starting to process 2Checkout webhook.' );

			global $wpdb;

			$hash  = strtoupper( md5( $_POST['sale_id'] . $this->seller_id . $_POST['invoice_id'] . $this->secret_word ) );

			if ( ! hash_equals( $hash, $_POST['md5_hash'] ) ) {
				rcp_log( 'Exiting 2Checkout webhook - invalid MD5 hash.' );

				die('-1');
			}

			if ( empty( $_POST['message_type'] ) ) {
				rcp_log( 'Exiting 2Checkout webhook - empty message_type.' );

				die( '-2' );
			}

			if ( empty( $_POST['vendor_id'] ) ) {
				rcp_log( 'Exiting 2Checkout webhook - empty vendor_id.' );

				die( '-3' );
			}

			$subscription_key = sanitize_text_field( $_POST['vendor_order_id'] );
			$member_id        = $wpdb->get_var( $wpdb->prepare( "SELECT user_id FROM $wpdb->usermeta WHERE meta_key = 'rcp_subscription_key' AND meta_value = %s LIMIT 1", $subscription_key ) );

			if ( ! $member_id ) {
				rcp_log( 'Exiting 2Checkout webhook - member ID not found.' );

				die( '-4' );
			}

			$member = new RCP_Member( $member_id );

			if( ! rcp_is_2checkout_subscriber( $member->ID ) ) {
				rcp_log( 'Exiting 2Checkout webhook - member is not a 2Checkout subscriber.' );

				return;
			}

			rcp_log( sprintf( 'Processing webhook for member #%d.', $member->ID ) );

			$payments = new RCP_Payments();

			switch( strtoupper( $_POST['message_type'] ) ) {

				case 'ORDER_CREATED' :
					rcp_log( 'Processing 2Checkout ORDER_CREATED webhook.' );

					break;

				case 'REFUND_ISSUED' :

					rcp_log( sprintf( 'Processing 2Checkout REFUND_ISSUED webhook for invoice ID %s.', $_POST['invoice_id'] ) );

					$payment = $payments->get_payment_by( 'transaction_id', $_POST['invoice_id'] );
					$payments->update( $payment->id, array( 'status' => 'refunded' ) );

					if( ! empty( $_POST['recurring'] ) ) {

						$member->cancel();
						$member->add_note( __( 'Subscription cancelled via refund 2Checkout', 'rcp' ) );

					}

					break;

				case 'RECURRING_INSTALLMENT_SUCCESS' :

					rcp_log( sprintf( 'Processing 2Checkout RECURRING_INSTALLMENT_SUCCESS webhook for member #%d.', $member->ID ) );

					$payment_data = array(
						'date'             => date( 'Y-m-d H:i:s', strtotime( $_POST['timestamp'], current_time( 'timestamp' ) ) ),
						'subscription'     => $member->get_subscription_name(),
						'payment_type'     => sanitize_text_field( $_POST['payment_type'] ),
						'subscription_key' => $subscription_key,
						'amount'           => sanitize_text_field( $_POST['item_list_amount_1'] ), // don't have a total from this call, but this should be safe
						'user_id'          => $member->ID,
						'transaction_id'   => sanitize_text_field( $_POST['invoice_id'] )
					);

					$recurring = ! empty( $_POST['recurring'] );
					$member->renew( $recurring );
					$payment_id = $payments->insert( $payment_data );
					$member->add_note( __( 'Subscription renewed in 2Checkout', 'rcp' ) );

					do_action( 'rcp_webhook_recurring_payment_processed', $member, $payment_id, $this );
					do_action( 'rcp_gateway_payment_processed', $member, $payment_id, $this );

					break;

				case 'RECURRING_INSTALLMENT_FAILED' :

					rcp_log( 'Processing 2Checkout RECURRING_INSTALLMENT_FAILED webhook.' );

					if ( ! empty( $_POST['sale_id'] ) ) {
						$this->webhook_event_id = sanitize_text_field( $_POST['sale_id'] );
					}

					do_action( 'rcp_recurring_payment_failed', $member, $this );

					break;

				case 'RECURRING_STOPPED' :

					rcp_log( 'Processing 2Checkout RECURRING_STOPPED webhook.' );

					if( ! $member->just_upgraded() ) {

						$member->cancel();
						$member->add_note( __( 'Subscription cancelled in 2Checkout', 'rcp' ) );

						do_action( 'rcp_webhook_cancel', $member, $this );

					}


					break;

				case 'RECURRING_COMPLETE' :

					rcp_log( 'Processing 2Checkout RECURRING_COMPLETE webhook.' );

					break;

				case 'RECURRING_RESTARTED' :

					rcp_log( 'Processing 2Checkout RECURRING_RESTARTED webhook.' );

					$member->set_status( 'active' );
					$member->add_note( __( 'Subscription restarted in 2Checkout', 'rcp' ) );

					do_action( 'rcp_webhook_recurring_payment_profile_created', $member, $this );

					break;


				case 'FRAUD_STATUS_CHANGED' :

					rcp_log( sprintf( 'Processing 2Checkout FRAUD_STATUS_CHANGED webhook. Status: %s', $_POST['fraud_status'] ) );

					switch ( $_POST['fraud_status'] ) {
						case 'pass':
							break;
						case 'fail':

							$member->set_status( 'pending' );
							$member->add_note( __( 'Payment flagged as fraudulent in 2Checkout', 'rcp' ) );

							break;
						case 'wait':
							break;
					}

					break;
			}

			do_action( 'rcp_2co_' . strtolower( $_POST['message_type'] ) . '_ins', $member );
			die( 'success');
		}
	}

	/**
	 * Display fields and add extra JavaScript
	 *
	 * @access public
	 * @since  2.3
	 * @return void
	 */
	public function fields() {
		ob_start();
		?>
		<script type="text/javascript">
			// Called when token created successfully.
			var successCallback = function(data) {
				// re-enable the submit button
				jQuery('#rcp_registration_form #rcp_submit').attr("disabled", false);
				// Remove loding overlay
				jQuery('#rcp_ajax_loading').hide();

				var form$ = jQuery('#rcp_registration_form');
				// token contains id, last4, and card type
				var token = data.response.token.token;
				// insert the token into the form so it gets submitted to the server
				form$.append("<input type='hidden' name='twoCheckoutToken' value='" + token + "' />");

				form$.get(0).submit();
			};
			// Called when token creation fails.
			var errorCallback = function(data) {
				if (data.errorCode === 200) {
					tokenRequest();
				} else {

					jQuery('#rcp_registration_form').unblock();
					jQuery('#rcp_submit').before( '<div class="rcp_message error"><p class="rcp_error"><span>' + data.errorMsg + '</span></p></div>' );
					jQuery('#rcp_submit').val( rcp_script_options.register );

				}
			};
			var tokenRequest = function() {
				// Setup token request arguments
				var args = {
					sellerId: '<?php echo $this->seller_id; ?>',
					publishableKey: '<?php echo $this->publishable_key; ?>',
					ccNo: jQuery('.rcp_card_number').val(),
					cvv: jQuery('.rcp_card_cvc').val(),
					expMonth: jQuery('.rcp_card_exp_month').val(),
					expYear: jQuery('.rcp_card_exp_year').val()
				};
				// Make the token request
				TCO.requestToken(successCallback, errorCallback, args);
			};
			jQuery(document).ready(function($) {
				// Pull in the public encryption key for our environment
				TCO.loadPubKey('<?php echo $this->environment; ?>');

				jQuery('body').off('rcp_register_form_submission').on('rcp_register_form_submission', function rcp_2co_register_form_submission_handler(event, response, form_id) {

					if ( response.gateway.slug !== 'twocheckout' ) {
						return;
					}

					event.preventDefault();

					if( jQuery('.rcp_level:checked').length ) {
						var price = jQuery('.rcp_level:checked').closest('.rcp_subscription_level').find('span.rcp_price').attr('rel');
					} else {
						var price = jQuery('.rcp_level').attr('rel');
					}

					if( price > 0 && ! jQuery('.rcp_gateway_fields').hasClass('rcp_discounted_100') ) {


						// Call our token request function
						tokenRequest();

						// Prevent form from submitting
						return false;

					}
				});
			});
		</script>
		<?php
		rcp_get_template_part( 'card-form', 'full' );
		return ob_get_clean();
	}

	/**
	 * Validate additional fields during registration submission
	 *
	 * @access public
	 * @since  2.3
	 * @return void
	 */
	public function validate_fields() {

		if( empty( $_POST['rcp_card_cvc'] ) ) {
			rcp_errors()->add( 'missing_card_code', __( 'The security code you have entered is invalid', 'rcp' ), 'register' );
		}

		if( empty( $_POST['rcp_card_address'] ) ) {
			rcp_errors()->add( 'missing_card_address', __( 'The address you have entered is invalid', 'rcp' ), 'register' );
		}

		if( empty( $_POST['rcp_card_city'] ) ) {
			rcp_errors()->add( 'missing_card_city', __( 'The city you have entered is invalid', 'rcp' ), 'register' );
		}

		if( empty( $_POST['rcp_card_state'] ) && $this->card_needs_state_and_zip() ) {
			rcp_errors()->add( 'missing_card_state', __( 'The state you have entered is invalid', 'rcp' ), 'register' );
		}

		if( empty( $_POST['rcp_card_country'] ) ) {
			rcp_errors()->add( 'missing_card_country', __( 'The country you have entered is invalid', 'rcp' ), 'register' );
		}

		if( empty( $_POST['rcp_card_zip'] ) && $this->card_needs_state_and_zip() ) {
			rcp_errors()->add( 'missing_card_zip', __( 'The zip / postal code you have entered is invalid', 'rcp' ), 'register' );
		}

	}

	/**
	 * Load 2Checkout JS
	 *
	 * @access public
	 * @since  2.3
	 * @return void
	 */
	public function scripts() {
		wp_enqueue_script( 'twocheckout', 'https://www.2checkout.com/checkout/api/2co.min.js', array( 'jquery' ) );
	}

	/**
	 * Determine if zip / state are required
	 *
	 * @access private
	 * @since  2.3
	 * @return bool
	 */
	private function card_needs_state_and_zip() {

		$ret = true;

		if( ! empty( $_POST['rcp_card_country'] ) ) {

			$needs_zip = array(
				'AR',
				'AU',
				'BG',
				'CA',
				'CH',
				'CY',
				'EG',
				'FR',
				'IN',
				'ID',
				'IT',
				'JP',
				'MY',
				'ME',
				'NL',
				'PA',
				'PH',
				'PO',
				'RO',
				'RU',
				'SR',
				'SG',
				'ZA',
				'ES',
				'SW',
				'TH',
				'TU',
				'GB',
				'US'
			);

			if( ! in_array( $_POST['rcp_card_country'], $needs_zip ) ) {
				$ret = false;
			}

		}

		return $ret;
	}
}
