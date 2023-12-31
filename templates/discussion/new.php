<?php
/**
 * Template for the UM Groups. The "Wtite post" form
 *
 * Page: "Group", tab "Discussions"
 * Caller: method Groups_Shortcode->discussion_activity()
 * Caller: method Groups_Shortcode->discussion_wall()
 *
 * @version 2.3.1
 *
 * This template can be overridden by copying it to yourtheme/ultimate-member/um-groups/discussion/new.php
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( UM()->Groups()->member()->get_role() == 'banned' ) {
	return;
}

global $um_group, $um_group_id;

$user_id = get_current_user_id();
$show_pending_approval = get_query_var( 'show' );

$group_post_id = isset( $_GET['group_post'] ) ? absint( $_GET['group_post'] ): false;
$has_joined = UM()->Groups()->api()->has_joined_group( $user_id, $um_group_id );
$privacy = UM()->Groups()->api()->get_privacy_slug( $um_group_id );

$hide_wall_post = false;
if ( ! in_array( $has_joined, array( 'approved' ) ) || 'pending' == $show_pending_approval || ! empty( $group_post_id ) || 'reported' == $show_pending_approval || 'reported_author' == $show_pending_approval ) {
	$hide_wall_post = true;
}

$total_pending_reviews = UM()->Groups()->discussion()->get_pending_reviews_count( $user_id, $um_group_id );

$group_moderation = get_post_meta( $um_group_id, '_um_groups_posts_moderation', true );

if ( $total_pending_reviews > 0 && is_user_logged_in() && 'require-moderation' == $group_moderation && ! $hide_wall_post ) {


	echo "<div class='um-groups-pending-approval'>";
	echo "<i class='um-groups-pending-icon um-faicon-exclamation-triangle'></i>";
	if ( UM()->Groups()->api()->can_moderate_posts( $um_group_id, $user_id ) ) {
		$group_pending_discussions_url = add_query_arg( array(
			'tab'   => 'discussion',
			'show'  => 'pending'
		), get_the_permalink( $um_group_id ) );
		echo "<a href='" . esc_url( $group_pending_discussions_url ) . "'>";
		echo sprintf( _n( "%s post requires approval", "%s posts require approval", $total_pending_reviews, 'um-groups' ), $total_pending_reviews );
		echo "</a>";
	} else {
		$group_pending_discussions_url = add_query_arg( array(
			'tab'   => 'discussion',
			'show'  => 'author_pending'
		), get_the_permalink( $um_group_id ) );
		echo "<a href='" . esc_url( $group_pending_discussions_url ) . "'>";
		echo sprintf( _n( "You have %s post requires admin approval", "You have %s posts require admin approval", $total_pending_reviews, 'um-groups' ), $total_pending_reviews );
		echo "</a>";
	}
	echo "</div>";
}

$total_reported_posts = UM()->Groups()->discussion()->get_reported_posts_count( $user_id, $um_group_id );

if ( $total_reported_posts > 0 && is_user_logged_in() && ! $hide_wall_post ) {
	$group_reported_discussions_url = add_query_arg( array(
		'tab'   => 'discussion',
		'show'  => 'reported'
	), get_the_permalink( $um_group_id ) );

	echo "<div class='um-groups-pending-approval'>";
	echo "<i class='um-groups-pending-icon um-faicon-exclamation-triangle'></i>";
	echo "<a href='" . esc_url( $group_reported_discussions_url ) . "'>";
	if ( UM()->Groups()->api()->can_moderate_posts( $um_group_id, $user_id ) ) {
		echo sprintf( _n( "%s reported post requires approval", "%s reported posts require approval", $total_reported_posts, 'um-groups' ), $total_reported_posts );
	}
	echo "</a>";
	echo "</div>";
}

if ( is_user_logged_in() && ! $hide_wall_post && ! UM()->Groups()->api()->can_moderate_posts( $um_group_id ) ) {
	$reported_by_count = 0;

	global $wpdb;
	$posts = $wpdb->get_results( $wpdb->prepare(
		"SELECT post_id 
			FROM {$wpdb->postmeta}
			WHERE meta_key = '_group_id'
			AND meta_value = %d",
		$um_group_id
	), ARRAY_A );

	if ( ! empty( $posts ) ) {
		foreach ( $posts as $post ) {
			$reported_by = get_post_meta( $post['post_id'], '_reported_by', true );
			$reported = get_post_meta( $post['post_id'], '_reported', true );
			if ( ! empty( $reported_by ) && $reported > 0 && array_key_exists( get_current_user_id(), $reported_by ) ) {
				$reported_by_count++;
			}
		}
		if ( $reported_by_count > 0 ) {
			$group_reported_discussions_url = add_query_arg( array(
				'tab'   => 'discussion',
				'show'  => 'reported_author'
			), get_the_permalink( $um_group_id ) );

			echo "<div class='um-groups-pending-approval'>";
			echo "<i class='um-groups-pending-icon um-faicon-exclamation-triangle'></i>";
			echo "<a href='" . esc_url( $group_reported_discussions_url ) . "'>";
			echo sprintf( _n( "You have %s post that you reported. It require administrator approval.", "You have %s posts that you reported. They require administrator approval.", $reported_by_count, 'um-groups' ), $reported_by_count );
			echo "</a>";
			echo "</div>";
		}
	}
}
?>

<div class="um-groups-widget um-groups-new-post" <?php echo esc_attr( $hide_wall_post ? "style='display:none;'" : '' ); ?> >
	<form action="" method="post" class="um-groups-publish">

		<div class="um-groups-head"><?php echo esc_html( ( um_profile_id() == get_current_user_id() ) ? __( 'Write Post', 'um-groups' ) : sprintf( __( 'Post on %s\'s wall', 'um-groups' ), um_user( 'display_name' ) ) ); ?></div>

		<div class="um-groups-body">

			<div class="um-groups-textarea">
				<textarea data-photoph="<?php _e( 'Say something about this photo', 'um-groups' ); ?>" data-ph="<?php _e( 'Write something...', 'um-groups' ); ?>" placeholder="<?php _e( 'Write something...', 'um-groups' ); ?>" class="um-groups-textarea-elem" name="_post_content" id="_post_content"></textarea>
			</div>

			<div class="um-groups-preview">
				<span class="um-groups-preview-spn">
					<img src="" alt="" title="" width="" height="" />
					<span class="um-groups-img-remove"><i class="um-icon-close"></i></span>
				</span>
				<input type="hidden" name="_post_img" id="_post_img" value="" />
				<input type="hidden" name="_post_img_url" id="_post_img_url" value="" />
			</div>

			<div class="um-clear"></div>
		</div>

		<div class="um-groups-foot">

			<div class="um-groups-left um-groups-insert">

				<?php do_action( 'um_groups_pre_insert_tools' ); ?>
				<?php 
				$allowed_image_types = array(
					"gif",
					"png",
					"jpeg",
					"jpg",
				);
				
				/**
				 * UM hook
				 *
				 * @type filter
				 * @title um_groups_discussion_allowed_image_types
				 * @description Filter allowed image types
				 * @input_vars
				 * [{"var":"$allowed_image_types","type":"array","desc":"Image Types"}]
				 * @change_log
				 * ["Since: 2.1.1"]
				 * @usage add_filter( 'um_groups_discussion_allowed_image_types', 'function_name', 10, 1 );
				 * @example
				 * <?php
				 * add_filter( 'um_groups_discussion_allowed_image_types', 'my_get_field', 10, 1 );
				 * function my_get_field( $data ) {
				 *     // your code here
				 *     return $data;
				 * }
				 * ?>
				 */
				$allowed_image_types = apply_filters("um_groups_discussion_allowed_image_types", $allowed_image_types );
				?>

				<?php if( !UM()->roles()->um_user_can( 'groups_photo_off' ) ) { ?>
					<?php $timestamp = current_time( "timestamp" ); ?>
					<?php $nonce = wp_create_nonce( 'um_upload_nonce-' . $timestamp ); ?>
					<a href="#" class="um-groups-insert-photo um-tip-s" data-timestamp="<?php echo esc_attr( $timestamp ); ?>" data-nonce="<?php echo esc_attr( $nonce ); ?>" title="<?php _e( 'Add photo', 'um-groups' ); ?>" data-allowed="<?php echo implode( ",", $allowed_image_types); ?>" data-size-err="<?php _e( 'Image is too large', 'um-groups' ); ?>" data-ext-err="<?php _e( 'Please upload a valid image', 'um-groups' ); ?>"><i class="um-faicon-camera"></i></a>
				<?php } ?>

				<?php do_action( 'um_groups_post_insert_tools' ); ?>

				<div class="um-clear"></div>
			</div>

			<div class="um-groups-right">
				<a href="#" class="um-button um-groups-post um-disabled"><?php _e( 'Post', 'um-groups' ); ?></a>
			</div>

			<div class="um-clear"></div>
		</div>

		<input type="hidden" name="_group_id" id="_group_id" value="<?php echo esc_attr( $um_group_id ); ?>" />
		<input type="hidden" name="_wall_id" id="_wall_id" value="<?php echo esc_attr( $user_id ); ?>" />
		<input type="hidden" name="_post_id" id="_post_id" value="0" />
		<input type="hidden" name="action" id="action" value="um_groups_publish" />
		<input type="hidden" name="nonce" id="nonce" value="<?php echo esc_attr( wp_create_nonce( 'um-frontend-nonce' ) ); ?>" />
	</form>
</div>