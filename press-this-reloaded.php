<?php
/*
  Plugin Name: Press This Reloaded
  Version: 1.1
  Description: Press This, using the regular Add New Post screen
  Author: scribu, mustela
  Plugin URI: http://wordpress.org/extend/plugins/press-this-reloaded/
 */

class Press_This_Reloaded {

	private static $title;
	private static $content;
	private static $url;
	const plugin_domain = 'press-this-reloaded';

	function init() {

		add_filter( 'shortcut_link', array( __CLASS__, 'shortcut_link' ) );
		add_filter( 'redirect_post_location', array( __CLASS__, 'redirect' ) );



		if ( isset( $_GET[ 'u' ] ) ) {
			add_action( 'load-post-new.php', array( __CLASS__, 'load' ) );
			add_action( 'load-post.php', array( __CLASS__, 'load' ) );

			add_action( 'admin_print_scripts-post-new.php', array( __CLASS__, 'add_scripts' ) );
			add_action( 'admin_print_scripts-post.php', array( __CLASS__, 'add_scripts' ) );

			//remove_action( 'media_buttons', 'media_buttons',1 );
			add_action( 'media_buttons', array( __CLASS__, 'press_this_media_buttons' ), 11 );

		}
		elseif ( isset($_REQUEST[ 'ajax' ]) ) { // this is for video only
			add_action( 'load-post-new.php', array( __CLASS__, 'manageAjaxRequest' ) );
		}

	}


