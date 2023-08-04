<?php
/**
 * Template for the UM Groups. Unconfirmed invites list
 *
 * Page "Groups"
 * Page "Profile", tab "Groups"
 * Called from the um_groups_directory_confirm() function
 *
 * @version 2.3.1
 *
 * This template can be overridden by copying it to yourtheme/ultimate-member/directory/directory_confirm.php.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( um_user( 'ID' ) != get_current_user_id() ) {
	return;
}
?>

<div class="um-groups-directory">

	<?php
	foreach ( $groups as $group ) :

		if( 'small' == $args[ 'avatar_size' ] ) {
			$image = UM()->Groups()->api()->get_group_image( $group->ID, 'default', 50, 50 );
		} else {
			$image = UM()->Groups()->api()->get_group_image( $group->ID, 'default', 100, 100 );
		}

		um_fetch_user( $group->user_id2 );
		$profile_name = um_user( 'display_name' );
		$profile_avatar = um_user( 'profile_photo', 40 );
		$profile_url = um_user_profile_url( $group->user_id2 );
		um_fetch_user( $user_id );

		// author
		if( ! empty( $args['show_author'] ) ){
			$author = get_userdata( $group->post_author );
		}

		if ( version_compare( get_bloginfo('version'),'5.4', '<' ) ) {
			$description = do_shortcode( $group->post_content );
		} else {
			$description = apply_shortcodes( $group->post_content );
		}
		?>
		<div class="um-group-item">

			<?php if ( true == $args['show_actions'] ) { ?>
				<div class="actions">
					<ul>
						<li><a href="<?php echo esc_url( $profile_url ) ?>" class="um-left"><?php echo $profile_avatar; ?></a><?php esc_html_e( 'You\'ve been invited by', 'um-groups' ); ?> <br><a href="<?php echo esc_url( $profile_url ); ?>"><?php echo esc_html( $profile_name ); ?></a></li>
						<li><?php echo esc_html( __( 'Would you like to join this group?', 'um-groups' ) ); ?></li>
						<li>
							<div class="um-groups-double-button">
								<a href="javascript:void(0);" data-group_id="<?php echo esc_attr( $group->group_id ); ?>" data-user_id="<?php echo esc_attr( $user_id ); ?>" class="um-button um-groups-confirm-invite" ><?php echo esc_html( __( "Confirm", "um-groups" ) ); ?></a>
								<a href="javascript:void(0);" data-group_id="<?php echo esc_attr( $group->group_id ); ?>" data-user_id="<?php echo esc_attr( $user_id ); ?>" class="um-button um-groups-ignore-invite um-alt" ><?php echo esc_html( __( "Ignore", "um-groups" ) ); ?></a>
							</div>
						</li>
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
						<li class="user" title="<?php esc_attr_e( 'Created by', 'um-groups' ) ?>">
							<i class="um-faicon-user"></i>
							<a href="<?php echo esc_url( um_user_profile_url( $author->ID ) ); ?>"><?php echo esc_html( $author->display_name ); ?></a>
						</li>
					<?php } ?>

					<?php if ( ! empty( $description ) ) { ?>
						<li class="description"><?php echo $description; ?></li>
					<?php } ?>

				</ul>
			</div>
		</div>

	<?php
	endforeach;
	um_reset_user();
	?>

</div>