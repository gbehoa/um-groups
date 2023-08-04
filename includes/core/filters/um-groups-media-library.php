<?php if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Hide attachment files from the Media Library's overlay (modal) view
 * if they have a certain meta key set.
 *
 * @param array $query An array of query variables.
 *
 * @return array
 */
function um_groups_media_overlay_view( $query ) {
	// Bail if this is not the admin area.
	if ( ! is_admin() ) {
		return $query;
	}

	// Modify the query.
	$query['meta_query'] = array(
		array(
			'key'     => '_um_groups_avatar',
			'compare' => 'NOT EXISTS',
		),
	);

	return $query;
}
add_filter( 'ajax_query_attachments_args', 'um_groups_media_overlay_view' );


/**
 * Hide attachment files from the Media Library's list view
 * if they have a certain meta key set.
 *
 * @param WP_Query $query The WP_Query instance (passed by reference).
 */
function um_groups_media_list_view( $query ) {
	// Bail if this is not the admin area.
	if ( ! is_admin() ) {
		return;
	}

	// Bail if this is not the main query.
	if ( ! $query->is_main_query() ) {
		return;
	}

	// Only proceed if this the attachment upload screen.
	$screen = get_current_screen();
	if ( ! $screen || 'upload' !== $screen->id || 'attachment' !== $screen->post_type ) {
		return;
	}

	// Modify the query.
	$query->set(
		'meta_query',
		array(
			array(
				'key'     => '_um_groups_avatar',
				'compare' => 'NOT EXISTS',
			),
		)
	);
}
add_action( 'pre_get_posts', 'um_groups_media_list_view', 10, 1 );

/**
 * @param WP_Query $query
 * @return void
 */
function um_groups_remove_hidden_groups( $query ) {
	// Hidden groups ignore Administrator users and are visible everytime
	if ( current_user_can( 'manage_options' ) ) {
		return;
	}
	if ( $query->is_main_query() && isset( $query->query['post_type'] ) && 'um_groups' === $query->query['post_type'] ) {
		if ( is_user_logged_in() ) {
			$hidden_groups = get_posts(
				array(
					'post_type'      => 'um_groups',
					'post_status'    => 'publish',
					'numberposts'    => -1,
					'meta_query'     => array(
						array(
							'key'     => '_um_groups_privacy',
							'value'   => 'hidden',
							'compare' => '=',
						),
					),
					'fields'         => 'ids',
					'author__not_in' => array( get_current_user_id() ),
				)
			);
			if ( ! empty( $hidden_groups ) ) {
				$joined_groups = UM()->Groups()->api()->get_joined_groups( get_current_user_id(), 'approved', 'ids' );
				if ( ! empty( $joined_groups ) ) {
					$hidden_groups = array_diff( $hidden_groups, $joined_groups );
				}
			}
		} else {
			$hidden_groups = get_posts(
				array(
					'post_type'   => 'um_groups',
					'post_status' => 'publish',
					'numberposts' => -1,
					'meta_query'  => array(
						array(
							'key'     => '_um_groups_privacy',
							'value'   => 'hidden',
							'compare' => '=',
						),
					),
					'fields'      => 'ids',
				)
			);
		}

		if ( ! empty( $hidden_groups ) ) {
			$post__not_in = $query->get( 'post__not_in', array() );
			$query->set( 'post__not_in', array_merge( wp_parse_id_list( $post__not_in ), $hidden_groups ) );
		}
	}
}
add_action( 'pre_get_posts', 'um_groups_remove_hidden_groups' );

function um_groups_count_posts_remove_hidden_groups( $counts, $type = 'post', $perm = '' ) {
	// Hidden groups ignore Administrator users and are visible everytime

	if ( current_user_can( 'manage_options' ) ) {
		return $counts;
	}

	global $wpdb;

	static $cache = array();

	$cache_key  = _count_posts_cache_key( $type, $perm );
	$force      = is_feed() || is_search() || is_admin();
	$cache_key .= $force ? 'force' : '';

	if ( array_key_exists( $cache_key, $cache ) ) {
		return $cache[ $cache_key ];
	}

	if ( is_user_logged_in() ) {
		$exclude_posts = get_posts(
			array(
				'post_type'      => 'um_groups',
				'post_status'    => 'publish',
				'numberposts'    => -1,
				'meta_query'     => array(
					array(
						'key'     => '_um_groups_privacy',
						'value'   => 'hidden',
						'compare' => '=',
					),
				),
				'fields'         => 'ids',
				'author__not_in' => array( get_current_user_id() ),
			)
		);

		if ( ! empty( $exclude_posts ) ) {
			$joined_groups = UM()->Groups()->api()->get_joined_groups( get_current_user_id(), 'approved', 'ids' );
			if ( ! empty( $joined_groups ) ) {
				$exclude_posts = array_diff( $exclude_posts, $joined_groups );
			}
		}
	} else {
		$exclude_posts = get_posts(
			array(
				'post_type'   => 'um_groups',
				'post_status' => 'publish',
				'numberposts' => -1,
				'meta_query'  => array(
					array(
						'key'     => '_um_groups_privacy',
						'value'   => 'hidden',
						'compare' => '=',
					),
				),
				'fields'      => 'ids',
			)
		);
	}

	if ( empty( $exclude_posts ) ) {
		$cache[ $cache_key ] = $counts;
		return $counts;
	}

	$query = "SELECT post_status, COUNT( * ) AS num_posts FROM {$wpdb->posts} WHERE post_type = %s";

	if ( 'readable' === $perm && is_user_logged_in() ) {
		$post_type_object = get_post_type_object( $type );
		if ( ! current_user_can( $post_type_object->cap->read_private_posts ) ) {
			$query .= $wpdb->prepare(
				" AND (post_status != 'private' OR ( post_author = %d AND post_status = 'private' ))",
				get_current_user_id()
			);
		}
	}

	$query .= " AND ID NOT IN('" . implode( "','", $exclude_posts ) . "')";

	$query .= ' GROUP BY post_status';

	$results = (array) $wpdb->get_results( $wpdb->prepare( $query, $type ), ARRAY_A );
	$counts  = array_fill_keys( get_post_stati(), 0 );

	foreach ( $results as $row ) {
		$counts[ $row['post_status'] ] = $row['num_posts'];
	}

	$counts = (object) $counts;

	$cache[ $cache_key ] = $counts;
	return $counts;
}
add_filter( 'wp_count_posts', 'um_groups_count_posts_remove_hidden_groups', 99, 3 );
