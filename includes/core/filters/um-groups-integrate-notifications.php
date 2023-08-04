<?php if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Add UM: Notifications logs
 * @param array $logs
 *
 * @return array
 */
function um_groups_notifications_core_log_types( $logs ) {

	$logs['groups_approve_member'] = array(
		'title'         => __( 'Groups - Approve Member', 'um-groups' ),
		'account_desc'  => __( 'When my group requests have been approved', 'um-groups' ),
	);

	$logs['groups_join_request'] = array(
		'title'         => __( 'Groups - Join Request', 'um-groups' ),
		'account_desc'  => __( 'When a user requested to join their group', 'um-groups' ),
	);

	$logs['groups_invite_member'] = array(
		'title'         => __( 'Groups - Invite Member', 'um-groups' ),
		'account_desc'  => __( 'When a member has invited to join a group', 'um-groups' ),
	);

	$logs['groups_change_role'] = array(
		'title'         => __( 'Groups - Change Group Role', 'um-groups' ),
		'account_desc'  => __( 'When my group roles have been changed', 'um-groups' ),
	);

	$logs['groups_new_post'] = [
		'title'         => __( 'Groups - New post', 'um-groups' ),
		'account_desc'  => __( 'When someone posts to a group', 'um-groups' ),
		'placeholders'  => ['author_name', 'author_photo', 'group_name', 'group_url', 'group_url_postid', 'post_url', 'member_name', 'member_address']
	];

	$logs['groups_new_comment'] = [
		'title'         => __( 'Groups - New comment', 'um-groups' ),
		'account_desc'  => __( 'When someone comments on a post in a group', 'um-groups' ),
		'placeholders'  => ['author_name', 'author_photo', 'group_name', 'group_url', 'group_url_postid', 'group_url_commentid', 'post_url', 'comment_url', 'member_name', 'member_address']
	];

	return $logs;
}
add_filter( 'um_notifications_core_log_types', 'um_groups_notifications_core_log_types', 200, 1 );


/**
 * Add notification icon
 *
 * @param $output
 * @param $type
 *
 * @return string
 */
function um_groups_add_notification_icon( $output, $type ) {
	$log_types_templates = (array) UM()->Groups()->setup()->get_log_types_templates();
	$log_types = (array) array_keys( $log_types_templates );

	if ( in_array( $type, $log_types ) ) {
		$output = '<i class="um-faicon-users" style="color: #3ba1da"></i>';
	}

	return $output;
}
add_filter('um_notifications_get_icon', 'um_groups_add_notification_icon', 10, 2 );