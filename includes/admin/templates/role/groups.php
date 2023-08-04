<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>

<div class="um-admin-metabox">
	<?php $role = $object['data'];

		UM()->admin_forms( array(
			'class'     => 'um-role-groups um-half-column',
			'prefix_id' => 'role',
			'fields'    => array(
				array(
					'id'    => '_um_group_create_off',
					'type'  => 'checkbox',
					'label' => __( 'Turn off creation group?', 'um-activity' ),
					'value' => ! empty( $role['_um_group_create_off'] ) ? $role['_um_group_create_off'] : 0,
				),
			)
		) )->render_form(); ?>

	<div class="um-admin-clear"></div>
</div>