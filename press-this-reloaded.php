<?php
/*
Plugin Name: Press This Reloaded
Description: Press This, using the regular Add New Post screen
Author Name: scribu
Author URI: http://scribu.net
Version: 1.0
*/

class Press_This_Reloaded {

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
		$link = str_replace('width=720', 'width=800', $link);

		return $link;
	}

	function load() {
		add_action('admin_print_styles', array( __CLASS__, 'style'));

		add_filter('default_title', array(__CLASS__, 'default_title'));
		add_filter('default_content', array(__CLASS__, 'default_content'));
	}

	function redirect($location) {
		if ( false !== strpos(wp_get_referer(), '?u=' ) )
			$location = add_query_arg('u', '', $location);

		return $location;
	}

	function default_title() {
		$title = isset( $_GET['t'] ) ? trim( strip_tags( html_entity_decode( stripslashes( $_GET['t'] ) , ENT_QUOTES) ) ) : '';

		return $title;
	}

	function default_content() {
		$selection = '';
		if ( !empty($_GET['s']) ) {
			$selection = str_replace('&apos;', "'", stripslashes($_GET['s']));
			$selection = trim( htmlspecialchars( html_entity_decode($selection, ENT_QUOTES) ) );
		}

		if ( !empty($selection) ) {
			$selection = preg_replace('/(\r?\n|\r)/', '</p><p>', $selection);
			$selection = '<p>' . str_replace('<p></p>', '', $selection) . '</p>';
		}

		if ( !empty($selection) )
			return $selection;

		$url = isset($_GET['u']) ? esc_url($_GET['u']) : '';
		$url = wp_kses(urldecode($url), null);

		// leave the embeding to the WP_Embed class
		return $url;
	}

	function style() {
?>
<style type="text/css">
/* hide the header */
#wphead, #screen-meta, #icon-edit, h2 {display: none !important}

/* hide the menu */
#wpbody {margin-left:10px !important}

/* hide the footer */
#footer {display: none !important}
#wpcontent {padding-bottom: 0 !important}
#normal-sortables {margin-bottom: -20px !important}
</style>
<?php
	}
}

Press_This_Reloaded::init();

