<?php
/**
 * Template for the UM Groups. Groups page
 *
 * Page: "Groups", "My Groups"
 * Shortcode: [ultimatemember_groups]
 * Call: UM()->Groups()->shortcode()->list_shortcode()
 *
 * @version 2.3.1
 *
 * This template can be overridden by copying it to yourtheme/ultimate-member/um-groups/list.php
 */
if( !defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="um">
	<?php
	do_action( 'um_groups_directory_header', $args );
	do_action( 'um_groups_directory_tabs', $args );
	do_action( 'um_groups_directory_search_form', $args );
	do_action( 'um_groups_directory', $args );
	do_action( 'um_groups_directory_footer', $args );
	?>
</div>
