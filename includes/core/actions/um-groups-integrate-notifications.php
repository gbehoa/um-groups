<?php if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Invite Members
 *
 * @param $invited_user_id
 * @param $group_id
 * @param $invited_by_user_id
 */
function um_groups_notify_invite_member( $invited_user_id, $group_id, $invited_by_user_id ) {
	if( ! class_exists('UM_Notifications_API') ) {
		return;
	}

	um_fetch_user( $invited_by_user_id );

	$vars = array();
	$vars['photo'] = um_get_avatar_url( get_avatar( $invited_by_user_id, 40 ) );
	$vars['group_name'] = ucwords( get_the_title( $group_id ) );
	$vars['notification_uri'] = get_the_permalink( $group_id );
	$vars['group_invitation_host_name'] = um_user('display_name');
	$privacy = UM()->Groups()->api()->get_privacy_slug( $group_id );
	if ( $privacy == 'hidden' ) {
		$vars['notification_uri'] = um_get_core_page( 'groups' ) . '?filter=own';
	}

	UM()->Notifications_API()->api()->store_notification( $invited_user_id, 'groups_invite_member', $vars );

	um_reset_user();
}
add_action( 'um_groups_after_member_changed_status__pending_member_review', 'um_groups_notify_invite_member', 1, 3 );


/**
 * Join Request
 *
 * @param $user_id
 * @param $group_id
 * @param $invited_by_user_id
 */
function um_groups_notify_join_request( $user_id, $group_id, $invited_by_user_id ) {
	if ( !class_exists( 'UM_Notifications_API' ) ) {
		return;
	}

	if( $user_id == $invited_by_user_id ) {

		um_fetch_user( $user_id );
		$vars = array();
		$vars['member_name'] = um_user('display_name');
		$vars['group_name'] = ucwords( get_the_title( $group_id ) );
		$vars['notification_uri'] = get_the_permalink( $group_id )."?tab=requests";

		$moderators = UM()->Groups()->member()->get_moderators( $group_id );
		foreach( $moderators as $key => $mod ){

			um_fetch_user( $mod->uid );

			$vars['photo'] = um_get_avatar_url( get_avatar( $user_id, 40 ) );
			$vars['group_invitation_host_name'] = um_user('display_name');

			UM()->Notifications_API()->api()->store_notification( $mod->uid, 'groups_join_request', $vars );
		}
		um_reset_user();
	}

}
add_action( 'um_groups_after_member_changed_status__pending_admin_review', 'um_groups_notify_join_request', 1, 3 );


/**
 * Approve Member
 *
 * @param $user_id
 * @param $group_id
 * @param $invited_by_user_id
 * @param $group_role
 * @param $new_group
 */
function um_groups_notify_approve_member( $user_id, $group_id, $invited_by_user_id, $group_role, $new_group ) {
	if ( ! class_exists('UM_Notifications_API') ) {
		return;
	}
	if ( $new_group ) {
		return;
	}

	um_fetch_user( $user_id );

	$vars = array();
	$vars['photo'] = UM()->Groups()->api()->get_group_image( $group_id, 'default', 50, 50, true );
	$vars['group_name'] = ucwords( get_the_title( $group_id ) );
	$vars['notification_uri'] = get_the_permalink( $group_id );
	$vars['group_invitation_host_name'] = um_user('display_name');

	foreach ( $vars as $key => $value ) {
		if( is_array( $value ) ){
			$vars[$key] = current( $value );
		}
	}

	UM()->Notifications_API()->api()->store_notification( $user_id, 'groups_approve_member', $vars );

	um_reset_user();
}
add_action( 'um_groups_after_member_changed_status__approved', 'um_groups_notify_approve_member', 1, 5 );
add_action( 'um_groups_after_member_changed_status__hidden_approved', 'um_groups_notify_approve_member', 1, 5 );


/**
 * Member Changed Role
 *
 * @param $user_id
 * @param $group_id
 * @param $new_role
 * @param $old_role
 */
function um_groups_notify_member_changed_role( $user_id, $group_id, $new_role, $old_role ) {

	if ( ! class_exists('UM_Notifications_API') ) {
		return;
	}

	um_fetch_user( $user_id );

	$group_member_roles = UM()->Groups()->api()->get_member_roles();

	$vars = array();
	$vars['photo'] = UM()->Groups()->api()->get_group_image( $group_id, 'default', 50, 50, true );
	$vars['group_name'] = ucwords( get_the_title( $group_id ) );
	$vars['group_role_new'] = $group_member_roles[ $new_role ];
	$vars['group_role_old'] = $group_member_roles[ $old_role ];
	$vars['notification_uri'] = get_the_permalink( $group_id );

	foreach ( $vars as $key => $value ) {
		if( is_array( $value ) ){
			$vars[$key] = current( $value );
		}
	}

	UM()->Notifications_API()->api()->store_notification( $user_id, 'groups_change_role', $vars );

	um_reset_user();
}
add_action( 'um_groups_after_member_changed_role', 'um_groups_notify_member_changed_role', 1, 4 );


