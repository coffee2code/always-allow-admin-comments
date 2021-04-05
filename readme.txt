=== Always Allow Admin Comments ===
Contributors: coffee2code
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=6ARCFJ9TX3522
Tags: comment, comments, comments_open, commenting, admin, post, page, coffee2code
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Requires at least: 4.6
Tested up to: 5.7
Stable tag: 1.3.1

Allow an admin user (when logged in) to always be able to comment on a post, even if comments are closed for the post.

== Description ==

This plugin enables a user with the administrator role the ability to comment on any post or page, even if the comments for that post or page are closed. When the plugin is active, this behavior is automatically enabled.

Administrators can be explicitly prevented from commenting on specific posts via two approaches:

* When creating or editing a post, in the "Prevent Admin Comments" metabox (in the block editor) or the "Discussion" metabox (in the classic editor) there is a checkbox labeled "Prevent administrators from commenting" that only administrators can access. Checking the checkbox will prevent administrators from commenting on the post even though this plugin is active. (If the metabox isn't visible for you when using the classic editor, then expand the "Screen Options" slide-down panel on the upper-right of the page.)
* Programmatically, via the use of the 'c2c_admin_can_always_comment_disable' filter. This can be used be provide more fine-grained access control and contextual handling. See the "Hooks" section for documentation on the filter's use.

Links: [Plugin Homepage](https://coffee2code.com/wp-plugins/always-allow-admin-comments/) | [Plugin Directory Page](https://wordpress.org/plugins/always-allow-admin-comments/) | [GitHub](https://github.com/coffee2code/always-allow-admin-comments/) | [Author Homepage](https://coffee2code.com)


== Installation ==

1. Install via the built-in WordPress plugin installer. Or download and unzip `always-allow-admin-comments.zip` inside the plugins directory for your site (typically `wp-content/plugins/`)
2. Activate the plugin through the 'Plugins' admin menu in WordPress


== Screenshots ==

1. A screenshot of the `Discussion` metabox when creating/editing a post or page that allows you to override the plugin to truly disable admin commenting for the post/page. This is the form field used for versions of WordPress older than 5.0, or for versions later than 5.0 when the block editor is disabled.
2. A screenshot of the `Prevent Admin Comments` metabox when creating/editing a post or page that allows you to override the plugin to truly disable admin commenting for the post/page. This is the form field used for WordPress 5.0 and later when the block editor is enabled (which it is by default).


== Frequently Asked Questions ==

= Will this plugin allow an administrator to comment on a post whose comments have been automatically closed for being old? =

Yes.

= Does this plugin support the block editor (aka Gutenberg)?

Yes, though not entirely. The primary functionality of the plugin--allowing administrators to always comment on posts--works regardless of the post editor being used. However, the checkbox that allows the plugin to be disabled on a per-post basis (in order to actively prevent administrators from being able to comment on the post) is currently only available in the classic editor. This is unlikely to affect most users.

= Does this plugin include unit tests? =

Yes.


== Hooks ==

The plugin is further customizable via two hooks. Such code should ideally be put into a mu-plugin or site-specific plugin (which is beyond the scope of this readme to explain).

**c2c_always_allow_admin_comments_disable (filter)**

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

**c2c_always_allow_admin_comments_post_types (filter)**

The 'c2c_always_allow_admin_comments_post_types' filter allows you to customize which post types are supported by the plugin. By default, all post types that have registered support for the 'comments' feature are supported.

Arguments:

* $post_types (array): Supported post types. Default is all the post types that support 'comments' as a feature.

Return:

* (array): Array of supported post types.

Example:

`
// Disable support for Always Allow Admin Comments for pages.
add_filter( 'c2c_always_allow_admin_comments_post_types', function( $post_types ) {
    if ( false !== $i = array_search( 'page', $post_types ) ) {
        unset( $post_types[ $i ] );
    }
    return $post_types;
} );
`


== Changelog ==

= 1.3.1 (2021-04-04) =
* Change: Note compatibility through WP 5.7+
* Change: Update copyright date (2021)

= 1.3 (2020-05-12) =
* Change: Add customization for post type support
    * New: Add filter `c2c_always_allow_admin_comments_post_types` to allow customizing supported post types
    * New: Add `get_post_types()` to get the list of supported post types
    * Change: Enhance `can_show_ui()` to check if post's post type is supported
* Change: Change label for setting
* New: Add TODO.md and move existing TODO list from top of main plugin file into it (and add more items to the list)
* Change: Use HTTPS for link to WP SVN repository in bin script for configuring unit tests
* Change: Note compatibility through WP 5.4+
* Change: Update links to coffee2code.com to be HTTPS
* Unit tests:
    * New: Add tests for registering of hooks
    * Change: Remove unnecessary unregistering of hooks

= 1.2.2 (2019-12-28) =
* Unit tests:
    * New: Add test to verify plugin hooks `plugins_loaded` action to initialize itself
    * New: Add unit tests for functions `can_show_ui()`, `comments_open_for_admin()`
* Change: Note compatibility through WP 5.3+
* Change: Add an FAQ to clarify that the checkbox to disable admin comments is only available for the classic editor
* Change: Update copyright date (2020)

_Full changelog is available in [CHANGELOG.md](https://github.com/coffee2code/always-allow-admin-comments/blob/master/CHANGELOG.md)._


== Upgrade Notice ==

= 1.3.1 =
Trivial update: noted compatibility through WP 5.7+ and updated copyright date (2021)

= 1.3 =
Minor update: Added hook for customizing post type support, updated a few URLs to be HTTPS, added TODO.md, and noted compatibility through WP 5.4+.

= 1.2.2 =
Trivial update: noted compatibility through WP 5.3+, add a few more unit tests, and updated copyright date (2020)

= 1.2.1 =
Trivial update: modernized unit tests and noted compatibility through WP 5.2+

= 1.2 =
Recommended update: fix so that the override setting is available in the block editor (as a metabox), modified post meta registeration, tweaked plugin initialization process, noted compatibility through WP 5.1+, updated copyright date (2019), more.

= 1.1.1 =
Trivial update: noted compatibility through WP 4.9+, added README.md for GitHub, and updated copyright date (2018)

= 1.1 =
Minor update: register meta field via `register_meta()` (but do not show in REST API), noted compatibility through WP 4.7+, dropped compatibility with versions of WP older than 4.6, and updated copyright date

= 1.0 =
Initial public release.
