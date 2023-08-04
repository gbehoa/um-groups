<?php
/**
 * Template for the UM Groups. Confirm invite list
 *
 * Shortcode: [ultimatemember_group_invite_list]
 * Caller: Groups_Shortcode->invite_list() method
 *
 * @version 2.3.1
 *
 * This template can be overridden by copying it to yourtheme/ultimate-member/um-groups/invite-list.php
 */
if( !defined( 'ABSPATH' ) ) {
	exit;
}
?>

<!-- um-groups/templates/invite-list.php -->
<div class="um um-groups-invite-list">
	<div class="um-groups-single">
		<div class="um-group-invite-name"><?php echo sprintf( __('%s group\'s invites','um-theme'), get_the_title( $group_id ) );?></div>
		<?php um_groups_single_page_content__invites( $group_id ); ?>
	</div>
</div>