/**
 * Real-time notification to group members when someone posts on the group
 *
 * @version 2.2.5
 *
 * @global  wpdb  $wpdb
 * @param   int   $post_id
 * @param   int   $user_id
 * @param   int   $wall_id
 */
function um_groups_notify_new_post( $post_id, $user_id, $wall_id ) {
	$key = 'groups_new_post';

	if ( !class_exists( 'UM_Notifications_API' ) ) {
		return;
	}
	if ( !UM()->options()->get( "log_$key" ) ) {
		return;
	}

	global $wpdb;
	$table_name = UM()->Groups()->setup()->db_groups_table;
	$group_id = get_post_meta( $post_id, '_group_id', true );
	$members = $wpdb->get_col( "SELECT `user_id1` FROM $table_name WHERE `group_id` = $group_id AND `status` = 'approved'" );

	foreach ( $members as $i => $member_id ) {
		if ( $user_id == $member_id ) {
			unset( $members[$i] );
			continue;
		}
		$prefs = get_user_meta( $user_id, '_notifications_prefs', true );
		if ( isset( $prefs[$key] ) && !$prefs[$key] ) {
			unset( $members[$i] );
			continue;
		}
	}
	if ( empty( $members ) ) {
		return;
	}

	um_fetch_user( $user_id );
	$author_name = um_user( 'display_name' );
	$photo = um_get_avatar_url( get_avatar( $user_id, 40 ) );
	$group_name = ucwords( get_the_title( $group_id ) );
	$group_url = get_the_permalink( $group_id );
	$group_url_postid = "$group_url#postid-$post_id";
	$post_url = UM()->Groups()->discussion()->get_permalink( $post_id );

	foreach ( $members as $member_id ) {

		$member_data = get_userdata( (int) $member_id );
		$member_name = $member_data->display_name;
		$member_address = $member_data->user_email;

		$vars = compact( 'author_name', 'photo', 'group_name', 'group_url', 'group_url_postid', 'post_url', 'member_name', 'member_address' );

		$vars['notification_uri'] = $group_url_postid;

		UM()->Notifications_API()->api()->store_notification( $member_id, $key, $vars );
	}

	um_reset_user();
}
add_action( 'um_groups_after_wall_post_published', 'um_groups_notify_new_post', 55, 3 );


/**
 * Real-time notification to group members when someone comments on group
 *
 * @version 2.2.5
 *
 * @global  wpdb  $wpdb
 * @param   int   $commentid
 * @param   int   $comment_parent
 * @param   int   $post_id
 * @param   int   $user_id
 */
function um_groups_notify_new_comment( $commentid, $comment_parent, $post_id, $user_id ) {
	$key = 'groups_new_comment';

	if ( !class_exists( 'UM_Notifications_API' ) ) {
		return;
	}
	if ( !UM()->options()->get( "log_$key" ) ) {
		return;
	}

	global $wpdb;
	$table_name = UM()->Groups()->setup()->db_groups_table;
	$group_id = get_post_meta( $post_id, '_group_id', true );
	$members = $wpdb->get_col( "SELECT `user_id1` FROM $table_name WHERE `group_id` = $group_id AND `status` = 'approved'" );

	foreach ( $members as $i => $member_id ) {
		if ( $user_id == $member_id ) {
			unset( $members[$i] );
			continue;
		}
		$prefs = get_user_meta( $user_id, '_notifications_prefs', true );
		if ( isset( $prefs[$key] ) && !$prefs[$key] ) {
			unset( $members[$i] );
			continue;
		}
	}
	if ( empty( $members ) ) {
		return;
	}

	um_fetch_user( $user_id );
	$author_name = um_user( 'display_name' );
	$photo = um_get_avatar_url( get_avatar( $user_id, 40 ) );
	$group_name = ucwords( get_the_title( $group_id ) );
	$group_url = get_the_permalink( $group_id );
	$group_url_postid = "$group_url#postid-$post_id";
	$group_url_commentid = "$group_url#commentid-$commentid";
	$post_url = UM()->Groups()->discussion()->get_permalink( $post_id );
	$comment_url = UM()->Groups()->discussion()->get_comment_link( $post_url, $commentid );

	foreach ( $members as $member_id ) {

		$member_data = get_userdata( (int) $member_id );
		$member_name = $member_data->display_name;
		$member_address = $member_data->user_email;

		$vars = compact( 'author_name', 'photo', 'group_name', 'group_url', 'group_url_postid', 'group_url_commentid', 'post_url', 'comment_url', 'member_name', 'member_address' );

		$vars['notification_uri'] = $group_url_commentid;

		UM()->Notifications_API()->api()->store_notification( $member_id, $key, $vars );
	}

	um_reset_user();
}
add_action( 'um_groups_after_wall_comment_published', 'um_groups_notify_new_comment', 55, 4 );