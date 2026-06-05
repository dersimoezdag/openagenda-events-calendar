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
 * Enqueues frontend assets.
 */
function openagenda_enqueue_calendar_assets() {
	wp_enqueue_style( 'openagenda-calendar' );
	wp_enqueue_script( 'openagenda-calendar' );
}

/**
 * Renders interactive month calendar shortcode.
 *
 * @param array $atts Shortcode attributes.
 * @return string
 */
function openagenda_calendar_shortcode( $atts ) {
	$raw_atts = is_array( $atts ) ? $atts : array();
	$atts = shortcode_atts(
		array(
			'topic'       => '',
			'height'      => 'auto',
			'category'    => '',
			'limit'       => '',
			'max-events'  => '',
			'max_events'  => '',
			'show-place'  => '',
			'show_place'  => '',
			'show-time'   => '',
			'show_time'   => '',
			'show_legend' => 'true',
			'style'       => '',
		),
		$atts,
		'openagenda_events_calendar'
	);

	$list_attribute_keys = array( 'category', 'limit', 'max-events', 'max_events', 'show-place', 'show_place', 'show-time', 'show_time', 'style' );
	$has_list_attributes = array_intersect( $list_attribute_keys, array_keys( $raw_atts ) );

	if ( ! empty( $has_list_attributes ) ) {
		return openagenda_render_upcoming_events(
			array(
				'category'   => openagenda_first_filled_value( array( $atts['category'], $atts['topic'] ) ),
				'max_events' => openagenda_first_filled_value( array( $atts['max-events'], $atts['max_events'], $atts['limit'] ), 6 ),
				'show_place' => openagenda_first_filled_value( array( $atts['show-place'], $atts['show_place'] ), 'true' ),
				'show_time'  => openagenda_first_filled_value( array( $atts['show-time'], $atts['show_time'] ), 'true' ),
				'style'      => openagenda_first_filled_value( array( $atts['style'] ), 'list' ),
			)
		);
	}

	openagenda_enqueue_calendar_assets();

	$calendar_id = wp_unique_id( 'openagenda-calendar-' );
	$topic       = sanitize_title( $atts['topic'] );
	$height      = 'auto' === $atts['height'] ? 'auto' : max( 320, absint( $atts['height'] ) ) . 'px';
	$show_legend = filter_var( $atts['show_legend'], FILTER_VALIDATE_BOOLEAN );

	ob_start();
	?>
	<div
		id="<?php echo esc_attr( $calendar_id ); ?>"
		class="openagenda-calendar"
		data-topic="<?php echo esc_attr( $topic ); ?>"
		data-show-legend="<?php echo esc_attr( $show_legend ? 'true' : 'false' ); ?>"
		style="--openagenda-calendar-min-height: <?php echo esc_attr( $height ); ?>;"
	>
		<div class="openagenda-calendar__toolbar">
			<button type="button" class="openagenda-calendar__button" data-openagenda-action="previous" aria-label="<?php esc_attr_e( 'Previous month', 'openagenda-events-calendar' ); ?>">
				<span aria-hidden="true">&lsaquo;</span>
			</button>
			<h2 class="openagenda-calendar__title" data-openagenda-title></h2>
			<div class="openagenda-calendar__actions">
				<button type="button" class="openagenda-calendar__button openagenda-calendar__button--text" data-openagenda-action="today"><?php esc_html_e( 'Today', 'openagenda-events-calendar' ); ?></button>
				<button type="button" class="openagenda-calendar__button" data-openagenda-action="next" aria-label="<?php esc_attr_e( 'Next month', 'openagenda-events-calendar' ); ?>">
					<span aria-hidden="true">&rsaquo;</span>
				</button>
			</div>
		</div>
		<div class="openagenda-calendar__status" data-openagenda-status role="status"><?php esc_html_e( 'Loading events...', 'openagenda-events-calendar' ); ?></div>
		<div class="openagenda-calendar__weekdays" data-openagenda-weekdays></div>
		<div class="openagenda-calendar__grid" data-openagenda-grid></div>
		<div class="openagenda-calendar__legend" data-openagenda-legend hidden></div>
	</div>
	<?php
	return ob_get_clean();
}
add_shortcode( 'openagenda_events_calendar', 'openagenda_calendar_shortcode' );

/**
 * Renders upcoming events shortcode.
 *
 * @param array $atts Shortcode attributes.
 * @return string
 */
