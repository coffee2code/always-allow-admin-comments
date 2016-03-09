<?php
/**
 * Plugin Name: Always Allow Admin Comments
 * Version:     1.0
 * Plugin URI:  http://coffee2code.com/wp-plugins/admin-can-always-comment/
 * Author:      Scott Reilly
 * Author URI:  http://coffee2code.com/
 * Text Domain: always-allow-admin-comments
 * License:     GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 * Description: An admin user (when logged in) will always be able to comment on a post, even if the post's comments are closed.
 *
 * Compatible with WordPress 4.1+ through 4.4+.
 *
 * =>> Read the accompanying readme.txt file for instructions and documentation.
 * =>> Also, visit the plugin's homepage for additional information and updates.
 * =>> Or visit: https://wordpress.org/plugins/always-allow-admin-comments/
 *
 * @package Always_Allow_Admin_Comments
 * @author  Scott Reilly
 * @version 1.0
 */

/*
	Copyright (c) 2013-2016 by Scott Reilly (aka coffee2code)

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
		return '1.0';
	}

	/**
	 * The constructor.
	 *
	 * @since 1.0
	 */
	private function __construct() {
		add_action( 'plugins_loaded', array( $this, 'plugins_loaded' ) );
	}

	/**
	 * Initializes the plugin.
	 *
	 * @since 1.0
	 */
	public function plugins_loaded() {
		// Load textdomain
		load_plugin_textdomain( 'always-allow-admin-comments' );

		add_filter( 'comments_open',                        array( $this, 'comments_open_for_admin' ), 20, 2 );
		add_action( 'post_comment_status_meta_box-options', array( $this, 'display_option' ) );
		add_action( 'save_post',                            array( $this, 'save_setting' ) );
		add_filter( 'is_protected_meta',                    array( $this, 'hide_meta' ), 10, 2 );
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
		if ( ! current_user_can( 'administrator' ) ) {
			return;
		}

		$disable_admin_commenting = isset( $_POST[ self::$setting_name ] ) && '1' === $_POST[ self::$setting_name ];
		$this->set_admin_can_comment_on_post( ! $disable_admin_commenting, $post_id );
	}

	/**
	 * Prevents the meta key from being displayed in the Custom Fields metabox.
	 *
	 * @since 1.0
	 *
	 * @param bool   $protected Is the meta key protected?
	 * @param string $key       The meta key name.
	 *
	 * @return bool  Is the meta key protected?
	 */
	public function hide_meta( $protected, $key ) {
		return ( self::$setting_name === $key ? true : $protected );
	}

	/**
	 * Displays option field in 'Discussion' metabox for a post/page, but only
	 * for administrators.
	 *
	 * @since 1.0
	 *
	 * @param WP_Post Post.
	 */
	public function display_option( $post ) {
		if ( ! current_user_can( 'administrator' ) ) {
			return;
		}

		$status = $this->is_admin_commenting_disabled( $post->ID, false ) ? '1' : '0';

		echo '<label for="' . self::$setting_name . '" class="selectit" style="display: block;">';
		echo '<input type="checkbox" name="' . self::$setting_name . '" value="1" ' . checked( $status, '1' ) . '/> ';
		_e( 'Prevent administrators from commenting.', 'always-allow-admin-comments' );
		echo '<span class="description" style="display: block; padding-left: 1.85em;">';
		_e( 'Administrators can currently comment even if commenting is closed.', 'always-allow-admin-comments' );
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
			$status = apply_filters( 'c2c_always_allow_admin_comments_disable', $status, $post_id );
		}

		return $status;
	}
}

c2c_AlwaysAllowAdminComments::get_instance();

endif;
