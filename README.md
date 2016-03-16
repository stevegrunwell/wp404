# WP404

[![Build Status](https://travis-ci.org/stevegrunwell/wp404.svg?branch=master)](https://travis-ci.org/stevegrunwell/wp404)

WP404 is meant to be a developer's best friend when tracking down vague or hard-to-reproduce 404 errors reported by clients or visitors. By hooking into the `template_redirect` action, WP404 can collect as much (or as little) information about the request as you'd like and save it to your error logs, enabling you to get all sorts of information about the request.


## Adding information to the log file

Once WP404 starts collecting information about a 404 error, it passes its report through the `wp404_report_data` filter. Once all hooked callbacks are run, the `$report` array is converted to JSON and written to the logs.

**Example:**

```php
/**
 * Save the IP address of a user when they hit a 404 page.
 *
 * @param array    $report The data that has been compiled for this 404 error.
 * @param WP_Query $wp_query The WP_Query object that determined the 404 status.
 */
function mytheme_save_user_ip( $report, $wp_query ) {
	$report['ip_address'] = $_SERVER['REMOTE_ADDR'];

	return $report;
}
add_filter( 'wp404_report_data', 'mytheme_save_user_ip', 10, 2 );
```

### Abort logging under certain conditions

If you'd like to prevent the log from being written at any point, simply return `false` on the `wp404_report_data` filter:

```php
/**
 * Don't log 404s coming from logged-in users.
 *
 * @param array    $report The data that has been compiled for this 404 error.
 * @param WP_Query $wp_query The WP_Query object that determined the 404 status.
 */
function mytheme_no_404_reports_for_known_users( $report ) {
	if ( is_user_logged_in() ) {
		return false;
	}

	return $report;
}
add_filter( 'wp404_report_data', 'mytheme_no_404_reports_for_known_users', 999999 );
```


### Built-in reporters

WP404 ships with a number of built-in reporters that you can choose to include (or not) in your reporting.

To enable any of these reporters simply add the following to your theme's functions.php file to [attach to the `wp404_report_data` filter](https://codex.wordpress.org/Function_Reference/add_filter):

```php
// WP404 configuration.
if ( function_exists( '\WP404\Core\template_redirect' ) ) {
	add_filter( 'wp404_report_data', '\WP404\Reporters\{REPORTER}', {PRIORITY}, 2 );
}
```

To remove any reporters, simply use the [`remove_filter()` function](https://codex.wordpress.org/Function_Reference/remove_filter), passing it the reporter's initial priority:

```php
remove_filter( 'wp404_report_data','\WP404\Reporters\{REPORTER}', {PRIORITY} );
```


#### `server_superglobal`

Capture the $_SERVER superglobal.

<dl>
	<dt>Enabled by default?</dt>
	<dd>Yes</dd>
	<dt>Initial priority</dt>
	<dd>10</dd>
</dl>


##### Example output

```
[$_SERVER] => Array
	(
		[SERVER_SOFTWARE] => nginx/1.9.5
		[REQUEST_URI] => /?p=66
		[PATH] => /srv/www/phpcs/scripts/:/usr/local/bin:/usr/bin:/bin
		[USER] => www-data
		[HOME] => /var/www
		[FCGI_ROLE] => RESPONDER
		[QUERY_STRING] => p=66
		[REQUEST_METHOD] => GET
		[CONTENT_TYPE] =>
		[CONTENT_LENGTH] =>
		[SCRIPT_NAME] => /index.php
		[DOCUMENT_URI] => /index.php
		[DOCUMENT_ROOT] => /srv/www/wordpress-trunk
		[SERVER_PROTOCOL] => HTTP/1.1
		[REQUEST_SCHEME] => http
		[GATEWAY_INTERFACE] => CGI/1.1
		[REMOTE_ADDR] => 192.168.50.1
		[REMOTE_PORT] => 59585
		[SERVER_ADDR] => 192.168.50.4
		[SERVER_PORT] => 80
		[SERVER_NAME] => local.wordpress-trunk.dev
		[REDIRECT_STATUS] => 200
		[SCRIPT_FILENAME] => /srv/www/wordpress-trunk/index.php
		[HTTP_HOST] => local.wordpress-trunk.dev
		[HTTP_CONNECTION] => keep-alive
		[HTTP_CACHE_CONTROL] => max-age=0
		[HTTP_ACCEPT] => text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8
		[HTTP_UPGRADE_INSECURE_REQUESTS] => 1
		[HTTP_USER_AGENT] => Mozilla/5.0 (Macintosh; Intel Mac OS X 10_11_3) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/48.0.2564.116 Safari/537.36
		[HTTP_DNT] => 1
		[HTTP_ACCEPT_ENCODING] => gzip, deflate, sdch
		[HTTP_ACCEPT_LANGUAGE] => en-US,en;q=0.8
		[PHP_SELF] => /index.php
		[REQUEST_TIME_FLOAT] => 1457042545.0064
		[REQUEST_TIME] => 1457042545
	)
```

##### Filtering the `$_SERVER` keys that are captured.

Some systems store potentially sensitive information in the `$_SERVER` superglobal (for instance, WordPress cookies that could be used to hijack sessions). To keep this *out* of the log files, WP404 will only save whitelisted keys from the $_SERVER superglobal.

If you'd like to adjust these keys, you can use the `wp404_server_superglobal_whitelisted_keys` filter:

```php
/**
 * When saving the $_SERVER superglobal, only capture the REQUEST_URI and HTTP_HOST.
 *
 * @param array $whitelist A flat array of keys within the $_SERVER superglobal array that should
 *                         be captured for 404 reports. Will be overridden by this callback.
 * @return The simplified $whitelist array.
 */
function mytheme_set_wp404_server_keys( $whitelist ) {
	return array( 'REQUEST_URI', 'HTTP_HOST' );
}
add_filter( 'wp404_server_superglobal_whitelisted_keys', 'mytheme_set_wp404_server_keys' );
```


#### `post_exists`

Try to determine if we have a post ID and, if so, get data directly from the database (bypassing any sort of cache) to get that post data.


<dl>
	<dt>Enabled by default?</dt>
	<dd>Yes</dd>
	<dt>Initial priority</dt>
	<dd>10</dd>
</dl>


##### Example output

```
[post_data] => stdClass Object
	(
		[ID] => 1
		[post_author] => 1
		[post_date] => 2014-11-11 20:46:47
		[post_date_gmt] => 2014-11-11 20:46:47
		[post_content] => Welcome to WordPress. This is your first post. Edit or delete it, then start blogging!
		[post_title] => Hello world!
		[post_excerpt] =>
		[post_status] => private
		[comment_status] => open
		[ping_status] => open
		[post_password] =>
		[post_name] => hello-world
		[to_ping] =>
		[pinged] =>
		[post_modified] => 2016-03-03 22:19:47
		[post_modified_gmt] => 2016-03-03 22:19:47
		[post_content_filtered] =>
		[post_parent] => 0
		[guid] => http://local.wordpress-trunk.dev/?p=1
		[menu_order] => 0
		[post_type] => post
		[post_mime_type] =>
		[comment_count] => 2
	)
```

#### `queries`

If the `SAVEQUERIES` constant is defined as `TRUE`, WordPress will log all the queries that have been made, which can help in some extreme debugging situations.

> **Note:** In order for this to capture any meaningful data, [`SAVEQUERIES` should be enabled within WordPress](https://codex.wordpress.org/Debugging_in_WordPress#SAVEQUERIES).


<dl>
	<dt>Enabled by default?</dt>
	<dd>No</dd>
	<dt>Initial priority</dt>
	<dd>n/a</dd>
</dl>


##### Example output

```
[queries] => Array
	(
		[0] => Array
			(
				[0] => SELECT option_name, option_value FROM wp_options WHERE autoload = 'yes'
				[1] => 0.00069403648376465
				[2] => require('wp-blog-header.php'), require_once('wp-load.php'), require_once('wp-config.php'), require_once('wp-settings.php'), wp_not_installed, is_blog_installed, wp_load_alloptions
			)

		[1] => Array
			(
				[0] =>
					SELECT ID, post_name, post_parent, post_type
					FROM wp_posts
					WHERE post_name IN ('draft-post')
					AND post_type IN ('page','attachment')

				[1] => 0.00027918815612793
				[2] => require('wp-blog-header.php'), wp, WP->main, WP->parse_request, get_page_by_path
			)

		[2] => Array
			(
				[0] => SELECT   wp_posts.* FROM wp_posts  WHERE 1=1  AND wp_posts.post_name = 'draft-post' AND wp_posts.post_type = 'post'  ORDER BY wp_posts.post_date DESC
				[1] => 0.00022292137145996
				[2] => require('wp-blog-header.php'), wp, WP->main, WP->query_posts, WP_Query->query, WP_Query->get_posts
			)

		[3] => Array
			(
				[0] => SELECT   wp_posts.* FROM wp_posts  WHERE 1=1  AND wp_posts.post_name = 'draft-post' AND wp_posts.post_type = 'post'  ORDER BY wp_posts.post_date DESC
				[1] => 0.00020909309387207
				[2] => require('wp-blog-header.php'), require_once('wp-includes/template-loader.php'), do_action('template_redirect'), call_user_func_array, WP404\Core\template_redirect, apply_filters('wp404_report_data'), call_user_func_array, WP404\Reporters\post_exists
			)

		)

	)
```

#### `wp_query`

Include the contents of the `$wp_query` object (in its entirety) in the report.

Chances are this will only be used for **extreme** 404 debugging, but it's still nice to offer.


<dl>
	<dt>Enabled by default?</dt>
	<dd>No</dd>
	<dt>Initial priority</dt>
	<dd>n/a</dd>
</dl>


##### Example output

```
[wp_query] => Array
	(
		[query] => Array
			(
				[page] =>
				[name] => this-post-does-not-exist
			)

		[query_vars] => Array
			(
				[page] => 0
				[name] => this-post-does-not-exist
				[error] =>
				[m] =>
				[p] => 0
				[post_parent] =>
				[subpost] =>
				[subpost_id] =>
				[attachment] =>
				[attachment_id] => 0
				[static] =>
				[pagename] =>
				[page_id] => 0
				[second] =>
				[minute] =>
				[hour] =>
				[day] => 0
				[monthnum] => 0
				[year] => 0
				[w] => 0
				[category_name] =>
				[tag] =>
				[cat] =>
				[tag_id] =>
				[author] =>
				[author_name] =>
				[feed] =>
				[tb] =>
				[paged] => 0
				[meta_key] =>
				[meta_value] =>
				[preview] =>
				[s] =>
				[sentence] =>
				[title] =>
				[fields] =>
				[menu_order] =>
				[category__in] => Array
					(
					)

				[category__not_in] => Array
					(
					)

				[category__and] => Array
					(
					)

				[post__in] => Array
					(
					)

				[post__not_in] => Array
					(
					)

				[post_name__in] => Array
					(
					)

				[tag__in] => Array
					(
					)

				[tag__not_in] => Array
					(
					)

				[tag__and] => Array
					(
					)

				[tag_slug__in] => Array
					(
					)

				[tag_slug__and] => Array
					(
					)

				[post_parent__in] => Array
					(
					)

				[post_parent__not_in] => Array
					(
					)

				[author__in] => Array
					(
					)

				[author__not_in] => Array
					(
					)

				[ignore_sticky_posts] =>
				[suppress_filters] =>
				[cache_results] => 1
				[update_post_term_cache] => 1
				[update_post_meta_cache] => 1
				[post_type] =>
				[posts_per_page] => 10
				[nopaging] =>
				[comments_per_page] => 50
				[no_found_rows] =>
				[order] => DESC
			)

		[tax_query] =>
		[meta_query] => Array
			(
				[queries] => Array
					(
					)

				[relation] =>
				[meta_table] =>
				[meta_id_column] =>
				[primary_table] =>
				[primary_id_column] =>
			)

		[date_query] =>
		[request] => SELECT   wp_posts.* FROM wp_posts  WHERE 1=1  AND wp_posts.post_name = 'this-post-does-not-exist' AND wp_posts.post_type = 'post'  ORDER BY wp_posts.post_date DESC
		[posts] => Array
			(
			)

		[post_count] => 0
		[current_post] => -1
		[in_the_loop] =>
		[comment_count] => 0
		[current_comment] => -1
		[found_posts] => 0
		[max_num_pages] => 0
		[max_num_comment_pages] => 0
		[is_single] =>
		[is_preview] =>
		[is_page] =>
		[is_archive] =>
		[is_date] =>
		[is_year] =>
		[is_month] =>
		[is_day] =>
		[is_time] =>
		[is_author] =>
		[is_category] =>
		[is_tag] =>
		[is_tax] =>
		[is_search] =>
		[is_feed] =>
		[is_comment_feed] =>
		[is_trackback] =>
		[is_home] =>
		[is_404] => 1
		[is_embed] =>
		[is_paged] =>
		[is_admin] =>
		[is_attachment] =>
		[is_singular] =>
		[is_robots] =>
		[is_posts_page] =>
		[is_post_type_archive] =>
		[thumbnails_cached] =>
		[updated_term_meta_cache] =>
		[updated_comment_meta_cache] =>
	)

)
```
