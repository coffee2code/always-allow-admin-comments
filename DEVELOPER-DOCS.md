# Developer Documentation

## Hooks

The plugin is further customizable via two hooks. Such code should ideally be put into a mu-plugin or site-specific plugin (which is beyond the scope of this readme to explain).

### `c2c_always_allow_admin_comments_disable` _(filter)_

The `c2c_always_allow_admin_comments_disable` filter allows you to customize whether comments should actually be disabled for a particular admin user. By default, all admin users can always comment on posts and pages. Using this filter, you have finer-grained controls to override this behavior. It takes into account the checkbox provided per post to explicitly prevent admin comments.

NOTE: This filter is only used if comments for the post are closed and the current user is an admin. This filter is only to override the behavior of the plugin and is not a general purpose filter for enabling or disabling comments.

#### Arguments:

* **$status** _(bool)_: The comment status of the current post for the current user.
* **$post_id** _(int)_: The ID of the post whose comments are being checked.

#### Return:

* _(bool)_: True if admin commenting is disabled, false if admin can comment.

#### Example:

```php
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
```

### `c2c_always_allow_admin_comments_post_types` _(filter)_

The `c2c_always_allow_admin_comments_post_types` filter allows you to customize which post types are supported by the plugin. By default, all post types that have registered support for the 'comments' feature are supported.

#### Arguments:

* **$post_types** _(array)_: Supported post types. Default is all the post types that support 'comments' as a feature.

#### Return:

* _(array)_: Array of supported post types.

#### Example:

```php
// Disable support for Always Allow Admin Comments for pages.
add_filter( 'c2c_always_allow_admin_comments_post_types', function( $post_types ) {
	if ( false !== $i = array_search( 'page', $post_types ) ) {
		unset( $post_types[ $i ] );
	}
	return $post_types;
} );
```
