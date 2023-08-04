<?php if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Change Group Title
 *
 * @param $title
 *
 * @return string
 */
function um_groups_change_title( $title ) {
	$screen = get_current_screen();

	if  ( 'um_groups' == $screen->post_type ) {
		$title = __('Enter group name...','um-groups');
	}

	return $title;
}
add_filter( 'enter_title_here', 'um_groups_change_title', 10, 1 );


/**
 * Change permalink for the group post comment
 * @since  2.2.2
 *
 * @param  string     $comment_link
 * @param  WP_Comment $comment
 * @return string
 */
function um_groups_get_comment_link( $comment_link, $comment ){
	$group_id = get_post_meta( $comment->comment_post_ID, '_group_id', true );
	if( $group_id ){
		$post_link = UM()->Groups()->discussion()->get_permalink( $comment->comment_post_ID );
		$comment_link = UM()->Groups()->discussion()->get_comment_link( $post_link, $comment->comment_ID );
	}
	return $comment_link;
}
add_filter( 'get_comment_link', 'um_groups_get_comment_link', 10, 2 );


/**
 * Add table columns
 *
 * @param $columns
 *
 * @return array
 */
function um_groups_posts_columns( $columns ) {
	unset( $columns['title'] );

	$columns['group_name'] = __('Name', 'um-groups');

	$new_columns = array(
		'members' => __('Members', 'um-groups'),
		'privacy' => __('Privacy', 'um-groups'),
		'creator'  => __('Creator', 'um-groups'),
	);

	$new_columns['date'] = $columns['date'];
	unset( $columns['date'] );

	return array_merge($columns, $new_columns);
}
add_filter( 'manage_um_groups_posts_columns', 'um_groups_posts_columns' );


/**
 * Add group avatar in `Title` Column
 *
 * @param $column_name
 * @param $post_id
 */
function um_groups_table_content( $column_name, $post_id ) {
	if ($column_name == 'group_name') {
		echo UM()->Groups()->api()->get_group_image( $post_id );
		the_title();
	}
}
add_action( 'manage_um_groups_posts_custom_column', 'um_groups_table_content', 10, 2 );


/**
 * Restrict posting in group wall when not completed profile option
 * @param $fields
 * @param $role
 *
 * @return array
 */
function um_groups_profile_completeness_options( $fields, $role ) {
	$fields[] = array(
		'id'            => '_um_profilec_prevent_group_post',
		'type'          => 'select',
		'label'         => __( 'Require profile to be complete to create post in groups?', 'um-groups' ),
		'tooltip'       => __( 'Prevent user from adding posts in groups If their profile completion is below the completion threshold set up above?', 'um-groups' ),
		'value'         => ! empty( $role['_um_profilec_prevent_group_post'] ) ? $role['_um_profilec_prevent_group_post'] : 0,
		'conditional'   => array( '_um_profilec', '=', '1' ),
		'options'       => array(
			0   => __( 'No', 'um-groups' ),
			1   => __( 'Yes', 'um-groups' ),
		),
	);

	return $fields;
}
add_filter( 'um_profile_completeness_roles_metabox_fields', 'um_groups_profile_completeness_options', 10, 2 );


/**
 * Register exporter for Plugin GDPR complicity.
 *
 * @param $exporters
 *
 * @return array
 */
function um_groups_register_exporters( $exporters ) {
	$exporters['um-groups-group-posts'] = array(
		'exporter_friendly_name' => um_groups_extension,
		'callback'               => 'um_groups_group_post_exporter',
	);
	return $exporters;
}
add_filter( 'wp_privacy_personal_data_exporters', 'um_groups_register_exporters' );


/**
 * Exporter handler
 *
 * @param $email_address
 * @param int $page
 *
 * @return array
 */
function um_groups_group_post_exporter( $email_address, $page = 1 ) {
	// Limit us to 500 comments at a time to avoid timing out.
	$number = 500;
	$page   = (int) $page;

	$data_to_export = array();
	$activity_posts = array();

	$user = get_user_by( 'email', $email_address );
	if ( $user && ! is_wp_error( $user ) ) {
		$user_id = $user->ID;

		$activity_posts = get_posts( array(
			'post_type'     => 'um_groups_discussion',
			'author'        => $user_id,
			'numberposts'   => $number,
			'offset'        => $number * ( $page - 1 ),
			'post_status'   => 'any',
		) );

		$post_prop_to_export = array(
			'ID'                    => __( 'Post ID', 'um-groups' ),
			'post_author'           => __( 'Post Author', 'um-groups' ),
			'post_author_email'     => __( 'Post Author Email', 'um-groups' ),
			'post_date'             => __( 'Post Date', 'um-groups' ),
			'post_title'            => __( 'Post Title', 'um-groups' ),
			'post_content'          => __( 'Post Content', 'um-groups' ),
			'post_link'             => __( 'Post URL', 'um-groups' ),
			'post_attachment'       => __( 'Post Image', 'um-groups' ),
		);
	}

	if ( empty( $activity_posts ) ) {
		$activity_posts = array();
	}

	foreach ( (array) $activity_posts as $post ) {
		$comment_data_to_export = array();

		foreach ( $post_prop_to_export as $key => $name ) {
			$value = '';

			switch ( $key ) {
				case 'post_author':
					$value = $user->display_name;
					break;
				case 'post_author_email':
					$value = $email_address;
					break;
				case 'ID':
				case 'post_date':
				case 'post_title':
					$value = $post->{$key};
					break;
				case 'post_content':
					$value = apply_filters( 'the_content', $post->post_content );
					break;
				case 'post_link':
					$value = UM()->Groups()->discussion()->get_permalink( $post->ID );
					break;
				case 'post_attachment':
					$photo_base = get_post_meta( $post->ID, '_photo', true );
					$photo_base = wp_basename( $photo_base );
					$photo_url = UM()->uploader()->get_upload_user_base_url( $user_id ) . "/{$photo_base}";
					$value = empty( $photo_base ) ? __( '(none)', 'um-groups' ) : $photo_url;
					break;
			}

			if ( ! empty( $value ) ) {
				$comment_data_to_export[] = array(
					'name'  => $name,
					'value' => $value,
				);
			}
		}

		$data_to_export[] = array(
			'group_id'          => 'um-groups-group-discussions',
			'group_label'       => __( 'Ultimate Member - Groups\' Discussions posts' ),
			'group_description' => __( 'User&#8217;s groups\' discussions post data.' ),
			'item_id'           => "um-groups-discussions-post-{$post->ID}",
			'data'              => $comment_data_to_export,
		);
	}

	$done = count( $activity_posts ) < $number;

	return array(
		'data' => $data_to_export,
		'done' => $done,
	);
}