<?php
/**
 * Plugin Name: Always Allow Admin Comments
 * Version:     1.2.1
 * Plugin URI:  http://coffee2code.com/wp-plugins/admin-can-always-comment/
 * Author:      Scott Reilly
 * Author URI:  http://coffee2code.com/
 * Text Domain: always-allow-admin-comments
 * License:     GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Description: Allow an admin user (when logged in) to always be able to comment on a post, even if comments are closed for the post.
 *
 * Compatible with WordPress 4.6+ through 5.3+.
 *
 * =>> Read the accompanying readme.txt file for instructions and documentation.
 * =>> Also, visit the plugin's homepage for additional information and updates.
 * =>> Or visit: https://wordpress.org/plugins/always-allow-admin-comments/
 *
 * @package Always_Allow_Admin_Comments
 * @author  Scott Reilly
 * @version 1.2.1
 */

/*
 * TODO:
 * - Add template tag that allows checking if commenting is enabled due to this plugin.
 *   (basically the value comments_open() would return if this plugin weren't active)
 *   perhaps c2c_are_comments_open_only_for_admins()
 * - Rename meta key to reflect its meaning rather than simple being the name of the plugin.
 * - Add filter for support post types
 * - Add support for new block editor (aka Gutenberg) (once it becomes possible to alter
 *   the contents of the discussion panel)
 */

/*
	Copyright (c) 2013-2019 by Scott Reilly (aka coffee2code)

	This program is free software; you can redistribute it and/or
	modify it under the terms of the GNU General Public License
	as published by the Free Software Foundation; either version 2
	of the License, or (at your option) any later version.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program; if not, write to the Free Software
	Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
*/

defined( 'ABSPATH' ) or die();

if ( ! class_exists( 'c2c_AlwaysAllowAdminComments' ) ) :

class c2c_AlwaysAllowAdminComments {

	/**
	 * The singleton instance of this class.
	 *
	 * @var c2c_AlwaysAllowAdminComments
	 */
	private static $instance;

	/**
	 * The meta key for storing an override to prevent admins from
	 * commenting on a particular post.
	 *
	 * @var string
	 */
	private static $setting_name = 'c2c_always_allow_admin_comments';

