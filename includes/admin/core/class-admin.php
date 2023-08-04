<?php
namespace um_ext\um_groups\admin\core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 *
 */
class Admin {

	/**
	 *
	 */
	public function __construct() {
		add_action( 'um_admin_custom_register_metaboxes', array( &$this, 'add_metabox_register' ) );
	}

	/**
	 * Add metabox for the registration form.
	 */
	public function add_metabox_register() {
		if ( ! is_admin() || ! current_user_can( 'manage_options' ) ) {
			return;
		}

		add_meta_box(
			'um-admin-form-register-groups{' . um_groups_path . '}',
			__( 'Groups settings', 'um-groups' ),
			array( UM()->metabox(), 'load_metabox_form' ),
			'um_form',
			'side'
		);
	}
}
