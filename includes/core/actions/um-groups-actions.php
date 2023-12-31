<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Groups join/leave button
 *
 * @param int $group_id
 */
function um_groups_join_button( $group_id = 0, $user_id = false ) {
	wp_enqueue_script( 'um_groups' );
	wp_enqueue_style( 'um_groups' );

	if ( empty( $user_id ) ) {
		$user_id = get_current_user_id();
	}

	$privacy       = get_post_meta( $group_id, '_um_groups_privacy', true );
	$privacy       = ! empty( $privacy ) ? $privacy : 'public';
	$button_labels = UM()->Groups()->api()->get_groups_button_labels( $privacy );
	if ( empty( $button_labels ) ) {
		return;
	}

	$has_joined = UM()->Groups()->api()->has_joined_group( $user_id, $group_id );
	?>

	<div class="um-groups-single-button">
		<?php
		if ( ! is_user_logged_in() ) {
			if ( 'hidden' !== $privacy ) {
				$login_url = add_query_arg(
					array(
						'redirect_to' => get_the_permalink( $group_id ),
					),
					um_get_core_page( 'login' )
				);
				?>
				<a href="<?php echo esc_attr( $login_url ); ?>" class="um-button um-groups-btn-guest">
					<?php echo isset( $button_labels['join'] ) ? esc_html( $button_labels['join'] ) : esc_html__( 'Join Group', 'um-groups' ); ?>
				</a>
				<?php
			}
		} else {
			switch ( $privacy ) {
				case 'hidden':
					if ( 'approved' === $has_joined ) {
						?>
						<a href="javascript:void(0);" class="um-button um-groups-btn-leave um-groups-btn-hidden"
						   data-groups-button-hover="<?php echo esc_attr( $button_labels['hover'] ); ?>"
						   data-groups-button-default="<?php echo esc_attr( $button_labels['leave'] ); ?>"
						   data-group_id="<?php echo esc_attr( $group_id ); ?>">
							<?php echo esc_html( $button_labels['leave'] ); ?>
						</a>
						<?php
					}
					break;
				case 'private':
					if ( 'approved' === $has_joined ) {
						?>
						<a href="javascript:void(0);" class="um-button um-groups-btn-leave"
						   data-groups-button-default="<?php echo esc_attr( $button_labels['_leave'] ); ?>"
						   data-group_id="<?php echo esc_attr( $group_id ); ?>">
							<?php echo esc_html( $button_labels['_leave'] ); ?>
						</a>
						<?php
					} elseif ( 'pending_admin_review' === $has_joined ) {
						?>
						<a href="javascript:void(0);" class="um-button um-groups-btn-leave"
						   data-groups-button-hover="<?php echo esc_attr( $button_labels['hover'] ); ?>"
						   data-groups-button-default="<?php echo esc_attr( $button_labels['leave'] ); ?>"
						   data-group_id="<?php echo esc_attr( $group_id ); ?>">
							<?php echo esc_html( $button_labels['leave'] ); ?>
						</a>
						<?php
					} elseif ( in_array( $has_joined, array( 'rejected', false ), true ) ) {
						?>
						<a href="javascript:void(0);" class="um-button um-groups-btn-join"
						   data-group_id="<?php echo esc_attr( $group_id ); ?>">
							<?php echo esc_html( $button_labels['join'] ); ?>
						</a>
						<?php
					}
					break;
				case 'public_role':
					$group_roles = get_post_meta( $group_id, '_um_groups_privacy_roles', true );
					$can_join    = false;
					if ( empty( $group_roles ) ) {
						$can_join = true;
					} else {
						if ( is_array( $group_roles ) ) {
							foreach ( $group_roles as $group_role ) {
								if ( user_can( $user_id, $group_role ) ) {
									$can_join = true;
									break;
								}
							}
						}
					}

					if ( 'approved' === $has_joined ) {
						?>
						<a href="javascript:void(0);" class="um-button um-groups-btn-leave"
						   data-groups-button-default="<?php echo esc_attr( $button_labels['_leave'] ); ?>"
						   data-group_id="<?php echo esc_attr( $group_id ); ?>">
							<?php echo esc_html( $button_labels['_leave'] ); ?>
						</a>
						<?php
					} elseif ( 'pending_admin_review' === $has_joined ) {
						?>
						<a href="javascript:void(0);" class="um-button um-groups-btn-leave"
						   data-groups-button-hover="<?php echo esc_attr( $button_labels['hover'] ); ?>"
						   data-groups-button-default="<?php echo esc_attr( $button_labels['leave'] ); ?>"
						   data-group_id="<?php echo esc_attr( $group_id ); ?>">
							<?php echo esc_html( $button_labels['leave'] ); ?>
						</a>
						<?php
					} elseif ( $can_join && in_array( $has_joined, array( 'rejected', false ), true ) ) {
						?>
						<a href="javascript:void(0);" class="um-button um-groups-btn-join"
						   data-group_id="<?php echo esc_attr( $group_id ); ?>">
							<?php echo esc_html( $button_labels['join'] ); ?>
						</a>
						<?php
					}
					break;
				case 'public':
				default:
					if ( 'approved' === $has_joined ) {
						?>
						<a href="javascript:void(0);" class="um-button um-groups-btn-leave"
						   data-groups-button-hover="<?php echo esc_attr( $button_labels['hover'] ); ?>"
						   data-groups-button-default="<?php echo esc_attr( $button_labels['leave'] ); ?>"
						   data-group_id="<?php echo esc_attr( $group_id ); ?>">
							<?php echo esc_html( $button_labels['leave'] ); ?>
						</a>
						<?php
					} elseif ( empty( $has_joined ) ) {
						?>
						<a href="javascript:void(0);" class="um-button um-groups-btn-join"
						   data-group_id="<?php echo esc_attr( $group_id ); ?>">
							<?php echo esc_html( $button_labels['join'] ); ?>
						</a>
						<?php
					}
					break;
			}
		}
		?>
	</div>
	<?php
}
add_action( 'um_groups_join_button', 'um_groups_join_button', 10, 2 );