	/**
	 * Gets singleton instance, creating it if necessary.
	 *
	 * @since 1.0
	 */
	public static function get_instance() {
		if ( ! self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	/**
	 * Returns version of the plugin.
	 *
	 * @since 1.0
	 */
	public static function version() {
		return '1.2.1';
	}

	/**
	 * The constructor.
	 *
	 * @since 1.0
	 */
	private function __construct() {
		// Load textdomain
		load_plugin_textdomain( 'always-allow-admin-comments' );

		add_filter( 'comments_open',                        array( $this, 'comments_open_for_admin' ), 20, 2 );
		add_action( 'post_comment_status_meta_box-options', array( $this, 'display_option' ) );
		add_action( 'save_post',                            array( $this, 'save_setting' ) );

		add_action( 'do_meta_boxes',                        array( $this, 'do_meta_box' ), 10, 3 );

		add_action( 'init',                                 array( __CLASS__, 'register_meta' ) );
	}

	/**
	 * Registers the post meta field.
	 *
	 * @since 1.1
	 */
	public static function register_meta() {
		$config = array(
			'type'              => 'boolean',
			'description'       => __( 'Disallow admin comments for the post', 'always-allow-admin-comments' ),
			'single'            => true,
			'sanitize_callback' => function ( $value ) {
				return (bool) $value;
			},
			'auth_callback'     => function() {
				return current_user_can( 'edit_posts' );
			},
			'show_in_rest'      => true,
		);

		$post_types = get_post_types_by_support( 'comments' );
		if ( ! $post_types ) {
			return;
		}

		foreach ( $post_types as $post_type ) {
			if ( function_exists( 'register_post_meta' ) ) {
				register_post_meta( $post_type, self::$setting_name, $config );
			}
			// Pre WP 4.9.8 support
			else {
				$config['object_subtype'] = $post_type;
				register_meta( 'post', self::$setting_name, $config );
			}
		}
	}

	/**
	 * Register meta box.
	 *
	 * Due to the block editor's current lack of panel customization, it is not
	 * presently feasible to insert the checkbox into the Duscussion panel where
	 * it appropriately belongs. So until it is possible to do so, just put the
	 * field into a meta box. (The meta box is not needed in the classic editor.)
	 *
	 * @since 1.2
	 *
	 * @param string  $post_type The post type.
	 * @param string  $type      The mode for the meta box (normal, advanced, or side).
	 * @param WP_Post $post      The post.
	 */
	public function do_meta_box( $post_type, $type, $post ) {
		// Can the UI be shown?
		if ( ! $this->can_show_ui() ) {
			return;
		}

		$current_screen = get_current_screen();

		// Don't show unless in the block editor.
		if ( ! method_exists( $current_screen, 'is_block_editor' ) || ! $current_screen->is_block_editor() ) {
			return;
		}

		add_meta_box(
			'always-allow-admin-comments-div',
			__( 'Prevent Admin Comments', 'always-allow-admin-comments' ),
			array( $this, 'display_option' ),
			$post_type,
			'side',
			'core'
		);
	}

	/**
	 * Determines if the UI for the plugin can be shown.
	 *
	 * Currently only requires that the current user be an administrator.
	 *
	 * @since 1.2
	 *
	 * @return bool
	 */
	public function can_show_ui() {
		return current_user_can( 'administrator' );
	}

	/**
	 * Explicitly sets override for disabling the ability for an admin to
	 * comment on a post.
	 *
	 * @since 1.0
	 *
	 * @param bool $can_comment Should admins be able to comment on this post?
	 * @param int  $post_id     The post ID.
	 */
	public function set_admin_can_comment_on_post( $can_comment, $post_id ) {
		$comments_disabled = $this->is_admin_commenting_disabled( $post_id, false );

		// If trying to enable/disable admin commenting and the opposite state
		// is currently in effect, then make a change.
		if ( $can_comment === $comments_disabled ) {
			if ( $can_comment ) {
				delete_post_meta( $post_id, self::$setting_name );
			} else {
				update_post_meta( $post_id, self::$setting_name, '1' );
			}
		}
	}

	/**
	 * Saves the setting to explicitly disable admin commenting.
	 *
	 * @since 1.0
	 *
	 * @param int $post_id The post ID.
	 */
	public function save_setting( $post_id ) {
		if ( ! $this->can_show_ui() ) {
			return;
		}

		$disable_admin_commenting = isset( $_POST[ self::$setting_name ] ) && '1' === $_POST[ self::$setting_name ];
		$this->set_admin_can_comment_on_post( ! $disable_admin_commenting, $post_id );
	}

	/**
	 * Displays option field  for a post/page, but only for administrators.
	 *
	 * @since 1.0
	 *
	 * @param WP_Post|null Post or null to denote current post. Default null.
	 */
	public function display_option( $post = null ) {
		if ( ! $this->can_show_ui() ) {
			return;
		}

		if ( ! $post ) {
			$post = get_post();
		}

		$status = $this->is_admin_commenting_disabled( $post->ID, false ) ? '1' : '0';
		echo <<<HTML
			<style>
				.always-allow-admin-comments-container,
				.always-allow-admin-comments-container .description {
					display: block;
				}
				#postbox-container-1 .always-allow-admin-comments-container .description,
				.edit-post-sidebar #postbox-container-2 .always-allow-admin-comments-container .description {
					margin-top: 1em;
				}
				#postbox-container-2 .always-allow-admin-comments-container .description {
					padding-left: 1.85em;
				}
				.edit-post-sidebar #postbox-container-2 .always-allow-admin-comments-container .description {
					padding-left: 0;
				}
			</style>
HTML;
		printf( '<label for="%s" class="selectit always-allow-admin-comments-container">', esc_attr( self::$setting_name ) );
		printf( '<input type="checkbox" name="%s" value="1" %s/> ', esc_attr( self::$setting_name ), checked( $status, '1', false ) );
		_e( 'Prevent administrators from commenting.', 'always-allow-admin-comments' );
		echo '<span class="description">';
		_e( 'Administrators can otherwise comment even if commenting is closed.', 'always-allow-admin-comments' );
		echo '</span></label>' . "\n";
	}

	/**
	 * Enables admin commenting if a post's comments are closed, unless
	 * explicitly prevented from doing so.
	 *
	 * @since 1.0
	 *
	 * @param  bool $status  If the post's comments are open or closed
	 * @param  int  $post_id The post ID
	 *
	 * @return bool Whether the post's comments are open or closed
	 */
	public function comments_open_for_admin( $status, $post_id ) {
		// Only do anything if commenting is closed and the current user is an
		// Administrator.
		if ( ! $status && current_user_can( 'administrator' ) ) {
			// Enable admin commenting unless it is specifically disabled.
			if ( ! $this->is_admin_commenting_disabled( $post_id ) ) {
				$status = true;
			}
		}

		return $status;
	}

	/**
	 * Checks if a specific post has explicitly disabled admin commenting.
	 *
	 * First checks for a custom field, which gets set via a checkbox in the
	 * Discussion metabox for a post. Then runs a filter to allow programmatic
	 * override of the value (unless told not to do so by its second argument).
	 *
	 * NOTE: This doesn't first check if admin commenting is otherwise enabled
	 * or even necessary for the post. It simply checks if admin commenting has
	 * been explicitly disabled.
	 *
	 * @since 1.0
	 *
	 * @param  int  $post_id      The post ID
	 * @param  bool $apply_filter Should the filter be applied?
	 *
	 * @return bool If admin commenting has been disabled for the post.
	 */
	public function is_admin_commenting_disabled( $post_id, $apply_filter = true ) {
		$status = '1' === get_post_meta( $post_id, self::$setting_name, true );

		if ( $apply_filter ) {
			/**
			 * Filters whether admin commenting has been explicitly disabled for the
			 * given post.
			 *
			 * @since 1.0
			 *
			 * @param bool $status  Is admin commenting explicitly disabled for the post?
			 * @param int  $post_id The post ID.
			 */
			$status = apply_filters( 'c2c_always_allow_admin_comments_disable', $status, $post_id );
		}

		return $status;
	}

}

add_action( 'plugins_loaded', array( 'c2c_AlwaysAllowAdminComments', 'get_instance' ) );

endif;