	public static function manageAjaxRequest() {

		$selection = '';
		if ( !empty($_GET['s']) ) {
			$selection = str_replace('&apos;', "'", stripslashes($_GET['s']));
			$selection = trim( htmlspecialchars( html_entity_decode($selection, ENT_QUOTES) ) );
		}

		if ( ! empty($selection) ) {
			$selection = preg_replace('/(\r?\n|\r)/', '</p><p>', $selection);
			$selection = '<p>' . str_replace('<p></p>', '', $selection) . '</p>';
		}

		$url = isset( $_GET[ 'u' ] ) ? esc_url( $_GET[ 'u' ] ) : '';
		$image = isset( $_GET[ 'i' ] ) ? $_GET[ 'i' ] : '';


		if ( !empty( $_REQUEST[ 'ajax' ] ) ) {
			switch ( $_REQUEST[ 'ajax' ] ) {
				case 'video':
					?>
					<script type="text/javascript">
						/* <![CDATA[ */
						jQuery('.select').click(function() {
							append_editor(jQuery('#embed-code').val());
							jQuery('#extra-fields').hide();
							jQuery('#extra-fields').html('');
							hideToolbar(false);
						});
						jQuery('.close').click(function() {
							jQuery('#extra-fields').hide();
							jQuery('#extra-fields').html('');
							hideToolbar(false);
						});
						/* ]]> */
					</script>
					<div class="postbox">
						<h2><label for="embed-code"><?php _e( 'Embed Code', self::plugin_domain) ?></label></h2>
						<div class="inside">
							<textarea name="embed-code" id="embed-code" rows="8" cols="40"><?php echo esc_textarea( $selection ); ?></textarea>
							<p id="options"><a href="#" class="select button"><?php _e( 'Insert Video', self::plugin_domain ); ?></a> <a href="#" class="close button"><?php _e( 'Cancel', self::plugin_domain ); ?></a></p>
						</div>
					</div>
					<?php
					break;

				case 'photo_thickbox':
					?>
					<script type="text/javascript">
						/* <![CDATA[ */
						jQuery('.cancel').click(function() {
							tb_remove();
						});
						jQuery('.select').click(function() {
							image_selector(this);
						});
						/* ]]> */
					</script>
					<h3 class="tb"><label for="tb_this_photo_description"><?php _e( 'Description', self::plugin_domain ) ?></label></h3>
					<div class="titlediv">
						<div class="titlewrap">
							<input id="tb_this_photo_description" name="photo_description" class="tb_this_photo_description tbtitle text" onkeypress="if (event.keyCode == 13)
								image_selector(this);" value="<?php echo esc_attr( self::$title ); ?>"/>
						</div>
					</div>

					<p class="centered">
						<input type="hidden" name="this_photo" value="<?php echo esc_attr( $image ); ?>" id="tb_this_photo" class="tb_this_photo" />
						<a href="#" class="select">
							<img src="<?php echo esc_url( $image ); ?>" alt="<?php echo esc_attr( __( 'Click to insert.', self::plugin_domain ) ); ?>" title="<?php echo esc_attr( __( 'Click to insert.', self::plugin_domain ) ); ?>" />
						</a>
					</p>

					<p id="options"><a href="#" class="select button"><?php _e( 'Insert Image', self::plugin_domain ); ?></a> <a href="#" class="cancel button"><?php _e( 'Cancel', self::plugin_domain ); ?></a></p>
					<?php
					break;
				case 'photo_images':

					/**
					 * Retrieve all image URLs from given URI.
					 *
					 * @package WordPress
					 * @subpackage Press_This
					 * @since 2.6.0
					 *
					 * @param string $uri
					 * @return string
					 */
					function get_images_from_uri( $uri ) {
						$uri = preg_replace( '/\/#.+?$/', '', $uri );
						if ( preg_match( '/\.(jpe?g|jpe|gif|png)\b/i', $uri ) && !strpos( $uri, 'blogger.com' ) )
							return "'" . esc_attr( html_entity_decode( $uri ) ) . "'";
						$content = wp_remote_fopen( $uri );
						if ( false === $content )
							return '';
						$host = parse_url( $uri );
						$pattern = '/<img ([^>]*)src=(\"|\')([^<>\'\"]+)(\2)([^>]*)\/*>/i';
						$content = str_replace( array( "\n", "\t", "\r" ), '', $content );
						preg_match_all( $pattern, $content, $matches );
						if ( empty( $matches[ 0 ] ) )
							return '';
						$sources = array( );
						foreach ( $matches[ 3 ] as $src ) {
							// if no http in url
							if ( strpos( $src, 'http' ) === false )
							// if it doesn't have a relative uri
								if ( strpos( $src, '../' ) === false && strpos( $src, './' ) === false && strpos( $src, '/' ) === 0 )
									$src = 'http://' . str_replace( '//', '/', $host[ 'host' ] . '/' . $src );
								else
									$src = 'http://' . str_replace( '//', '/', $host[ 'host' ] . '/' . dirname( $host[ 'path' ] ) . '/' . $src );
							$sources[ ] = esc_url( $src );
						}
						return "'" . implode( "','", $sources ) . "'";
					}

					$url = wp_kses( urldecode( $url ), null );
					echo 'new Array(' . get_images_from_uri( $url ) . ')';
					break;

				case 'photo_js':
					?>
					// gather images and load some default JS
					var last = null
					var img, img_tag, aspect, w, h, skip, i, strtoappend = "",hasImages = true;
					if(photostorage == false) {
					var my_src = eval(
					jQuery.ajax({
					type: "GET",
					url: "<?php echo esc_url( $_SERVER[ 'PHP_SELF' ] ); ?>",
					cache : false,
					async : false,
					data: "ajax=photo_images&u=<?php echo urlencode( $url ); ?>",
					dataType : "script"
					}).responseText
					);
					if(my_src.length == 0) {
					var my_src = eval(
					jQuery.ajax({
					type: "GET",
					url: "<?php echo esc_url( $_SERVER[ 'PHP_SELF' ] ); ?>",
					cache : false,
					async : false,
					data: "ajax=photo_images&u=<?php echo urlencode( $url ); ?>",
					dataType : "script"
					}).responseText
					);
					if(my_src.length == 0) {
					hasImages = false;
					strtoappend = '<?php _e( 'Unable to retrieve images or no images on page.', self::plugin_domain ); ?>';
					}
					}
					}
					for (i = 0; i < my_src.length; i++) {
					img = new Image();
					img.src = my_src[i];
					img_attr = 'id="img' + i + '"';
					skip = false;

					maybeappend = '<a href="?ajax=photo_thickbox&amp;i=' + encodeURIComponent(img.src) + '&amp;u=<?php echo urlencode( $url ); ?>&amp;height=400&amp;width=640" title="" class="thickbox"><img src="' + img.src + '" ' + img_attr + '/></a>';

					if (img.width && img.height) {
					if (img.width >= 30 && img.height >= 30) {
					aspect = img.width / img.height;
					scale = (aspect > 1) ? (71 / img.width) : (71 / img.height);

					w = img.width;
					h = img.height;

					if (scale < 1) {
					w = parseInt(img.width * scale);
					h = parseInt(img.height * scale);
					}
					img_attr += ' style="width: ' + w + 'px; height: ' + h + 'px;"';
					strtoappend += maybeappend;
					}
					} else {
					strtoappend += maybeappend;
					}
					}

					function pick(img, desc) {
					if (img) {
					if('object' == typeof jQuery('.photolist input') && jQuery('.photolist input').length != 0) length = jQuery('.photolist input').length;
					if(length == 0) length = 1;
					jQuery('.photolist').append('<input name="photo_src[' + length + ']" value="' + img +'" type="hidden"/>');
					jQuery('.photolist').append('<input name="photo_description[' + length + ']" value="' + desc +'" type="hidden"/>');
					insert_editor( "\n\n" + encodeURI('<p style="text-align: center;"><a href="<?php echo $url; ?>"><img src="' + img +'" alt="' + desc + '" /></a></p>'));
					}
					return false;
					}

					function image_selector(el) {
					var desc, src


					desc = jQuery('#tb_this_photo_description').val() || '';
					src = jQuery('#tb_this_photo').val() || ''

					tb_remove();
					pick(src, desc);
					jQuery('#extra-fields').hide();
					jQuery('#extra-fields').html('');
					hideToolbar(false);
					return false;
					}




					jQuery('#extra-fields').html('<div class="postbox"><h2><?php _e( 'Add Photos', self::plugin_domain ); ?> <small id="photo_directions">(<?php _e( "click images to select", self::plugin_domain ) ?>)</small></h2><div class="inside"><div class="titlewrap"><div id="img_container"></div></div><p id="options"><a href="#" class="close button"><?php _e( 'Cancel', self::plugin_domain ); ?></a><a href="#" class="refresh button"><?php _e( 'Refresh', self::plugin_domain ); ?></a></p></div>');


					//var display = hasImages?'':' style="display:none;"';

						jQuery('#img_container').html(strtoappend);
						<?php
						break;
				}
				die();
			}
		}



