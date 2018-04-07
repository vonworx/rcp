<?php
/**
 * MC Payment Gateway
 *
 * @package     Restrict Content Pro
 * @subpackage  Classes/Gateways/2Checkout
 * @copyright   Copyright (c) 2017, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       2.3
 */

class RCP_Payment_Gateway_Mcpayment extends RCP_Payment_Gateway {

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

		//$this->test_mode   = isset( $rcp_options['sandbox'] );		

	} // end init

	/**
	 * Process registration
	 *
	 * @access public
	 * @since  2.3
	 * @return void
	 */
	public function process_signup() {

		// Set up the query args
		$args = array(
			'price'        => $this->amount,
			'description'  => $this->subscription_name,
			'custom'       => $this->user_id,
			'email'        => $this->email,
			'return'       => $this->return_url
		);
		if( $this->auto_renew ) {
			$args['interval']       = $this->length_unit; // month, day, year
			$args['interval_count'] = $this->length; // 1, 2, 3, 4 . . . 
		}
		if( ! empty( $this->signup_fee ) ) {
			$args['one_time_fee']   = $this->signup_fee;
		}
		// Redirect to the external payment page
		//wp_redirect( add_query_arg( $args, 'http://paymentpage.com/api/' ) );
		exit;

	}

	/**
	 * Proccess webhooks
	 *
	 * @access public
	 * @since  2.3
	 * @return void
	 */
	public function process_webhooks() {

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

	}

	/**
	 * Load 2Checkout JS
	 *
	 * @access public
	 * @since  2.3
	 * @return void
	 */
	public function scripts() {
		
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

		return $ret;
	}
}