function openagenda_upcoming_events_shortcode( $atts ) {
	$atts = shortcode_atts(
		array(
			'category'    => '',
			'limit'       => 6,
			'max-events'  => '',
			'max_events'  => '',
			'show-place'  => 'true',
			'show_place'  => '',
			'show-time'   => 'true',
			'show_time'   => '',
			'style'       => 'list',
			'topic'       => '',
		),
		$atts,
		'openagenda_events'
	);

	return openagenda_render_upcoming_events(
		array(
			'category'   => openagenda_first_filled_value( array( $atts['category'], $atts['topic'] ) ),
			'max_events' => openagenda_first_filled_value( array( $atts['max-events'], $atts['max_events'], $atts['limit'] ) ),
			'show_place' => openagenda_first_filled_value( array( $atts['show-place'], $atts['show_place'] ), 'true' ),
			'show_time'  => openagenda_first_filled_value( array( $atts['show-time'], $atts['show_time'] ), 'true' ),
			'style'      => $atts['style'],
		)
	);
}
add_shortcode( 'openagenda_events', 'openagenda_upcoming_events_shortcode' );

/**
 * Prepends event details to single event content.
 *
 * @param string $content Post content.
 * @return string
 */
function openagenda_add_single_event_details_to_content( $content ) {
	static $rendering = false;

	if ( ! is_singular( 'openagenda_event' ) || ! in_the_loop() || ! is_main_query() ) {
		return $content;
	}

	if ( $rendering ) {
		return $content;
	}

	$rendering = true;
	$details = openagenda_render_single_event_details( get_the_ID() );
	$next    = openagenda_render_single_next_events();
	$rendering = false;

	if ( empty( $details ) && empty( $next ) ) {
		return $content;
	}

	return $details . $content . $next;
}
add_filter( 'the_content', 'openagenda_add_single_event_details_to_content', 8 );

/**
 * Renders a compact upcoming-events section below single event content.
 *
 * @return string
 */
function openagenda_render_single_next_events() {
	$list = openagenda_render_upcoming_events(
		array(
			'max_events' => 5,
			'show_place' => true,
			'show_time'  => true,
			'style'      => 'minimal-list',
		)
	);

	if ( empty( $list ) ) {
		return '';
	}

	ob_start();
	?>
	<section class="openagenda-single-next-events" aria-labelledby="openagenda-single-next-events-title">
		<h2 id="openagenda-single-next-events-title" class="openagenda-single-next-events__title"><?php esc_html_e( 'Next Events', 'openagenda-events-calendar' ); ?></h2>
		<?php echo $list; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
	</section>
	<?php
	return ob_get_clean();
}

/**
 * Renders the detail panel for a single event page.
 *
 * @param int $post_id Event post ID.
 * @return string
 */
function openagenda_render_single_event_details( $post_id ) {
	$start_date = get_post_meta( $post_id, '_openagenda_start_date', true );
	$start_time = get_post_meta( $post_id, '_openagenda_start_time', true );
	$end_date   = get_post_meta( $post_id, '_openagenda_end_date', true );
	$end_time   = get_post_meta( $post_id, '_openagenda_end_time', true );
	$all_day    = '1' === get_post_meta( $post_id, '_openagenda_all_day', true );
	$location   = get_post_meta( $post_id, '_openagenda_location', true );
	$external   = get_post_meta( $post_id, '_openagenda_external_url', true );

	if ( empty( $start_date ) && empty( $location ) && empty( $external ) ) {
		return '';
	}

	$details = array();

	if ( ! empty( $start_date ) ) {
		$details[] = array(
			'label' => __( 'Date', 'openagenda-events-calendar' ),
			'value' => openagenda_format_event_date_range_value( $start_date, $end_date ),
		);
	}

	if ( ! $all_day && ! empty( $start_time ) ) {
		$details[] = array(
			'label' => __( 'Time', 'openagenda-events-calendar' ),
			'value' => openagenda_format_event_time_value( $start_date, $start_time, $end_date, $end_time, $all_day ),
		);
	}

	if ( ! empty( $location ) ) {
		$details[] = array(
			'label' => __( 'Location', 'openagenda-events-calendar' ),
			'value' => $location,
		);
	}

	ob_start();
	?>
	<div class="openagenda-single-event">
		<dl class="openagenda-single-event__details">
			<?php foreach ( $details as $detail ) : ?>
				<div class="openagenda-single-event__row">
					<dt><?php echo esc_html( $detail['label'] ); ?></dt>
					<dd><?php echo esc_html( $detail['value'] ); ?></dd>
				</div>
			<?php endforeach; ?>
			<?php if ( ! empty( $external ) ) : ?>
				<div class="openagenda-single-event__row">
					<dt><?php esc_html_e( 'External URL', 'openagenda-events-calendar' ); ?></dt>
					<dd><a href="<?php echo esc_url( $external ); ?>"><?php echo esc_html( wp_parse_url( $external, PHP_URL_HOST ) ? wp_parse_url( $external, PHP_URL_HOST ) : $external ); ?></a></dd>
				</div>
			<?php endif; ?>
		</dl>
	</div>
	<?php
	return ob_get_clean();
}

