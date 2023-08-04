<?php
namespace um_ext\um_groups\admin\core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'um_ext\um_groups\admin\core\Admin_Upgrade' ) ) {

	/**
	 * This class handles all functions that changes data structures and moving files
	 */
	class Admin_Upgrade {

		/**
		 * @var array
		 */
		public $update_versions;

		/**
		 * @var string
		 */
		public $packages_dir;

		public function __construct() {
			$this->packages_dir = plugin_dir_path( __FILE__ ) . 'packages' . DIRECTORY_SEPARATOR;

			$um_groups_last_version_upgrade = get_option( 'um_groups_last_version_upgrade' );

			if ( ! $um_groups_last_version_upgrade || version_compare( $um_groups_last_version_upgrade, um_groups_version, '<' ) ) {
				add_action( 'admin_init', array( $this, 'packages' ) );
			}
		}

		/**
		 * Load packages
		 */
		public function packages() {
			$this->set_update_versions();

			$um_groups_last_version_upgrade = get_option( 'um_groups_last_version_upgrade' );
			$um_groups_last_version_upgrade = ! $um_groups_last_version_upgrade ? '0.0.0' : $um_groups_last_version_upgrade;

			foreach ( $this->update_versions as $update_version ) {

				if ( version_compare( $update_version, $um_groups_last_version_upgrade, '<=' ) ) {
					continue;
				}

				if ( version_compare( $update_version, um_groups_version, '>' ) ) {
					continue;
				}

				$file_path = $this->packages_dir . $update_version . '.php';

				if ( file_exists( $file_path ) ) {
					include_once $file_path;
					update_option( 'um_groups_last_version_upgrade', $update_version );
				}
			}

			update_option( 'um_groups_last_version_upgrade', um_groups_version );
		}

		/**
		 * Parse packages dir for packages files
		 */
		public function set_update_versions() {
			$update_versions = array();

			$handle = opendir( $this->packages_dir );
			while ( false !== ( $filename = readdir( $handle ) ) ) {
				if ( '.' !== $filename && '..' !== $filename ) {
					$update_versions[] = preg_replace( '/(.*?)\.php/i', '$1', $filename );
				}
			}
			closedir( $handle );

			sort( $update_versions );

			$this->update_versions = $update_versions;
		}
	}
}
