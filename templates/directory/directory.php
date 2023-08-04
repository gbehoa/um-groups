<?php
/**
 * Template for the UM Groups list
 * Used on the "Groups" page, and "Profile" page "Groups" tab
 * Called from the um_groups_directory() function
 *
 * @version 2.3.1
 *
 * This template can be overridden by copying it to yourtheme/ultimate-member/um-groups/directory/directory.php
 */
if ( ! defined( 'ABSPATH' ) ) exit; ?>

<div class="um-groups-directory">

	<?php
	foreach ( $groups as $group ) {
		$count = um_groups_get_member_count( $group->ID );
		$posts_count = UM()->Groups()->discussion()->get_posts_number( $group->ID );

		if ( 'small' == $args['avatar_size'] ) {
			$image = UM()->Groups()->api()->get_group_image( $group->ID, 'default', 50, 50 );
		} else {
			$image = UM()->Groups()->api()->get_group_image( $group->ID, 'default', 100, 100 );
		}

		// author
		if ( ! empty( $args['show_author'] ) ) {
			$author = get_userdata( $group->post_author );
		}

		// categories
		if ( ! empty( $args['show_search_categories'] ) ) {
			$group_categories = wp_get_object_terms( $group->ID, 'um_group_categories' );
			if ( is_array( $group_categories ) ) {
				$group_categories_name = array_map( function( $term ) {
					return $term->name;
				}, $group_categories );
			}
		}

		// tags
		if ( ! empty( $args['show_search_tags'] ) ) {
			$group_tags = wp_get_object_terms( $group->ID, 'um_group_tags' );
			if ( is_array( $group_tags ) ) {
				$group_tags_name = array_map( function( $term ) {
					return $term->name;
				}, $group_tags );
			}
		}

		if ( version_compare( get_bloginfo( 'version' ),'5.4', '<' ) ) {
			$description = do_shortcode( $group->post_content );
		} else {
			$description = apply_shortcodes( $group->post_content );
		}
		?>

		<div class="um-group-item">

			<?php if ( true == $args['show_actions'] ) { ?>
				<div class="actions">
					<ul>
						<?php if ( ! isset( $user_id ) ) {
							$user_id = false;
						} ?>
						<li><?php do_action( 'um_groups_join_button', $group->ID, $user_id ); ?></li>
						<li class="last-active"><?php echo esc_html( __( 'Last active: ', 'um-groups' ) . human_time_diff( UM()->Groups()->api()->get_group_last_activity( $group->ID, true ), current_time( 'timestamp' ) ) . __( ' ago', 'um-groups' ) ); ?></li>
						<li class="count-members"><?php echo sprintf( _n( '<span>%s</span> member', '<span>%s</span> members', $count, 'um-groups' ), number_format_i18n( $count ) ); ?></li>
						<li class="count-posts"><?php echo sprintf( _n( '<span>%s</span> post', '<span>%s</span> posts', $posts_count, 'um-groups' ), number_format_i18n( $posts_count ) ); ?></li>
					</ul>
				</div>
			<?php } ?>

			<a href="<?php echo esc_url( get_permalink( $group->ID ) ); ?>">
				<?php echo $image; ?>
				<div class="um-group-name"><strong><?php echo esc_html( $group->post_title ); ?></strong></div>
			</a>

			<div class="um-group-meta">
				<ul>
					<li class="privacy" title="<?php esc_attr_e( 'Privacy', 'um-groups' ) ?>">
						<?php echo um_groups_get_privacy_icon( $group->ID ); ?>
						<?php printf( __( '%s Group', 'um-groups' ), um_groups_get_privacy_title( $group->ID ) ); ?>
					</li>

					<?php if ( ! empty( $author ) ) { ?>
						<?php um_fetch_user( $author->ID ); ?>
						<li class="user" title="<?php esc_attr_e( 'Created by', 'um-groups' ) ?>">
							<i class="um-faicon-user"></i>
							<a href="<?php echo esc_url( um_user_profile_url( $author->ID ) ); ?>"><?php echo um_user( 'display_name', 'html' ); ?></a>
						</li>
						<?php um_reset_user(); ?>
					<?php } ?>

					<?php if ( ! empty( $group_categories_name ) ) { ?>
						<li class="categories" title="<?php esc_attr_e( 'Category', 'um-groups' ) ?>">
							<i class="um-faicon-folder"></i>
							<?php echo esc_html( implode( ', ', $group_categories_name ) ); ?>
						</li>
					<?php } ?>

					<?php if ( ! empty( $group_tags_name ) ) { ?>
						<li class="tags" title="<?php esc_attr_e( 'Tags', 'um-groups' ) ?>">
							<i class="um-faicon-tags"></i>
							<?php echo esc_html( implode( ', ', $group_tags_name ) ); ?>
						</li>
					<?php } ?>

					<?php if ( ! empty( $description ) ) { ?>
						<li class="description"><?php echo $description; ?></li>
					<?php } ?>

				</ul>
			</div>
		</div>

	<?php } ?>

</div>