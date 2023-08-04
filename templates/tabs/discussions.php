<?php

/**
 * Template for the UM Groups, The group "Discussions" tab content
 *
 * Page: "Group", tab "Discussions"
 * Caller: function um_groups_single_page_content__discussion()
 *
 * @version 2.3.1
 *
 * This template can be overridden by copying it to yourtheme/ultimate-member/um-groups/tabs/discussions.php
 */
if( !defined( 'ABSPATH' ) ) {
	exit;
}

if ( version_compare( get_bloginfo('version'),'5.4', '<' ) ) {
	echo do_shortcode( '[ultimatemember_group_discussion_activity group_id="' . $group_id . '"]' );
} else {
	echo apply_shortcodes( '[ultimatemember_group_discussion_activity group_id="' . $group_id . '"]' );
}