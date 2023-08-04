<?php
/**
 * Template for the "Groups - New comment".
 * Whether to send the user an email when someone posts comment on group.
 *
 * This template can be overridden by copying it to {your-theme}/ultimate-member/email/groups_new_comment.php
 *
 * @version 2.3.1
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
} ?>

Hi {member_name},<br /><br />
{author_name} added a new comment on group "{group_name}".<br /><br />
To view comment, please click the following link: <a href="{group_url_commentid}" style="color: #3ba1da;text-decoration: none;">{group_url_commentid}</a>