/**
 * Returns a readable recurrence label.
 *
 * @param int $post_id Event post ID.
 * @return string
 */
function openagenda_get_recurrence_label( $post_id ) {
	$recurrence = openagenda_sanitize_recurrence( get_post_meta( $post_id, '_openagenda_recurrence', true ) );
	$interval   = max( 1, absint( get_post_meta( $post_id, '_openagenda_recurrence_interval', true ) ) );
	$until      = get_post_meta( $post_id, '_openagenda_recurrence_until', true );
	$labels     = array(
		'daily'   => __( 'Daily', 'openagenda-events-calendar' ),
		'weekly'  => __( 'Weekly', 'openagenda-events-calendar' ),
		'monthly' => __( 'Monthly', 'openagenda-events-calendar' ),
		'yearly'  => __( 'Yearly', 'openagenda-events-calendar' ),
	);

	$label = isset( $labels[ $recurrence ] ) ? $labels[ $recurrence ] : __( 'Does not repeat', 'openagenda-events-calendar' );

	if ( $interval > 1 ) {
		$label = sprintf(
			/* translators: 1: Recurrence frequency, 2: Interval number. */
			__( '%1$s, every %2$d intervals', 'openagenda-events-calendar' ),
			$label,
			$interval
		);
	}

	if ( ! empty( $until ) ) {
		$label .= ' ' . sprintf(
			/* translators: %s: End date. */
			__( 'until %s', 'openagenda-events-calendar' ),
			wp_date( get_option( 'date_format' ), strtotime( $until ) )
		);
	}

	return $label;
}

/**
 * Renders the dynamic Upcoming Events block.
 *
 * @param array $attributes Block attributes.
 * @return string
 */
function openagenda_render_upcoming_events_block( $attributes ) {
	return openagenda_render_upcoming_events(
		array(
			'category'   => isset( $attributes['category'] ) ? $attributes['category'] : '',
			'max_events' => isset( $attributes['maxEvents'] ) ? $attributes['maxEvents'] : 6,
			'show_place' => isset( $attributes['showPlace'] ) ? $attributes['showPlace'] : true,
			'show_time'  => isset( $attributes['showTime'] ) ? $attributes['showTime'] : true,
			'style'      => isset( $attributes['displayStyle'] ) ? $attributes['displayStyle'] : 'list',
		)
	);
}

/**
 * Renders a styled upcoming-events list.
 *
 * @param array $args Display arguments.
 * @return string
 */
