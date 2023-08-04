<?php
namespace um_ext\um_groups\core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Groups_Account
 * @package um_ext\um_groups\core
 */
class Groups_Account {

	/**
	 * Groups_Account constructor.
	 */
	public function __construct() {
		add_action( 'um_post_account_update', array( &$this, 'account_update' ) );

		add_filter( 'um_account_page_default_tabs_hook', array( &$this, 'account_notification_tab' ) );
		add_filter( 'um_account_content_hook_notifications', array( &$this, 'account_tab' ), 51, 2 );
	}

	/**
	 * Update Account action
	 */
	public function account_update() {
		// phpcs:ignore WordPress.Security.NonceVerification -- already verified here
		$current_tab = isset( $_POST['_um_account_tab'] ) ? sanitize_key( $_POST['_um_account_tab'] ) : null;
		if ( 'notifications' !== $current_tab ) {
			return;
		}

		$user_id = um_user( 'ID' );

		// phpcs:ignore WordPress.Security.NonceVerification -- already verified here
		if ( isset( $_POST['um_group_post_notification'] ) ) {
			update_user_meta( $user_id, 'um_group_post_notification', 'yes' );
		} else {
			update_user_meta( $user_id, 'um_group_post_notification', 'no' );
		}

		// phpcs:ignore WordPress.Security.NonceVerification -- already verified here
		if ( isset( $_POST['um_group_comment_notification'] ) ) {
			update_user_meta( $user_id, 'um_group_comment_notification', 'yes' );
		} else {
			update_user_meta( $user_id, 'um_group_comment_notification', 'no' );
		}
	}

	/**
	 * Add Notifications tab to account page
	 *
	 * @param array $tabs
	 * @return array
	 */
	public function account_notification_tab( $tabs ) {
		if ( empty( $tabs[400]['notifications'] ) ) {
			$tabs[400]['notifications'] = array(
				'icon'         => 'um-faicon-envelope',
				'title'        => __( 'Notifications', 'um-groups' ),
				'submit_title' => __( 'Update Notifications', 'um-groups' ),
			);
		}

		return $tabs;
	}


	/**
	 * Add settings 'Groups' to the Account page, Notifications tab
	 *
	 * @version 2.2.2
	 *
	 * @param   string $output
	 * @param   array  $shortcode_args
	 * @return  string
	 */
	public function account_tab( $output, $shortcode_args ) {
		$show_post_notification    = UM()->options()->get( 'groups_new_post_on' ) || ( isset( $shortcode_args['um_group_post_notification'] ) && $shortcode_args['um_group_post_notification'] );
		$show_comment_notification = UM()->options()->get( 'groups_new_comment_on' ) || ( isset( $shortcode_args['um_group_comment_notification'] ) && $shortcode_args['um_group_comment_notification'] );

		if ( ! $show_post_notification && ! $show_comment_notification ) {
			return $output;
		}

		$post_notification    = UM()->Groups()->api()->enabled_email( get_current_user_id(), 'um_group_post_notification' );
		$comment_notification = UM()->Groups()->api()->enabled_email( get_current_user_id(), 'um_group_comment_notification' );

		$t_args = compact( 'post_notification', 'comment_notification', 'show_post_notification', 'show_comment_notification' );

		$output .= UM()->get_template( 'account_notifications.php', um_groups_plugin, $t_args );

		return $output;
	}
}
