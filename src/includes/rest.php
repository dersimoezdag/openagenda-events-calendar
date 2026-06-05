<?php
/**
 * OpenAgenda Events Calendar module.
 *
 * @package OpenAgendaEventsCalendar
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Registers REST API route.
 */
function openagenda_register_rest_routes() {
	register_rest_route(
		'openagenda-events-calendar/v1',
		'/events',
		array(
			'methods'             => WP_REST_Server::READABLE,
			'callback'            => 'openagenda_rest_events',
			'permission_callback' => '__return_true',
			'args'                => array(
				'from'  => array(
					'sanitize_callback' => 'openagenda_sanitize_date',
				),
				'to'    => array(
					'sanitize_callback' => 'openagenda_sanitize_date',
				),
				'topic' => array(
					'sanitize_callback' => 'sanitize_title',
				),
			),
		)
	);

	register_rest_route(
		'openagenda-events-calendar/v1',
		'/event-meta/(?P<id>\d+)',
		array(
			'methods'             => WP_REST_Server::EDITABLE,
			'callback'            => 'openagenda_rest_save_event_meta',
			'permission_callback' => 'openagenda_rest_can_save_event_meta',
			'args'                => array(
				'id'   => array(
					'sanitize_callback' => 'absint',
				),
				'meta' => array(
					'type' => 'object',
				),
			),
		)
	);
}
add_action( 'rest_api_init', 'openagenda_register_rest_routes' );

/**
 * Checks whether event metadata can be saved through REST.
 *
 * @param WP_REST_Request $request Request object.
 * @return bool
 */
function openagenda_rest_can_save_event_meta( WP_REST_Request $request ) {
	$post_id = absint( $request->get_param( 'id' ) );
	$post    = get_post( $post_id );

	return $post && 'openagenda_event' === $post->post_type && current_user_can( 'edit_post', $post_id );
}

/**
 * Saves event metadata through a dedicated REST endpoint.
 *
 * @param WP_REST_Request $request Request object.
 * @return WP_REST_Response
 */
function openagenda_rest_save_event_meta( WP_REST_Request $request ) {
	$post_id = absint( $request->get_param( 'id' ) );
	$meta    = $request->get_param( 'meta' );

	if ( ! is_array( $meta ) ) {
		$meta = array();
	}

	$allowed = openagenda_event_meta_keys();

	foreach ( $allowed as $meta_key ) {
		if ( ! array_key_exists( $meta_key, $meta ) ) {
			continue;
		}

		$value = openagenda_sanitize_registered_event_meta( $meta[ $meta_key ], $meta_key );

		if ( '' === $value || null === $value ) {
			delete_post_meta( $post_id, $meta_key );
			continue;
		}

		update_post_meta( $post_id, $meta_key, $value );
	}

	return rest_ensure_response(
		array(
			'success' => true,
			'meta'    => openagenda_get_event_meta_for_response( $post_id ),
		)
	);
}

/**
 * Returns event meta keys managed by this plugin.
 *
 * @return array
 */
function openagenda_event_meta_keys() {
	return array(
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
}

/**
 * Returns current event metadata for REST responses.
 *
 * @param int $post_id Event post ID.
 * @return array
 */
function openagenda_get_event_meta_for_response( $post_id ) {
	$response = array();

	foreach ( openagenda_event_meta_keys() as $meta_key ) {
		$response[ $meta_key ] = get_post_meta( $post_id, $meta_key, true );
	}

	return $response;
}

/**
 * Handles REST event requests.
 *
 * @param WP_REST_Request $request Request object.
 * @return WP_REST_Response
 */
function openagenda_rest_events( WP_REST_Request $request ) {
	$events = openagenda_get_events(
		array(
			'from'  => $request->get_param( 'from' ),
			'to'    => $request->get_param( 'to' ),
			'topic' => $request->get_param( 'topic' ),
			'limit' => 200,
		)
	);

	return rest_ensure_response( $events );
}


