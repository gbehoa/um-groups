<?php
/**
 * @Template: Single Group page
 *
 * Extension: Ultimate Member - Groups
 * Page: Group
 * Call: UM()->Groups()->shortcode()->single()
 *
 * @version 2.3.1
 *
 * This template can be overridden by copying it to yourtheme/ultimate-member/um-groups/single.php
 */
if( !defined( 'ABSPATH' ) ) {
	exit;
}
global $um_group, $um_group_id;

$group = is_a( $um_group, 'WP_Post' ) ? $um_group : get_post( $um_group_id );
$group->group_id = $um_group_id;
$group->user_id2 = $user_id2;
$groups = array( $group );

global $wpdb;
$table_name = UM()->Groups()->setup()->db_groups_table;
$current_group_role = $wpdb->get_var( $wpdb->prepare(
	"SELECT role
				FROM {$table_name}
				WHERE user_id1 = %d AND
				      group_id = %d",
	get_current_user_id(),
	$um_group_id
) );
?>

<div class="um um-groups-single">

	<div class='um-group-single-header'>

		<?php
		if( in_array( $has_joined, array('pending_member_review') ) ){

			$t_args = compact( 'args', 'groups' );
			UM()->get_template( 'directory/directory_confirm.php', um_groups_plugin, $t_args, true );

		} else {

			$t_args = compact( 'args', 'groups' );
			UM()->get_template( 'directory/directory.php', um_groups_plugin, $t_args, true );

		}
		?>
		<div class='um-clear'></div>
	</div>

	<?php do_action( 'um_groups_before_page_tabs', $um_group_id ); ?>

	<?php if( is_user_logged_in() ): ?>

		<?php do_action( 'um_groups_single_page_tabs', $um_group_id ); ?>

		<?php $current_tab = UM()->Groups()->api()->current_group_tab; ?>
		<?php $sub_tab = UM()->Groups()->api()->current_group_subtab; ?>

		<div class="um-group-tab-content-wrap um-group-tab-content__<?php echo esc_attr( $current_tab ); ?>">
			<?php do_action( "um_groups_single_page_content", $um_group_id, $current_tab, $sub_tab ); ?>
			<?php do_action( "um_groups_single_page_content__{$current_tab}", $um_group_id ); ?>
			<?php do_action( "um_groups_single_page_sub_content__{$current_tab}_{$sub_tab}", $um_group_id ); ?>
		</div>

	<?php
	else:
		echo UM()->Groups()->discussion()->login_to_interact( $um_group_id );
	endif; ?>
</div>
