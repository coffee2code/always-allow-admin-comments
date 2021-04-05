# TODO

The following list comprises ideas, suggestions, and known issues, all of which are in consideration for possible implementation in future releases.

***This is not a roadmap or a task list.*** Just because something is listed does not necessarily mean it will ever actually get implemented. Some might be bad ideas. Some might be impractical. Some might either not benefit enough users to justify the effort or might negatively impact too many existing users. Or I may not have the time to devote to the task.

* Add template tag that allows checking if commenting is enabled due to this plugin. (basically the value `comments_open()` would return if this plugin wasn't active) perhaps `comments_open_raw()`
* Rename meta key to reflect its meaning rather than simply being the name of the plugin. This would require back-compatibility handling, which may not make this worthwhile.
* Add support for new block editor (aka Gutenberg) (once it becomes possible to alter the contents of the discussion panel)
* Check if post type supports comments before adding checkbox to Discussion box?
* Flip order of screenshots so the current (WP 5.0+) screenshot appears first


Feel free to make your own suggestions or champion for something already on the list (via the [plugin's support forum on WordPress.org](https://wordpress.org/support/plugin/always-allow-admin-comments/) or on [GitHub](https://github.com/coffee2code/always-allow-admin-comments/) as an issue or PR).