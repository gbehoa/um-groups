<?php
/**
 * Template for the UM Groups. The list of groups
 *
 * Page: "Profile"
 * Shortcode: [ultimatemember_groups_profile_list]
 * Call: UM()->Groups()->shortcode()->profile_list()
 *
 * @version 2.3.1
 *
 * This template can be overridden by copying it to yourtheme/ultimate-member/um-groups/groups-list.php
 */
if( !defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="um um-groups-list">
	<?php
	do_action( 'um_groups_directory_search_form', $args );
	do_action( 'um_groups_directory', $args );
	do_action( 'um_groups_directory_footer', $args );
	?>
</div>
