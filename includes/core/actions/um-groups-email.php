<?php if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Email notification for approved group membership
 * @param integer $user_id
 * @param integer $group_id
 */
function um_groups_after_member_changed_status__approved( $user_id, $group_id ) {
	um_fetch_user( $user_id );

	$member_address = um_user( 'user_email' );
	$group_name = get_the_title( $group_id );
	$group_url = get_the_permalink( $group_id );

	UM()->mail()->send(
		$member_address,
		'groups_approve_member',
		array(
			'plain_text'    => 1,
			'path'          => um_groups_path . 'templates/email/',
			'tags'          => array(
				'{group_name}',
				'{group_url}',
			),
			'tags_replace'  => array(
				$group_name,
				$group_url,
			)
		)
	);

	um_reset_user();
}
add_action( 'um_groups_after_member_changed_status__approved', 'um_groups_after_member_changed_status__approved', 10, 2 );


/**
 * Email notification for join request to a group
 *
 * @param integer $user_id
 * @param integer $group_id
 */
function um_groups_after_member_changed_status__pending_admin_review( $user_id, $group_id ) {
	$option = UM()->options()->get( 'groups_join_request_on' );
	if ( ! $option ) {
		return;
	}

	$group_name = get_the_title( $group_id );
	$group_url = get_the_permalink( $group_id );
	$groups_request_tab_url = add_query_arg( 'tab', 'requests', $group_url );
	$moderators = UM()->Groups()->member()->get_moderators( $group_id );

	um_fetch_user( $user_id );
	$member_name = um_user( 'display_name' );
	um_reset_user();
	$profile_link = um_user_profile_url( $user_id );

	foreach ( $moderators as $key => $moderator ) {

		// moderator
		$moderator_data = get_userdata( intval( $moderator->uid ) );
		um_fetch_user( $moderator->uid );
		$moderator_name = um_user( 'display_name' );
		um_reset_user();
		$moderator_address = $moderator_data->user_email;

		UM()->mail()->send(
			$moderator_address,
			'groups_join_request',
			array(
				'plain_text'    => 1,
				'path'          => um_groups_path . 'templates/email/',
				'tags'          => array(
					'{moderator_name}',
					'{member_name}',
					'{group_name}',
					'{group_url}',
					'{groups_request_tab_url}',
					'{profile_link}'
				),
				'tags_replace'  => array(
					$moderator_name,
					$member_name,
					$group_name,
					$group_url,
					$groups_request_tab_url,
					$profile_link
				)
			)
		);
	}

	um_reset_user();
}
add_action( 'um_groups_after_member_changed_status__pending_admin_review', 'um_groups_after_member_changed_status__pending_admin_review', 10, 2 );


/**
 * Email notification for join request to a group
 * @param integer $user_id
 * @param integer $group_id
 */
function um_groups_after_member_changed_status__pending_member_review( $user_id, $group_id, $invited_by_user_id = null ) {

	$group = get_post( $group_id );
	$group_name = get_the_title( $group_id );
	$group_url = get_the_permalink( $group_id );
	$privacy = UM()->Groups()->api()->get_privacy_slug( $group_id );
	if ( $privacy == 'hidden' ) {
		$group_url = um_get_core_page( 'groups' ) . '?filter=own';
	}

	$group_invitation_host_id = empty( $invited_by_user_id ) ? $group->post_author : $invited_by_user_id;
	um_fetch_user( $group_invitation_host_id );
	$group_invitation_host_name = um_user( 'display_name' );
	um_reset_user();

	$group_invitation_guest = get_userdata( $user_id );
	um_fetch_user( $user_id );
	$group_invitation_guest_name = um_user( 'display_name' );
	um_reset_user();
	$group_invitation_guest_email = $group_invitation_guest->user_email;

	UM()->mail()->send(
		$group_invitation_guest_email,
		'groups_invite_member',
		array(
			'plain_text'    => 1,
			'path'          => um_groups_path . 'templates/email/',
			'tags'          => array(
				'{group_name}',
				'{group_url}',
				'{group_invitation_guest_name}',
				'{group_invitation_host_name}'
			),
			'tags_replace'  => array(
				$group_name,
				$group_url,
				$group_invitation_guest_name,
				$group_invitation_host_name
			)
		)
	);

}
add_action( 'um_groups_after_member_changed_status__pending_member_review', 'um_groups_after_member_changed_status__pending_member_review', 10, 3 );


