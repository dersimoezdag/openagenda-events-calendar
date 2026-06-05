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
 * Fetches normalized event data.
 *
 * @param array $args Query arguments.
 * @return array
 */
function openagenda_get_events( $args = array() ) {
	$args = wp_parse_args(
		$args,
		array(
			'from'  => '',
			'to'    => '',
			'topic' => '',
			'limit' => 100,
		)
	);

	$from  = openagenda_sanitize_date( $args['from'] );
	$to    = openagenda_sanitize_date( $args['to'] );
	$limit = max( 1, min( 200, absint( $args['limit'] ) ) );

	if ( empty( $from ) ) {
		$from = current_time( 'Y-m-d' );
	}

	$query_to = ! empty( $to ) ? $to : wp_date( 'Y-m-d', strtotime( $from . ' +2 years' ) );

	$meta_query = array(
		'relation' => 'AND',
		array(
			'key'     => '_openagenda_start_date',
			'compare' => 'EXISTS',
		),
		array(
			'key'     => '_openagenda_start_date',
			'value'   => $query_to,
			'compare' => '<=',
			'type'    => 'DATE',
		),
		array(
			'relation' => 'OR',
			array(
				'key'     => '_openagenda_start_date',
				'value'   => $from,
				'compare' => '>=',
				'type'    => 'DATE',
			),
			array(
				'key'     => '_openagenda_end_date',
				'value'   => $from,
				'compare' => '>=',
				'type'    => 'DATE',
			),
			array(
				'key'     => '_openagenda_recurrence',
				'value'   => array( 'daily', 'weekly', 'monthly', 'yearly' ),
				'compare' => 'IN',
			),
		),
	);

	$query_args = array(
		'post_type'              => 'openagenda_event',
		'post_status'            => 'publish',
		'posts_per_page'         => openagenda_get_event_query_post_limit( $limit ),
		'no_found_rows'          => true,
		'update_post_term_cache' => false,
		// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key -- Public event lists are ordered by the registered start-date meta field.
		'meta_key'               => '_openagenda_start_date',
		'orderby'                => array(
			// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value -- Public event lists are ordered by the registered start-date meta field.
			'meta_value' => 'ASC',
			'date'       => 'ASC',
		),
		// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query -- Public event lists filter by the registered start-date meta field.
		'meta_query'             => $meta_query,
	);

	if ( ! empty( $args['topic'] ) ) {
		// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query -- Topic filtering is an intentional public event-list feature.
		$query_args['tax_query'] = array(
			array(
				'taxonomy' => 'openagenda_event_topic',
				'field'    => 'slug',
				'terms'    => $args['topic'],
			),
		);
	}

	$query  = new WP_Query( $query_args );
	$events = array();

	foreach ( $query->posts as $post ) {
		$events = array_merge( $events, openagenda_expand_event_occurrences( $post, $from, $to ) );
	}

	wp_reset_postdata();

	usort(
		$events,
		function ( $left, $right ) {
			return strcmp( $left['start'], $right['start'] );
		}
	);

	return array_slice( $events, 0, $limit );
}

/**
 * Returns a bounded post count for public event lookups.
 *
 * @param int $limit Requested event occurrence limit.
 * @return int
 */
function openagenda_get_event_query_post_limit( $limit ) {
	$post_limit = max( 50, min( 500, $limit * 4 ) );

	/**
	 * Filters the maximum number of event posts loaded for one public event query.
	 *
	 * @param int $post_limit Maximum event posts.
	 * @param int $limit      Requested occurrence limit.
	 */
	return (int) apply_filters( 'openagenda_event_query_post_limit', $post_limit, $limit );
}

/**
 * Expands one event post into displayable occurrences.
 *
 * @param WP_Post $post Event post.
 * @param string  $from Range start date.
 * @param string  $to   Range end date.
 * @return array
 */