/**
 * Groups form error handler
 *
 * @param $arr_posts
 */
function um_groups_publisher_errors_hook( $arr_posts ) {

	UM()->form()->post_form = $_POST;

	$nonce = sanitize_key( $_REQUEST['_wpnonce'] );
	if ( ! wp_verify_nonce( $nonce, 'um-groups-nonce_'.get_current_user_id()  ) ) {

		wp_die('Invalid Nonce.', 'um-groups');

	} else {

		if( isset( $arr_posts['group_name'] ) && ! empty( $arr_posts['group_name'] ) ){
			UM()->Groups()->api()->single_group_title = $arr_posts['group_name'];
		}

		if( isset( $arr_posts['group_name'] ) && empty( $arr_posts['group_name']  ) ){
			UM()->form()->add_error('group_name', __('You must enter a group name','um-groups') );
		}

		if( isset( $arr_posts['group_name'] ) && ! empty( $arr_posts['group_name']  ) && strlen( $arr_posts['group_name'] ) < 3 ){
			UM()->form()->add_error('group_name', __('Minimum of 3 characters are allowed.','um-groups') );
		}

		if( isset( $arr_posts['group_description'] ) && empty( $arr_posts['group_description']  ) ){
			UM()->form()->add_error('group_description', __('You must enter a description','um-groups') );
		}

		if( isset( $arr_posts['categories'] ) && empty( $arr_posts['categories']  ) ){
			UM()->form()->add_error('categories', __('You must select a category','um-groups') );
		}

		if( isset( $arr_posts['group_tags'] ) && empty( $arr_posts['group_tags']  ) ){
			UM()->form()->add_error('group_tags', __('You must select a tag','um-groups') );
		}
	}
}
add_action( 'um_groups_publisher_errors_hook', 'um_groups_publisher_errors_hook' );


/**
 * Groups form upload error handler
 *
 * @param $post
 */
