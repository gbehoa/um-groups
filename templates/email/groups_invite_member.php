<?php
/**
 * Template for the "Groups - Invite Member Email".
 * Whether to send the user an email when user has invited to join a group.
 *
 * This template can be overridden by copying it to {your-theme}/ultimate-member/email/groups_invite_member.php
 *
 * @version 2.3.1
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
} ?>

Hi {group_invitation_guest_name},<br /><br />
{group_invitation_host_name} has invited you to join to group "{group_name}".<br /><br />
To confirm/reject this invitation please click the following link: <br />
<a href="{group_url}" style="color: #3ba1da;text-decoration: none;">{group_url}</a>
