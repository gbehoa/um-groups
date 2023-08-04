<?php
/**
 * Template for the UM Groups. Group posts
 *
 * Page: "Group", tab "Discussions"
 * Caller: method Groups_Shortcode->discussion_activity()
 * Caller: method Groups_Shortcode->discussion_wall()
 *
 * @version 2.3.1
 *
 * This template can be overridden by copying it to yourtheme/ultimate-member/um-groups/discussion/user-wall.php
 */
if ( !defined( 'ABSPATH' ) ) {
	exit;
}

global $core_page, $um_group, $um_group_id;

if ( empty( $um_group_id ) ) {
	error_log( 'UM Error: Undefined variable: um_group_id in ' . __FILE__ );
	return;
}

$args = array(
	'post_type'         => 'um_groups_discussion',
	'posts_per_page'    => ( UM()->mobile()->isMobile() ) ? UM()->options()->get( 'groups_posts_num_mob' ) : UM()->options()->get( 'groups_posts_num' ),
	'post_status'       => array( 'publish' ),
);

if ( isset( $offset ) ) {
	$args[ 'offset' ] = $offset;
}

if ( isset( $user_wall ) && $user_wall ) {
	$args[ 'author' ] = sanitize_html_class( $user_id );
}

if ( isset( $wall_post ) && $wall_post > 0 ) {

	$args[ 'post__in' ] = array( $wall_post );
} elseif ( isset( $hashtag ) && $hashtag ) {

	$args[ 'tax_query' ] = array( array( 'taxonomy' => 'um_hashtag', 'field' => 'slug', 'terms' => array( $hashtag ) ) );
} elseif ( UM()->Groups()->discussion()->followed_ids() ) {

	$args[ 'meta_query' ][] = array( 'key' => '_user_id', 'value' => UM()->Groups()->discussion()->followed_ids(), 'compare' => 'IN' );
} elseif ( UM()->Groups()->discussion()->friends_ids() ) {

	$args[ 'meta_query' ][] = array( 'key' => '_user_id', 'value' => UM()->Groups()->discussion()->friends_ids(), 'compare' => 'IN' );
} elseif ( um_is_core_page( 'user' ) || ( isset( $core_page ) && $core_page == 'user' & defined( 'DOING_AJAX' ) ) ) {

	$um_current_page_tab = get_query_var( 'profiletab' );

	if ( $um_current_page_tab == 'activity' && ! defined( 'DOING_AJAX' ) ) {
		unset( $args[ 'author' ] );

		$args[ 'meta_query' ][] = array(
			'relation' => 'OR',
			array(
				'key'       => '_wall_id',
				'value'     => $user_id,
				'compare'   => '='
			),
			array(
				'key'       => '_user_id',
				'value'     => $user_id,
				'compare'   => '='
			),
		);
	}
}

if ( isset( $user_wall ) && $user_wall && isset( $core_page ) && $core_page != 'user' ) {
	$args[ 'author' ] = sanitize_html_class( $user_id );
}

$args[ 'meta_query' ][] = array(
	'key'     => '_group_id',
	'value'   => $um_group_id,
	'compare' => '='
);

$um_group_moderation = get_post_meta( $um_group_id, '_um_groups_posts_moderation', true );

$can_moderate_post = UM()->Groups()->api()->can_moderate_posts( $um_group_id );
$show_pending_approval = get_query_var( 'show' );