/**
 * Email notification to group members when someone posts on group
 *
 * @version 2.2.2
 *
 * @global  wpdb    $wpdb
 * @param   integer $post_id
 * @param   integer $author_id
 * @param   integer $wall_id
 */
function um_groups_send_notification_to_group_members( $post_id, $author_id, $wall_id ) {
	global $wpdb;

	$option = UM()->options()->get( 'groups_new_post_on' );
	if ( ! $option ){
		return;
	}

	$table_name = UM()->Groups()->setup()->db_groups_table;
	$group_id = get_post_meta( $post_id, '_group_id', true );
	$members = $wpdb->get_col( "SELECT user_id1 FROM $table_name WHERE group_id = $group_id AND `status` = 'approved'" );

	foreach ( $members as $i => $member_id ) {
		if ( $author_id == $member_id ) {
			unset( $members[ $i ] );
			continue;
		}
		$post_notification_enabled = UM()->Groups()->api()->enabled_email( $member_id, 'um_group_post_notification' );
		/* variable $post_notification_enabled is integer 0 or 1 */
		if ( ! $post_notification_enabled ) {
			unset( $members[ $i ] );
			continue;
		}
	}
	if ( empty( $members ) ) {
		return;
	}

	um_fetch_user( $author_id );
	$author_name = um_user( 'display_name' );
	$author_photo = um_get_avatar_url( get_avatar( $author_id, 40 ) );
	$group_name = ucwords( get_the_title( $group_id ) );
	$group_url = get_the_permalink( $group_id );
	$group_url_postid = "$group_url#postid-$post_id";
	$post_url = UM()->Groups()->discussion()->get_permalink( $post_id );

	foreach ( $members as $member_id ) {

		$member_data = get_userdata( intval( $member_id ) );
		um_fetch_user( $member_id );
		$member_name = um_user( 'display_name' );
		um_reset_user();
		$member_address = $member_data->user_email;

		// email notification
		UM()->mail()->send( $member_address, 'groups_new_post', array(
			'plain_text'        => 1,
			'path'              => um_groups_path . 'templates/email/',
			'tags'              => array(
				'{group_name}',
				'{group_url}',
				'{group_url_postid}',
				'{post_url}',
				'{author_name}',
				'{author_photo}',
				'{member_name}'
			),
			'tags_replace'      => array(
				$group_name,
				$group_url,
				$group_url_postid,
				$post_url,
				$author_name,
				$author_photo,
				$member_name
			)
		) );
	} // end loop

	um_reset_user();
}
add_action( 'um_groups_after_wall_post_published', 'um_groups_send_notification_to_group_members', 50, 3 );


/**
 * Email notification to group members when someone comments on group
 *
 * @version 2.2.2
 *
 * @global  wpdb    $wpdb
 * @param   integer $commentid
 * @param   integer $comment_parent
 * @param   integer $post_id
 * @param   integer $user_id
 */
