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
 * Sanitizes a date field.
 *
 * @param string $date Submitted date.
 * @return string
 */
function openagenda_sanitize_date( $date ) {
	$date = sanitize_text_field( $date );
	return preg_match( '/^\d{4}-\d{2}-\d{2}$/', $date ) ? $date : '';
}

/**
 * Sanitizes a time field.
 *
 * @param string $time Submitted time.
 * @return string
 */
function openagenda_sanitize_time( $time ) {
	$time = sanitize_text_field( $time );
	return preg_match( '/^\d{2}:\d{2}$/', $time ) ? $time : '';
}

/**
 * Sanitizes recurrence frequency.
 *
 * @param string $recurrence Submitted recurrence.
 * @return string
 */
function openagenda_sanitize_recurrence( $recurrence ) {
	$recurrence = sanitize_key( $recurrence );
	return in_array( $recurrence, array( 'none', 'daily', 'weekly', 'monthly', 'yearly' ), true ) ? $recurrence : 'none';
}


/**
 * Formats event date and time for display.
 *
 * @param int $post_id Event post ID.
 * @return string
 */
function openagenda_format_event_datetime( $post_id ) {
	$start_date = get_post_meta( $post_id, '_openagenda_start_date', true );
	$start_time = get_post_meta( $post_id, '_openagenda_start_time', true );
	$end_date   = get_post_meta( $post_id, '_openagenda_end_date', true );
	$end_time   = get_post_meta( $post_id, '_openagenda_end_time', true );
	$all_day    = '1' === get_post_meta( $post_id, '_openagenda_all_day', true );

	return openagenda_format_event_datetime_value( $start_date, $start_time, $end_date, $end_time, $all_day );
}

/**
 * Formats event date and time values for display.
 *
 * @param string $start_date Start date.
 * @param string $start_time Start time.
 * @param string $end_date   End date.
 * @param string $end_time   End time.
 * @param bool   $all_day    Whether this is all day.
 * @return string
 */
function openagenda_format_event_datetime_value( $start_date, $start_time, $end_date, $end_time, $all_day ) {
	if ( empty( $start_date ) ) {
		return '';
	}

	$date_format = get_option( 'date_format' );
	$start_label = wp_date( $date_format, strtotime( $start_date ) );

	if ( ! empty( $end_date ) && $end_date !== $start_date ) {
		$start_label .= ' - ' . wp_date( $date_format, strtotime( $end_date ) );
	}

	if ( ! $all_day && ! empty( $start_time ) ) {
		$start_label .= ', ' . openagenda_format_local_time_value( $start_time );

		if ( ! empty( $end_time ) ) {
			$start_label .= ' - ' . openagenda_format_local_time_value( $end_time );
		}
	}

	return $start_label;
}

/**
 * Formats an event date range.
 *
 * @param string $start_date Start date.
 * @param string $end_date   End date.
 * @return string
 */
function openagenda_format_event_date_range_value( $start_date, $end_date = '' ) {
	if ( empty( $start_date ) ) {
		return '';
	}

	$date_format = get_option( 'date_format' );
	$label       = wp_date( $date_format, strtotime( $start_date ) );

	if ( ! empty( $end_date ) && $end_date !== $start_date ) {
		$label .= ' - ' . wp_date( $date_format, strtotime( $end_date ) );
	}

	return $label;
}

/**
 * Formats event date as a compact numeric label.
 *
 * @param int $post_id Event post ID.
 * @return string
 */
function openagenda_format_event_short_date( $post_id ) {
	$start_date = get_post_meta( $post_id, '_openagenda_start_date', true );
	$end_date   = get_post_meta( $post_id, '_openagenda_end_date', true );

	return openagenda_format_event_short_date_value( $start_date, $end_date );
}

/**
 * Formats event date value as a compact numeric label or range.
 *
 * @param string $start_date Start date.
 * @param string $end_date   End date.
 * @return string
 */
function openagenda_format_event_short_date_value( $start_date, $end_date = '' ) {
	if ( empty( $start_date ) ) {
		return '';
	}

	$label = openagenda_format_event_short_single_date_value( $start_date );

	if ( ! empty( $end_date ) && $end_date !== $start_date ) {
		$label .= ' - ' . openagenda_format_event_short_single_date_value( $end_date );
	}

	return $label;
}

