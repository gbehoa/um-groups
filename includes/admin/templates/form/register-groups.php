<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Filters get posts `um_groups` query arguments for registration form settings.
 *
 * @param {array} $args Get 'um_groups' posts query args.
 *
 * @return {array} Get posts query args.
 *
 * @since 2.4.0
 * @hook um_groups_assign_groups_registration_args
 *
 * @example <caption>Get all groups.</caption>
 * function my_um_groups_assign_groups_registration_args( $args ) {
 *     unset( $args['meta_query'] );
 *     return $args;
 * }
 * add_filter( 'um_groups_assign_groups_registration_args', 'my_um_groups_assign_groups_registration_args' );
 */
$assign_groups_query_args = apply_filters(
	'um_groups_assign_groups_registration_args',
	array(
		'post_type'   => 'um_groups',
		'post_status' => 'publish',
		'numberposts' => -1,
		'meta_query'  => array(
			array(
				'key'     => '_um_groups_privacy',
				'value'   => 'public',
				'compare' => '=',
			),
		),
		'fields'      => 'ids',
	)
);

$groups = get_posts( $assign_groups_query_args );

$options = array();
foreach ( $groups as $group_id ) {
	$options[ $group_id ] = get_the_title( $group_id );
}
?>

<div class="um-admin-metabox">
	<?php
	UM()->admin_forms(
		array(
			'class'     => 'um-form-register-groups um-top-label',
			'prefix_id' => 'form',
			'fields'    => array(
				array(
					'id'      => '_um_register_enable_groups_assign',
					'type'    => 'select',
					'label'   => __( 'Assign user after registration on these groups', 'um-groups' ),
					'value'   => UM()->query()->get_meta_value( '_um_register_enable_groups_assign', null, '' ),
					'options' => array(
						0 => __( 'No', 'um-groups' ),
						1 => __( 'Yes', 'um-groups' ),
					),
				),
				array(
					'id'          => '_um_register_groups_assign',
					'type'        => 'select',
					'label'       => __( 'Groups', 'um-groups' ),
					'value'       => ! empty( get_post_meta( get_the_ID(), '_um_register_groups_assign', true ) ) ? get_post_meta( get_the_ID(), '_um_register_groups_assign', true ) : array(),
					'options'     => $options,
					'multi'       => true,
					'conditional' => array( '_um_register_enable_groups_assign', '=', '1' ),
				),
			),
		)
	)->render_form();
	?>
	<div class="um-admin-clear"></div>
</div>
