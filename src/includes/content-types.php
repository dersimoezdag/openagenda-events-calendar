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
 * Registers the Event post type and event topic taxonomy.
 */
function openagenda_register_content_types() {
	$event_labels = array(
		'name'                  => _x( 'Events', 'post type general name', 'openagenda-events-calendar' ),
		'singular_name'         => _x( 'Event', 'post type singular name', 'openagenda-events-calendar' ),
		'menu_name'             => _x( 'Events', 'admin menu', 'openagenda-events-calendar' ),
		'name_admin_bar'        => _x( 'Event', 'add new on admin bar', 'openagenda-events-calendar' ),
		'add_new'               => _x( 'Add New', 'event', 'openagenda-events-calendar' ),
		'add_new_item'          => __( 'Add New Event', 'openagenda-events-calendar' ),
		'new_item'              => __( 'New Event', 'openagenda-events-calendar' ),
		'edit_item'             => __( 'Edit Event', 'openagenda-events-calendar' ),
		'view_item'             => __( 'View Event', 'openagenda-events-calendar' ),
		'all_items'             => __( 'All Events', 'openagenda-events-calendar' ),
		'search_items'          => __( 'Search Events', 'openagenda-events-calendar' ),
		'not_found'             => __( 'No events found.', 'openagenda-events-calendar' ),
		'not_found_in_trash'    => __( 'No events found in Trash.', 'openagenda-events-calendar' ),
		'featured_image'        => __( 'Event image', 'openagenda-events-calendar' ),
		'set_featured_image'    => __( 'Set event image', 'openagenda-events-calendar' ),
		'remove_featured_image' => __( 'Remove event image', 'openagenda-events-calendar' ),
	);

	register_post_type(
		'openagenda_event',
		array(
			'labels'       => $event_labels,
			'public'       => true,
			'show_in_rest' => true,
			'menu_icon'    => 'dashicons-calendar-alt',
			'supports'     => array( 'title', 'editor', 'excerpt', 'thumbnail', 'revisions', 'custom-fields' ),
			'rewrite'      => array( 'slug' => 'events' ),
			'has_archive'  => true,
		)
	);

	register_taxonomy(
		'openagenda_event_topic',
		'openagenda_event',
		array(
			'labels'            => array(
				'name'          => _x( 'Event Topics', 'taxonomy general name', 'openagenda-events-calendar' ),
				'singular_name' => _x( 'Event Topic', 'taxonomy singular name', 'openagenda-events-calendar' ),
				'search_items'  => __( 'Search Event Topics', 'openagenda-events-calendar' ),
				'all_items'     => __( 'All Event Topics', 'openagenda-events-calendar' ),
				'edit_item'     => __( 'Edit Event Topic', 'openagenda-events-calendar' ),
				'update_item'   => __( 'Update Event Topic', 'openagenda-events-calendar' ),
				'add_new_item'  => __( 'Add New Event Topic', 'openagenda-events-calendar' ),
				'new_item_name' => __( 'New Event Topic Name', 'openagenda-events-calendar' ),
				'menu_name'     => __( 'Event Topics', 'openagenda-events-calendar' ),
			),
			'hierarchical'      => false,
			'public'            => true,
			'show_admin_column' => true,
			'show_in_rest'      => true,
			'rewrite'           => array( 'slug' => 'event-topic' ),
		)
	);
}
add_action( 'init', 'openagenda_register_content_types' );

/**
 * Registers event metadata for the REST API and block editor.
 */
function openagenda_register_event_meta() {
	$meta_fields = array(
		'_openagenda_start_date'          => array( 'type' => 'string', 'default' => '' ),
		'_openagenda_start_time'          => array( 'type' => 'string', 'default' => '' ),
		'_openagenda_end_date'            => array( 'type' => 'string', 'default' => '' ),
		'_openagenda_end_time'            => array( 'type' => 'string', 'default' => '' ),
		'_openagenda_location'            => array( 'type' => 'string', 'default' => '' ),
		'_openagenda_external_url'        => array( 'type' => 'string', 'default' => '' ),
		'_openagenda_color'               => array( 'type' => 'string', 'default' => '#ffffff' ),
		'_openagenda_recurrence'          => array( 'type' => 'string', 'default' => 'none' ),
		'_openagenda_recurrence_interval' => array( 'type' => 'integer', 'default' => 1 ),
		'_openagenda_recurrence_until'    => array( 'type' => 'string', 'default' => '' ),
		'_openagenda_all_day'             => array( 'type' => 'string', 'default' => '0' ),
	);

	foreach ( $meta_fields as $meta_key => $schema ) {
		register_post_meta(
			'openagenda_event',
			$meta_key,
			array(
				'type'              => $schema['type'],
				'single'            => true,
				'default'           => $schema['default'],
				'show_in_rest'      => array(
					'schema' => array(
						'type'    => $schema['type'],
						'default' => $schema['default'],
					),
				),
				'auth_callback'     => function ( $allowed, $meta_key, $post_id ) {
					return $post_id ? current_user_can( 'edit_post', $post_id ) : current_user_can( 'edit_posts' );
				},
				'sanitize_callback' => 'openagenda_sanitize_registered_event_meta',
			)
		);
	}
}
add_action( 'init', 'openagenda_register_event_meta' );

/**
 * Sanitizes registered event metadata.
 *
 * @param mixed  $value    Submitted value.
 * @param string $meta_key Meta key.
 * @return mixed
 */
function openagenda_sanitize_registered_event_meta( $value, $meta_key ) {
	if ( '_openagenda_start_date' === $meta_key || '_openagenda_end_date' === $meta_key || '_openagenda_recurrence_until' === $meta_key ) {
		return openagenda_sanitize_date( $value );
	}

	if ( '_openagenda_start_time' === $meta_key || '_openagenda_end_time' === $meta_key ) {
		return openagenda_sanitize_time( $value );
	}

	if ( '_openagenda_external_url' === $meta_key ) {
		return esc_url_raw( $value );
	}

	if ( '_openagenda_color' === $meta_key ) {
		return sanitize_hex_color( $value ) ? sanitize_hex_color( $value ) : '#ffffff';
	}

	if ( '_openagenda_recurrence' === $meta_key ) {
		return openagenda_sanitize_recurrence( $value );
	}

	if ( '_openagenda_recurrence_interval' === $meta_key ) {
		return max( 1, min( 99, absint( $value ) ) );
	}

	if ( '_openagenda_all_day' === $meta_key ) {
		return ! empty( $value ) && '0' !== (string) $value ? '1' : '0';
	}

	return sanitize_text_field( $value );
}

/**
 * Flush rewrites after activation so event URLs work immediately.
 */
function openagenda_activate() {
	openagenda_register_content_types();
	flush_rewrite_rules();
}
register_activation_hook( OPENAGENDA_PLUGIN_FILE, 'openagenda_activate' );

/**
 * Flush rewrites after deactivation.
 */
function openagenda_deactivate() {
	flush_rewrite_rules();
}
register_deactivation_hook( OPENAGENDA_PLUGIN_FILE, 'openagenda_deactivate' );