/**
 * Formats a single date as a compact numeric label.
 *
 * @param string $date Date value.
 * @return string
 */
function openagenda_format_event_short_single_date_value( $date ) {
	if ( empty( $date ) ) {
		return '';
	}

	return wp_date( 'd.m.Y', strtotime( $date ) );
}

/**
 * Formats event time range.
 *
 * @param int $post_id Event post ID.
 * @return string
 */
function openagenda_format_event_time( $post_id ) {
	$start_date = get_post_meta( $post_id, '_openagenda_start_date', true );
	$start_time = get_post_meta( $post_id, '_openagenda_start_time', true );
	$end_date   = get_post_meta( $post_id, '_openagenda_end_date', true );
	$end_time   = get_post_meta( $post_id, '_openagenda_end_time', true );
	$all_day    = '1' === get_post_meta( $post_id, '_openagenda_all_day', true );

	return openagenda_format_event_time_value( $start_date, $start_time, $end_date, $end_time, $all_day );
}

/**
 * Formats event time values.
 *
 * @param string $start_date Start date.
 * @param string $start_time Start time.
 * @param string $end_date   End date.
 * @param string $end_time   End time.
 * @param bool   $all_day    Whether this is all day.
 * @return string
 */
function openagenda_format_event_time_value( $start_date, $start_time, $end_date, $end_time, $all_day ) {
	if ( $all_day || empty( $start_date ) || empty( $start_time ) ) {
		return '';
	}

	$label = openagenda_format_local_time_value( $start_time );

	if ( ! empty( $end_time ) ) {
		$label .= ' - ' . openagenda_format_local_time_value( $end_time );
	}

	return $label;
}

/**
 * Formats a stored local HH:MM time without applying timezone conversion.
 *
 * @param string $time Stored time value.
 * @return string
 */
function openagenda_format_local_time_value( $time ) {
	$time = openagenda_sanitize_time( $time );

	if ( empty( $time ) ) {
		return '';
	}

	return substr( $time, 0, 5 );
}

/**
 * Formats event time values for compact upcoming-event meta rows.
 *
 * @param string $start_date Start date.
 * @param string $start_time Start time.
 * @param string $end_date   End date.
 * @param string $end_time   End time.
 * @param bool   $all_day    Whether this is all day.
 * @return string
 */
function openagenda_format_event_compact_time_value( $start_date, $start_time, $end_date, $end_time, $all_day ) {
	if ( $all_day || empty( $start_date ) || empty( $start_time ) ) {
		return '';
	}

	$start_label = openagenda_format_local_time_value( $start_time );

	if ( ! empty( $end_time ) ) {
		$end_label = openagenda_format_local_time_value( $end_time );

		return sprintf(
			/* translators: 1: event start time, 2: event end time. */
			__( '%1$s - %2$s o\'clock', 'openagenda-events-calendar' ),
			$start_label,
			$end_label
		);
	}

	return sprintf(
		/* translators: %s: event start time. */
		__( '%s o\'clock', 'openagenda-events-calendar' ),
		$start_label
	);
}

/**
 * Formats event day for the visual date badge.
 *
 * @param int $post_id Event post ID.
 * @return string
 */
function openagenda_format_event_day( $post_id ) {
	$start_date = get_post_meta( $post_id, '_openagenda_start_date', true );

	return openagenda_format_event_day_value( $start_date );
}

/**
 * Formats event day value for the visual date badge.
 *
 * @param string $start_date Start date.
 * @return string
 */
function openagenda_format_event_day_value( $start_date ) {
	if ( empty( $start_date ) ) {
		return '';
	}

	return wp_date( 'd', strtotime( $start_date ) );
}

/**
 * Formats event month for the visual date badge.
 *
 * @param int $post_id Event post ID.
 * @return string
 */
function openagenda_format_event_month( $post_id ) {
	$start_date = get_post_meta( $post_id, '_openagenda_start_date', true );

	return openagenda_format_event_month_value( $start_date );
}

/**
 * Formats event month value for the visual date badge.
 *
 * @param string $start_date Start date.
 * @return string
 */
function openagenda_format_event_month_value( $start_date ) {
	if ( empty( $start_date ) ) {
		return '';
	}

	return wp_date( 'M', strtotime( $start_date ) );
}

