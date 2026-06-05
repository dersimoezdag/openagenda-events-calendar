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
 * Adds event details meta box.
 */
function openagenda_add_event_meta_box() {
	add_meta_box(
		'openagenda_event_details',
		__( 'Event Details', 'openagenda-events-calendar' ),
		'openagenda_render_event_meta_box',
		'openagenda_event',
		'normal',
		'high'
	);
}
add_action( 'add_meta_boxes', 'openagenda_add_event_meta_box' );

/**
 * Renders event metadata fields in the editor.
 *
 * @param WP_Post $post Current event post.
 */
function openagenda_render_event_meta_box( $post ) {
	wp_nonce_field( 'openagenda_save_event_meta', 'openagenda_event_meta_nonce' );

	$start_date = get_post_meta( $post->ID, '_openagenda_start_date', true );
	$start_time = get_post_meta( $post->ID, '_openagenda_start_time', true );
	$end_date   = get_post_meta( $post->ID, '_openagenda_end_date', true );
	$end_time   = get_post_meta( $post->ID, '_openagenda_end_time', true );
	$location   = get_post_meta( $post->ID, '_openagenda_location', true );
	$url        = get_post_meta( $post->ID, '_openagenda_external_url', true );
	$all_day    = get_post_meta( $post->ID, '_openagenda_all_day', true );
	$color      = get_post_meta( $post->ID, '_openagenda_color', true );
	$recurrence = get_post_meta( $post->ID, '_openagenda_recurrence', true );
	$interval   = get_post_meta( $post->ID, '_openagenda_recurrence_interval', true );
	$until      = get_post_meta( $post->ID, '_openagenda_recurrence_until', true );

	if ( empty( $color ) ) {
		$color = '#ffffff';
	}

	if ( empty( $recurrence ) ) {
		$recurrence = 'none';
	}

	if ( empty( $interval ) ) {
		$interval = 1;
	}

	?>
	<div class="openagenda-admin-grid">
		<p>
			<label for="openagenda_start_date"><strong><?php esc_html_e( 'Start date', 'openagenda-events-calendar' ); ?></strong></label>
			<input required type="date" id="openagenda_start_date" name="openagenda_start_date" value="<?php echo esc_attr( $start_date ); ?>" />
		</p>
		<p>
			<label for="openagenda_start_time"><strong><?php esc_html_e( 'Start time', 'openagenda-events-calendar' ); ?></strong></label>
			<input type="time" id="openagenda_start_time" name="openagenda_start_time" value="<?php echo esc_attr( $start_time ); ?>" />
		</p>
		<p>
			<label for="openagenda_end_date"><strong><?php esc_html_e( 'End date', 'openagenda-events-calendar' ); ?></strong></label>
			<input type="date" id="openagenda_end_date" name="openagenda_end_date" value="<?php echo esc_attr( $end_date ); ?>" />
		</p>
		<p>
			<label for="openagenda_end_time"><strong><?php esc_html_e( 'End time', 'openagenda-events-calendar' ); ?></strong></label>
			<input type="time" id="openagenda_end_time" name="openagenda_end_time" value="<?php echo esc_attr( $end_time ); ?>" />
		</p>
		<p>
			<label for="openagenda_location"><strong><?php esc_html_e( 'Location', 'openagenda-events-calendar' ); ?></strong></label>
			<input type="text" id="openagenda_location" name="openagenda_location" value="<?php echo esc_attr( $location ); ?>" class="widefat" />
		</p>
		<p>
			<label for="openagenda_external_url"><strong><?php esc_html_e( 'External URL', 'openagenda-events-calendar' ); ?></strong></label>
			<input type="url" id="openagenda_external_url" name="openagenda_external_url" value="<?php echo esc_url( $url ); ?>" class="widefat" />
		</p>
		<p>
			<label for="openagenda_color"><strong><?php esc_html_e( 'Calendar color', 'openagenda-events-calendar' ); ?></strong></label>
			<input type="color" id="openagenda_color" name="openagenda_color" value="<?php echo esc_attr( $color ); ?>" />
		</p>
		<p>
			<label for="openagenda_recurrence"><strong><?php esc_html_e( 'Repeat', 'openagenda-events-calendar' ); ?></strong></label>
			<select id="openagenda_recurrence" name="openagenda_recurrence" class="widefat">
				<option value="none" <?php selected( $recurrence, 'none' ); ?>><?php esc_html_e( 'Does not repeat', 'openagenda-events-calendar' ); ?></option>
				<option value="daily" <?php selected( $recurrence, 'daily' ); ?>><?php esc_html_e( 'Daily', 'openagenda-events-calendar' ); ?></option>
				<option value="weekly" <?php selected( $recurrence, 'weekly' ); ?>><?php esc_html_e( 'Weekly', 'openagenda-events-calendar' ); ?></option>
				<option value="monthly" <?php selected( $recurrence, 'monthly' ); ?>><?php esc_html_e( 'Monthly', 'openagenda-events-calendar' ); ?></option>
				<option value="yearly" <?php selected( $recurrence, 'yearly' ); ?>><?php esc_html_e( 'Yearly', 'openagenda-events-calendar' ); ?></option>
			</select>
		</p>
		<p>
			<label for="openagenda_recurrence_interval"><strong><?php esc_html_e( 'Repeat every', 'openagenda-events-calendar' ); ?></strong></label>
			<input type="number" id="openagenda_recurrence_interval" name="openagenda_recurrence_interval" value="<?php echo esc_attr( $interval ); ?>" min="1" max="99" />
		</p>
		<p>
			<label for="openagenda_recurrence_until"><strong><?php esc_html_e( 'Repeat until', 'openagenda-events-calendar' ); ?></strong></label>
			<input type="date" id="openagenda_recurrence_until" name="openagenda_recurrence_until" value="<?php echo esc_attr( $until ); ?>" />
		</p>
		<p class="openagenda-admin-checkbox">
			<label>
				<input type="checkbox" name="openagenda_all_day" value="1" <?php checked( $all_day, '1' ); ?> />
				<?php esc_html_e( 'All-day event', 'openagenda-events-calendar' ); ?>
			</label>
		</p>
	</div>
	<?php
}

