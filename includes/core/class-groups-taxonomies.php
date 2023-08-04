<?php
namespace um_ext\um_groups\core;


if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Class Groups_Taxonomies
 * @package um_ext\um_groups\core
 */
class Groups_Taxonomies {


	/**
	 * Groups_Taxonomies constructor.
	 */
	function __construct() {
		add_action( 'init', [ &$this, 'create_taxonomies' ], 9999 );
		add_filter( 'post_row_actions', [ &$this, 'remove_quick_edit' ] );
	}


	/**
	 * @param array $actions
	 *
	 * @return array
	 */
	function remove_quick_edit( $actions ) {
		global $current_screen;

		if ( $current_screen->post_type != 'um_groups' ) {
			return $actions;
		}
		unset( $actions['inline hide-if-no-js'] );

		return $actions;
	}


	/**
	 * Create post types
	 */
	function create_taxonomies() {

		$cpt_args = apply_filters( 'um_groups_cpt_arguments', [
			'labels' => [
				'name'                  => __( 'Groups', 'um-groups' ),
				'singular_name'         => __( 'Group', 'um-groups' ),
				'menu_name'             => _x( 'Groups', 'Admin menu name', 'um-groups' ),
				'add_new'               => __( 'Add New Group', 'um-groups' ),
				'add_new_item'          => __( 'Add New Group', 'um-groups' ),
				'edit'                  => __( 'Edit', 'um-groups' ),
				'edit_item'             => __( 'Edit Group', 'um-groups' ),
				'new_item'              => __( 'New Group', 'um-groups' ),
				'view'                  => __( 'View Group', 'um-groups' ),
				'view_item'             => __( 'View Group', 'um-groups' ),
				'search_items'          => __( 'Search Groups', 'um-groups' ),
				'not_found'             => __( 'No Groups found', 'um-groups' ),
				'not_found_in_trash'    => __( 'No Groups found in trash', 'um-groups' ),
				'parent'                => __( 'Parent Group', 'um-groups' ),
				'featured_image'        => __( 'Group Image', 'um-groups' ),
				'set_featured_image'    => __( 'Set group image', 'um-groups' ),
				'remove_featured_image' => __( 'Remove group image', 'um-groups' ),
				'use_featured_image'    => __( 'Use as group image', 'um-groups' ),
			],
			'show_ui'               => true,
			'show_in_menu'          => true,
			'public'                => true,
			'publicly_queryable'    => true,
			'hierarchical'          => false,
			'menu_position'         => null,
			'menu_icon'             => 'dashicons-groups',
			'supports'              => [ 'title', 'editor', 'thumbnail' ],
			'taxonomies'            => [ 'um_group_categories', 'um_group_tags' ],
			'rewrite'               => [ 'slug' => UM()->options()->get( 'groups_slug' ) ],
			'capability_type'       => 'page',
		] );

		register_post_type( 'um_groups', $cpt_args );

		$taxonomies = apply_filters( 'um_groups_taxonomies_arguments', [
			'um_group_categories'   => [
				'post_types'    => 'um_groups',
				'tax_args'      => [
					'hierarchical'          => true,
					'labels'                => [
						'name'                       => _x( 'Group Categories', 'taxonomy general name', 'um-groups' ),
						'singular_name'              => _x( 'Group Category', 'taxonomy singular name', 'um-groups' ),
						'search_items'               => __( 'Search Group Categories', 'um-groups' ),
						'popular_items'              => __( 'Popular Group Categories', 'um-groups' ),
						'all_items'                  => __( 'All Group Categories', 'um-groups' ),
						'parent_item'                => null,
						'parent_item_colon'          => null,
						'edit_item'                  => __( 'Edit Group Category', 'um-groups' ),
						'update_item'                => __( 'Update Group Category', 'um-groups' ),
						'add_new_item'               => __( 'Add New Group Category', 'um-groups' ),
						'new_item_name'              => __( 'New Group Category Name', 'um-groups' ),
						'separate_items_with_commas' => __( 'Separate group categories with commas', 'um-groups' ),
						'add_or_remove_items'        => __( 'Add or remove group categories', 'um-groups' ),
						'choose_from_most_used'      => __( 'Choose from the most used group categories', 'um-groups' ),
						'not_found'                  => __( 'No group categories found.', 'um-groups' ),
						'menu_name'                  => __( 'Group Categories', 'um-groups' ),
					],
					'show_ui'               => true,
					'show_admin_column'     => false,
					'show_in_menu'          => true,
					'update_count_callback' => '_update_post_term_count',
					'query_var'             => false,
					'rewrite'               => [ 'slug' => UM()->options()->get( 'group_category_slug' ) ],
				],
			],
			'um_group_tags'         => [
				'post_types'    => 'um_groups',
				'tax_args'      => [
					'hierarchical'          => false,
					'labels'                => [
						'name'                          => _x( 'Group Tags', 'taxonomy general name', 'um-groups' ),
						'singular_name'                 => _x( 'Tag', 'taxonomy singular name', 'um-groups' ),
						'search_items'                  => __( 'Search Group Tags', 'um-groups' ),
						'popular_items'                 => __( 'Popular Group Tags', 'um-groups' ),
						'all_items'                     => __( 'All Group Tags', 'um-groups' ),
						'parent_item'                   => null,
						'parent_item_colon'             => null,
						'edit_item'                     => __( 'Edit Group Tag', 'um-groups' ),
						'update_item'                   => __( 'Update Group Tag', 'um-groups' ),
						'add_new_item'                  => __( 'Add New Group Tag', 'um-groups' ),
						'new_item_name'                 => __( 'New Group Tag Name', 'um-groups' ),
						'separate_items_with_commas'    => __( 'Separate group tags with commas', 'um-groups' ),
						'add_or_remove_items'           => __( 'Add or remove group tags', 'um-groups' ),
						'choose_from_most_used'         => __( 'Choose from the most used tags', 'um-groups' ),
						'not_found'                     => __( 'No group tags found.', 'um-groups' ),
						'menu_name'                     => __( 'Group Tags', 'um-groups' ),
					],
					'show_ui'               => true,
					'update_count_callback' => '_update_post_term_count',
					'query_var'             => true,
					'rewrite'               => [ 'slug' => UM()->options()->get( 'group_tag_slug' ) ],
				],
			],
		] );
		foreach ( $taxonomies as $key => $taxonomy ) {
			register_taxonomy( $key, $taxonomy['post_types'], $taxonomy['tax_args'] );
		}
	}
}