	function press_this_media_buttons() {

		?>
		<?php _e( 'Add media from page:', self::plugin_domain ); ?>

		<?php
		if ( current_user_can( 'upload_files' ) ) {
			?>
			<a id="photo_button" title="<?php esc_attr_e( 'Insert an Image', self::plugin_domain ); ?>" href="#">
				<img alt="<?php esc_attr_e( 'Insert an Image', self::plugin_domain ); ?>" src="<?php echo esc_url( admin_url( 'images/media-button-image.gif?ver=20100531' ) ); ?>"/></a>
			<?php
		}
		?>
		<a id="video_button" title="<?php esc_attr_e( 'Embed a Video', self::plugin_domain ); ?>" href="#"><img alt="<?php esc_attr_e( 'Embed a Video', self::plugin_domain ); ?>" src="<?php echo esc_url( admin_url( 'images/media-button-video.gif?ver=20100531' ) ); ?>"/></a>
		<div id="waiting" style="display: none"><span class="spinner"></span> <span><?php esc_html_e( 'Loading...', self::plugin_domain ); ?></span></div>

		<div id="extra-fields" style='clear:both; display:none'>

		</div>

		<?php
	}

	function add_scripts() {

		wp_enqueue_script( 'press-this-reloaded', plugin_dir_url( __FILE__ ) . '/press-this-reloaded.js', 'jquery' );

		$type = "";

		if ( preg_match( "/youtube\.com\/watch/i", self::$url ) )
			$type = 'video';
		elseif ( preg_match( "/vimeo\.com\/[0-9]+/i", self::$url ) )
			$type = 'video';
		elseif ( preg_match( "/flickr\.com/i", self::$url ) )
			$type = 'photo';

		$data = array(
			'pressThisUrl' => admin_url( 'post-new.php' ),
			'content' => self::$content,
			'url' => self::$url,
			'urlEncoded' => urlencode( self::$url ),
			'type' => $type
		);

		wp_localize_script( 'press-this-reloaded', 'PTReloaded', $data );
	}

	function shortcut_link( $link ) {
		$link = str_replace( 'press-this.php', 'post-new.php', $link );
		$link = str_replace( 'width=720', 'width=810', $link );

		return $link;
	}

	function redirect( $location ) {
		$referrer = wp_get_referer();

		if ( false !== strpos( $referrer, '?u=' ) || false !== strpos( $referrer, '&u=' ) )
			$location = add_query_arg( 'u', 1, $location );

		return $location;
	}

	function load() {



		$title = isset( $_GET[ 't' ] ) ? trim( strip_tags( html_entity_decode( stripslashes( $_GET[ 't' ] ), ENT_QUOTES ) ) ) : '';

		self::$url = isset( $_GET[ 'u' ] ) ? esc_url( $_GET[ 'u' ] ) : '';
		self::$url = wp_kses( urldecode( self::$url ), null );

		$selection = '';
		if ( !empty( $_GET[ 's' ] ) ) {
			$selection = str_replace( '&apos;', "'", stripslashes( $_GET[ 's' ] ) );
			$selection = trim( htmlspecialchars( html_entity_decode( $selection, ENT_QUOTES ) ) );
		}

		self::$content = '';
		if ( !empty( $selection ) ) {
			self::$content = "<blockquote>$selection</blockquote>\n\n";
			self::$content .= __( 'via ', self::plugin_domain ) . sprintf( "<a href='%s'>%s</a>.</p>", esc_url( self::$url ), esc_html( $title ) );
		} else {
			self::$content = self::$url;
		}

		self::$title = $title;

		add_action( 'admin_print_styles', array( __CLASS__, 'style' ) );

		add_filter( 'default_title', array( __CLASS__, 'default_title' ) );
		add_filter( 'default_content', array( __CLASS__, 'default_content' ) );

		add_filter( 'show_admin_bar', '__return_false' );

		self::manageAjaxRequest();
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