/**
 * Saves event metadata.
 *
 * @param int $post_id Current post ID.
 */
function openagenda_save_event_meta( $post_id ) {
	if ( defined( 'REST_REQUEST' ) && REST_REQUEST ) {
		return;
	}

	if ( ! isset( $_POST['openagenda_event_meta_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['openagenda_event_meta_nonce'] ) ), 'openagenda_save_event_meta' ) ) {
		return;
	}

	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}

	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	if ( ! isset( $_POST['openagenda_start_date'] ) && ! isset( $_POST['openagenda_location'] ) ) {
		return;
	}

	$fields = array(
		'_openagenda_start_date'       => isset( $_POST['openagenda_start_date'] ) ? openagenda_sanitize_date( sanitize_text_field( wp_unslash( $_POST['openagenda_start_date'] ) ) ) : '',
		'_openagenda_start_time'       => isset( $_POST['openagenda_start_time'] ) ? openagenda_sanitize_time( sanitize_text_field( wp_unslash( $_POST['openagenda_start_time'] ) ) ) : '',
		'_openagenda_end_date'         => isset( $_POST['openagenda_end_date'] ) ? openagenda_sanitize_date( sanitize_text_field( wp_unslash( $_POST['openagenda_end_date'] ) ) ) : '',
		'_openagenda_end_time'         => isset( $_POST['openagenda_end_time'] ) ? openagenda_sanitize_time( sanitize_text_field( wp_unslash( $_POST['openagenda_end_time'] ) ) ) : '',
		'_openagenda_location'         => isset( $_POST['openagenda_location'] ) ? sanitize_text_field( wp_unslash( $_POST['openagenda_location'] ) ) : '',
		'_openagenda_external_url'     => isset( $_POST['openagenda_external_url'] ) ? esc_url_raw( wp_unslash( $_POST['openagenda_external_url'] ) ) : '',
		'_openagenda_color'            => isset( $_POST['openagenda_color'] ) ? sanitize_hex_color( sanitize_text_field( wp_unslash( $_POST['openagenda_color'] ) ) ) : '',
		'_openagenda_recurrence'       => isset( $_POST['openagenda_recurrence'] ) ? openagenda_sanitize_recurrence( sanitize_key( wp_unslash( $_POST['openagenda_recurrence'] ) ) ) : '',
		'_openagenda_recurrence_until' => isset( $_POST['openagenda_recurrence_until'] ) ? openagenda_sanitize_date( sanitize_text_field( wp_unslash( $_POST['openagenda_recurrence_until'] ) ) ) : '',
	);

	foreach ( $fields as $meta_key => $value ) {
		if ( '' === $value || null === $value ) {
			delete_post_meta( $post_id, $meta_key );
			continue;
		}

		update_post_meta( $post_id, $meta_key, $value );
	}

	$all_day = isset( $_POST['openagenda_all_day'] ) ? '1' : '0';
	update_post_meta( $post_id, '_openagenda_all_day', $all_day );

	$interval = isset( $_POST['openagenda_recurrence_interval'] ) ? absint( wp_unslash( $_POST['openagenda_recurrence_interval'] ) ) : 1;
	update_post_meta( $post_id, '_openagenda_recurrence_interval', max( 1, min( 99, $interval ) ) );
}
add_action( 'save_post_openagenda_event', 'openagenda_save_event_meta' );

/**
 * Adds event date columns to the event list table.
 *
 * @param array $columns Existing columns.
 * @return array
 */
function openagenda_event_columns( $columns ) {
	$insert = array(
		'openagenda_start'    => __( 'Starts', 'openagenda-events-calendar' ),
		'openagenda_location' => __( 'Location', 'openagenda-events-calendar' ),
	);

	return array_slice( $columns, 0, 2, true ) + $insert + array_slice( $columns, 2, null, true );
}
add_filter( 'manage_openagenda_event_posts_columns', 'openagenda_event_columns' );

/**
 * Prints event list table column values.
 *
 * @param string $column  Column key.
 * @param int    $post_id Post ID.
 */
function openagenda_event_column_content( $column, $post_id ) {
	if ( 'openagenda_start' === $column ) {
		echo esc_html( openagenda_format_event_datetime( $post_id ) );
		openagenda_print_quick_edit_event_data( $post_id );
	}

	if ( 'openagenda_location' === $column ) {
		echo esc_html( get_post_meta( $post_id, '_openagenda_location', true ) );
	}
}
add_action( 'manage_openagenda_event_posts_custom_column', 'openagenda_event_column_content', 10, 2 );

/**
 * Prints hidden event metadata used to populate Quick Edit.
 *
 * @param int $post_id Event post ID.
 */
function openagenda_print_quick_edit_event_data( $post_id ) {
	$data = array(
		'startDate' => get_post_meta( $post_id, '_openagenda_start_date', true ),
		'startTime' => get_post_meta( $post_id, '_openagenda_start_time', true ),
		'endDate'   => get_post_meta( $post_id, '_openagenda_end_date', true ),
		'endTime'   => get_post_meta( $post_id, '_openagenda_end_time', true ),
		'location'  => get_post_meta( $post_id, '_openagenda_location', true ),
		'allDay'    => get_post_meta( $post_id, '_openagenda_all_day', true ),
	);

	printf(
		'<span class="openagenda-quick-edit-data" hidden data-start-date="%1$s" data-start-time="%2$s" data-end-date="%3$s" data-end-time="%4$s" data-location="%5$s" data-all-day="%6$s"></span>',
		esc_attr( $data['startDate'] ),
		esc_attr( $data['startTime'] ),
		esc_attr( $data['endDate'] ),
		esc_attr( $data['endTime'] ),
		esc_attr( $data['location'] ),
		esc_attr( $data['allDay'] )
	);
}

/**
 * Adds event fields to the post list Quick Edit form.
 *
 * @param string $column_name Current column name.
 * @param string $post_type   Current post type.
 */
function openagenda_quick_edit_event_fields( $column_name, $post_type ) {
	if ( 'openagenda_event' !== $post_type || 'openagenda_start' !== $column_name ) {
		return;
	}

	wp_nonce_field( 'openagenda_save_event_meta', 'openagenda_event_meta_nonce' );
	?>
	<fieldset class="inline-edit-col-left openagenda-quick-edit-fields">
		<div class="inline-edit-col">
			<span class="title"><?php esc_html_e( 'Event date, time and place', 'openagenda-events-calendar' ); ?></span>
			<label>
				<span class="title"><?php esc_html_e( 'Start date', 'openagenda-events-calendar' ); ?></span>
				<span class="input-text-wrap">
					<input type="date" name="openagenda_start_date" value="" />
				</span>
			</label>
			<label>
				<span class="title"><?php esc_html_e( 'Start time', 'openagenda-events-calendar' ); ?></span>
				<span class="input-text-wrap">
					<input type="time" name="openagenda_start_time" value="" />
				</span>
			</label>
			<label>
				<span class="title"><?php esc_html_e( 'End date', 'openagenda-events-calendar' ); ?></span>
				<span class="input-text-wrap">
					<input type="date" name="openagenda_end_date" value="" />
				</span>
			</label>
			<label>
				<span class="title"><?php esc_html_e( 'End time', 'openagenda-events-calendar' ); ?></span>
				<span class="input-text-wrap">
					<input type="time" name="openagenda_end_time" value="" />
				</span>
			</label>
			<label>
				<span class="title"><?php esc_html_e( 'Location', 'openagenda-events-calendar' ); ?></span>
				<span class="input-text-wrap">
					<input type="text" name="openagenda_location" value="" />
				</span>
			</label>
			<label class="inline-edit-group">
				<span class="title"><?php esc_html_e( 'All-day event', 'openagenda-events-calendar' ); ?></span>
				<input type="checkbox" name="openagenda_all_day" value="1" />
			</label>
		</div>
	</fieldset>
	<?php
}
add_action( 'quick_edit_custom_box', 'openagenda_quick_edit_event_fields', 10, 2 );

/**
 * Makes start column sortable.
 *
 * @param array $columns Sortable columns.
 * @return array
 */
function openagenda_sortable_event_columns( $columns ) {
	$columns['openagenda_start'] = 'openagenda_start';
	return $columns;
}
add_filter( 'manage_edit-openagenda_event_sortable_columns', 'openagenda_sortable_event_columns' );

/**
 * Applies archive filtering and explicit event start date sorting in admin.
 *
 * @param WP_Query $query Query instance.
 */
function openagenda_admin_event_ordering( $query ) {
	if ( ! is_admin() || ! $query->is_main_query() || 'openagenda_event' !== $query->get( 'post_type' ) ) {
		return;
	}

	$post_status = $query->get( 'post_status' );

	if ( in_array( $post_status, array( 'trash', 'auto-draft' ), true ) ) {
		return;
	}

	if ( 'archive' === openagenda_get_admin_event_time_filter() ) {
		// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query -- Event date filtering relies on registered post meta.
		$query->set( 'meta_query', openagenda_get_admin_event_archive_meta_query() );
	}

	if ( 'openagenda_start' === $query->get( 'orderby' ) ) {
		// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key -- Admin event sorting uses the registered start-date meta field.
		$query->set( 'meta_key', '_openagenda_start_date' );
		// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value -- Admin event sorting uses the registered start-date meta field.
		$query->set( 'orderby', 'meta_value' );
	}
}
add_action( 'pre_get_posts', 'openagenda_admin_event_ordering' );

/**
 * Returns the selected admin event time filter.
 *
 * @return string
 */
function openagenda_get_admin_event_time_filter() {
	if ( ! isset( $_GET['openagenda_event_time_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['openagenda_event_time_nonce'] ) ), 'openagenda_filter_events' ) ) {
		return 'active';
	}

	$filter = isset( $_GET['openagenda_event_time'] ) ? sanitize_key( wp_unslash( $_GET['openagenda_event_time'] ) ) : '';

	return 'archive' === $filter ? 'archive' : 'active';
}

/**
 * Adds an archive view to the admin event table.
 *
 * @param array $views Existing view links.
 * @return array
 */
function openagenda_admin_event_views( $views ) {
	$current = openagenda_get_admin_event_time_filter();

	$archive_url = wp_nonce_url( add_query_arg( 'openagenda_event_time', 'archive', admin_url( 'edit.php?post_type=openagenda_event' ) ), 'openagenda_filter_events', 'openagenda_event_time_nonce' );

	$views['openagenda_event_archive'] = sprintf(
		'<a href="%1$s"%2$s>%3$s</a>',
		esc_url( $archive_url ),
		'archive' === $current ? ' class="current" aria-current="page"' : '',
		esc_html__( 'Archive', 'openagenda-events-calendar' )
	);

	return $views;
}
add_filter( 'views_edit-openagenda_event', 'openagenda_admin_event_views' );

/**
 * Returns meta query for active events in the admin list.
 *
 * @return array
 */
function openagenda_get_admin_event_active_meta_query() {
	$today = current_time( 'Y-m-d' );

	return array(
		'relation' => 'AND',
		array(
			'key'     => '_openagenda_start_date',
			'compare' => 'EXISTS',
		),
		array(
			'relation' => 'OR',
			array(
				'relation' => 'AND',
				openagenda_get_admin_event_non_recurring_meta_query(),
				openagenda_get_admin_event_active_date_meta_query( $today ),
			),
			array(
				'relation' => 'AND',
				openagenda_get_admin_event_recurring_meta_query(),
				openagenda_get_admin_event_active_recurrence_meta_query( $today ),
			),
		),
	);
}

/**
 * Returns meta query for archived events in the admin list.
 *
 * @return array
 */
function openagenda_get_admin_event_archive_meta_query() {
	$today = current_time( 'Y-m-d' );

	return array(
		'relation' => 'AND',
		array(
			'key'     => '_openagenda_start_date',
			'compare' => 'EXISTS',
		),
		array(
			'relation' => 'OR',
			array(
				'relation' => 'AND',
				openagenda_get_admin_event_non_recurring_meta_query(),
				openagenda_get_admin_event_past_date_meta_query( $today ),
			),
			array(
				'relation' => 'AND',
				openagenda_get_admin_event_recurring_meta_query(),
				openagenda_get_admin_event_past_recurrence_meta_query( $today ),
			),
		),
	);
}

/**
 * Returns meta query for non-recurring events.
 *
 * @return array
 */
function openagenda_get_admin_event_non_recurring_meta_query() {
	return array(
		'relation' => 'OR',
		array(
			'key'     => '_openagenda_recurrence',
			'compare' => 'NOT EXISTS',
		),
		array(
			'key'     => '_openagenda_recurrence',
			'value'   => '',
			'compare' => '=',
		),
		array(
			'key'     => '_openagenda_recurrence',
			'value'   => 'none',
			'compare' => '=',
		),
	);
}

/**
 * Returns meta query for recurring events.
 *
 * @return array
 */
function openagenda_get_admin_event_recurring_meta_query() {
	return array(
		'key'     => '_openagenda_recurrence',
		'value'   => array( 'daily', 'weekly', 'monthly', 'yearly' ),
		'compare' => 'IN',
	);
}

/**
 * Returns meta query for active non-recurring event dates.
 *
 * @param string $today Current WordPress date.
 * @return array
 */
function openagenda_get_admin_event_active_date_meta_query( $today ) {
	return array(
		'relation' => 'OR',
		array(
			'relation' => 'AND',
			array(
				'key'     => '_openagenda_end_date',
				'value'   => '',
				'compare' => '!=',
			),
			array(
				'key'     => '_openagenda_end_date',
				'value'   => $today,
				'compare' => '>=',
				'type'    => 'DATE',
			),
		),
		array(
			'relation' => 'AND',
			array(
				'key'     => '_openagenda_end_date',
				'compare' => 'NOT EXISTS',
			),
			array(
				'key'     => '_openagenda_start_date',
				'value'   => $today,
				'compare' => '>=',
				'type'    => 'DATE',
			),
		),
		array(
			'relation' => 'AND',
			array(
				'key'     => '_openagenda_end_date',
				'value'   => '',
				'compare' => '=',
			),
			array(
				'key'     => '_openagenda_start_date',
				'value'   => $today,
				'compare' => '>=',
				'type'    => 'DATE',
			),
		),
	);
}

/**
 * Returns meta query for archived non-recurring event dates.
 *
 * @param string $today Current WordPress date.
 * @return array
 */
function openagenda_get_admin_event_past_date_meta_query( $today ) {
	return array(
		'relation' => 'OR',
		array(
			'relation' => 'AND',
			array(
				'key'     => '_openagenda_end_date',
				'value'   => '',
				'compare' => '!=',
			),
			array(
				'key'     => '_openagenda_end_date',
				'value'   => $today,
				'compare' => '<',
				'type'    => 'DATE',
			),
		),
		array(
			'relation' => 'AND',
			array(
				'key'     => '_openagenda_end_date',
				'compare' => 'NOT EXISTS',
			),
			array(
				'key'     => '_openagenda_start_date',
				'value'   => $today,
				'compare' => '<',
				'type'    => 'DATE',
			),
		),
		array(
			'relation' => 'AND',
			array(
				'key'     => '_openagenda_end_date',
				'value'   => '',
				'compare' => '=',
			),
			array(
				'key'     => '_openagenda_start_date',
				'value'   => $today,
				'compare' => '<',
				'type'    => 'DATE',
			),
		),
	);
}

/**
 * Returns meta query for active recurring event dates.
 *
 * @param string $today Current WordPress date.
 * @return array
 */
function openagenda_get_admin_event_active_recurrence_meta_query( $today ) {
	return array(
		'relation' => 'OR',
		array(
			'key'     => '_openagenda_recurrence_until',
			'compare' => 'NOT EXISTS',
		),
		array(
			'key'     => '_openagenda_recurrence_until',
			'value'   => '',
			'compare' => '=',
		),
		array(
			'key'     => '_openagenda_recurrence_until',
			'value'   => $today,
			'compare' => '>=',
			'type'    => 'DATE',
		),
	);
}

/**
 * Returns meta query for archived recurring event dates.
 *
 * @param string $today Current WordPress date.
 * @return array
 */
function openagenda_get_admin_event_past_recurrence_meta_query( $today ) {
	return array(
		'key'     => '_openagenda_recurrence_until',
		'value'   => $today,
		'compare' => '<',
		'type'    => 'DATE',
	);
}

/**
 * Adds a duplicate action to event row actions.
 *
 * @param array   $actions Existing row actions.
 * @param WP_Post $post    Current post.
 * @return array
 */
function openagenda_add_duplicate_event_action( $actions, $post ) {
	if ( 'openagenda_event' !== $post->post_type || ! current_user_can( 'edit_post', $post->ID ) ) {
		return $actions;
	}

	$url = wp_nonce_url(
		add_query_arg(
			array(
				'action'  => 'openagenda_duplicate_event',
				'post_id' => $post->ID,
			),
			admin_url( 'admin.php' )
		),
		'openagenda_duplicate_event_' . $post->ID
	);

	$actions['openagenda_duplicate_event'] = sprintf(
		'<a href="%1$s" aria-label="%2$s">%3$s</a>',
		esc_url( $url ),
		esc_attr__( 'Duplicate this event', 'openagenda-events-calendar' ),
		esc_html__( 'Duplicate', 'openagenda-events-calendar' )
	);

	return $actions;
}
add_filter( 'post_row_actions', 'openagenda_add_duplicate_event_action', 10, 2 );

/**
 * Handles event duplication.
 */
function openagenda_duplicate_event() {
	$post_id = isset( $_GET['post_id'] ) ? absint( $_GET['post_id'] ) : 0;

	if ( ! $post_id || ! current_user_can( 'edit_post', $post_id ) ) {
		wp_die( esc_html__( 'You are not allowed to duplicate this event.', 'openagenda-events-calendar' ) );
	}

	check_admin_referer( 'openagenda_duplicate_event_' . $post_id );

	$source = get_post( $post_id );
	if ( ! $source || 'openagenda_event' !== $source->post_type ) {
		wp_die( esc_html__( 'Event could not be found.', 'openagenda-events-calendar' ) );
	}

	$new_post_id = wp_insert_post(
		array(
			'post_author'           => get_current_user_id(),
			'post_content'          => $source->post_content,
			'post_excerpt'          => $source->post_excerpt,
			'post_name'             => '',
			'post_parent'           => $source->post_parent,
			'post_password'         => $source->post_password,
			'post_status'           => 'draft',
			'post_title'            => sprintf(
				/* translators: %s: Original event title. */
				__( '%s copy', 'openagenda-events-calendar' ),
				$source->post_title
			),
			'post_type'             => 'openagenda_event',
			'post_content_filtered' => $source->post_content_filtered,
		),
		true
	);

	if ( is_wp_error( $new_post_id ) ) {
		wp_die( esc_html( $new_post_id->get_error_message() ) );
	}

	openagenda_copy_event_metadata( $post_id, $new_post_id );
	openagenda_copy_event_terms( $post_id, $new_post_id );
	openagenda_copy_event_thumbnail( $post_id, $new_post_id );

	wp_safe_redirect( admin_url( 'post.php?action=edit&post=' . $new_post_id ) );
	exit;
}
add_action( 'admin_action_openagenda_duplicate_event', 'openagenda_duplicate_event' );

/**
 * Copies event metadata to a duplicated event.
 *
 * @param int $source_id Source event ID.
 * @param int $target_id Target event ID.
 */
function openagenda_copy_event_metadata( $source_id, $target_id ) {
	$meta = get_post_meta( $source_id );

	foreach ( $meta as $key => $values ) {
		if ( '_edit_lock' === $key || '_edit_last' === $key ) {
			continue;
		}

		delete_post_meta( $target_id, $key );

		foreach ( $values as $value ) {
			add_post_meta( $target_id, $key, maybe_unserialize( $value ) );
		}
	}
}

/**
 * Copies event taxonomies to a duplicated event.
 *
 * @param int $source_id Source event ID.
 * @param int $target_id Target event ID.
 */
function openagenda_copy_event_terms( $source_id, $target_id ) {
	$taxonomies = get_object_taxonomies( 'openagenda_event' );

	foreach ( $taxonomies as $taxonomy ) {
		$terms = wp_get_object_terms( $source_id, $taxonomy, array( 'fields' => 'ids' ) );

		if ( is_wp_error( $terms ) ) {
			continue;
		}

		wp_set_object_terms( $target_id, $terms, $taxonomy );
	}
}

/**
 * Copies the featured image to a duplicated event.
 *
 * @param int $source_id Source event ID.
 * @param int $target_id Target event ID.
 */
function openagenda_copy_event_thumbnail( $source_id, $target_id ) {
	$thumbnail_id = get_post_thumbnail_id( $source_id );

	if ( $thumbnail_id ) {
		set_post_thumbnail( $target_id, $thumbnail_id );
	}
}


/**
 * Enqueues shortcode generator assets on the event list screen.
 *
 * @param string $hook Current admin hook.
 */
function openagenda_enqueue_shortcode_generator_assets( $hook ) {
	if ( 'edit.php' !== $hook ) {
		return;
	}

	$screen = get_current_screen();
	if ( ! $screen || 'openagenda_event' !== $screen->post_type ) {
		return;
	}

	wp_enqueue_style( 'openagenda-admin' );
	wp_enqueue_script( 'openagenda-shortcode-generator' );
	wp_enqueue_script( 'openagenda-quick-edit' );
}
add_action( 'admin_enqueue_scripts', 'openagenda_enqueue_shortcode_generator_assets' );

/**
 * Enqueues the event editor sidebar for event posts.
 *
 * @param string $hook Current admin hook.
 */
function openagenda_enqueue_event_editor_assets( $hook ) {
	if ( 'post.php' !== $hook && 'post-new.php' !== $hook ) {
		return;
	}

	$screen = get_current_screen();
	if ( ! $screen || 'openagenda_event' !== $screen->post_type ) {
		return;
	}

	wp_enqueue_style( 'openagenda-admin' );
	wp_enqueue_script( 'openagenda-event-editor' );
	openagenda_add_event_editor_locale_data();
}
add_action( 'admin_enqueue_scripts', 'openagenda_enqueue_event_editor_assets' );

/**
 * Renders a shortcode generator below the event list table.
 */
function openagenda_render_shortcode_generator( $which ) {
	if ( 'bottom' !== $which ) {
		return;
	}

	$screen = get_current_screen();
	if ( ! $screen || 'edit-openagenda_event' !== $screen->id ) {
		return;
	}

	$topics = get_terms(
		array(
			'taxonomy'   => 'openagenda_event_topic',
			'hide_empty' => false,
		)
	);
	?>
	<div class="openagenda-shortcode-generator postbox" style="margin-top: 20px; max-width: 960px;">
		<div class="postbox-header">
			<h2><?php esc_html_e( 'Shortcode Generator', 'openagenda-events-calendar' ); ?></h2>
		</div>
		<div class="inside">
			<p><?php esc_html_e( 'Use these shortcodes in pages, posts, widgets, or template areas to show your events.', 'openagenda-events-calendar' ); ?></p>

			<div style="display: grid; gap: 16px; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));">
				<div>
					<h3><?php esc_html_e( 'Upcoming events list', 'openagenda-events-calendar' ); ?></h3>
					<p>
						<label for="openagenda_shortcode_category"><strong><?php esc_html_e( 'Event category slug', 'openagenda-events-calendar' ); ?></strong></label>
						<select id="openagenda_shortcode_category" class="widefat" data-openagenda-shortcode-field="category">
							<option value=""><?php esc_html_e( 'All event topics', 'openagenda-events-calendar' ); ?></option>
							<?php if ( ! is_wp_error( $topics ) ) : ?>
								<?php foreach ( $topics as $topic ) : ?>
									<option value="<?php echo esc_attr( $topic->slug ); ?>"><?php echo esc_html( $topic->name . ' (' . $topic->slug . ')' ); ?></option>
								<?php endforeach; ?>
							<?php endif; ?>
						</select>
					</p>
					<p>
						<label for="openagenda_shortcode_max_events"><strong><?php esc_html_e( 'Maximum events', 'openagenda-events-calendar' ); ?></strong></label>
						<input id="openagenda_shortcode_max_events" type="number" min="1" max="50" value="6" class="small-text" data-openagenda-shortcode-field="max-events" />
					</p>
					<p>
						<label for="openagenda_shortcode_style"><strong><?php esc_html_e( 'Style', 'openagenda-events-calendar' ); ?></strong></label>
						<select id="openagenda_shortcode_style" class="widefat" data-openagenda-shortcode-field="style">
							<option value="list"><?php esc_html_e( 'List', 'openagenda-events-calendar' ); ?></option>
							<option value="minimal-list"><?php esc_html_e( 'Minimal list', 'openagenda-events-calendar' ); ?></option>
							<option value="calendar"><?php esc_html_e( 'Calendar', 'openagenda-events-calendar' ); ?></option>
						</select>
					</p>
					<p>
						<label><input type="checkbox" checked data-openagenda-shortcode-field="show-place" /> <?php esc_html_e( 'Show place', 'openagenda-events-calendar' ); ?></label><br />
						<label><input type="checkbox" checked data-openagenda-shortcode-field="show-time" /> <?php esc_html_e( 'Show time', 'openagenda-events-calendar' ); ?></label>
					</p>
					<p>
						<label for="openagenda_shortcode_events"><strong><?php esc_html_e( 'Generated shortcode', 'openagenda-events-calendar' ); ?></strong></label>
						<input id="openagenda_shortcode_events" class="widefat code" type="text" readonly data-openagenda-shortcode-output="events" value='[openagenda_events max-events="6" show-place="true" show-time="true" style="list"]' />
					</p>
					<p><button type="button" class="button" data-openagenda-copy-shortcode="openagenda_shortcode_events"><?php esc_html_e( 'Copy shortcode', 'openagenda-events-calendar' ); ?></button></p>
				</div>

				<div>
					<h3><?php esc_html_e( 'Full month calendar', 'openagenda-events-calendar' ); ?></h3>
					<p>
						<label for="openagenda_shortcode_calendar_topic"><strong><?php esc_html_e( 'Event category slug', 'openagenda-events-calendar' ); ?></strong></label>
						<select id="openagenda_shortcode_calendar_topic" class="widefat" data-openagenda-calendar-field="topic">
							<option value=""><?php esc_html_e( 'All event topics', 'openagenda-events-calendar' ); ?></option>
							<?php if ( ! is_wp_error( $topics ) ) : ?>
								<?php foreach ( $topics as $topic ) : ?>
									<option value="<?php echo esc_attr( $topic->slug ); ?>"><?php echo esc_html( $topic->name . ' (' . $topic->slug . ')' ); ?></option>
								<?php endforeach; ?>
							<?php endif; ?>
						</select>
					</p>
					<p>
						<label><input type="checkbox" checked data-openagenda-calendar-field="show_legend" /> <?php esc_html_e( 'Show legend', 'openagenda-events-calendar' ); ?></label>
					</p>
					<p>
						<label for="openagenda_shortcode_calendar"><strong><?php esc_html_e( 'Generated shortcode', 'openagenda-events-calendar' ); ?></strong></label>
						<input id="openagenda_shortcode_calendar" class="widefat code" type="text" readonly data-openagenda-shortcode-output="calendar" value='[openagenda_events_calendar show_legend="true"]' />
					</p>
					<p><button type="button" class="button" data-openagenda-copy-shortcode="openagenda_shortcode_calendar"><?php esc_html_e( 'Copy shortcode', 'openagenda-events-calendar' ); ?></button></p>
				</div>
			</div>

			<p style="margin-top: 16px;">
				<strong><?php esc_html_e( 'Quick examples', 'openagenda-events-calendar' ); ?></strong><br />
				<code>[openagenda_events max-events="5" style="minimal-list"]</code><br />
				<code>[openagenda_events_calendar]</code>
			</p>
		</div>
	</div>
	<?php
}
add_action( 'manage_posts_extra_tablenav', 'openagenda_render_shortcode_generator' );