function openagenda_expand_event_occurrences( $post, $from = '', $to = '' ) {
	$start_date = get_post_meta( $post->ID, '_openagenda_start_date', true );
	$end_date   = get_post_meta( $post->ID, '_openagenda_end_date', true );

	if ( empty( $start_date ) ) {
		return array();
	}

	if ( empty( $end_date ) ) {
		$end_date = $start_date;
	}

	$recurrence = openagenda_sanitize_recurrence( get_post_meta( $post->ID, '_openagenda_recurrence', true ) );
	$interval   = max( 1, absint( get_post_meta( $post->ID, '_openagenda_recurrence_interval', true ) ) );
	$until      = get_post_meta( $post->ID, '_openagenda_recurrence_until', true );
	$range_from = ! empty( $from ) ? $from : current_time( 'Y-m-d' );
	$range_to   = ! empty( $to ) ? $to : wp_date( 'Y-m-d', strtotime( $range_from . ' +2 years' ) );
	$duration   = max( 0, openagenda_days_between( $start_date, $end_date ) );

	if ( 'none' === $recurrence ) {
		if ( openagenda_event_range_intersects( $start_date, $end_date, $range_from, $range_to ) ) {
			return array( openagenda_normalize_event( $post, $start_date, $end_date ) );
		}

		return array();
	}

	if ( ! empty( $until ) && $until < $range_from ) {
		return array();
	}

	$occurrences = array();
	$cursor      = new DateTimeImmutable( $start_date );
	$max_date    = new DateTimeImmutable( ! empty( $until ) && $until < $range_to ? $until : $range_to );
	$guard       = 0;

	while ( $cursor <= $max_date && $guard < 1000 ) {
		$occurrence_start = $cursor->format( 'Y-m-d' );
		$occurrence_end   = $cursor->modify( '+' . $duration . ' days' )->format( 'Y-m-d' );

		if ( openagenda_event_range_intersects( $occurrence_start, $occurrence_end, $range_from, $range_to ) ) {
			$occurrences[] = openagenda_normalize_event( $post, $occurrence_start, $occurrence_end );
		}

		$cursor = openagenda_next_recurrence_date( $cursor, $recurrence, $interval );
		$guard++;
	}

	return $occurrences;
}

/**
 * Returns the next recurrence date.
 *
 * @param DateTimeImmutable $date       Current date.
 * @param string            $recurrence Recurrence frequency.
 * @param int               $interval   Interval.
 * @return DateTimeImmutable
 */
function openagenda_next_recurrence_date( DateTimeImmutable $date, $recurrence, $interval ) {
	$interval = max( 1, absint( $interval ) );

	if ( 'daily' === $recurrence ) {
		return $date->modify( '+' . $interval . ' days' );
	}

	if ( 'weekly' === $recurrence ) {
		return $date->modify( '+' . $interval . ' weeks' );
	}

	if ( 'monthly' === $recurrence ) {
		return $date->modify( '+' . $interval . ' months' );
	}

	if ( 'yearly' === $recurrence ) {
		return $date->modify( '+' . $interval . ' years' );
	}

	return $date->modify( '+1 day' );
}

/**
 * Checks whether an event range intersects a display range.
 *
 * @param string $event_start Event start date.
 * @param string $event_end   Event end date.
 * @param string $range_start Range start date.
 * @param string $range_end   Range end date.
 * @return bool
 */
function openagenda_event_range_intersects( $event_start, $event_end, $range_start, $range_end ) {
	return $event_start <= $range_end && $event_end >= $range_start;
}

/**
 * Returns whole-day distance between two dates.
 *
 * @param string $start Start date.
 * @param string $end   End date.
 * @return int
 */
function openagenda_days_between( $start, $end ) {
	$start_date = new DateTimeImmutable( $start );
	$end_date   = new DateTimeImmutable( $end );

	return (int) $start_date->diff( $end_date )->format( '%r%a' );
}

/**
 * Normalizes a post into event data for frontend use.
 *
 * @param WP_Post $post             Event post.
 * @param string  $occurrence_start Occurrence start date.
 * @param string  $occurrence_end   Occurrence end date.
 * @return array
 */
