<?php
/**
 * Template for the UM Groups. Unconfirmed invites list
 *
 * Called from the um_groups_directory_confirm() function
 *
 * @version 2.3.1
 *
 * This template can be overridden by copying it to yourtheme/ultimate-member/users_invite_list.php.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$user_id = get_current_user_id();
$joined_groups = UM()->Groups()->api()->get_joined_groups( $user_id, 'pending_member_review' );

$groups = array();
foreach( $joined_groups as $data ) {
	$group = get_post( $data->group_id );
	$group->group_id = $data->group_id;
	$group->user_id2 = $data->user_id2;
	$groups[] = $group;
}
?>

<div class="um-groups-directory">

	<?php
	if ( ! empty( $groups ) ) {
		foreach ( $groups as $group ) :

			$image = UM()->Groups()->api()->get_group_image( $group->ID, 'default', 100, 100 );

			// author
			$author = get_userdata( $group->post_author );
			um_fetch_user( $author->ID );
			$author_name = um_user( 'display_name' );

			um_fetch_user( $group->user_id2 );
			$profile_name   = um_user( 'display_name' );
			$profile_avatar = um_user( 'profile_photo', 40 );
			$profile_url    = um_user_profile_url( $group->user_id2 );
			um_fetch_user( $user_id );

			if ( version_compare( get_bloginfo( 'version' ), '5.4', '<' ) ) {
				$description = do_shortcode( $group->post_content );
			} else {
				$description = apply_shortcodes( $group->post_content );
			}
			?>
			<div class="um-group-item">
				<?php if ( $user_id == get_current_user_id() ) { ?>
					<div class="actions">
						<ul>
							<li><a href="<?php echo esc_url( $profile_url ) ?>"
								   class="um-left"><?php echo $profile_avatar; ?></a><?php esc_html_e( 'You\'ve been invited by', 'um-groups' ); ?>
								<br><a href="<?php echo esc_url( $profile_url ); ?>"><?php echo esc_html( $profile_name ); ?></a>
							</li>
							<li><?php echo esc_html( __( 'Would you like to join this group?', 'um-groups' ) ); ?></li>
							<li>
								<div class="um-groups-double-button">
									<a href="javascript:void(0);"
									   data-group_id="<?php echo esc_attr( $group->group_id ); ?>"
									   data-user_id="<?php echo esc_attr( $user_id ); ?>"
									   class="um-button um-groups-confirm-invite"><?php echo esc_html( __( "Confirm", "um-groups" ) ); ?></a>
									<a href="javascript:void(0);"
									   data-group_id="<?php echo esc_attr( $group->group_id ); ?>"
									   data-user_id="<?php echo esc_attr( $user_id ); ?>"
									   class="um-button um-groups-ignore-invite um-alt"><?php echo esc_html( __( "Ignore", "um-groups" ) ); ?></a>
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
								<a href="<?php echo esc_url( um_user_profile_url( $author->ID ) ); ?>"><?php echo $author_name; ?></a>
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
	} else {
		echo esc_html( $empty_text );
	}
	?>

</div>