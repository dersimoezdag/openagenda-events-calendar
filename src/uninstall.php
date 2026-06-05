<?php
/**
 * Uninstall handler for OpenAgenda Events Calendar.
 *
 * @package OpenAgendaEventsCalendar
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

global $wpdb;

$delete_data_option = 'openagenda_delete_data_on_uninstall';
$delete_data        = '1' === get_option( $delete_data_option, '0' );

delete_option( $delete_data_option );

if ( ! $delete_data ) {
	flush_rewrite_rules();
	return;
}

$event_post_type = 'openagenda_event';
$event_taxonomy  = 'openagenda_event_topic';
$event_meta_keys = array(
	'_openagenda_start_date',
	'_openagenda_start_time',
	'_openagenda_end_date',
	'_openagenda_end_time',
	'_openagenda_location',
	'_openagenda_external_url',
	'_openagenda_color',
	'_openagenda_recurrence',
	'_openagenda_recurrence_interval',
	'_openagenda_recurrence_until',
	'_openagenda_all_day',
);

$event_ids = $wpdb->get_col(
	$wpdb->prepare(
		"SELECT ID FROM {$wpdb->posts} WHERE post_type = %s",
		$event_post_type
	)
);

foreach ( $event_ids as $event_id ) {
	wp_delete_post( (int) $event_id, true );
}

foreach ( $event_meta_keys as $meta_key ) {
	delete_metadata( 'post', 0, $meta_key, '', true );
}

$term_rows = $wpdb->get_results(
	$wpdb->prepare(
		"SELECT term_taxonomy_id, term_id FROM {$wpdb->term_taxonomy} WHERE taxonomy = %s",
		$event_taxonomy
	)
);

if ( ! empty( $term_rows ) ) {
	$term_taxonomy_ids = array_map( 'intval', wp_list_pluck( $term_rows, 'term_taxonomy_id' ) );
	$term_ids          = array_map( 'intval', wp_list_pluck( $term_rows, 'term_id' ) );
	$term_taxonomy_in  = implode( ',', $term_taxonomy_ids );
	$term_id_in        = implode( ',', array_unique( $term_ids ) );
	$delete_term_ids   = $wpdb->get_col(
		"SELECT term_id FROM {$wpdb->term_taxonomy} WHERE term_id IN ({$term_id_in}) GROUP BY term_id HAVING COUNT(*) = 1"
	);

	$wpdb->query( "DELETE FROM {$wpdb->term_relationships} WHERE term_taxonomy_id IN ({$term_taxonomy_in})" );
	$wpdb->query( "DELETE FROM {$wpdb->term_taxonomy} WHERE term_taxonomy_id IN ({$term_taxonomy_in})" );

	if ( ! empty( $delete_term_ids ) ) {
		$delete_term_id_in = implode( ',', array_map( 'intval', $delete_term_ids ) );

		$wpdb->query( "DELETE FROM {$wpdb->termmeta} WHERE term_id IN ({$delete_term_id_in})" );
		$wpdb->query( "DELETE FROM {$wpdb->terms} WHERE term_id IN ({$delete_term_id_in})" );
	}
}

flush_rewrite_rules();
