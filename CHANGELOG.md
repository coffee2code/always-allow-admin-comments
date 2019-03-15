# Changelog

## 1.1.1 _(2017-11-07)_
* New: Add README.md
* Change: Minor tweak to plugin description
* Change: Minor whitespace changes to unit test bootstrap
* Change: Add GitHub link to readme
* Change: Note compatibility through WP 4.9+
* Change: Update copyright date (2018)

## 1.1 _(2017-01-23)_
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

## 1.0 _(2016-03-08)_
* Initial public release
