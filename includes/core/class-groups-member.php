<?php
namespace um_ext\um_groups\core;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Class Groups_Member
 * @package um_ext\um_groups\core
 */
class Groups_Member {


	/**
	 * @var
	 */
	var $member;


	/**
	 * Groups_Member constructor.
	 */
	function __construct() {

	}


	/**
	 * Set member's current group
	 * @param integer $group_id
	 */
	function set_group( $group_id, $user_id = null ) {
		global $wpdb;

		if ( ! $user_id ) {
			$user_id = get_current_user_id();
		}

		$table_name = UM()->Groups()->setup()->db_groups_table;

		$this->member = $wpdb->get_row( $wpdb->prepare(
			"SELECT *
			FROM {$table_name}
			WHERE group_id = %d AND
			      user_id1 = %d",
			$group_id,
			$user_id
		) );
	}


	/**
	 * Get member role
	 *
	 * @param null|int $group_id
	 * @param null|int $user_id
	 *
	 * @return string
	 */
	function get_role( $group_id = null, $user_id = null ) {
		if ( isset( $this->member->role ) ) {
			return $this->member->role;
		} elseif ( isset( $group_id ) ) {
			if ( ! $user_id ) {
				$user_id = get_current_user_id();
			}

			global $wpdb;
			$table_name = UM()->Groups()->setup()->db_groups_table;
			$current_group_role = $wpdb->get_var( $wpdb->prepare(
				"SELECT role
				FROM {$table_name}
				WHERE user_id1 = %d AND
				      group_id = %d",
				$user_id,
				$group_id
			) );

			return $current_group_role;
		}

		return '';
	}


	/**
	 * Get member status
	 *
	 * @param null|int $group_id
	 * @param null|int $user_id
	 *
	 * @return string
	 */
	function get_status( $group_id = null, $user_id = null ) {
		if ( isset( $this->member->status ) ) {
			return $this->member->status;
		} elseif ( isset( $group_id ) ) {
			if ( ! $user_id ) {
				$user_id = get_current_user_id();
			}
			global $wpdb;
			$table_name = UM()->Groups()->setup()->db_groups_table;
			$current_group_role = $wpdb->get_var( $wpdb->prepare(
				"SELECT status
				FROM {$table_name}
				WHERE user_id1 = %d AND
				      group_id = %d",
				$user_id,
				$group_id
			) );

			return $current_group_role;
		}

		return '';
	}


	/**
	 * Set member status to approve
	 *
	 */
	function approve() {
		UM()->check_ajax_nonce();

		if ( empty( $_REQUEST['group'] ) || empty( $_REQUEST['user_id'] ) ) {
			wp_send_json_error( __( 'Invalid data', 'um-groups' ) );
		}

		$group_id = UM()->Groups()->members()->get_group_by_hash( sanitize_key( $_REQUEST['group'] ) );
		$user_id = sanitize_key( $_REQUEST['user_id'] );
		$role = 'member';
		$status = 'approved';

		if ( ! is_user_logged_in() || ( ! UM()->Groups()->api()->can_approve_requests( $group_id ) && ! um_groups_admin_all_access() ) ) {
			wp_send_json_error( __( 'Invalid request', 'um-groups' ) );
		}

		global $wpdb;
		$table_name = UM()->Groups()->setup()->db_groups_table;
		$wpdb->update(
			$table_name,
			array(
				'status'    => $status,
				'role'      => $role,
			),
			array(
				'group_id' => $group_id,
				'user_id1' => $user_id,
			),
			array(
				'%s',
				'%s'
			),
			array(
				'%d',
				'%d'
			)
		);

		$this->member = $wpdb->get_row( $wpdb->prepare(
			"SELECT *
			FROM {$table_name}
			WHERE group_id = %d AND
			      user_id1 = %d",
			$group_id,
			$user_id
		) );

		do_action( 'um_groups_after_member_changed_status', $user_id, $group_id, $status );
		do_action( "um_groups_after_member_changed_status__{$status}", $user_id, $group_id, false, $role, false );

		do_action( 'um_groups_after_member_approve', $user_id, $group_id, $status );

		wp_send_json_success();
	}


