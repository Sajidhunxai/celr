
<div id="wp-media-grid" class="wrap">
  
  <div class="mlfp-container">
    
    <?php echo $this->display_mlfp_header() ?>
    
    <h1 id="mlfp-page-title"><?php esc_html_e('Thumbnails', 'maxgalleria-media-library' ); ?></h1>
    <nav class="nav-tab-wrapper">
      <!--<a id="mlfp-help"><img id="mlfp-help-icon" src="< php echo MAXGALLERIA_MEDIA_LIBRARY_PLUGIN_URL ?>/images/mlfp-help.png" alt="help icon" width:="28" height="28"></a>-->
    </nav>
<!--    <div id="mlfp-help-panel" style="display:none">
      <div id="mlfp-help-panel-inner" >
        <div>Help Panel</div>
      </div>
    </div>      -->
    
    <div class="wrap">
          
      <div class="tab-content">
    <?php  
      
		global $wpdb;
    
    $allowed_html = array(
      'a' => array(
        'href' => array(),
        'id' => array()
      )    
    );                  
    

		?>

      <div id="message" class="updated fade" style="display:none"></div>
            
<?php

		// If the button was clicked
		if ( ! empty( $_POST['regenerate-thumbnails'] ) || ! empty( $_REQUEST['ids'] ) ) {
			// Capability check
			if ( ! current_user_can( $this->capability ) )
				wp_die( esc_html__( 'Cheatin&#8217; uh?' ) );

			// Form nonce check
			check_admin_referer(MAXGALLERIA_MEDIA_LIBRARY_NONCE);

			// Create the list of image IDs
			if ( ! empty( $_REQUEST['ids'] ) ) {
				$images = array_map( 'intval', explode( ',', trim( sanitize_text_field($_REQUEST['ids']), ',' ) ) );
				$ids = implode( ',', $images );
			} else {
				if ( ! $images = $wpdb->get_results( "SELECT ID FROM $wpdb->posts WHERE post_type = 'attachment' AND post_mime_type LIKE 'image/%' ORDER BY ID DESC" ) ) {
					echo '	<p>' . sprintf( esc_html__( "Unable to find any images. Are you sure", 'maxgalleria-media-library') . "<a href='%s'>" . esc_html__(" some exist? ", 'maxgalleria-media-library' ) . "</a>",  esc_url_raw(admin_url( 'upload.php?post_mime_type=image'))) . "</p></div>";
					return;
				}

				// Generate the list of IDs
				$ids = array();
				foreach ( $images as $image )
					$ids[] = $image->ID;
				$ids = implode( ',', $ids );
			}

			echo '	<p id="wait-message">' . esc_html__( "Please wait while the thumbnails are regenerated. This may take a while.", 'maxgalleria-media-library' ) . '</p>';

			$count = count( $images );

			$text_goback = ( ! empty( $_GET['goback'] ) ) ? esc_html__('To go back to the previous page, ', 'maxgalleria-media-library') . '<a href="javascript:history.go(-1)">click here</a>.' : '';
			$text_failures = sprintf( __( 'All done! %1$s image(s) were successfully resized in %2$s seconds and there were %3$s failure(s). To try regenerating the failed images again, <a href="%4$s">click here</a>. %5$s', 'maxgalleria-media-library' ), "' + rt_successes + '", "' + rt_totaltime + '", "' + rt_errors + '", esc_url( wp_nonce_url( admin_url( 'tools.php?page=mlp-regenerate-thumbnails&goback=1' ), 'mlp-regenerate-thumbnails' ) . '&ids=' ) . "' + rt_failedlist + '", $text_goback );
			$text_nofailures = sprintf( __( 'All done! %1$s image(s) were successfully resized in %2$s seconds and there were 0 failures. %3$s', 'maxgalleria-media-library' ), "' + rt_successes + '", "' + rt_totaltime + '", $text_goback );
?>

	<noscript><p><em><?php esc_html_e( 'You must enable Javascript in order to proceed!', 'maxgalleria-media-library' ) ?></em></p></noscript>

	<div id="regenthumbs-bar" style="position:relative;height:25px;">
		<div id="regenthumbs-bar-percent" style="position:absolute;left:50%;top:50%;width:300px;margin-left:-150px;height:25px;margin-top:-9px;font-weight:bold;text-align:center;"></div>
	</div>

	<p><input type="button" class="button hide-if-no-js" name="regenthumbs-stop" id="regenthumbs-stop" value="<?php esc_html_e( 'Abort Resizing Images', 'maxgalleria-media-library' ) ?>" /></p>

	<h3 class="title"><?php esc_html_e( 'Debugging Information', 'maxgalleria-media-library' ) ?></h3>

	<p>
    <?php echo esc_html( __( 'Total Images: ', 'maxgalleria-media-library' ) . (int) $count) ?><br />
    <?php echo esc_html__( 'Images Resized: ', 'maxgalleria-media-library' ) . '<span id="regenthumbs-debug-successcount">0</span>' ?><br />
    <?php echo esc_html__( 'Resize Failures: ', 'maxgalleria-media-library' ) . '<span id="regenthumbs-debug-failurecount">0</span>' ?>
	</p>

	<ol id="regenthumbs-debuglist">
		<li style="display:none"></li>
	</ol>

	<script type="text/javascript">
	// <![CDATA[
		jQuery(document).ready(function($){
			var i;
			var rt_images = [<?php echo esc_attr($ids) ?>];
			var rt_total = rt_images.length;
			var rt_count = 1;
			var rt_percent = 0;
			var rt_successes = 0;
			var rt_errors = 0;
			var rt_failedlist = '';
			var rt_resulttext = '';
			var rt_timestart = new Date().getTime();
			var rt_timeend = 0;
			var rt_totaltime = 0;
			var rt_continue = true;

			// Create the progress bar
			$("#regenthumbs-bar").progressbar();
			$("#regenthumbs-bar-percent").html( "0%" );

			// Stop button
			//$("#regenthumbs-stop").click(function() {
      $(document).on("click", "#regenthumbs-stop", function () {
				rt_continue = false;
				$('#regenthumbs-stop').val("<?php echo $this->esc_quotes( esc_html__( 'Stopping...', 'maxgalleria-media-library' ) ); ?>");
			});

			// Clear out the empty list element that's there for HTML validation purposes
			$("#regenthumbs-debuglist li").remove();

			// Called after each resize. Updates debug information and the progress bar.
			function RegenThumbsUpdateStatus( id, success, response ) {
				$("#regenthumbs-bar").progressbar( "value", ( rt_count / rt_total ) * 100 );
				$("#regenthumbs-bar-percent").html( Math.round( ( rt_count / rt_total ) * 1000 ) / 10 + "%" );
				rt_count = rt_count + 1;

				if ( success ) {
					rt_successes = rt_successes + 1;
					$("#regenthumbs-debug-successcount").html(rt_successes);
					$("#regenthumbs-debuglist").append("<li>" + response.success + "</li>");
				}
				else {
					rt_errors = rt_errors + 1;
					rt_failedlist = rt_failedlist + ',' + id;
					$("#regenthumbs-debug-failurecount").html(rt_errors);
					$("#regenthumbs-debuglist").append("<li>" + response.error + "</li>");
				}
			}

			// Called when all images have been processed. Shows the results and cleans up.
			function RegenThumbsFinishUp() {
				rt_timeend = new Date().getTime();
				rt_totaltime = Math.round( ( rt_timeend - rt_timestart ) / 1000 );

				$('#regenthumbs-stop').hide();

				if ( rt_errors > 0 ) {
					rt_resulttext = '<?php echo wp_kses($text_failures, $allowed_html) ?>';
				} else {
					rt_resulttext = '<?php echo wp_kses($text_nofailures, $allowed_html) ?>';
				}

				$("#wait-message").html("");
				$("#message").html("<p><strong>" + rt_resulttext + "</strong></p>");
				$("#message").show();
			}

			// Regenerate a specified image via AJAX
			function RegenThumbs( id ) {
				$.ajax({
					type: 'POST',
					url: ajaxurl,
					data: { action: "regeneratethumbnail", id: id },
					success: function( response ) {
						if ( response !== Object( response ) || ( typeof response.success === "undefined" && typeof response.error === "undefined" ) ) {
							response = new Object;
							response.success = false;
							response.error = "<?php printf( esc_js( __( 'The resize request was abnormally terminated (ID %s). This is likely due to the image exceeding available memory or some other type of fatal error.', 'maxgalleria-media-library' ) ), '" + id + "' ); ?>";
						}

						if ( response.success ) {
							RegenThumbsUpdateStatus( id, true, response );
						}
						else {
							RegenThumbsUpdateStatus( id, false, response );
						}

						if ( rt_images.length && rt_continue ) {
							RegenThumbs( rt_images.shift() );
						}
						else {
							RegenThumbsFinishUp();
						}
					},
					error: function( response ) {
						RegenThumbsUpdateStatus( id, false, response );

						if ( rt_images.length && rt_continue ) {
							RegenThumbs( rt_images.shift() );
						}
						else {
							RegenThumbsFinishUp();
						}
					}
				});
			}

			RegenThumbs( rt_images.shift() );
		});
	// ]]>
	</script>
<?php
		}

		// No button click? Display the form.
		else {
?>
	<form method="post" action="">
<?php wp_nonce_field(MAXGALLERIA_MEDIA_LIBRARY_NONCE) ?>

	<p><?php printf( esc_html__( "Click the button below to regenerate thumbnails for all images in the Media Library. This is helpful if you have added new thumbnail sizes to your site. Existing thumbnails will not be removed to prevent breaking any links.", 'maxgalleria-media-library' ), admin_url( 'options-media.php' ) ); ?></p>

	<p><?php printf( esc_html__( "You can regenerate thumbnails for individual images from the Media Library Folders page by checking the box below one or more images and clicking the Regenerate Thumbnails button. The regenerate operation is not reversible but you can always generate the sizes you need by adding additional thumbnail sizes to your theme.", 'maxgalleria-media-library'), admin_url( 'upload.php' ) ); ?></p>

	<p><input type="submit" class="button-primary hide-if-no-js" name="regenerate-thumbnails" id="regenerate-thumbnails" value="<?php esc_html_e( 'Regenerate All Thumbnails', 'maxgalleria-media-library' ) ?>" /></p>

	<noscript><p><em><?php esc_html_e( 'You must enable Javascript in order to proceed!', 'maxgalleria-media-library' ) ?></em></p></noscript>

	</form>
<?php
		} // End if button
?>
<!--			</div>
		</div>
	</div>--> 
    
      </div><!--tab-content-->
    </div><!--wrap-->
    
  </div><!--mlfp-container-->
    
</div><!--wp-media-grid-->
<script>
jQuery(document).ready(function(){

  jQuery(document).on("click", "#mlfp-help-icon", function (e) {
    jQuery("#mlfp-help-panel").animate({
        width: "toggle"
    });

  });  

});  
</script>   
