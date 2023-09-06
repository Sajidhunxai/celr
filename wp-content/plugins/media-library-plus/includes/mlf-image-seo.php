
<div id="wp-media-grid" class="wrap">
  
  <div class="mlfp-container">
    
    <?php echo $this->display_mlfp_header() ?>
    
    <h1 id="mlfp-page-title"><?php esc_html_e('Image SEO', 'maxgalleria-media-library' ); ?></h1>
    <nav class="nav-tab-wrapper">
    </nav>
    
    <p><?php esc_html_e('When Image SEO is enabled, Media Library Folders automatically adds the alt and the title attributes to your images (as they are uploaded) using the default settings below. You can override the Image SEO default settings by using the Image Alt Text and Image Title Text fields under the Uploads box when uploading images.', 'maxgalleria-media-library' ); ?></p>
    <p><?php esc_html_e('Image SEO supports two special tags:', 'maxgalleria-media-library' ); ?><br>
    <strong>%filename</strong> - <?php esc_html_e('replaces image file name (without extension)', 'maxgalleria-media-library' ); ?><br>
    <strong>%foldername</strong> - <?php esc_html_e('replaces image folder name', 'maxgalleria-media-library' ); ?></p>
    
    <div class="wrap">
          
    <div class="tab-content">
      
      <?php 
      $default_alt = get_option(MAXGALLERIA_MEDIA_LIBRARY_ATL_DEFAULT);
      $default_title = get_option(MAXGALLERIA_MEDIA_LIBRARY_TITLE_DEFAULT);

      if($default_alt === '')
        $default_alt = '%foldername - %filename';
      if($default_title === '')
        $default_title = '%foldername photo';

      $checked = get_option(MAXGALLERIA_MEDIA_LIBRARY_IMAGE_SEO, 'off');						
      ?>
      
      <table id="mlp-image-seo">
        <tbody>
          <tr>
            <td><?php esc_html_e('Enable Image SEO:','maxgalleria-media-library'); ?></td>
            <td><input name="seo-images" id="seo-images" type="checkbox" <?php echo esc_attr(checked($checked, 'on', false )); ?> </td>
            <td></td>
          </tr>
          <tr>
            <td><?php esc_html_e('Image ALT attribute:','maxgalleria-media-library'); ?></td>
            <td><input type="text" value="<?php echo esc_attr($default_alt) ?>" name="default-alt" id="default-alt" autocomplete="off"></td>
            <td><em><?php esc_html_e('example','maxgalleria-media-library'); ?> %foldername - %filename</em></td>									
          </tr>
          <tr>
            <td><?php esc_html_e('Image Title attribute:','maxgalleria-media-library'); ?></td>
            <td><input type="text" value="<?php echo esc_attr($default_title) ?>" name="default-title" id="default-title" autocomplete="off"></td>
            <td><em><?php esc_html_e('example','maxgalleria-media-library'); ?> %filename photo</em></td>									
          </tr>								
          <tr>
            <td colspan="3"><a class="button-primary" id="mlp-update-seo-settings"><?php esc_html_e('Update Settings','maxgalleria-media-library'); ?></a></td>									
          </tr>
        </tbody>							
      </table>
      <div id="folder-message" class="mlf-no-background"></div>
            
    </div><!--tab-content-->
    
  </div><!--wrap-->
    
</div><!--wp-media-grid-->
<script>
jQuery(document).ready(function(){

  jQuery(document).on("click", "#mlfp-help-icon", function (e) {
    jQuery("#mlfp-help-panel").animate({
        width: "toggle"
    });

  });  
  
  jQuery(document).on("click", "#seo-images", function (e) {
    jQuery("#folder-message").text("");
  });    
  
  jQuery(document).on("click", "#mlp-update-seo-settings", function (e) {
    
    console.log('mlp-update-seo-settings click');

    var checked = "off";
    if(jQuery("#seo-images").is(":checked")) {
      checked = 'on';
    }
    var default_alt = jQuery("#default-alt").val();
    var default_title = jQuery("#default-title").val();

    jQuery.ajax({
      type: "POST",
      async: true,
      data: { action: "mlp_image_seo_change", checked: checked, default_alt: default_alt, default_title: default_title, nonce: mgmlp_ajax.nonce },
      url : mgmlp_ajax.ajaxurl,
      dataType: "html",
      success: function (data) {
        jQuery("#folder-message").html(data);
        window.location.reload();                    
      },
      error: function (err)
        { 
          alert(err.responseText);
        }
    });                

  });
  

});  
</script>   
