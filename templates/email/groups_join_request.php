<?php
/**
 * Template for the "Groups - Join Request Email".
 * Whether to send the group moderators an email when user has requested to join their group.
 *
 * This template can be overridden by copying it to {your-theme}/ultimate-member/email/groups_join_request.php
 *
 * @version 2.3.1
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
} ?>

Hi {moderator_name},<br/><br/>
{member_name} has requested to join {group_name}.<br/>
You can view their profile here: {profile_link}.<br/><br/>
To approve/reject this request please click the following link:<br/>
<a href="{groups_request_tab_url}" style="color: #3ba1da;text-decoration: none;">{groups_request_tab_url}</a>
