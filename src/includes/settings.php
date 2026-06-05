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
 * Adds the OpenAgenda settings page under Events.
 */
function openagenda_add_settings_page() {
	add_submenu_page(
		'edit.php?post_type=openagenda_event',
		__( 'OpenAgenda Settings', 'openagenda-events-calendar' ),
		__( 'Settings', 'openagenda-events-calendar' ),
		'manage_options',
		'openagenda-settings',
		'openagenda_render_settings_page'
	);
}
add_action( 'admin_menu', 'openagenda_add_settings_page' );

/**
 * Registers plugin settings.
 */
function openagenda_register_settings() {
	register_setting(
		'openagenda_settings',
		OPENAGENDA_DELETE_DATA_OPTION,
		array(
			'type'              => 'string',
			'default'           => '0',
			'sanitize_callback' => 'openagenda_sanitize_checkbox_option',
		)
	);
}
add_action( 'admin_init', 'openagenda_register_settings' );

/**
 * Sanitizes a checkbox option to a stored string boolean.
 *
 * @param mixed $value Submitted value.
 * @return string
 */
function openagenda_sanitize_checkbox_option( $value ) {
	return ! empty( $value ) && '0' !== (string) $value ? '1' : '0';
}

/**
 * Renders the OpenAgenda settings page.
 */
function openagenda_render_settings_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	$delete_data_on_uninstall = '1' === get_option( OPENAGENDA_DELETE_DATA_OPTION, '0' );
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'OpenAgenda Settings', 'openagenda-events-calendar' ); ?></h1>
		<form method="post" action="options.php">
			<?php settings_fields( 'openagenda_settings' ); ?>
			<table class="form-table" role="presentation">
				<tr>
					<th scope="row"><?php esc_html_e( 'Uninstall behavior', 'openagenda-events-calendar' ); ?></th>
					<td>
						<label for="openagenda_delete_data_on_uninstall">
							<input
								type="checkbox"
								id="openagenda_delete_data_on_uninstall"
								name="<?php echo esc_attr( OPENAGENDA_DELETE_DATA_OPTION ); ?>"
								value="1"
								<?php checked( $delete_data_on_uninstall ); ?>
							/>
							<?php esc_html_e( 'Delete OpenAgenda events, event topics, and event metadata when uninstalling the plugin.', 'openagenda-events-calendar' ); ?>
						</label>
						<p class="description">
							<?php esc_html_e( 'Leave this unchecked to keep event content after uninstalling. Page content that contains OpenAgenda shortcodes or blocks is not changed.', 'openagenda-events-calendar' ); ?>
						</p>
					</td>
				</tr>
			</table>
			<?php submit_button(); ?>
		</form>
	</div>
	<?php
}

/**
 * Adds a Settings link on the Plugins screen.
 *
 * @param array $links Existing plugin action links.
 * @return array
 */
function openagenda_plugin_action_links( $links ) {
	$settings_link = sprintf(
		'<a href="%1$s">%2$s</a>',
		esc_url( admin_url( 'edit.php?post_type=openagenda_event&page=openagenda-settings' ) ),
		esc_html__( 'Settings', 'openagenda-events-calendar' )
	);

	array_unshift( $links, $settings_link );

	return $links;
}
add_filter( 'plugin_action_links_' . plugin_basename( OPENAGENDA_PLUGIN_FILE ), 'openagenda_plugin_action_links' );