function um_groups_upload_file_errors_hook( $post ) {

	UM()->form()->post_form = $_POST;
	$arr_file = $_FILES;

	$nonce = sanitize_key( $_REQUEST['_wpnonce'] );
	if ( ! wp_verify_nonce( $nonce, 'um-groups-nonce_upload_'.get_current_user_id()  ) ) {

		wp_die( __('Invalid Nonce.','um-groups') );

	} else {

		if( ! UM()->Groups()->api()->can_manage_group( get_the_ID() ) && ! um_groups_admin_all_access() ){
			wp_die( __('You don\'t have a permission to change something in this group!','um-groups') );
		}

		if( isset( $arr_file['um_groups_avatar']['error'] ) && $arr_file['um_groups_avatar']['error'] > 0 ){
			UM()->form()->add_error('um_groups_avatar', __('You must select an image file','um-groups') );
		}

	}

}
add_action( 'um_groups_upload_file_errors_hook', 'um_groups_upload_file_errors_hook' );


/**
 * Groups form delete file error handler
 *
 * @param $post
 */
function um_groups_delete_file_errors_hook( $post ) {

	UM()->form()->post_form = $_POST;
	$arr_file = $_FILES;

	$nonce = sanitize_key( $_REQUEST['_wpnonce'] );
	if ( ! wp_verify_nonce( $nonce, 'um-groups-nonce_upload_'.get_current_user_id()  ) ) {

		wp_die( __('Invalid Nonce.','um-groups') );

	} else {

		if( ! UM()->Groups()->api()->can_manage_group( get_the_ID() ) && ! um_groups_admin_all_access() ){
			wp_die( __('You don\'t have a permission to change something in this group!','um-groups') );
		}

	}

}
add_action( 'um_groups_delete_file_errors_hook', 'um_groups_delete_file_errors_hook' );


/**
 * Groups form delete group error handler
 *
 * @param $post
 */
function um_groups_delete_group_errors_hook( $post ) {

	UM()->form()->post_form = $_POST;
	$arr_file = $_FILES;

	$nonce = sanitize_key( $_REQUEST['_wpnonce'] );
	if ( ! wp_verify_nonce( $nonce, 'um-groups-nonce_delete_group_'.get_current_user_id()  ) ) {

		wp_die( __('Invalid Nonce.','um-groups') );

	} else {

		if( ! UM()->Groups()->api()->can_manage_group( get_the_ID() ) && ! um_groups_admin_all_access() ){
			wp_die( __('You don\'t have a permission to change something in this group!','um-groups') );
		}

	}

}
add_action('um_groups_delete_group_errors_hook','um_groups_delete_group_errors_hook');


/**
 * Groups delete group process
 *
 * @param $arr_posts
 */
function um_groups_delete_group_process_form( $arr_posts ) {
	$group_id = get_the_ID();

	if( ! UM()->Groups()->api()->can_manage_group( $group_id ) && ! um_groups_admin_all_access() ){
		wp_die( __('You don\'t have a permission to change something in this group!','um-groups') );
	}

	if (  isset( UM()->form()->errors ) ) {
		UM()->Groups()->form_process()->form_process_successful = false;
		return;
	}


	UM()->Groups()->form_process()->form_process_successful = true;

	$has_deleted = UM()->Groups()->api()->delete_group_members( $group_id );

	if ( $has_deleted ) {
		$redirect_url = add_query_arg( 'um_group_deleted', true, um_get_core_page('groups') );

		$attachment_id = get_post_thumbnail_id( $group_id );

		wp_delete_attachment( $attachment_id, true );
		wp_delete_post( $group_id );

		wp_safe_redirect( $redirect_url ); exit;
	} else {
		UM()->form()->add_error('um_groups_avatar', __('Something went wrong.','um-groups') );
	}
}
add_action( 'um_groups_delete_group_process_form', 'um_groups_delete_group_process_form' );


/**
 * Groups delete file process
 *
 * @param $arr_posts
 */
