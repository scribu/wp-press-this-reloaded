	var photostorage = false;
	
	var wpActiveEditor = 'content';

	function insert_plain_editor(text) {
		if ( typeof(QTags) != 'undefined' )
			QTags.insertContent(text);
	}
	function set_editor(text) {
		if ( '' == text || '<p></p>' == text )
			text = '<p><br /></p>';

		if ( tinyMCE.activeEditor )
			tinyMCE.execCommand('mceSetContent', false, text);
	}
	function insert_editor(text) {
		
		if ( '' != text && tinyMCE.activeEditor && ! tinyMCE.activeEditor.isHidden()) {
			tinyMCE.execCommand('mceInsertContent', false, '<p>' + decodeURI(tinymce.DOM.decode(text)) + '</p>', {format : 'raw'});
		} else {
			insert_plain_editor(decodeURI(text));
		}
	}
	function append_editor(text) {
		if ( '' != text && tinyMCE.activeEditor && ! tinyMCE.activeEditor.isHidden()) {
			tinyMCE.execCommand('mceSetContent', false, tinyMCE.activeEditor.getContent({format : 'raw'}) + '<p>' + text + '</p>');
		} else {
			insert_plain_editor(text);
		}
	}

	function show(tab_name) {
		jQuery('#extra-fields').html('');
		hideToolbar( true );
		
		switch(tab_name) {
			case 'video' :
				
				jQuery('#extra-fields').load(PTReloaded.pressThisUrl, { ajax: 'video', s: PTReloaded.content}, function() {
					jQuery('#embed-code').prepend(PTReloaded.content);
				});
				jQuery('#extra-fields').fadeIn();
				
				
				return false;
				break;
			case 'photo' :
				function setup_photo_actions() {
					
					jQuery('.close').click(function() {
						jQuery('#extra-fields').hide();
						jQuery('#extra-fields').html('');
						hideToolbar( false );
					});
					jQuery('.refresh').click(function() {
						photostorage = false;
						show('photo');
					});
					
					jQuery('#waiting').hide();
					
					if ( hasImages )
						jQuery('#extra-fields').fadeIn();
					else if ( firstTime ){
						hideToolbar( false );
						firstTime = false;
					}
						
					//jQuery('#extra-fields').show();
				}

				jQuery('#waiting').show();
				if(photostorage == false) {
					jQuery.ajax({
						type: "GET",
						cache : false,
						url: PTReloaded.pressThisUrl,
						data: "ajax=photo_js&u="+PTReloaded.urlEncoded,
						dataType : "script",
						success : function(data) {
							eval(data);
							photostorage = jQuery('#extra-fields').html();
							setup_photo_actions();
						}
					});
				} else {
					jQuery('#extra-fields').html(photostorage);
					setup_photo_actions();
				}
				return false;
				break;
		}
	}
	function hideToolbar( value ){
		if ( value )
			jQuery('#wp-content-media-buttons').fadeOut('fast');
		else
			jQuery('#wp-content-media-buttons').fadeIn('fast');
	}
	jQuery(document).ready(function($) {
		
		firstTime = true;
		
		//$('#extra-fields').remove();
		$('#extra-fields').prependTo('#wp-content-wrap');
		$('#waiting').prependTo('#wp-content-wrap');
		
		//resize screen
		//window.resizeTo(740,580);
		// set button actions
		$('#photo_button').click(function() {  
			show('photo');
			jQuery('#extra-fields').fadeIn();
			return false; });
		$('#video_button').click(function() {  show('video'); return false; });
		// auto select
		 
		//show(PTReloaded.type);
		
		jQuery('#title').unbind();
		jQuery('#publish, #save').click(function() { jQuery('.press-this #publishing-actions .spinner').css('display', 'inline-block'); });

		$('#tagsdiv-post_tag, #categorydiv').children('h3, .handlediv').click(function(){
			$(this).siblings('.inside').toggle();
		});
		
		
		
		//By default lets read the images
		show('photo');
		
		
	});




