<?php
/**
 * Template for the "Groups - Approve Member Email".
 * Whether to send the user an email when user is approved to a group.
 *
 * This template can be overridden by copying it to {your-theme}/ultimate-member/email/groups_approve_member.php
 *
 * @version 2.3.1
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
} ?>

Your request to join to group "{group_name}" has been approved.<br /><br />
To view a group, please click the following link: <a href="{group_url}" style="color: #3ba1da;text-decoration: none;">{group_url}</a>
