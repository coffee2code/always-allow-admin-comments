=== Always Allow Admin Comments ===
Contributors: coffee2code
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=6ARCFJ9TX3522
Tags: comment, comments, comments_open, commenting, admin, post, page, coffee2code
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Requires at least: 4.6
Tested up to: 5.3
Stable tag: 1.2.1

Allow an admin user (when logged in) to always be able to comment on a post, even if comments are closed for the post.

== Description ==

This plugin enables a user with the administrator role the ability to comment on any post or page, even if the comments for that post or page are closed. When the plugin is active, this behavior is automatically enabled.

Administrators can be explicitly prevented from commenting on specific posts via two approaches:

* When creating or editing a post, in the "Prevent Admin Comments" metabox (in the block editor) or the "Discussion" metabox (in the classic editor) there is a checkbox labeled "Prevent administrators from commenting" that only administrators can access. Checking the checkbox will prevent administrators from commenting on the post even though this plugin is active. (If the metabox isn't visible for you when using the classic editor, then expand the "Screen Options" slide-down panel on the upper-right of the page.)
* Programmatically, via the use of the 'c2c_admin_can_always_comment_disable' filter. This can be used be provide more fine-grained access control and contextual handling. See the "Hooks" section for documentation on the filter's use.

Links: [Plugin Homepage](http://coffee2code.com/wp-plugins/always-allow-admin-comments/) | [Plugin Directory Page](https://wordpress.org/plugins/always-allow-admin-comments/) | [GitHub](https://github.com/coffee2code/always-allow-admin-comments/) | [Author Homepage](http://coffee2code.com)


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

The plugin is further customizable via one hook. Such code should ideally be put into a mu-plugin or site-specific plugin (which is beyond the scope of this readme to explain).

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


== Changelog ==

= 1.2.1 (2019-06-18) =
* Change: Update unit test install script and bootstrap to use latest WP unit test repo
* Change: Note compatibility through WP 5.2+
* Change: Add link to CHANGELOG.md in README.md
* Fix: Correct typo in GitHub URL
* Fix: Use full path to CHANGELOG.md in the Changelog section of readme.txt

= 1.2 (2019-03-20) =
Highlights:

This release mainly adds support for the block editor (aka Gutenberg) via an interim metabox "Prevent Admin Comments". (The block editor does not currently support adding the plugin's checkbox in the "Discussion" panel as expected.) Otherwise, there were a number of behind-the-scenes code and documentation changes.

Details:

* New: Add `do_meta_box()` for interim metabox for block editor (aka Gutenberg) support
* New: Add `can_show_ui()` to determine if the UI should generally be shown
* New: Add CHANGELOG.md file and move all but most recent changelog entries into it
* New: Add inline documentation for hook
* New: Add .gitignore file
* New: Add screenshot of new block editor metabox
* Change: Modify how the meta field is registered:
    * Use `register_post_meta()` instead of `register_meta()`, if available
    * Explicitly register meta for each post type that supports having comments
    * Set `show_in_rest` to true
    * Set `type` to boolean
    * Add callbacks to `auth_callback` and `sanitize_callback`
    * Register meta on `init` instead of `plugins_loaded`
* Change: Revamp `display_option()` to operate in multiple contexts
    * Make `$post` argument optional, defaulting to global post
    * Improve styling when metabox appears on the right
    * Use `sprintf()` instead of string concatenation to build output strings
    * Move styles into `style` tag instead of being inline styles
* Change: Add README link to plugin's page in Plugin Directory
* Change: Split paragraph in README.md's "Support" section into two
* Change: Initialize plugin on 'plugins_loaded' action instead of on load
* Change: Merge `plugins_loaded()` into constructor
* Change: Tweak plugin description
* Change: Rename readme.txt section from 'Filters' to 'Hooks'
* Change: Modify formatting of hook name in readme to prevent being uppercased when shown in the Plugin Directory
* Change: Note compatibility through WP 5.1+
* Change: Update copyright date (2019)
* Change: Update License URI to be HTTPS

= 1.1.1 (2017-11-07) =
* New: Add README.md
* Change: Minor tweak to plugin description
* Change: Minor whitespace changes to unit test bootstrap
* Change: Add GitHub link to readme
* Change: Note compatibility through WP 4.9+
* Change: Update copyright date (2018)

_Full changelog is available in [CHANGELOG.md](https://github.com/coffee2code/always-allow-admin-comments/blob/master/CHANGELOG.md)._


== Upgrade Notice ==

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
