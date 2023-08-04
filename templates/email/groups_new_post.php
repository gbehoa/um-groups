<?php
/**
 * Template for the "Groups - New post".
 * Whether to send the user an email when someone posts on group.
 *
 * This template can be overridden by copying it to {your-theme}/ultimate-member/email/groups_new_post.php
 *
 * @version 2.3.1
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
} ?>

Hi {member_name},<br /><br />
{author_name} has posted new post on "{group_name}".<br /><br />
To view post, please click the following link: <a href="{group_url_postid}" style="color: #3ba1da;text-decoration: none;">{group_url_postid}</a>