	/**
	 * Set member status to reject
	 */
	function reject() {
		UM()->check_ajax_nonce();

		if ( empty( $_REQUEST['group'] ) || empty( $_REQUEST['user_id'] ) ) {
			wp_send_json_error( __( 'Invalid data', 'um-groups' ) );
		}

		global $wpdb;

		$group_id = UM()->Groups()->members()->get_group_by_hash( sanitize_key( $_REQUEST['group'] ) );
		$user_id = sanitize_key( $_REQUEST['user_id'] );

		if ( ! is_user_logged_in() || ! UM()->Groups()->api()->can_approve_requests( $group_id ) && ! um_groups_admin_all_access() ) {
			wp_send_json_error( __( 'Invalid request', 'um-groups' ) );
		}

		$table_name = UM()->Groups()->setup()->db_groups_table;
		$status = 'rejected';

		$wpdb->update(
			$table_name,
			array(
				'status'    => $status,
			),
			array(
				'group_id' => $group_id,
				'user_id1' => $user_id,
			),
			array(
				'%s'
			),
			array(
				'%d',
				'%d'
			)
		);

		$this->member = $wpdb->get_row( $wpdb->prepare(
			"SELECT *
			FROM {$table_name}
			WHERE group_id = %d AND
			      user_id1 = %d",
			$group_id,
			$user_id
		) );

		do_action("um_groups_after_member_changed_status", $user_id, $group_id, $status );
		do_action("um_groups_after_member_changed_status__{$status}", $user_id, $group_id, false, false, false );

		wp_send_json_success();
	}


