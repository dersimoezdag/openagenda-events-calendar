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
 * Registers scripts, styles, shortcodes, and REST route.
 */
function openagenda_register_frontend_assets() {
	wp_register_style(
		'openagenda-calendar',
		OPENAGENDA_PLUGIN_URL . 'assets/calendar.css',
		array(),
		OPENAGENDA_VERSION
	);

	wp_register_script(
		'openagenda-calendar',
		OPENAGENDA_PLUGIN_URL . 'assets/calendar.js',
		array(),
		OPENAGENDA_VERSION,
		true
	);

	wp_localize_script(
		'openagenda-calendar',
		'openagendaCalendarSettings',
		array(
			'restUrl'      => esc_url_raw( rest_url( 'openagenda-events-calendar/v1/events' ) ),
			'locale'       => str_replace( '_', '-', get_locale() ),
			'firstWeekday' => absint( get_option( 'start_of_week', 1 ) ),
			'labels'       => array(
				'next'      => __( 'Next month', 'openagenda-events-calendar' ),
				'previous'  => __( 'Previous month', 'openagenda-events-calendar' ),
				'today'     => __( 'Today', 'openagenda-events-calendar' ),
				'loading'   => __( 'Loading events...', 'openagenda-events-calendar' ),
				'noEvents'  => __( 'No events this month.', 'openagenda-events-calendar' ),
				'viewEvent' => __( 'View event', 'openagenda-events-calendar' ),
			),
		)
	);
}
add_action( 'init', 'openagenda_register_frontend_assets' );

/**
 * Registers editor assets and dynamic blocks.
 */
function openagenda_register_blocks() {
	wp_register_script(
		'openagenda-upcoming-events-block',
		OPENAGENDA_PLUGIN_URL . 'assets/upcoming-events-block.js',
		array( 'wp-blocks', 'wp-components', 'wp-element', 'wp-i18n', 'wp-block-editor', 'wp-server-side-render' ),
		OPENAGENDA_VERSION,
		true
	);

	wp_set_script_translations( 'openagenda-upcoming-events-block', 'openagenda-events-calendar', OPENAGENDA_PLUGIN_DIR . 'languages' );
	openagenda_add_block_editor_locale_data();

	register_block_type(
		'openagenda-events-calendar/upcoming-events',
		array(
			'api_version'     => 2,
			'title'           => __( 'Upcoming Events', 'openagenda-events-calendar' ),
			'description'     => __( 'Shows a styled list of upcoming events.', 'openagenda-events-calendar' ),
			'category'        => 'widgets',
			'icon'            => 'calendar-alt',
			'editor_script'   => 'openagenda-upcoming-events-block',
			'render_callback' => 'openagenda_render_upcoming_events_block',
			'attributes'      => array(
				'category'  => array(
					'type'    => 'string',
					'default' => '',
				),
				'maxEvents' => array(
					'type'    => 'number',
					'default' => 6,
				),
				'showPlace' => array(
					'type'    => 'boolean',
					'default' => true,
				),
				'showTime'  => array(
					'type'    => 'boolean',
					'default' => true,
				),
				'displayStyle' => array(
					'type'    => 'string',
					'default' => 'list',
				),
			),
		)
	);
}
add_action( 'init', 'openagenda_register_blocks' );

/**
 * Registers admin editor assets.
 */
function openagenda_register_admin_assets() {
	wp_register_style(
		'openagenda-admin',
		OPENAGENDA_PLUGIN_URL . 'assets/admin.css',
		array(),
		OPENAGENDA_VERSION
	);

	wp_register_script(
		'openagenda-event-editor',
		OPENAGENDA_PLUGIN_URL . 'assets/event-editor.js',
		array( 'wp-api-fetch', 'wp-components', 'wp-compose', 'wp-data', 'wp-edit-post', 'wp-element', 'wp-i18n', 'wp-plugins' ),
		OPENAGENDA_VERSION,
		true
	);

	wp_set_script_translations( 'openagenda-event-editor', 'openagenda-events-calendar', OPENAGENDA_PLUGIN_DIR . 'languages' );

	wp_register_script(
		'openagenda-shortcode-generator',
		OPENAGENDA_PLUGIN_URL . 'assets/shortcode-generator.js',
		array(),
		OPENAGENDA_VERSION,
		true
	);

	wp_register_script(
		'openagenda-quick-edit',
		OPENAGENDA_PLUGIN_URL . 'assets/quick-edit.js',
		array( 'inline-edit-post' ),
		OPENAGENDA_VERSION,
		true
	);
}
add_action( 'admin_init', 'openagenda_register_admin_assets' );