function openagenda_render_upcoming_events( $args = array() ) {
	$args = wp_parse_args(
		$args,
		array(
			'category'   => '',
			'max_events' => 6,
			'show_place' => true,
			'show_time'  => true,
			'style'      => 'list',
		)
	);

	wp_enqueue_style( 'openagenda-calendar' );

	$category   = sanitize_title( $args['category'] );
	$max_events = max( 1, min( 50, absint( $args['max_events'] ) ) );
	$show_place = is_bool( $args['show_place'] ) ? $args['show_place'] : filter_var( $args['show_place'], FILTER_VALIDATE_BOOLEAN );
	$show_time  = is_bool( $args['show_time'] ) ? $args['show_time'] : filter_var( $args['show_time'], FILTER_VALIDATE_BOOLEAN );
	$style      = openagenda_normalize_upcoming_style( $args['style'] );

	$events = openagenda_get_upcoming_list_events( $max_events, $category );

	$classes = array(
		'openagenda-upcoming',
		'openagenda-upcoming--' . $style,
	);

	ob_start();
	?>
	<div class="<?php echo esc_attr( implode( ' ', $classes ) ); ?>">
		<?php if ( empty( $events ) ) : ?>
			<p class="openagenda-upcoming__empty"><?php esc_html_e( 'Currently there are no upcoming events.', 'openagenda-events-calendar' ); ?></p>
		<?php else : ?>
			<ul class="openagenda-upcoming__list">
				<?php foreach ( $events as $event ) : ?>
					<?php
					$compact_meta = array_filter(
						array(
							'date'     => $event['shortDateLabel'],
							'time'     => $show_time ? $event['compactTimeLabel'] : '',
							'location' => $show_place ? $event['location'] : '',
						)
					);
					?>
					<li class="openagenda-upcoming__item<?php echo ! empty( $event['multiDay'] ) ? ' openagenda-upcoming__item--multi-day' : ''; ?>" style="--openagenda-event-color: <?php echo esc_attr( $event['color'] ); ?>;">
						<div class="openagenda-upcoming__content">
							<span class="openagenda-upcoming__date" aria-hidden="true">
								<span class="openagenda-upcoming__day"><?php echo esc_html( $event['dayLabel'] ); ?></span>
								<span class="openagenda-upcoming__month"><?php echo esc_html( $event['monthLabel'] ); ?></span>
								<span class="openagenda-upcoming__short-date">
									<span class="openagenda-upcoming__short-date-start"><?php echo esc_html( $event['startShortDateLabel'] ); ?></span>
									<?php if ( ! empty( $event['multiDay'] ) ) : ?>
										<span class="openagenda-upcoming__short-date-end"><?php echo esc_html( $event['endShortDateLabel'] ); ?></span>
									<?php endif; ?>
								</span>
							</span>
							<span class="openagenda-upcoming__body">
								<span class="openagenda-upcoming__headline">
									<strong class="openagenda-upcoming__inline-date"><?php echo esc_html( $event['shortDateLabel'] ); ?></strong>
									<a class="openagenda-upcoming__title" href="<?php echo esc_url( $event['url'] ); ?>"><?php echo esc_html( $event['title'] ); ?></a>
								</span>
								<span class="openagenda-upcoming__compact-meta">
									<?php foreach ( $compact_meta as $meta_index => $meta_value ) : ?>
										<span class="openagenda-upcoming__compact-meta-item openagenda-upcoming__compact-meta-item--<?php echo esc_attr( $meta_index ); ?>"><?php echo esc_html( $meta_value ); ?></span>
									<?php endforeach; ?>
								</span>
								<span class="openagenda-upcoming__meta">
									<?php if ( $show_time && ! empty( $event['timeLabel'] ) ) : ?>
										<span><?php echo esc_html( $event['timeLabel'] ); ?></span>
									<?php endif; ?>
									<?php if ( $show_place && ! empty( $event['location'] ) ) : ?>
										<span><?php echo esc_html( $event['location'] ); ?></span>
									<?php endif; ?>
								</span>
							</span>
						</div>
					</li>
				<?php endforeach; ?>
			</ul>
		<?php endif; ?>
	</div>
	<?php
	return ob_get_clean();
}

/**
 * Fetches upcoming events for list displays.
 *
 * @param int    $max_events Maximum number of events.
 * @param string $category   Optional event topic slug.
 * @return array
 */
function openagenda_get_upcoming_list_events( $max_events, $category = '' ) {
	return openagenda_get_events(
		array(
			'from'  => current_time( 'Y-m-d' ),
			'limit' => $max_events,
			'topic' => $category,
		)
	);
}

/**
 * Returns the first non-empty value from a list.
 *
 * @param array $values   Candidate values.
 * @param mixed $fallback Fallback value.
 * @return mixed
 */
function openagenda_first_filled_value( $values, $fallback = '' ) {
	foreach ( $values as $value ) {
		if ( '' !== $value && null !== $value ) {
			return $value;
		}
	}

	return $fallback;
}

/**
 * Normalizes upcoming-events display styles.
 *
 * @param string $style Requested style.
 * @return string
 */
function openagenda_normalize_upcoming_style( $style ) {
	$style = str_replace( '_', '-', sanitize_key( (string) $style ) );

	return in_array( $style, array( 'list', 'minimal-list', 'calendar' ), true ) ? $style : 'list';
}


