<?php
/**
 * Template for the UM Groups. The list of user groups
 *
 * Shortcode: [ultimatemember_my_groups]
 * Call: UM()->Groups()->shortcode()->own_groups()
 *
 * @version 2.3.1
 *
 * This template can be overridden by copying it to yourtheme/ultimate-member/um-groups/own.php
 */
if( !defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="um">
	<?php
	do_action( 'um_groups_directory_header', $args );
	do_action( 'um_groups_own_directory_tabs', $args );
	do_action( 'um_groups_directory', $args );
	do_action( 'um_groups_directory_footer', $args );
	?>
</div>