function um_groups_delete_file_process_form( $arr_posts ) {
	$group_id = get_the_ID();

	if( ! UM()->Groups()->api()->can_manage_group( $group_id ) && ! um_groups_admin_all_access() ){
		wp_die( __('You don\'t have a permission to change something in this group!','um-groups') );
	}

	if (  isset( UM()->form()->errors ) ) {
		UM()->Groups()->form_process()->form_process_successful = false;
		return;
	}

	if( has_post_thumbnail() ){

		UM()->Groups()->form_process()->form_process_successful = true;

		$attachment_id = get_post_thumbnail_id( $group_id );

		delete_post_thumbnail( $group_id, $attachment_id );
		wp_delete_attachment( $attachment_id, true );

		$redirect_url = add_query_arg( 'updated', true, $arr_posts['_wp_http_referer'] );

		wp_safe_redirect( $redirect_url ); exit;

	}
}
add_action( 'um_groups_delete_file_process_form', 'um_groups_delete_file_process_form' );


/**
 * Groups upload file process
 *
 * @param $arr_posts
 */
function um_groups_upload_file_process_form( $arr_posts ) {
	$group_id = get_the_ID();

	if( ! UM()->Groups()->api()->can_manage_group( $group_id ) && ! um_groups_admin_all_access() ){
		wp_die( __('You don\'t have a permission to change something in this group!','um-groups') );
	}

	if (  isset( UM()->form()->errors ) ) {
		UM()->Groups()->form_process()->form_process_successful = false;
		return;
	}

	if( ! empty( $_FILES ) ) {
	  foreach( $_FILES as $file ) {
		if( is_array( $file ) ) {

				// Delete existing group image
				$thumbnail_id = get_post_thumbnail_id( $group_id );
				if ( $thumbnail_id ) {
					delete_post_thumbnail( $group_id, $thumbnail_id );
					wp_delete_attachment( $thumbnail_id, true );
				}

				// Upload new group image
		  $attachment_id = um_groups_upload_user_file( $file );
				if( $attachment_id ){
					set_post_thumbnail( $group_id, $attachment_id );
					UM()->Groups()->form_process()->form_process_successful = true;
					$redirect_url = add_query_arg( 'updated', true, $arr_posts['_wp_http_referer'] );
					wp_safe_redirect( $redirect_url ); exit;
				}

		}
	  }
	}
}
add_action( 'um_groups_upload_file_process_form', 'um_groups_upload_file_process_form' );


/**
 * Groups updater process form
 *
 * @param $arr_posts
 */