function um_groups_after_user_comments( $commentid, $comment_parent, $post_id, $user_id ) {
	global $wpdb;

	$option = UM()->options()->get( 'groups_new_comment_on' );
	if ( ! $option ) {
		return;
	}

	$group_id = get_post_meta( $post_id, '_group_id', true );
	$table_name = UM()->Groups()->setup()->db_groups_table;
	$members = $wpdb->get_col( "SELECT user_id1 FROM $table_name WHERE group_id = $group_id AND `status` = 'approved'" );

	foreach ( $members as $i => $member_id ) {
		if ( $user_id == $member_id ) {
			unset( $members[ $i ] );
			continue;
		}
		$comment_notification_enabled = UM()->Groups()->api()->enabled_email( $member_id, 'um_group_comment_notification' );
		/* variable $comment_notification_enabled is integer 0 or 1 */
		if ( ! $comment_notification_enabled ) {
			unset( $members[ $i ] );
			continue;
		}
	}
	if ( empty( $members ) ) {
		return;
	}

	um_fetch_user( $user_id );
	$author_name = um_user( 'display_name' );
	$author_photo = um_get_avatar_url( get_avatar( $user_id, 40 ) );
	$group_name = ucwords( get_the_title( $group_id ) );
	$group_url = get_the_permalink( $group_id );
	$group_url_postid = "$group_url#postid-$post_id";
	$group_url_commentid = "$group_url#commentid-$commentid";
	$post_url = UM()->Groups()->discussion()->get_permalink( $post_id );
	$comment_url = UM()->Groups()->discussion()->get_comment_link( $post_url, $commentid );

	foreach ( $members as $member_id ) {

		$member_data = get_userdata( intval( $member_id ) );
		um_fetch_user( $member_id );
		$member_name = um_user( 'display_name' );
		um_reset_user();
		$member_address = $member_data->user_email;

		// email notification
		UM()->mail()->send( $member_address, 'groups_new_comment', array(
			'plain_text'        => 1,
			'path'              => um_groups_path . 'templates/email/',
			'tags'              => array(
				'{group_name}',
				'{group_url}',
				'{group_url_postid}',
				'{group_url_commentid}',
				'{post_url}',
				'{comment_url}',
				'{author_name}',
				'{author_photo}',
				'{member_name}'
			),
			'tags_replace'      => array(
				$group_name,
				$group_url,
				$group_url_postid,
				$group_url_commentid,
				$post_url,
				$comment_url,
				$author_name,
				$author_photo,
				$member_name
			)
		) );
	} // end loop

	um_reset_user();
}
add_action( 'um_groups_after_wall_comment_published', 'um_groups_after_user_comments', 10, 4 );


/**
 * Show available placeholders for the email template
 *
 * @since  2.2.2
 *
 * @param  array  $fields
 * @param  string $email_key
 * @return array
 */
function um_groups_settings_email_placeholders( $fields, $email_key = '' ) {

	switch ( $email_key ) {
		case 'groups_approve_member':
			$fields[] = array(
				'id'    => 'um_info_text',
				'type'  => 'info_text',
				'value' => __( 'Placeholders:', 'um-groups' ) .
					' {group_name}'.
					' {group_url}',
				'conditional'   => array( 'groups_approve_member_on', '=', 1 )
			);
			break;

		case 'groups_join_request':
			$fields[] = array(
				'id'    => 'um_info_text',
				'type'  => 'info_text',
				'value' => __( 'Placeholders:', 'um-groups' ) .
					' {moderator_name}'.
					' {member_name}'.
					' {group_name}'.
					' {group_url}'.
					' {groups_request_tab_url}'.
					' {profile_link}',
				'conditional'   => array( 'groups_join_request_on', '=', 1 )
			);
			break;

		case 'groups_invite_member':
			$fields[] = array(
				'id'    => 'um_info_text',
				'type'  => 'info_text',
				'value' => __( 'Placeholders:', 'um-groups' ) .
					' {group_name}'.
					' {group_url}'.
					' {group_invitation_guest_name}'.
					' {group_invitation_host_name}',
				'conditional'   => array( 'groups_invite_member_on', '=', 1 )
			);
			break;

		case 'groups_new_post':
			$fields[] = array(
				'id'    => 'um_info_text',
				'type'  => 'info_text',
				'value' => __( 'Placeholders:', 'um-groups' ) .
					' {group_name}'.
					' {group_url}'.
					' {group_url_postid}'.
					' {post_url}'.
					' {author_name}'.
					' {author_photo}'.
					' {member_name}',
				'conditional'   => array( 'groups_new_post_on', '=', 1 )
			);
			break;

		case 'groups_new_comment':
			$fields[] = array(
				'id'    => 'um_info_text',
				'type'  => 'info_text',
				'value' => __( 'Placeholders:', 'um-groups' ) .
					' {group_name}'.
					' {group_url}'.
					' {group_url_postid}'.
					' {group_url_commentid}'.
					' {post_url}'.
					' {comment_url}'.
					' {author_name}'.
					' {author_photo}'.
					' {member_name}',
				'conditional'   => array( 'groups_new_comment_on', '=', 1 )
			);
			break;

		default:
			break;
	}

	return $fields;
}
add_filter( 'um_admin_settings_email_section_fields', 'um_groups_settings_email_placeholders', 20, 2 );
