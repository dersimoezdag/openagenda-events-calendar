<?php
/**
 * Plugin Name: OpenAgenda Events Calendar
 * Description: Adds an accessible events calendar and upcoming-events list to any WordPress site.
 * Version: 0.1.41
 * Author: dersim
 * License: GPL-2.0-or-later
 * Text Domain: openagenda-events-calendar
 * Domain Path: /languages
 *
 * @package OpenAgendaEventsCalendar
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'OPENAGENDA_VERSION', '0.1.41' );
define( 'OPENAGENDA_PLUGIN_FILE', __FILE__ );
define( 'OPENAGENDA_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'OPENAGENDA_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'OPENAGENDA_DELETE_DATA_OPTION', 'openagenda_delete_data_on_uninstall' );

/**
 * Loads bundled plugin translations.
 */
function openagenda_load_textdomain() {
	load_plugin_textdomain(
		'openagenda-events-calendar',
		false,
		dirname( plugin_basename( __FILE__ ) ) . '/languages'
	);
}
add_action( 'plugins_loaded', 'openagenda_load_textdomain' );

/**
 * Loads plugin modules.
 */
require_once OPENAGENDA_PLUGIN_DIR . 'includes/content-types.php';
require_once OPENAGENDA_PLUGIN_DIR . 'includes/formatting.php';
require_once OPENAGENDA_PLUGIN_DIR . 'includes/settings.php';
require_once OPENAGENDA_PLUGIN_DIR . 'includes/admin.php';
require_once OPENAGENDA_PLUGIN_DIR . 'includes/assets.php';
require_once OPENAGENDA_PLUGIN_DIR . 'includes/events.php';
require_once OPENAGENDA_PLUGIN_DIR . 'includes/rest.php';
require_once OPENAGENDA_PLUGIN_DIR . 'includes/shortcodes.php';