function um_groups_updater_process_form( $arr_posts ) {

	if ( isset( UM()->form()->errors ) ) {
		UM()->Groups()->form_process()->form_process_successful = false;
		return;
	}

	$group_id = get_the_ID();

	if ( !UM()->Groups()->api()->can_manage_group( $group_id ) && !um_groups_admin_all_access() ) {
		wp_die( __( 'You don\'t have a permission to change something in this group!', 'um-groups' ) );
	}

	$formdata = apply_filters( 'um_groups_process_form_posts', array_merge( array(
		'group_name'				 => '',
		'group_description'	 => '',
		'group_privacy'			 => 'public',
		'invites_settings'	 => 0,
		'can_invite_members' => 0,
		'post_moderations'	 => 'auto-published',
		'categories'				 => array(),
		'group_tags'				 => array()
			), $arr_posts ) );

	do_action( 'um_groups_before_front_update', $formdata );

	wp_update_post( array(
		'ID'						 => $group_id,
		'post_type'			 => 'um_groups',
		'post_title'		 => $formdata[ 'group_name' ],
		'post_content'	 => $formdata[ 'group_description' ],
		'post_status'		 => 'publish',
		'comment_status' => 'closed',
		'ping_status'		 => 'closed',
	) );


	// insert post meta
	update_post_meta( $group_id, '_um_groups_privacy', $formdata[ 'group_privacy' ] );
	update_post_meta( $group_id, '_um_groups_invites_settings', $formdata[ 'invites_settings' ] );
	update_post_meta( $group_id, '_um_groups_can_invite', $formdata[ 'can_invite_members' ] );
	update_post_meta( $group_id, '_um_groups_posts_moderation', $formdata[ 'post_moderations' ] );

	wp_set_object_terms( $group_id, $formdata[ 'categories' ], 'um_group_categories', false );
	wp_set_object_terms( $group_id, $formdata[ 'group_tags' ], 'um_group_tags', false );

	// avatar
	if ( isset( $_FILES ) && isset( $_FILES['um_groups_avatar'] ) && is_array( $_FILES['um_groups_avatar'] ) && $_FILES['um_groups_avatar']['size'] != 0 ) {
		// Delete existing group image
		$thumbnail_id = get_post_thumbnail_id( $group_id );
		if ( $thumbnail_id ) {
			delete_post_thumbnail( $group_id, $thumbnail_id );
			wp_delete_attachment( $thumbnail_id, true );
		}

		// Upload new group image
		$attachment_id = um_groups_upload_user_file( $_FILES['um_groups_avatar'] );
		if ( $attachment_id ) {
			set_post_thumbnail( $group_id, $attachment_id );
		}
	}

	UM()->Groups()->form_process()->form_process_successful = true;

	do_action( 'um_groups_after_front_update', $formdata, $group_id );

	$redirect_url = add_query_arg( 'updated', true, $formdata[ '_wp_http_referer' ] );
	wp_safe_redirect( $redirect_url );
	exit;
}
add_action( 'um_groups_updater_process_form', 'um_groups_updater_process_form' );


/**
 * Form process handler
 *
 * @param $arr_posts
 */
function um_groups_publisher_process_form( $arr_posts ) {

	if ( isset( UM()->form()->errors ) ) {
		UM()->Groups()->form_process()->form_process_successful = false;
		return;
	}

	$formdata = apply_filters( 'um_groups_process_form_posts', array_merge( array(
		'group_name'				 => '',
		'group_description'	 => '',
		'group_privacy'			 => 'public',
		'invites_settings'	 => 0,
		'can_invite_members' => 0,
		'post_moderations'	 => 'auto-published',
		'categories'				 => array(),
		'group_tags'				 => array()
			), $arr_posts ) );

	do_action( 'um_groups_before_front_insert', $formdata );

	$group_id = wp_insert_post(
		array(
			'post_type'      => 'um_groups',
			'post_title'     => $formdata['group_name'],
			'post_content'   => $formdata['group_description'],
			'post_status'    => 'publish',
			'comment_status' => 'closed',
			'ping_status'    => 'closed',
			'post_author'    => get_current_user_id(),
		)
	);

	if ( $group_id ) {
		// insert post meta
		add_post_meta( $group_id, '_um_groups_privacy', $formdata[ 'group_privacy' ] );
		add_post_meta( $group_id, '_um_groups_invites_settings', $formdata[ 'invites_settings' ] );
		add_post_meta( $group_id, '_um_groups_can_invite', $formdata[ 'can_invite_members' ] );
		add_post_meta( $group_id, '_um_groups_posts_moderation', $formdata[ 'post_moderations' ] );

		wp_set_object_terms( $group_id, $formdata[ 'categories' ], 'um_group_categories', false );
		wp_set_object_terms( $group_id, $formdata[ 'group_tags' ], 'um_group_tags', false );

		// avatar
		if ( isset( $_FILES ) && isset( $_FILES['um_groups_avatar'] ) && is_array( $_FILES['um_groups_avatar'] ) ) {
			$attachment_id = um_groups_upload_user_file( $_FILES['um_groups_avatar'] );
			if ( $attachment_id ) {
				set_post_thumbnail( $group_id, $attachment_id );
			}
		}

		UM()->Groups()->form_process()->form_process_successful = true;
	}

	do_action( 'um_groups_after_front_insert', $formdata, $group_id );

	wp_safe_redirect( get_permalink( $group_id ) );
	exit;
}
add_action( 'um_groups_publisher_process_form', 'um_groups_publisher_process_form' );


