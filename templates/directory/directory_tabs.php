<?php
/**
 * Template for the UM Groups. Used on the "Groups" page.
 *
 * Caller: function um_groups_directory_tabs()
 * Hook:   um_groups_directory_tabs
 *
 * @version 2.3.1
 *
 * This template can be overridden by copying it to yourtheme/ultimate-member/um-groups/directory/directory_tabs.php.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div id="um-groups-filters" class="um-groups-found-posts">
	<ul class="filters">
		<li class="all <?php echo ( 'all' == $filter || empty( $filter ) ) ? 'active' : ''; ?>">
			<a href="<?php echo esc_attr( um_get_core_page( 'groups' ) ); ?>">
				<?php printf( __( 'All Groups <span>%s</span>', 'um-groups' ), um_groups_get_all_groups_count() ) ?>
			</a>
		</li>

		<?php if ( is_user_logged_in() ) { ?>
			<li class="own <?php echo 'own' == $filter ? 'active' : ''; ?>">
				<a href="<?php echo esc_attr( add_query_arg( [ 'filter' => 'own' ], um_get_core_page( 'groups' ) ) ); ?>">
					<?php printf( __( 'My Groups <span>%s</span>', 'um-groups' ), um_groups_get_own_groups_count() ) ?>
				</a>
			</li>
			<?php if ( ! UM()->roles()->um_user_can( 'group_create_off' ) ) { ?>
				<li class="create">
					<a href="<?php echo esc_attr( um_get_core_page( 'create_group' ) ); ?>">
						<?php _e( 'Create a Group', 'um-groups' ) ?>
					</a>
				</li>
			<?php } ?>
		<?php } ?>
	</ul>
</div>