	/**
	 * Confirm group invite
	 */
	public function confirm_invitation( $group_id = null, $user_id = null ) {
		global $wpdb;

		if ( empty( $group_id ) ) {
			$group_id = absint( $_GET['group_id'] );
		}

		if ( empty( $user_id ) ) {
			$user_id = absint( $_GET['user_id'] );
		}

		if ( ! is_user_logged_in() ) {
			wp_die( esc_html__( 'Invalid request', 'um-groups' ) );
		}

		$table_name = UM()->Groups()->setup()->db_groups_table;

		$status = 'approved';

		$wpdb->update(
			$table_name,
			array(
				'status'          => $status,
				'date_joined_gmt' => gmdate( 'Y-m-d H:i:s' ),
			),
			array(
				'group_id' => $group_id,
				'user_id1' => $user_id,
			),
			array(
				'%s',
				'%s',
			),
			array(
				'%d',
				'%d',
			)
		);

		$this->member = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT *
				FROM {$table_name}
				WHERE group_id = %d AND
				      user_id1 = %d",
				$group_id,
				$user_id
			)
		);

		do_action( 'um_groups_after_member_changed_status', $user_id, $group_id, $status );

		do_action( 'um_groups_after_member_confirm_invitation', $user_id, $group_id, $status );

		return (array) $this->member;
	}


	/**
	 * Reject Invitation
	 */
	public function reject_invitation( $group_id = null, $user_id = null ) {
		global $wpdb;

		if ( is_null( $group_id ) ) {
			$group_id = absint( $_REQUEST['group_id'] );
		}

		if ( is_null( $user_id ) ) {
			$user_id = absint( $_REQUEST['user_id'] );
		}

		if ( ! is_user_logged_in() ) {
			wp_die( esc_html__( 'Invalid request', 'um-groups' ) );
		}

		$table_name = UM()->Groups()->setup()->db_groups_table;

		$status = 'rejected';

		$wpdb->update(
			$table_name,
			array(
				'status' => $status,
			),
			array(
				'group_id' => $group_id,
				'user_id1' => $user_id,
			),
			array(
				'%s',
			),
			array(
				'%d',
				'%d',
			)
		);

		$this->member = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT *
				FROM {$table_name}
				WHERE group_id = %d AND
				      user_id1 = %d",
				$group_id,
				$user_id
			)
		);

		$role = um_user( 'role' );
		do_action( 'um_groups_after_member_changed_status', $user_id, $group_id, $status );
		do_action( "um_groups_after_member_changed_status__{$status}", $user_id, $group_id, false, $role, false );
		do_action( 'um_groups_after_member_reject_invitation', $user_id, $group_id, $status );

		return (array) $this->member;
	}

	/**
	 * Set member status to block
	 */
	function block() {
		UM()->check_ajax_nonce();

		if ( empty( $_REQUEST['group'] ) || empty( $_REQUEST['user_id'] ) ) {
			wp_send_json_error( __( 'Invalid data', 'um-groups' ) );
		}

		global $wpdb;

		$group_id = UM()->Groups()->members()->get_group_by_hash( sanitize_key( $_REQUEST['group'] ) );
		$user_id = sanitize_key( $_REQUEST['user_id'] );

		UM()->Groups()->member()->set_group( $group_id, $user_id );
		$role = UM()->Groups()->member()->get_role();

		if ( ! is_user_logged_in() || ! UM()->Groups()->api()->can_approve_requests( $group_id ) && ! um_groups_admin_all_access() ) {
			wp_send_json_error( __( 'Invalid request', 'um-groups' ) );
		}

		$table_name = UM()->Groups()->setup()->db_groups_table;
		$status = 'blocked';

		$wpdb->update(
			$table_name,
			array(
				'status'        => $status,
			),
			array(
				'group_id' => $group_id,
				'user_id1' => $user_id,
			),
			array(
				'%s'
			),
			array(
				'%d',
				'%d'
			)
		);

		$this->member = $wpdb->get_row( $wpdb->prepare(
			"SELECT *
			FROM {$table_name}
			WHERE group_id = %d AND
			      user_id1 = %d",
			$group_id,
			$user_id
		) );

		do_action('um_groups_after_member_changed_status', $user_id, $group_id, $status );
		do_action( "um_groups_after_member_changed_status__{$status}", $user_id, $group_id, false, $role, false );

		wp_send_json_success();
	}


	/**
	 * Set member status to unblock
	 */
	function unblock() {
		UM()->check_ajax_nonce();

		if ( empty( $_REQUEST['group'] ) || empty( $_REQUEST['user_id'] ) ) {
			wp_send_json_error( __( 'Invalid data', 'um-groups' ) );
		}

		global $wpdb;

		$group_id = UM()->Groups()->members()->get_group_by_hash( sanitize_key( $_REQUEST['group'] ) );
		$user_id = sanitize_key( $_REQUEST['user_id'] );

		if ( ! is_user_logged_in() || ! UM()->Groups()->api()->can_approve_requests( $group_id ) && ! um_groups_admin_all_access() ) {
			wp_send_json_error( __( 'Invalid request', 'um-groups' ) );
		}

		$table_name = UM()->Groups()->setup()->db_groups_table;

		$wpdb->delete(
			$table_name,
			array(
				'group_id' => $group_id,
				'user_id1' => $user_id,
			),
			array(
				'%d',
				'%d'
			)
		);

		$this->member = $wpdb->get_row( $wpdb->prepare(
			"SELECT *
			FROM {$table_name}
			WHERE group_id = %d AND
			      user_id1 = %d",
			$group_id,
			$user_id
		) );

		wp_send_json_success();
	}

	/**
	 * Get Moderators
	 * @param  integer $group_id
	 * @return array
	 */
	function get_moderators( $group_id = null ){
		global $wpdb;

		$table_name = UM()->Groups()->setup()->db_groups_table;

		$moderators = $wpdb->get_results( $wpdb->prepare(
			"SELECT user_id1 as uid
			FROM {$table_name}
			WHERE group_id = %d AND
			      role IN( 'moderator', 'admin' )",
			$group_id
		) );

		return $moderators;
	}

	/**
	 * Get user's joined groups
	 * @param  integer $user_id
	 * @return array
	 */
	function get_groups_joined( $user_id = null ) {
		global $wpdb;

		if ( ! $user_id ) {
			$user_id = um_profile_id();
		}

		$table_name = UM()->Groups()->setup()->db_groups_table;

		$groups = $wpdb->get_results( $wpdb->prepare(
			"SELECT gg.group_id
			FROM {$table_name} AS gg
			WHERE gg.user_id1 = %d AND
			      gg.status IN( 'approved' ) AND
			      gg.group_id IN( SELECT p.ID FROM {$wpdb->posts} AS p WHERE p.post_type = 'um_groups' AND p.ID = gg.group_id )
			GROUP BY gg.group_id",
			$user_id
		) );

		return $groups;
	}
}