/**
 * Add self/author to own group on front-end creation
 *
 * @param $arr_posts
 * @param $group_id
 */
function um_groups_add_self_to_own_group( $arr_posts, $group_id ){
	$user_id = get_current_user_id();
	$new_group = true;
	UM()->Groups()->api()->join_group( $user_id, $user_id, $group_id, 'admin', $new_group);
}
add_action( 'um_groups_after_front_insert', 'um_groups_add_self_to_own_group', 10, 2 );


/**
 * Add self/author to own group on back-end creation
 *
 * @param \WP_Post $arr_posts
 * @param int $group_id
 * @param bool $update
 */
function um_groups_add_self_to_own_group_backend( $arr_posts, $group_id, $update ) {
	if ( is_admin() && ( $arr_posts->post_status == 'draft' || $arr_posts->post_status == 'auto-draft' ) ) {
		$user_id = get_current_user_id();
		UM()->Groups()->api()->join_group( $user_id, $user_id, $group_id, 'admin', true );
	}
}
add_action( 'um_groups_after_backend_insert', 'um_groups_add_self_to_own_group_backend', 99999, 3 );


/**
 * Groups form header notices
 *
 * @param $arr_settings
 */
function um_groups_form_notice( $arr_settings ) {
	$updated = get_query_var('updated');

	if ( $updated == 1 ) {
		echo '<p class="um-notice success"><i class="um-icon-ios-close-empty" onclick="jQuery(this).parent().fadeOut();"></i>'.__('Group was updated successfully.','um-groups').'</p>';
	}
}
add_action( 'um_groups_create_form_header', 'um_groups_form_notice' );
add_action( 'um_groups_upload_form_header', 'um_groups_form_notice' );


/**
 * Get Members pre user query
 *
 * @param $uqi
 */
function um_groups_get_members_pre_user_query( $uqi ) {
	global $wpdb;

	if ( isset( $uqi->query_vars['um_groups_get_members'] ) ) {
		$group_id = $uqi->query_vars['um_group_id'];
		$groups_table_name = UM()->Groups()->setup()->db_groups_table;

		$group_meta = $wpdb->prepare("
				{$wpdb->users}.ID NOT IN(
					SELECT DISTINCT tbg.user_id1 FROM {$groups_table_name} as tbg
					WHERE tbg.user_id1 = {$wpdb->users}.ID AND tbg.group_id = %d AND tbg.role NOT IN('approved','blocked','rejected') GROUP BY tbg.user_id1
				)", $group_id );

		$uqi->query_where = str_replace(
				'WHERE 1=1 AND (',
				"WHERE 1=1 AND (" . $group_meta . " AND ",
				$uqi->query_where );
	}

}
add_action( 'pre_user_query', 'um_groups_get_members_pre_user_query' );


/**
 * Delete member on user delete
 *
 * @param $user_id
 */
function um_groups_delete_user( $user_id ) {
	global $wpdb;
	$table_name = UM()->Groups()->setup()->db_groups_table;

	$wpdb->delete(
		$table_name,
		array(
			'user_id1' => $user_id,
		),
		array(
			'%d',
		)
	);
}
add_action( 'delete_user', 'um_groups_delete_user' );

/**
 * Assign user to the groups after registration.
 *
 * @param int   $user_id
 * @param array $submitted_data
 * @param array $form_data
 */
function um_groups_assign_to_group( $user_id, $submitted_data, $form_data ) {
	if ( empty( $form_data['enable_groups_assign'] ) ) {
		return;
	}

	if ( empty( $form_data['groups_assign'] ) ) {
		return;
	}

	$group_ids = maybe_unserialize( $form_data['groups_assign'] );
	if ( empty( $group_ids ) ) {
		return;
	}

	foreach ( $group_ids as $group_id ) {
		UM()->Groups()->api()->join_group( $user_id, null, $group_id );
	}
}
add_action( 'um_registration_set_extra_data', 'um_groups_assign_to_group', 10, 3 );
