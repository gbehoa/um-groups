<?php
/**
 * Template for the UM Groups.
 * This template displays likes and comments counters.
 *
 * Call: um_groups_discussion_post_counters( $post )
 * Hook: 'um_groups_discussion_post_body_after'
 * Page: "Group", tab "Discussions"
 *
 * @version 2.3.1
 *
 * This template can be overridden by copying it to yourtheme/ultimate-member/um-groups/discussion/counters.php
 */
if ( !defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="um-groups-disp">
	<div class="um-groups-left">
		<div class="um-groups-disp-likes">
			<a href="#" class="um-groups-show-likes um-link" data-post_id="<?php echo esc_attr( $post->ID ); ?>">
				<span class="um-groups-post-likes"><?php echo esc_html( $likes ); ?></span>
				<span class="um-groups-disp-span"><?php _e( 'likes', 'um-groups' ); ?></span>
			</a>
		</div>
		<div class="um-groups-disp-comments">
			<a href="#" class="um-link">
				<span class="um-groups-post-comments"><?php echo esc_html( $comments ); ?></span>
				<span class="um-groups-disp-span"><?php _e( 'comments', 'um-groups' ); ?></span>
			</a>
		</div>
	</div>
	<div class="um-groups-faces um-groups-right">
		<?php echo UM()->Groups()->discussion()->get_faces( $post->ID ); ?>
	</div>
	<div class="um-clear"></div>
</div>