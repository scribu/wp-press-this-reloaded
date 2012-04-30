<?php
/*
Plugin Name: Press This Reloaded
Version: 1.0.3
Description: Press This, using the regular Add New Post screen
Author: scribu
Author URI: http://scribu.net
Plugin URI: http://scribu.net/wordpress/press-this-reloaded
*/

class Press_This_Reloaded {

	private static $title;
	private static $content;

	function init() {
		add_filter('shortcut_link', array( __CLASS__, 'shortcut_link'));
		add_filter('redirect_post_location', array(__CLASS__, 'redirect'));

		if ( isset($_GET['u']) ) {
			add_action('load-post-new.php', array( __CLASS__, 'load'));
			add_action('load-post.php', array( __CLASS__, 'load'));
		}
	}

	function shortcut_link($link) {
		$link = str_replace('press-this.php', 'post-new.php', $link);
		$link = str_replace('width=720', 'width=810', $link);

		return $link;
	}

	function redirect($location) {
		$referrer = wp_get_referer();

		if ( false !== strpos($referrer, '?u=' ) || false !== strpos($referrer, '&u=' ) )
			$location = add_query_arg('u', 1, $location);

		return $location;
	}

	function load() {
		$title = isset( $_GET['t'] ) ? trim( strip_tags( html_entity_decode( stripslashes( $_GET['t'] ) , ENT_QUOTES) ) ) : '';

		$url = isset($_GET['u']) ? esc_url($_GET['u']) : '';
		$url = wp_kses(urldecode($url), null);

		$selection = '';
		if ( !empty($_GET['s']) ) {
			$selection = str_replace('&apos;', "'", stripslashes($_GET['s']));
			$selection = trim( htmlspecialchars( html_entity_decode($selection, ENT_QUOTES) ) );
		}

		self::$content = '';
		if ( !empty($selection) ) {
			self::$content  = "<blockquote>$selection</blockquote>\n\n";
			self::$content .= __('via ') . sprintf( "<a href='%s'>%s</a>.</p>", esc_url( $url ), esc_html( $title ) );
		} else {
			self::$content = $url;
		}

		self::$title = $title;

		add_action('admin_print_styles', array( __CLASS__, 'style'));

		add_filter('default_title', array(__CLASS__, 'default_title'));
		add_filter('default_content', array(__CLASS__, 'default_content'));

		add_filter( 'show_admin_bar', '__return_false' );
	}

	function default_title() {
		return self::$title;
	}

	function default_content() {
		return self::$content;
	}

	function style() {
?>
<style type="text/css">
/* hide the header */
#wphead, #screen-meta, #icon-edit, h2 {display: none !important}

/* hide the menu */
#wpbody {margin-left:7px !important}

/* hide the footer */
#footer {display: none !important}
#wpcontent {padding-bottom: 0 !important}
#normal-sortables {margin-bottom: -20px !important}
</style>
<?php
	}
}

Press_This_Reloaded::init();