if ( 'require-moderation' == $um_group_moderation ) {

	if ( 'pending' == $show_pending_approval || ( isset( $show_pending ) && $show_pending == true ) ) {
		$args[ 'meta_key' ] = '_group_moderation';
		$args[ 'meta_value' ] = 'pending_review';
		if ( ! $can_moderate_post ) {
			$args[ 'author' ] = get_current_user_id();
		}
	} elseif ( 'author_pending' == $show_pending_approval ) {
		$args[ 'meta_key' ] = '_group_moderation';
		$args[ 'meta_value' ] = 'pending_review';
		$args[ 'author' ] = get_current_user_id();
	} else {
		$args[ 'meta_key' ] = '_group_moderation';
		$args[ 'meta_value' ] = 'approved';
	}

	if ( $can_moderate_post ) {
		if ( 'pending' == $show_pending_approval || ( isset( $show_pending ) && $show_pending == true ) ) {
			$args[ 'meta_key' ] = '_group_moderation';
			$args[ 'meta_value' ] = 'pending_review';
		} elseif ( 'reported' == $show_pending_approval || ( isset( $show_pending ) && $show_pending == 'reported' ) ) {
			$args[ 'meta_key' ] = '_reported';
			$args[ 'meta_value' ] = 1;
			$args[ 'meta_compare' ] = '>=';
		} else {
			$args[ 'meta_key' ] = '_group_moderation';
			$args[ 'meta_value' ] = 'approved';
		}
	} else {
		if ( 'reported_author' == $show_pending_approval || ( isset( $show_pending ) && $show_pending == 'reported_author' ) ) {
			global $wpdb;
			$posts = $wpdb->get_results( $wpdb->prepare(
				"SELECT post_id 
				FROM {$wpdb->postmeta}
				WHERE meta_key = '_group_id'
				AND meta_value = %d",
				$group_id
			), ARRAY_A );

			$posts_in = array();
			if ( ! empty( $posts ) ) {
				foreach ( $posts as $post ) {
					$reported_by = get_post_meta( $post['post_id'], '_reported_by', true );
					$reported    = get_post_meta( $post['post_id'], '_reported', true );
					if ( ! empty( $reported_by ) && $reported > 0 &&  array_key_exists( get_current_user_id(), $reported_by ) ) {
						$posts_in[] = $post['post_id'];
					}
				}
			}
			if ( ! empty( $posts_in ) ) {
				$args['post__in'] = $posts_in;
			}
		}
	}
} else {
	if ( 'reported' == $show_pending_approval || ( isset( $show_pending ) && $show_pending == 'reported' ) ) {
		$args[ 'meta_key' ] = '_reported';
		$args[ 'meta_value' ] = 1;
		$args[ 'meta_compare' ] = '>=';
	} elseif ( 'reported_author' == $show_pending_approval || ( isset( $show_pending ) && $show_pending == 'reported_author' ) ) {
		global $wpdb;
		$posts = $wpdb->get_results( $wpdb->prepare(
			"SELECT post_id 
			FROM {$wpdb->postmeta}
			WHERE meta_key = '_group_id'
			AND meta_value = %d",
			$group_id
		), ARRAY_A );
		$posts_in = array();
		if ( ! empty( $posts ) ) {
			foreach ( $posts as $post ) {
				$reported_by = get_post_meta( $post['post_id'], '_reported_by', true );
				$reported    = get_post_meta( $post['post_id'], '_reported', true );
				if ( ! empty( $reported_by ) && $reported > 0 &&  array_key_exists( get_current_user_id(), $reported_by ) ) {
					$posts_in[] = $post['post_id'];
				}
			}
		}
		if ( ! empty( $posts_in ) ) {
			$args['post__in'] = $posts_in;
		}
	}
}

if ( is_array( $args ) ) {
	$args = apply_filters( 'um_groups_discussion_wall_args', $args );
}

$wallposts = new WP_Query( $args );

if ( $wallposts->found_posts == 0 ) {
	return;
}

