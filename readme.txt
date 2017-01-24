=== Always Allow Admin Comments ===
Contributors: coffee2code
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=6ARCFJ9TX3522
Tags: comment, comments, comments_open, commenting, admin, post, page, coffee2code
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
Requires at least: 4.6
Tested up to: 4.7
Stable tag: 1.0

An admin user (when logged in) will always be able to comment on a post, even if the post's comments are closed.

== Description ==

This plugin enables a user with the administrator role the ability to comment on any post or page, even if the comments for that post or page are closed. When the plugin is active, this behavior is automatically enabled.

Administrators can be explicitly prevented from commenting on specific posts via two approaches:

* When creating or editing a post, via the 'Discussion' metabox where there is a checkbox labeled "Prevent administrators from commenting" that only administrators can access. Checking the checkbox will prevent administrators from commenting on the post even though this plugin is active. (If the metabox isn't visible for you, then expand the "Screen Options" slide-down panel on the upper-right of the page.)
* Programmatically, via the use of the 'c2c_admin_can_always_comment_disable' filter. This can be used be provide more fine-grained access control and contextual handling. See the "Other Notes" section for documentation on the filter's use.

Links: [Plugin Homepage](http://coffee2code.com/wp-plugins/always-allow-admin-comments/) | [Plugin Directory Page](https://wordpress.org/plugins/always-allow-admin-comments/) | [Author Homepage](http://coffee2code.com)


== Installation ==

1. Install via the built-in WordPress plugin installer. Or download and unzip `always-allow-admin-comments.zip` inside the plugins directory for your site (typically `wp-content/plugins/`)
2. Activate the plugin through the 'Plugins' admin menu in WordPress


== Screenshots ==

1. A screenshot of the `Discussion` metabox when creating/editing a post or page that allows you to override the plugin to truly disable admin commenting for the post/page.


== Frequently Asked Questions ==

= Will this plugin allow an administrator to comment on a post whose comments have been automatically closed for being old? =

Yes.

= Does this plugin include unit tests? =

Yes.


== Filters ==

The plugin is further customizable via three hooks. Such code should ideally be put into a mu-plugin or site-specific plugin (which is beyond the scope of this readme to explain).

= c2c_always_allow_admin_comments_disable =

The 'c2c_always_allow_admin_comments_disable' filter allows you to customize whether comments should actually be disabled for a particular admin user. By default, all admin users can always comment on posts and pages. Using this filter, you have finer-grained controls to override this behavior. It takes into account the checkbox provided per post to explicitly prevent admin comments.

NOTE: This filter is only used if comments for the post are closed and the current user is an admin. This filter is only to override the behavior of the plugin and is not a general purpose filter for enabling or disabling comments.

Arguments:

* $status (bool): The comment status of the current post for the current user.
* $post_id (int): The ID of the post whose comments are being checked.

Return:

* (bool): True if admin commenting is disabled, false if admin can comment.

Example:

`
/**
 * Only allow certain admins the ability to comment on all posts.
 *
 * @param  bool $status  The comment open status.
 * @param  int  $post_id The post id.
 * @return bool True if admin commenting is disabled, false if not.
 */
function restrict_admin_commenting( $status, $post_id ) {
	// User IDs of the admins who can always comment.
	$admins_who_can_comment = array( 2, 13 );

	// Admins not specified above cannot comment when comments are closed.
	return ! in_array( get_current_user_id(), $admins_who_can_comment );
}
add_filter( 'c2c_always_allow_admin_comments_disable', 'restrict_admin_commenting', 10, 2 );
`


== Changelog ==

= () =
* Change: Register meta field via `register_meta()`.
    * Add own `register_meta()`
    * Remove `hide_meta()` in favor of use of `register_meta()`
* Change: Sanitize meta key name when used as input attribute (it's not a user input value so no security issue existed).
* Change: Enable more error output for unit tests.
* Change: Default `WP_TESTS_DIR` to `/tmp/wordpress-tests-lib` rather than erroring out if not defined via environment variable.
* Change: Minor readme.txt documentation tweaks.
* Change: Note compatibility through WP 4.7+.
* Change: Remove support for WordPress older than 4.6 (should still work for earlier versions)
* Change: Minor readme improvements.
* Change: Update copyright date (2017).

= 1.0 (2016-03-08) =
* Initial public release


== Upgrade Notice ==

= 1.0 =
Initial public release.
