<?php
namespace um_ext\um_groups\core;

if( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Groups_Shortcode {

	/**
	 * @var array|string[]
	 */
	public $args = array();

	/**
	 *
	 */
	public function __construct() {
		add_shortcode( 'ultimatemember_groups', array( $this, 'list_shortcode' ) );
		add_shortcode( 'ultimatemember_groups_profile_list', array( $this, 'profile_list' ) );
		add_shortcode( 'ultimatemember_group_discussion_activity', array( $this, 'discussion_activity' ) );
		add_shortcode( 'ultimatemember_group_discussion_wall', array( $this, 'discussion_wall' ) );
		add_shortcode( 'ultimatemember_group_invite_list', array( $this, 'invite_list' ) );
		add_shortcode( 'ultimatemember_group_users_invite_list', array( $this, 'users_invite_list' ) );

		add_shortcode( 'ultimatemember_group_members', array( $this, 'members' ) );

		add_shortcode( 'ultimatemember_group_new', array( $this, 'create' ) );
		add_shortcode( 'ultimatemember_group_single', array( $this, 'single' ) );
		add_shortcode( 'ultimatemember_my_groups', array( $this, 'own_groups' ) );

		// This shortcode returns block, that contains posts with the latest comments
		if ( ! shortcode_exists( 'ultimatemember_group_comments' ) ) {
			add_shortcode( 'ultimatemember_group_comments', array( $this, 'comments' ) );
		}

		add_action( 'template_redirect', array( $this, 'redirect_to_login' ) );

		$this->args = array( '' );
	}

	/**
	 * Create a group form
	 *
	 * @example [ultimatemember_group_new]
	 *
	 * @global WP_Post $um_group
	 * @global int $um_group_id
	 * @param array $atts
	 * 'group_id' => 0
	 *
	 * @return string
	 */
	public function create( $atts = array() ) {
		if ( UM()->roles()->um_user_can( 'group_create_off' ) || ! is_user_logged_in() ) {
			return '<div class="um">' . __("You do not have the necessary permission for the creation groups.", 'um-groups' ) . '</div>';
		}

		$output = UM()->get_template( 'tabs/create.php', um_groups_plugin );

		wp_enqueue_script( 'um_groups' );
		wp_enqueue_style( 'um_groups' );

		return $output;
	}

	/**
	 * Shortcode: Last comments
	 *
	 * @example [ultimatemember_group_comments]
	 *
	 * @global WP_User $current_user
	 * @global wpdb $wpdb
	 *
	 * @param array $atts
	 * @return string html
	 */
	public function comments( $atts = array() ) {
		global $current_user, $wpdb;

		if ( ! is_user_logged_in() ) {
			return '<div class="um">' . __("You do not have the necessary permission for the specified Group to perform this action.", 'um-groups' ) . '</div>';
		}

		$args = shortcode_atts( array(
			'numberposts'	=> 5,
			'order'			=> 'DESC',
			'orderby'		=> '`c`.`comment_date`',
			), $atts, 'ultimatemember_group_comments' );

		$public_groups = $wpdb->get_col( "
			SELECT DISTINCT `post_id`
			FROM `{$wpdb->postmeta}`
			WHERE `meta_key` = '_um_groups_privacy'
				AND `meta_value` = 'public';" );

		$joined_groups = $wpdb->get_col( "
			SELECT DISTINCT `group_id`
			FROM `{$wpdb->prefix}um_groups_members`
			WHERE `status` = 'approved'
				AND (`user_id1` = {$current_user->ID} OR `user_id2` = {$current_user->ID});" );

		$allowed_groups = array_unique( array_merge( $public_groups, $joined_groups ) );
		if( empty( $allowed_groups ) ){
			return;
		}

		$posts_ids = $wpdb->get_col( "
			SELECT DISTINCT `comment_post_ID`
			FROM `{$wpdb->comments}` AS `c`
			INNER JOIN `{$wpdb->posts}` AS `p`
				ON (`c`.`comment_post_ID` = `p`.`ID`)
			INNER JOIN `{$wpdb->postmeta}` AS `pm_id`
				ON (`c`.`comment_post_ID` = `pm_id`.`post_id` AND `pm_id`.`meta_key` = '_group_id' )
			WHERE `c`.`comment_approved` = 1
				AND `p`.`post_status` = 'publish'
				AND `p`.`post_type` = 'um_groups_discussion'
				AND `pm_id`.`meta_value` IN (" . implode( ',', $allowed_groups ) . ")
			ORDER BY {$args[ 'orderby' ]} {$args[ 'order' ]}
			LIMIT {$args[ 'numberposts' ]};" );


		$posts = array_map( 'get_post', $posts_ids );

		$t_args = compact( 'args', 'posts' );
		$output = UM()->get_template( 'latest_comments.php', um_groups_plugin, $t_args );

		wp_enqueue_script( 'um_groups_discussion' );
		wp_enqueue_style( 'um_groups_discussion' );

		return '<div class="ultimatemember_group_comments">' . $output . '</div>';
	}

	/**
	 * Display Discussion
	 *
	 * @example [ultimatemember_group_discussion_activity group_id="309"]
	 *
	 * @param array $atts
	 * 'group_id' => {current_group_id OR 0},
	 * 'user_id' => {current_user_id},
	 * 'hashtag' => '',
	 * 'wall_post' => 0,
	 * 'template' => 'activity',
	 * 'mode' => 'activity',
	 * 'form_id' => 'um_group_id',
	 * 'user_wall' => 0
	 *
	 * @return string
	 */
	public function discussion_activity( $atts = array() ) {
		global $um_group, $um_group_id;

		// phpcs:disable WordPress.Security.NonceVerification
		$hashtag = '';
		if ( ! empty( $_GET['hashtag'] ) ) {
			$hashtag = sanitize_key( $_GET['hashtag'] );
		}

		$wall_post = '';
		if ( ! empty( $_GET['group_post'] ) ) {
			$wall_post = absint( $_GET['group_post'] );
		}
		// phpcs:enable WordPress.Security.NonceVerification

		$args = shortcode_atts(
			array(
				'group_id'  => $um_group_id ? $um_group_id : (int) get_the_ID(),
				'user_id'   => get_current_user_id(),
				'hashtag'   => $hashtag,
				'wall_post' => $wall_post,
				'template'  => 'activity',
				'mode'      => 'activity',
				'form_id'   => 'um_group_id',
				'user_wall' => 0,
			),
			$atts
		);

		$this->args = $args;

		if ( $args['group_id'] ) {
			$um_group_id   = $args['group_id'];
			$args['group'] = get_post( $um_group_id );
			$um_group      = $args['group'];
		}

		$privacy = UM()->Groups()->api()->get_privacy_slug( $um_group_id );
		if ( ! is_user_logged_in() || ( 'public' !== $privacy && UM()->Groups()->api()->has_joined_group( get_current_user_id(), $um_group_id ) !== 'approved' ) ) {
			return '<div class="um-groups-widget">' . esc_html__( 'You do not have the necessary permission for the specified Group to perform this action.', 'um-groups' ) . '</div>';
		}

		if ( empty( $args['use_custom_settings'] ) ) {
			$args = array_merge( $args, UM()->shortcodes()->get_css_args( $args ) );
		} else {
			$args = array_merge( UM()->shortcodes()->get_css_args( $args ), $args );
		}

		$per_page = ( UM()->mobile()->isMobile() ) ? UM()->options()->get( 'groups_posts_num_mob' ) : UM()->options()->get( 'groups_posts_num' );

		$um_current_page_tab = get_query_var( 'tab' );
		if ( 'discussion' === $um_current_page_tab ) {
			$widget_class = 'user';
		} else {
			$widget_class = "custom_page-{$um_current_page_tab}";
		}

		$show_pending_approval = get_query_var( 'show' );
		if ( ! empty( $show_pending_approval ) && 'pending' === $show_pending_approval ) {
			$show_pending_approval = true;
		}

		$is_single_post = (bool) $args['wall_post'];

		$can_write = UM()->Groups()->discussion()->can_write() && ! $is_single_post && UM()->Groups()->api()->has_joined_group( $args['user_id'], $args['group_id'] ) === 'approved';

		$can_write_restrict_text = UM()->Groups()->discussion()->can_write_restrict_text();

		ob_start();
		?>

		<div class="um <?php echo esc_attr( UM()->shortcodes()->get_class( $args['mode'] ) ); ?> um-<?php echo esc_attr( $args['form_id'] ); ?>">
			<div class="um-form">

				<?php
				if ( ! empty( $hashtag ) && $hashtag ) {
					$get_hashtag = get_term_by( 'slug', $hashtag, 'um_hashtag' );
					if ( isset( $get_hashtag->name ) ) {
						echo '<div class="um-groups-bigtext">#' . $get_hashtag->name . '</div>';
					}
				}

				if ( $can_write ) {
					UM()->get_template( 'discussion/new.php', um_groups_plugin, $args, true );
				} elseif ( $can_write_restrict_text ) {
					echo '<div class="um-profile-note" style="display:block;">' . $can_write_restrict_text . '</div>';
				}
				?>

				<div class="um-groups-wall"
						data-group_id="<?php echo esc_attr( $args['group_id'] ); ?>"
						data-core_page="<?php echo esc_attr( $widget_class ); ?>"
						data-hashtag="<?php echo esc_attr( $hashtag ); ?>"
						data-per_page="<?php echo esc_attr( $per_page ); ?>"
						data-show-pending="<?php echo esc_attr( $show_pending_approval ); ?>"
						data-single_post="<?php echo esc_attr( $is_single_post ); ?>"
						data-user_id="<?php echo esc_attr( $args['user_id'] ); ?>"
						data-user_wall="<?php echo esc_attr( $args['user_wall'] ); ?>" >

					<?php
					if ( $can_write ) {
						UM()->get_template( 'discussion/clone.php', um_groups_plugin, $args, true );
					}
					UM()->get_template( 'discussion/user-wall.php', um_groups_plugin, $args, true );
					?>

				</div>
			</div>
		</div>

		<?php
		if ( ! is_admin() && ! defined( 'DOING_AJAX' ) ) {
			UM()->shortcodes()->dynamic_css( $args );
		}
		add_action( 'wp_footer', 'um_groups_confirm_box' );
		$output = ob_get_clean();

		wp_enqueue_script( 'um_scrollto' );
		wp_enqueue_script( 'um_groups' );
		wp_enqueue_style( 'um_groups' );
		wp_enqueue_script( 'um_groups_discussion' );
		wp_enqueue_style( 'um_groups_discussion' );

		return $output;
	}

	/**
	 * Discussion Wall
	 *
	 * @example [ultimatemember_group_discussion_wall group_id="309"]
	 *
	 * @param array $atts
	 * 'group_id' => {current_group_id OR 0},
	 * 'user_id' => {current_user_id},
	 * 'hashtag' => '',
	 * 'wall_post' => 0,
	 * 'user_wall' => 1
	 *
	 * @return string
	 */
	public function discussion_wall( $atts = array() ) {
		global $um_group, $um_group_id;

		// phpcs:disable WordPress.Security.NonceVerification
		$hashtag = '';
		if ( empty( $_GET['hashtag'] ) ) {
			$hashtag = sanitize_key( $_GET['hashtag'] );
		}

		$wall_post = '';
		if ( empty( $_GET['group_post'] ) ) {
			$wall_post = absint( $_GET['group_post'] );
		}
		// phpcs:enable WordPress.Security.NonceVerification

		$args = shortcode_atts(
			array(
				'group_id'  => $um_group_id ? $um_group_id : (int) get_the_ID(),
				'user_id'   => get_current_user_id(),
				'hashtag'   => $hashtag,
				'wall_post' => $wall_post,
				'user_wall' => 1,
			),
			$atts
		);

		$this->args = $args;

		if ( $args['group_id'] ) {
			$um_group_id = $args['group_id'];
			$um_group    = $args['group'] = get_post( $um_group_id );
		}

		$privacy = UM()->Groups()->api()->get_privacy_slug( $um_group_id );
		if ( ! is_user_logged_in() || ( 'public' !== $privacy && UM()->Groups()->api()->has_joined_group( get_current_user_id(), $um_group_id ) !== 'approved' ) ) {
			return '<div class="um">' . esc_html__( 'You do not have the necessary permission for the specified Group to perform this action.', 'um-groups' ) . '</div>';
		}

		$per_page = ( UM()->mobile()->isMobile() ) ? UM()->options()->get( 'groups_posts_num_mob' ) : UM()->options()->get( 'groups_posts_num' );

		$um_current_page_tab = get_query_var( 'tab' );
		if ( 'um_groups' === get_post_type() && 'discussion' === $um_current_page_tab ) {
			$widget_class = 'group_profile';
		} else {
			$widget_class = "custom_page-{$um_current_page_tab}";
		}

		$is_single_post = (bool) $args['wall_post'];

		$can_write = UM()->Groups()->discussion()->can_write() && ! $is_single_post && UM()->Groups()->api()->has_joined_group( $args['user_id'], $args['group_id'] ) === 'approved';

		$can_write_restrict_text = UM()->Groups()->discussion()->can_write_restrict_text();

		ob_start();
		?>
		<div class="um">
			<div class="um-form">
				<?php
				if ( ! $hashtag ) {
					if ( $can_write ) {
						UM()->get_template( 'discussion/new.php', um_groups_plugin, $args, true );
					} elseif ( $can_write_restrict_text ) {
						echo '<div class="um-profile-note" style="display:block;">' . $can_write_restrict_text . '</div>';
					}
				}
				?>
				<div class="um-groups-wall"
						data-group_id="<?php echo esc_attr( $args['group_id'] ); ?>"
						data-core_page="<?php echo esc_attr( $widget_class ); ?>"
						data-hashtag="<?php echo esc_attr( $hashtag ); ?>"
						data-per_page="<?php echo esc_attr( $per_page ); ?>"
						data-single_post="<?php echo esc_attr( $is_single_post ); ?>"
						data-user_id="<?php echo esc_attr( $args['user_id'] ); ?>"
						data-user_wall="<?php echo esc_attr( $args['user_wall'] ); ?>">

					<?php
					if ( $can_write ) {
						UM()->get_template( 'discussion/clone.php', um_groups_plugin, $args, true );
					}
					UM()->get_template( 'discussion/user-wall.php', um_groups_plugin, $args, true );
					?>

				</div>
			</div>
		</div>

		<?php
		add_action( 'wp_footer', 'um_groups_confirm_box' );
		$output = ob_get_clean();

		wp_enqueue_script( 'um_scrollto' );
		wp_enqueue_script( 'um_groups' );
		wp_enqueue_style( 'um_groups' );
		wp_enqueue_script( 'um_groups_discussion' );
		wp_enqueue_style( 'um_groups_discussion' );

		return $output;
	}

	/**
	 * Invite List
	 *
	 * @example [ultimatemember_group_invite_list]
	 *
	 * @param array $atts
	 * 'group_id' => {current_group_id OR 0},
	 * 'user_id' => {current_user_id},
	 *
	 * @return string
	 */
	public function invite_list( $atts ) {
		global $um_group, $um_group_id;

		if ( ! is_user_logged_in() ) {
			return;
		}

		$args = shortcode_atts(
			array(
				'group_id' => $um_group_id ? $um_group_id : 0,
			),
			$atts
		);

		if ( $args['group_id'] ) {
			$um_group_id = $args[ 'group_id' ];
			$group_id    = $args[ 'group_id' ];
			$um_group    = $args[ 'group' ] = get_post( $um_group_id );
		} else {
			return;
		}

		$invites_enabled = get_post_meta( $um_group_id, '_um_groups_invites_settings', true );
		$privacy         = UM()->Groups()->api()->get_privacy_slug( $um_group_id );

		if ( ! $invites_enabled && ! UM()->Groups()->api()->can_manage_group( $um_group_id, get_current_user_id(), $privacy ) ) {
			return;
		}

		if ( $invites_enabled && ! UM()->Groups()->api()->can_invite_members( $um_group_id, null ) ) {
			return '<div class="um">' . esc_html__( "You can't invite users to this group, you are not approved member.", 'um-groups' ) . '</div>';

		}

		$t_args = compact( 'group_id' );
		$output = UM()->get_template( 'invite-list.php', um_groups_plugin, $t_args );

		wp_enqueue_script( 'um_groups' );
		wp_enqueue_style( 'um_groups' );

		return $output;
	}

	/**
	 * Invite List
	 *
	 * @example [ultimatemember_group_users_invite_list]
	 *
	 * @param array $atts
	 * 'user_id' => {current_user_id},
	 *
	 * @return string
	 */
	public function users_invite_list( $atts ) {

		$args = shortcode_atts( array(
			'empty_text'   => __( 'You were not invited to the groups.', 'um-groups' )
		), $atts );

		$output = UM()->get_template( 'users_invite_list.php', um_groups_plugin, $args );

		wp_enqueue_script( 'um_groups' );
		wp_enqueue_style( 'um_groups' );

		return $output;
	}

	/**
	 * List groups form. Sort users by recent_activity, newest_groups, oldest_groups and most_members.
	 *
	 * @example [ultimatemember_groups]
	 *
	 * @param array $atts
	 *  'avatar_size' => 'default',
	 *  'category' => 0,
	 *  'groups_per_page' => 10,
	 *  'groups_per_page_mobile' => 10,
	 *  'privacy' => 'all',
	 *  'show_actions' => true,
	 *	'show_author'  => 0
	 *  'show_pagination' => true,
	 *  'show_search_form' => true,
	 *  'show_search_categories' => 1,
	 *  'show_search_tags' => 1,
	 *  'show_total_groups_count' => true,
	 *  'show_with_greater_than' => 0,
	 *  'show_with_less_than' => 0,
	 *  'sort' => 'newest_groups'
	 *
	 * @return string
	 */
	public function list_shortcode( $atts ) {

		$args = shortcode_atts( array(
			'avatar_size'             => 'default',
			'category'                => 0,
			'groups_per_page'         => 10,
			'groups_per_page_mobile'  => 10,
			'privacy'                 => 'all',
			'show_actions'            => 1,
			'show_author'             => 0,
			'show_pagination'         => 1,
			'show_search_form'        => 1,
			'show_search_categories'  => 1,
			'show_search_tags'        => 1,
			'show_total_groups_count' => 1,
			'show_with_greater_than'  => 0,
			'show_with_less_than'     => 0,
			'sort'                    => 'newest_groups',
		), $atts );

		do_action( 'pre_groups_shortcode_query_list', $args );

		$t_args = compact( 'args' );
		$output = UM()->get_template( 'list.php', um_groups_plugin, $t_args );

		wp_enqueue_script( 'um_groups' );
		wp_enqueue_style( 'um_groups' );

		return $output;
	}

	/**
	 * Shortcode: List of the group members
	 *
	 * @todo
	 * @example [ultimatemember_group_members group_id="309"]
	 *
	 * @param array $atts
	 * 		'group_id' => 0,
	 * 		'load_more' => 'users'
	 */
	public function members( $atts = array() ) {

		$arr_settings = shortcode_atts( array(
			'group_id'  => 0,
		), $atts );

		if ( $arr_settings['group_id'] ) {
			$group_id = $arr_settings['group_id'];

			$privacy = UM()->Groups()->api()->get_privacy_slug( $group_id );
			if ( ! is_user_logged_in() || ( $privacy != 'public' &&  UM()->Groups()->api()->has_joined_group( get_current_user_id() , $group_id ) != 'approved' ) ) {
				return '<div class="um">' . __("You do not have the necessary permission for the specified Group to perform this action.", 'um-groups' ) . '</div>';
			}

			$args = UM()->Groups()->api()->get_members( $group_id, 'approved' );
			if ( $args ) {
				$args['group_id'] = $group_id;
				$args = apply_filters( 'um_groups_user_lists_args', $args );
				$args = apply_filters( 'um_groups_user_lists_args__approved', $args );
			} else {
				return '';
			}

			$t_args = compact( 'args', 'group_id' );
			$output = UM()->get_template( 'tabs/members.php', um_groups_plugin, $t_args );

			ob_start();
?>

			<div class="um um-groups-single">
				<div class="um-groups-wrapper"><?php echo $output ?></div>
			</div>
			<div class="um-clear"></div>

<?php
			$output = ob_get_clean();

			wp_enqueue_script( 'um_groups' );
			wp_enqueue_style( 'um_groups' );

			return $output;
		}

		return '';
	}

	/**
	 * List own groups
	 *
	 * @example [ultimatemember_my_groups]
	 *
	 * @param array $atts
	 * 'avatar_size' => 'default',
	 * 'category' => 0,
	 * 'groups_per_page' => 10,
	 * 'groups_per_page_mobile' => 10,
	 * 'privacy' => 'all',
	 * 'show_actions' => true,
	 * 'show_pagination' => true,
	 * 'show_search_form' => true,
	 * 'show_search_categories' => 1,
	 * 'show_search_tags' => 1,
	 * 'show_total_groups_count' => true,
	 * 'show_with_greater_than' => 0,
	 * 'show_with_less_than' => 0,
	 * 'sort' => 'newest_groups',
	 * 'own_groups' => true
	 *
	 * @return string
	 */
	public function own_groups( $atts ) {

		$args = shortcode_atts( array(
				'avatar_size'				=> 'default',
				'category'					=> 0,
				'groups_per_page'			=> 10,
				'groups_per_page_mobile'	=> 10,
				'privacy'					=> 'all',
				'show_actions'				=> 1,
				'show_pagination'			=> 1,
				'show_search_form'			=> 1,
				'show_search_categories'	=> 1,
				'show_search_tags'			=> 1,
				'show_total_groups_count'	=> 1,
				'show_with_greater_than'	=> 0,
				'show_with_less_than'		=> 0,
				'sort'						=> 'newest_groups',
				'own_groups'				=> 1
				), $atts );

		do_action( 'pre_groups_shortcode_query_list', $args );

		$t_args = compact( 'args' );
		$output = UM()->get_template( 'own.php', um_groups_plugin, $t_args );

		wp_enqueue_script( 'um_groups' );
		wp_enqueue_style( 'um_groups' );

		return $output;
	}

	/**
	 * Display single group
	 *
	 * @example [ultimatemember_group_single group_id="309"]
	 *
	 * @param array $atts
	 *  'group_id'     => {current_group_id OR 0},
	 *  'avatar_size'  => '',
	 *  'show_actions' => 1,
	 *	'show_author'  => 1
	 *
	 * @return string
	 */
	public function single( $atts ) {
		global $um_group, $um_group_id;

		$args = shortcode_atts( array(
			'group_id'     => $um_group_id ? $um_group_id : (int) get_the_ID(),
			'avatar_size'  => '',
			'show_actions' => 1,
			'show_author'  => 1
		), $atts );

		if ( $args['group_id'] ) {
			$um_group_id = $args['group_id'];
			$um_group = $args['group'] = get_post( $um_group_id );
		}

		$user_id = um_user( 'ID' );
		$has_joined = UM()->Groups()->api()->has_joined_group( $user_id, $um_group_id );
		$user_id2 = false;
		if ( $has_joined ) {
			$user_id2 = UM()->Groups()->api()->invited_by_user_id;
		}

		$t_args = compact( 'args', 'has_joined', 'user_id', 'user_id2' );

		$filename = apply_filters( 'um_groups_single_group_template_name', 'single', $args );
		$output = UM()->get_template( $filename . '.php', um_groups_plugin, $t_args );

		wp_enqueue_script( 'um_scrollto' );
		wp_enqueue_script( 'um_groups' );
		wp_enqueue_style( 'um_groups' );
		wp_enqueue_script( 'um_groups_discussion' );
		wp_enqueue_style( 'um_groups_discussion' );

		return $output;
	}

	/**
	 * List profile groups
	 *
	 * @example [ultimatemember_groups_profile_list]
	 *
	 * @param array $atts
	 * 'avatar_size' => 'default',
	 * 'category' => 0,
	 * 'groups_per_page' => 10,
	 * 'groups_per_page_mobile' => 10,
	 * 'privacy' => 'all',
	 * 'show_actions' => true,
	 * 'show_pagination' => true,
	 * 'show_search_form' => true,
	 * 'show_search_categories' => 1,
	 * 'show_search_tags' => 1,
	 * 'show_total_groups_count' => false,
	 * 'show_with_greater_than' => 0,
	 * 'show_with_less_than' => 0,
	 * 'sort' => 'newest_groups',
	 * 'own_groups' => ''
	 *
	 * @return string
	 */
	public function profile_list( $atts ) {

		$args = shortcode_atts( array(
				'avatar_size'				=> 'default',
				'category'					=> 0,
				'groups_per_page'			=> 10,
				'groups_per_page_mobile'	=> 10,
				'privacy'					=> 'all',
				'show_actions'				=> 1,
				'show_pagination'			=> 1,
				'show_search_form'			=> 1,
				'show_search_categories'	=> 1,
				'show_search_tags'			=> 1,
				'show_total_groups_count'	=> 0,
				'show_with_greater_than'	=> 0,
				'show_with_less_than'		=> 0,
				'sort'						=> 'newest_groups',
				'own_groups'				=> ''
				), $atts );

		do_action( 'pre_groups_shortcode_query_list', $args );

		$t_args = compact( 'args' );
		$output = UM()->get_template( 'groups-list.php', um_groups_plugin, $t_args );

		wp_enqueue_script( 'um_groups' );
		wp_enqueue_style( 'um_groups' );

		return $output;
	}

	/**
	 * Redirect to "Login" page from the "Join Requests", "Create Group" and "Invites" pages
	 */
	function redirect_to_login() {
		if ( is_user_logged_in() ) {
			return;
		}

		$redirect = false;

		if ( 'um_groups' == get_post_type() && get_query_var( 'tab' ) === 'requests' ) {
			$redirect = true;
		}

		if ( um_is_core_page( 'group_invites' ) || um_is_core_page( 'create_group' ) ) {
			$redirect = true;
		}

		if ( $redirect ) {
			$curr = UM()->permalinks()->get_current_url();
			exit( wp_redirect( esc_url( add_query_arg( 'redirect_to', urlencode_deep( $curr ), um_get_core_page( 'login' ) ) ) ) );
		}
	}
}