foreach ( $wallposts->posts as $post ) {
	setup_postdata( $post );

	$post_id = $post->ID;
	$author_id = UM()->Groups()->discussion()->get_author( $post->ID );
	$wall_id = UM()->Groups()->discussion()->get_wall( $post->ID );
	$post_link = UM()->Groups()->discussion()->get_permalink( $post->ID );

	um_fetch_user( $author_id );

	$pending_approval = false;
	$can_view = apply_filters( 'um_groups_wall_can_view', -1, $author_id );

	// exclude private walls
	if ( $can_view >= 0 ) {
		continue;
	}

	if ( ( isset( $show_pending_approval ) && 'pending' == $show_pending_approval ) || ( isset( $show_pending ) && $show_pending == true ) ) {
		$post_link = add_query_arg( 'show', 'pending', $post_link );
		$pending_approval = true;
	}

	$has_video = UM()->Groups()->discussion()->get_video( $post->ID );
	$has_text_video = get_post_meta( $post->ID, '_video_url', true );
	$has_oembed = get_post_meta( $post->ID, '_oembed', true );
	?>

	<div class="um-groups-widget" id="postid-<?php echo esc_attr( $post->ID ); ?>">

		<div class="um-groups-head">

			<div class="um-groups-left um-groups-author">
				<div class="um-groups-ava"><a href="<?php echo esc_url( um_user_profile_url() ); ?>"><?php echo get_avatar( $author_id, 80 ); ?></a></div>
				<div class="um-groups-author-meta">
					<div class="um-groups-author-url">
						<a href="<?php echo esc_url( um_user_profile_url() ); ?>" class="um-link"><?php echo um_user( 'display_name', 'html' ); ?></a>
						<?php
						if ( $wall_id && $wall_id != $author_id ) {
							um_fetch_user( $wall_id );
							echo '<i class="um-icon-forward"></i>';
							echo '<a href="' . um_user_profile_url() . '" class="um-link">' . um_user( 'display_name', 'html' ) . '</a>';
						}
						?>
					</div>
					<span class="um-groups-metadata">
						<a href="<?php echo esc_url( $post_link ); ?>"><?php echo esc_html( UM()->Groups()->discussion()->get_post_time( $post->ID ) ); ?></a>
					</span>
				</div>
			</div>

			<?php if ( UM()->Groups()->member()->get_role() != 'banned' ) { ?>
				<div class="um-groups-right">

					<?php if( isset( $can_moderate_post ) && $can_moderate_post && $pending_approval ) { ?>
						<a href="#" class="um-groups-ticon um-groups-post-approval-tool um-groups-start-dialog um-tip-n" title="<?php _e( "Approve", "um-groups" ); ?>" original-title="<?php _e( "Approve", "um-groups" ); ?>" data-discussion-id="<?php echo esc_attr( $post->ID ); ?>" data-uid="<?php echo esc_attr( $author_id ); ?>" data-role="approve">
							<i class="um-faicon-check"></i>
						</a>
						<?php if( $author_id != get_current_user_id() ) { ?>
							<a href="#" class="um-groups-ticon um-groups-post-approval-tool um-groups-start-dialog  um-tip-n" title="<?php _e( "Delete", "um-groups" ); ?>" data-msg="<?php _e( "Are you sure you want to delete this post?", "um-groups" ); ?>"   original-title="<?php _e( "Delete", "um-groups" ); ?>"  data-discussion-id="<?php echo esc_attr( $post->ID ); ?>"  data-uid="<?php echo esc_attr( $author_id ); ?>" data-role="delete">
								<i class="um-faicon-remove"></i>
							</a>
						<?php } ?>


					<?php } ?>

					<?php if( is_user_logged_in() && (!$pending_approval || $author_id == get_current_user_id() ) ) { ?>

						<a href="#" class="um-groups-ticon um-groups-start-dialog" data-role="um-groups-tool-dialog"><i class="um-faicon-chevron-down"></i></a>

						<div class="um-groups-dialog um-groups-tool-dialog">

							<?php if( ( current_user_can( 'edit_users' ) || $author_id == get_current_user_id() ) || ( UM()->Groups()->discussion()->get_action_type( $post->ID ) == 'status' ) && $can_moderate_post && !$pending_approval ) { ?>
								<a href="#" class="um-groups-manage" data-cancel_text="<?php _e( 'Cancel editing', 'um-groups' ); ?>" data-update_text="<?php _e( 'Update', 'um-groups' ); ?>"><?php _e( 'Edit', 'um-groups' ); ?></a>
							<?php } ?>

							<?php if( current_user_can( 'edit_users' ) || $author_id == get_current_user_id() || $can_moderate_post) { ?>
								<a href="#" class="um-groups-trash" data-msg="<?php _e( 'Are you sure you want to delete this post?', 'um-groups' ); ?>"><?php _e( 'Delete', 'um-groups' ); ?></a>
							<?php } ?>

							<?php if( $author_id != get_current_user_id() || ($author_id == get_current_user_id() && $can_moderate_post ) ) { ?>
								<?php if( ( current_user_can( 'edit_users' ) || $author_id == get_current_user_id() ) || ( UM()->Groups()->discussion()->get_action_type( $post->ID ) == 'status' ) && $can_moderate_post && !$pending_approval ) { ?>
									<span class="sep"></span>
								<?php } ?>
								<?php $users_reported = get_post_meta( $post->ID, '_reported_by', true ) ? get_post_meta( $post->ID, '_reported_by', true ) : array(); ?>
								<?php if ( UM()->Groups()->discussion()->reported( $post->ID ) && ( $can_moderate_post || in_array( get_current_user_id(), array_keys( $users_reported ) ) ) ) { ?>
									<a href="#" class="um-groups-report flagged" data-report="<?php _e( 'Report', 'um-groups' ); ?>" data-cancel_report="<?php _e( 'Cancel report', 'um-groups' ); ?>">
										<?php _e( 'Cancel report', 'um-groups' ); ?>
									</a>
								<?php } ?>
								<?php if ( ! $can_moderate_post && ! array_key_exists( get_current_user_id(), $users_reported ) ) { ?>
									<a href="#" class="um-groups-report" data-report="<?php _e( 'Report', 'um-groups' ); ?>" data-cancel_report="<?php _e( 'Cancel report', 'um-groups' ); ?>">
										<?php _e( 'Report', 'um-groups' ); ?>
									</a>
								<?php } ?>
							<?php } ?>

						</div>

					<?php } ?>

				</div>
			<?php } ?>
			<div class="um-clear"></div>

		</div>

		<div class="um-groups-body">
			<div class="um-groups-bodyinner<?php
			if ( $has_video || $has_text_video ) {
				echo ' has-embeded-video';
			}
			?> <?php
			if ( $has_oembed ) {
				echo ' has-oembeded';
			}
			?>">
				<div class="um-groups-bodyinner-edit">
					<textarea style="display: none;"><?php echo esc_attr( get_post_meta( $post->ID, '_original_content', true ) ); ?></textarea>

					<?php $photo_base = get_post_meta( $post->ID, '_photo', true ); ?>
					<input type="hidden" name="_photo_" id="_photo_" value="<?php echo esc_attr( $photo_base ); ?>" />

					<?php $photo_base = wp_basename( $photo_base ); ?>
					<?php $photo_url = UM()->uploader()->get_upload_user_base_url( $author_id ) . "/{$photo_base}"; ?>
					<input type="hidden" name="_photo_url" id="_photo_url" value="<?php echo esc_attr( $photo_url ); ?>" />


				</div>

				<?php $um_groups_discussion_post = UM()->Groups()->discussion()->get_content( $post->ID, $has_video ); ?>
				<?php $um_shared_link = get_post_meta( $post->ID, '_shared_link', true ); ?>
				<?php if( $um_groups_discussion_post || $um_shared_link ) { ?>
					<div class="um-groups-bodyinner-txt">
						<?php echo $um_groups_discussion_post; ?>
						<?php echo $um_shared_link; ?>
					</div>
				<?php } ?>

				<div class="um-groups-bodyinner-photo">
					<?php echo UM()->Groups()->discussion()->get_photo( $post->ID, '', $author_id ); ?>
				</div>

				<?php if( empty( $um_shared_link ) ) { ?>
					<div class="um-groups-bodyinner-video">
						<?php echo $has_video; ?>
					</div>
				<?php } ?>

			</div>

			<?php
			/**
			 * Hook:   um_groups_discussion_post_body_after.
			 * @since  2.2.2
			 * @hooked um_groups_discussion_post_counters - 10
			 */
			do_action( 'um_groups_discussion_post_body_after', $post );
			?>

		</div>

		<?php if ( ! $pending_approval && UM()->Groups()->member()->get_role() != 'banned' ) { ?>
			<div class="um-groups-foot status" id="wallcomments-<?php echo esc_attr( $post->ID ); ?>">

				<?php if ( is_user_logged_in() && UM()->Groups()->api()->has_joined_group() ) { ?>

					<div class="um-groups-left um-groups-actions">
						<?php if ( UM()->Groups()->discussion()->user_liked( $post->ID ) ) { ?>
							<div class="um-groups-like active" data-like_text="<?php _e( 'Like', 'um-groups' ); ?>" data-unlike_text="<?php _e( 'Unlike', 'um-groups' ); ?>"><a href="#"><i class="um-faicon-thumbs-up um-active-color"></i><span class=""><?php _e( 'Unlike', 'um-groups' ); ?></span></a></div>
						<?php } else { ?>
							<div class="um-groups-like" data-like_text="<?php _e( 'Like', 'um-groups' ); ?>" data-unlike_text="<?php _e( 'Unlike', 'um-groups' ); ?>"><a href="#"><i class="um-faicon-thumbs-up"></i><span class=""><?php _e( 'Like', 'um-groups' ); ?></span></a></div>
						<?php } ?>
						<?php if( UM()->Groups()->discussion()->can_comment() ) { ?>
							<div class="um-groups-comment"><a href="#"><i class="um-faicon-comment"></i><span class=""><?php _e( 'Comment', 'um-groups' ); ?></span></a></div>
						<?php } ?>
					</div>

				<?php } else { ?>
					<div class="um-groups-left um-groups-join"><?php _e( 'Please join group to like or comment on this post.', 'um-activity' ); ?></div>
				<?php } ?>

				<div class="um-clear"></div>
			</div>

			<?php
			$t_args = compact( 'post', 'post_id' );
			UM()->get_template( 'discussion/comments.php', um_groups_plugin, $t_args, true );
			?>

		<?php } ?>
	</div>

	<?php
}

um_reset_user();
wp_reset_postdata();
?>

<div class="um-groups-load"></div>