function openagenda_normalize_event( $post, $occurrence_start = '', $occurrence_end = '' ) {
	$start_date = get_post_meta( $post->ID, '_openagenda_start_date', true );
	$start_time = get_post_meta( $post->ID, '_openagenda_start_time', true );
	$end_date   = get_post_meta( $post->ID, '_openagenda_end_date', true );
	$end_time   = get_post_meta( $post->ID, '_openagenda_end_time', true );
	$location   = get_post_meta( $post->ID, '_openagenda_location', true );
	$external   = get_post_meta( $post->ID, '_openagenda_external_url', true );
	$all_day    = '1' === get_post_meta( $post->ID, '_openagenda_all_day', true );
	$color      = get_post_meta( $post->ID, '_openagenda_color', true );
	$terms      = get_the_terms( $post, 'openagenda_event_topic' );

	if ( empty( $end_date ) ) {
		$end_date = $start_date;
	}

	if ( ! empty( $occurrence_start ) ) {
		$start_date = $occurrence_start;
	}

	if ( ! empty( $occurrence_end ) ) {
		$end_date = $occurrence_end;
	}

	if ( empty( $color ) ) {
		$color = '#ffffff';
	}

	return array(
		'id'        => $post->ID . ':' . $start_date,
		'postId'    => $post->ID,
		'title'     => get_the_title( $post ),
		'start'     => openagenda_combine_date_time( $start_date, $start_time, $all_day ),
		'end'       => openagenda_combine_date_time( $end_date, $end_time, $all_day ),
		'allDay'    => $all_day,
		'url'       => ! empty( $external ) ? $external : get_permalink( $post ),
		'permalink' => get_permalink( $post ),
		'location'  => $location,
		'excerpt'   => openagenda_get_plain_event_excerpt( $post ),
		'color'     => sanitize_hex_color( $color ) ? $color : '#ffffff',
		'topics'    => is_array( $terms ) ? wp_list_pluck( $terms, 'name' ) : array(),
		'recurring' => 'none' !== openagenda_sanitize_recurrence( get_post_meta( $post->ID, '_openagenda_recurrence', true ) ),
		'multiDay'  => ! empty( $end_date ) && $end_date !== $start_date,
		'dateLabel' => openagenda_format_event_datetime_value( $start_date, $start_time, $end_date, $end_time, $all_day ),
		'startShortDateLabel' => openagenda_format_event_short_single_date_value( $start_date ),
		'endShortDateLabel' => openagenda_format_event_short_single_date_value( $end_date ),
		'shortDateLabel' => openagenda_format_event_short_date_value( $start_date, $end_date ),
		'timeLabel' => openagenda_format_event_time_value( $start_date, $start_time, $end_date, $end_time, $all_day ),
		'compactTimeLabel' => openagenda_format_event_compact_time_value( $start_date, $start_time, $end_date, $end_time, $all_day ),
		'dayLabel'  => openagenda_format_event_day_value( $start_date ),
		'monthLabel' => openagenda_format_event_month_value( $start_date ),
	);
}

/**
 * Returns a plain excerpt without invoking content/excerpt filters.
 *
 * @param WP_Post $post Event post.
 * @return string
 */
function openagenda_get_plain_event_excerpt( $post ) {
	if ( ! empty( $post->post_excerpt ) ) {
		return wp_strip_all_tags( $post->post_excerpt );
	}

	if ( empty( $post->post_content ) ) {
		return '';
	}

	return wp_trim_words( wp_strip_all_tags( strip_shortcodes( $post->post_content ) ), 28 );
}

/**
 * Combines date and time metadata into an ISO-like local string.
 *
 * @param string $date    Date.
 * @param string $time    Time.
 * @param bool   $all_day Whether this is all day.
 * @return string
 */
function openagenda_combine_date_time( $date, $time, $all_day ) {
	if ( empty( $date ) ) {
		return '';
	}

	if ( $all_day || empty( $time ) ) {
		return $date;
	}

	return $date . 'T' . $time . ':00';
}