/**
 * Enqueues event styles on single event pages.
 */
function openagenda_enqueue_single_event_assets() {
	if ( is_singular( 'openagenda_event' ) ) {
		wp_enqueue_style( 'openagenda-calendar' );
	}
}
add_action( 'wp_enqueue_scripts', 'openagenda_enqueue_single_event_assets' );

/**
 * Adds lightweight JavaScript translations for the event editor sidebar.
 */
function openagenda_add_event_editor_locale_data() {
	$locale = determine_locale();

	if ( 0 !== strpos( $locale, 'de_' ) && 'de' !== $locale ) {
		return;
	}

	$locale_data = array(
		''                    => array(
			'domain' => 'openagenda-events-calendar',
			'lang'   => $locale,
		),
		'Event date, time and place' => array( 'Veranstaltungsdatum, Uhrzeit und Ort' ),
		'Start date'          => array( 'Startdatum' ),
		'Start time'          => array( 'Startzeit' ),
		'End date'            => array( 'Enddatum' ),
		'End time'            => array( 'Endzeit' ),
		'All-day event'       => array( 'Ganztägige Veranstaltung' ),
		'Location'            => array( 'Ort' ),
		'External URL'        => array( 'Externe URL' ),
		'Calendar color'      => array( 'Kalenderfarbe' ),
		'Repeat'              => array( 'Wiederholen' ),
		'Does not repeat'     => array( 'Wiederholt sich nicht' ),
		'Daily'               => array( 'Täglich' ),
		'Weekly'              => array( 'Wöchentlich' ),
		'Monthly'             => array( 'Monatlich' ),
		'Yearly'              => array( 'Jährlich' ),
		'Repeat every'        => array( 'Wiederholen alle' ),
		'Repeat until'        => array( 'Wiederholen bis' ),
	);

	wp_add_inline_script(
		'openagenda-event-editor',
		'wp.i18n.setLocaleData(' . wp_json_encode( $locale_data ) . ', "openagenda-events-calendar");',
		'before'
	);
}

/**
 * Adds lightweight JavaScript translations for the editor block labels.
 */
function openagenda_add_block_editor_locale_data() {
	$locale = determine_locale();

	if ( 0 !== strpos( $locale, 'de_' ) && 'de' !== $locale ) {
		return;
	}

	$locale_data = array(
		''                              => array(
			'domain' => 'openagenda-events-calendar',
			'lang'   => $locale,
		),
		'Event list settings'           => array( 'Einstellungen der Veranstaltungsliste' ),
		'Event category slug'           => array( 'Veranstaltungskategorie-Slug' ),
		'Leave empty to show all event topics.' => array( 'Leer lassen, um alle Veranstaltungsthemen anzuzeigen.' ),
		'Maximum events'                => array( 'Maximale Anzahl Veranstaltungen' ),
		'Show place'                    => array( 'Ort anzeigen' ),
		'Show time'                     => array( 'Uhrzeit anzeigen' ),
		'Style'                         => array( 'Darstellung' ),
		'List'                          => array( 'Liste' ),
		'Minimal list'                  => array( 'Minimale Liste' ),
		'Calendar'                      => array( 'Kalender' ),
	);

	wp_add_inline_script(
		'openagenda-upcoming-events-block',
		'wp.i18n.setLocaleData(' . wp_json_encode( $locale_data ) . ', "openagenda-events-calendar");',
		'before'
	);
}


