<?php

function pw_rcp_register_2checkout_gateway( $gateways ) {
	
	$gateways['2checkout'] = array(
		'label'        => 'Custom Payment',
		'admin_label'  => 'Custom Payment Redirect',
		'class'        => 'RCP_Payment_Gateway_Custom'
	);
	return $gateways;
}
add_filter( 'rcp_payment_gateways', 'pw_rcp_register_custom_gateway' );
