=== Mars Sol Date ===
Contributors:      kraftbj
Tags:              block
Stable tag:        0.1.0
License:           GPL-2.0-or-later
Tested up to:      6.9
Requires at least: 5.0
Requires PHP:      7.2

Display post publication dates in Martian sols, calculated from your site's first post using NASA's sol definition.

== Description ==

The Mars Sol Date block lets you display the post's published date in terms of Martian sols, starting from Sol 1 at the first post on your site. 
A Martian sol is approximately 24 hours, 39 minutes, and 35 seconds long. 
Perfect for science blogs and Mars enthusiasts, the block automatically calculates and displays something like “Sol 51”.

How it works:
- Sol 1 is the timestamp of your site's first published post.
- Each Martian sol is 24h 39m 35s (88775 Earth seconds).
- For each post, the block computes the sol number based on the time difference between that post and the site's first post.

No configuration required — just insert the block wherever you wish to show the sol date!

== Installation ==

1. Upload the plugin files to the `/wp-content/plugins/mars-sol-date` directory, or install the plugin through the WordPress plugins screen directly.
2. Activate the plugin through the 'Plugins' screen in WordPress.

== Frequently Asked Questions ==

= How is Sol 1 determined? =
It's the date and time of the very first (oldest) published post on your site.

= Will this block update automatically for new posts? =
Yes, the sol number is calculated dynamically for each post.

== Screenshots ==

1. Example of a Mars Sol Date block displaying "Sol 42" in the content.

== Upgrade Notice ==

= 0.1.0 =
Initial release of the Mars Sol Date block plugin.

== Changelog ==

= 0.1.0 =
* Initial release.
