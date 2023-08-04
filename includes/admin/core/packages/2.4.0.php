<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'UM' ) ) {
	return;
}

if ( function_exists( 'um_maybe_unset_time_limit' ) ) {
	um_maybe_unset_time_limit();
}

// make sure that DB has a new structure
UM()->Groups()->setup()->sql_setup();

global $wpdb;

$dates = $wpdb->get_results( "SELECT id, date_joined FROM {$wpdb->prefix}um_groups_members", ARRAY_A );

// Find the difference between WordPress time settings and server time.
$server_time    = time();
$wordpress_time = current_time( 'timestamp' );
$time_diff      = $wordpress_time - $server_time;

// Set `date_joined_gmt` time.
if ( ! empty( $dates ) ) {
	foreach ( $dates as $date_data ) {
		$gmdate = gmdate( 'Y-m-d H:i:s', strtotime( $date_data['date_joined'] ) - $time_diff );

		$wpdb->update(
			"{$wpdb->prefix}um_groups_members",
			array(
				'date_joined_gmt' => $gmdate,
			),
			array(
				'id' => $date_data['id'],
			),
			array(
				'%s',
			),
			array(
				'%d',
			)
		);
	}
}
