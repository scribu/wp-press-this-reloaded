=== Press This Reloaded ===
Contributors: scribu
Donate link: http://scribu.net/paypal
Tags: bookmarklet, press-this, pressthis
Requires at least: 3.0
Tested up to: 3.1-alpha
Stable tag: trunk

Press This, using the regular Add New Post screen

== Description ==

[Press This](http://codex.wordpress.org/Press_This) is neat, but wouldn't it be a lot neater if you had access to all the functionality you have on the normal post editing screen?

With this plugin, you will have access to all the meta boxes, not just Categories and Tags.

Also, plain URLs are inserted in the post content, letting the [Embeds](http://codex.wordpress.org/Embeds) feature do the rest.

**Important:** After activating the plugin, you will have to re-add the bookmarklet, from WP Admin -> Tools.

Links: [Plugin News](http://scribu.net/wordpress/press-this-reloaded) | [Author's Site](http://scribu.net)

== Installation ==

You can either install it automatically from the WordPress admin, or do it manually:

1. Unzip the press-this-reloaded archive and put the folder into your plugins folder (/wp-content/plugins/).
1. Activate the plugin from the Plugins menu.

== Frequently Asked Questions ==

= Error on activation: "Parse error: syntax error, unexpected..." =

Make sure your host is running PHP 5. The only foolproof way to do this is to add this line to wp-config.php (after the opening `<?php` tag):

`var_dump(PHP_VERSION);`
<br>

== Screenshots ==

1. The UI

== Changelog ==

= 1.0.2 =
* add via link when there's a text selection
* wrap text selection in a &lt;blockquote&gt;

= 1.0.1 =
* fixed redirection on subsequent saves

= 1.0 =
* initial release
* [more info](http://scribu.net/wordpress/press-this-reloaded/ptr-1-0.html)

