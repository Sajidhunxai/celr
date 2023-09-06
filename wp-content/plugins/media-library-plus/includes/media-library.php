
<?php

global $wpdb;
global $pagenow;
global $post;
global $current_user;
$ajax_nonce = wp_create_nonce( "media-send-to-editor" );				

if(isset($_GET['post'])) {
  $post_id = sanitize_textarea_field($_GET['post']);
} else {
  if(isset($post->ID))
    $post_id = $post->ID;
  else
    $post_id = '0';
}	

if(is_multisite()) {
  $table_name = $wpdb->prefix . MAXGALLERIA_MEDIA_LIBRARY_FOLDER_TABLE;
  if($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {		
    $this->activate();
  }	
}

if(get_option('mlpp_show_template_ad', "on") == "on")
  $show_temp_ad = true;
else
  $show_temp_ad = false;

$sort_order = trim(get_option( MAXGALLERIA_MEDIA_LIBRARY_SORT_ORDER ));    
$sort_type = trim(get_option( MAXGALLERIA_MLF_SORT_TYPE ));    
$move_or_copy = get_option( MAXGALLERIA_MEDIA_LIBRARY_MOVE_OR_COPY );

$display_info = get_user_meta( $current_user->ID, MAXGALLERIA_MLP_DISPLAY_INFO, true );
//$disable_ft = get_user_meta( $current_user->ID, MAXGALLERIA_MLP_DISABLE_FT, true );

if ((isset($_GET['media-folder'])) && (strlen(trim($_GET['media-folder'])) > 0)) {
  $current_folder_id = trim(sanitize_text_field($_GET['media-folder']));
  if(!is_numeric($current_folder_id)) {
    $current_folder = get_option(MAXGALLERIA_MEDIA_LIBRARY_UPLOAD_FOLDER_NAME, "uploads");      
    $current_folder_id = get_option(MAXGALLERIA_MEDIA_LIBRARY_UPLOAD_FOLDER_ID );        
    $this->uploads_folder_name = $current_folder;
    $this->uploads_folder_name_length = strlen($current_folder);
    $this->uploads_folder_ID = $current_folder_id;				
  }
  else {
    $current_folder = $this->get_folder_name($current_folder_id);
  }	
} else {             
  if(get_option(MAXGALLERIA_MEDIA_LIBRARY_UPLOAD_FOLDER_NAME, "none") !== 'none') { 
    $current_folder = get_option(MAXGALLERIA_MEDIA_LIBRARY_UPLOAD_FOLDER_NAME, "uploads");      
    $current_folder_id = get_option(MAXGALLERIA_MEDIA_LIBRARY_UPLOAD_FOLDER_ID );
    $this->uploads_folder_name = $current_folder;
    $this->uploads_folder_name_length = strlen($current_folder);
    $this->uploads_folder_ID = $current_folder_id;				
  } else {
    $current_folder_id = $this->fetch_uploads_folder_id();
    update_option(MAXGALLERIA_MEDIA_LIBRARY_UPLOAD_FOLDER_ID, $current_folder_id);
    $current_folder = $this->lookup_uploads_folder_name($current_folder_id);
    update_option(MAXGALLERIA_MEDIA_LIBRARY_UPLOAD_FOLDER_NAME, $current_folder);
    $this->uploads_folder_name = $current_folder;
    $this->uploads_folder_name_length = strlen($current_folder);
    $this->uploads_folder_ID = $current_folder_id;				        
  }
}  

$image_seo = get_option(MAXGALLERIA_MEDIA_LIBRARY_IMAGE_SEO, 'off');
if($image_seo === 'on') {
  $seo_file_title = get_option(MAXGALLERIA_MEDIA_LIBRARY_TITLE_DEFAULT);
  $seo_alt_text = get_option(MAXGALLERIA_MEDIA_LIBRARY_ATL_DEFAULT);
}

?>

<noscript>
  <p><?php esc_html_e('Media Library Folders has detected that Javascript has been turned off in this browser. It is necessary for Javascript to be running in order for Media Library Folders to function.','maxgalleria-media-library'); ?></p>
</noscript>

<?php
  $phpversion = phpversion();		
  if($phpversion < '7.4')		
    echo wp_kses_post("<br><div>" . __('Current PHP version, ','maxgalleria-media-library') . $phpversion . __(', is outdated. Please upgrade to version 7.4.','maxgalleria-media-library') . "</div>");

  $folder_location = $this->get_folder_path($current_folder_id);

  $folders_path = "";
  $parents = $this->get_parents($current_folder_id);

  $folder_count = count($parents);
  $folder_counter = 0;        
  $current_folder_string = site_url() . "/" . MLF_WP_CONTENT_FOLDER_NAME;
  foreach( $parents as $key => $obj) { 
    $folder_counter++;
    if($folder_counter === $folder_count)
      $folders_path .= $obj['name'];      
    else
      $folders_path .= '<a folder="' . $obj['id'] . '" class="media-link">' . $obj['name'] . '</a>/';      
    $current_folder_string .= '/' . $obj['name'];
  }

?>

<div id="breadcrumbs-wrapper">
  <?php echo wp_kses_post("<h3 id='mgmlp-breadcrumbs'>" . __('Location:','maxgalleria-media-library') . " $folders_path</h3>") ?>
</div>
<input type='hidden' id='display_type' value='1'>
<input type="hidden" id="current-folder-id" value="<?php echo esc_attr($current_folder_id) ?>" />
<input type="hidden" id="previous-folder-id" value="<?php echo esc_attr($current_folder_id) ?>" />
<input type="hidden" id="move-or-copy-status" value="<?php echo esc_attr($move_or_copy) ?>" />
<input type="hidden" id="sort-type" value="<?php echo esc_attr($sort_type) ?>" />

<?php if($this->bda == 'on') { ?>
  <?php 
    $download_page = get_permalink(get_option(MLFP_BDA_DOWNLOAD_PAGE)); 
    $download_link = esc_url(add_query_arg('download', '', $download_page));             
    ?>
  <input type="hidden" id="mlfp-download-page" value="<?php echo $download_link ?>" />
<?php } ?>

<div id="mgmlp-outer-container">
  
  <div id="folder-tree-container">
    
    <div class="mg-folders-tool-bar bottom-border">
      <ul id="mlf-folder-buttons">
        <li>
          <a id="mlf-add-folder" title="<?php esc_html_e('Add Folder','maxgalleria-media-library') ?>">
            <i class="fa-solid fa-folder-plus fa-2x"></i>
          </a>
        </li>  
        <li>  
          <a id="add-new_attachment" title="<?php esc_html_e('Upload files','maxgalleria-media-library') ?>" href="javascript:slideonlyone('add-new-area');">
            <i class="fa-solid fa-upload fa-2x"></i>
          </a>
        </li>  
        <li>  
          <a id="mlf-refresh-folders" title="<?php esc_html_e('Refresh Folders','maxgalleria-media-library') ?>">
            <i class="fa-solid fa-arrows-rotate fa-2x"></i>
          </a>
        </li>          
      </ul>
    </div>
        
    <div id="ft-panel">
			<ul id="folder-tree">
      </ul>  
    </div>  
   
  </div><!--folder-tree-container-->
  
  <div id="mgmlp-library-container">
    
    <div class="mg-folders-tool-bar full-border">
      
      <ul id="mlf-mode-buttons">
        <li>  
          <?php $move_class = ($move_or_copy == 'on') ? 'mlf-active':''; ?>
          <a id="mlf-move" title="<?php esc_html_e('Move Files','maxgalleria-media-library') ?>">
            <i class="fa-solid fa-file-import fa-2x <?php echo esc_attr($move_class) ?>"></i>
          </a>
        </li>          
        <li>  
          <?php $copy_class = ($move_or_copy == 'off') ? 'mlf-active':''; ?>
          <a id="mlf-copy" title="<?php esc_html_e('Copy Files','maxgalleria-media-library') ?>">
            <i class="fa-solid fa-clone fa-2x <?php echo esc_attr($copy_class) ?>"></i>
          </a>
        </li>          
      </ul>  

      <ul id="mlf-sort-buttons">
        <li>  
          <?php $date_class = ($sort_order == '0') ? 'mlf-active':'';   ?>
          <a id="mlf-sort-date" title="<?php esc_html_e('Order by date','maxgalleria-media-library') ?>">
            <i class="fa-solid fa-calendar-days fa-2x <?php echo esc_attr($date_class) ?>"></i>
          </a>
        </li>          
        <li>  
          <?php $title_class = ($sort_order == '1') ? 'mlf-active':'';   ?>
          <a id="mlf-sort-title" title="<?php esc_html_e('Order by title','maxgalleria-media-library') ?>">
            <i class="fa-solid fa-file-image fa-2x <?php echo esc_attr($title_class) ?>"></i>
          </a>
        </li>          
        <li>  
          <a id="mlf-sort-reverse" title="<?php esc_html_e('Reverse Order','maxgalleria-media-library') ?>">
            <i class="fa-solid fa-arrows-up-down fa-2x"></i>
          </a>
        </li>                        
      </ul>  
                  
      <ul id="mlf-sync-button">
        <li>  
          <a id="mlf-rename-mf" title="<?php esc_html_e('Rename a File','maxgalleria-media-library') ?>">
            <i class="fa-solid fa-pen fa-2x"></i>               
          </a>
        </li>          
        <li>  
          <a id="sync-media" title="<?php esc_html_e('Sync folder contents','maxgalleria-media-library') ?>">
            <i class="fas fa-bolt fa-2x mlf-active"></i>
          </a>
        </li>             
        <?php if(class_exists('MaxGalleria') || class_exists('MaxGalleriaPro')) { ?>
          <li>  
            <a id="add-mg-gallery" title="<?php esc_html_e('Add images to MaxGalleria gallery','maxgalleria-media-library') ?>">
              <i class="fa-solid fa-images fa-2x mlf-active"></i>
            </a>
          </li> 
        <?php } ?>
        <li>  
          <a id="mgmlp-regen-thumbnails" title="<?php esc_html_e('Regenerate thumnail images','maxgalleria-media-library') ?>">
            <i class="far fa-object-group fa-2x mlf-active"></i>
          </a>
        </li> 
        <li>  
          <a id="delete-media" title="<?php esc_html_e('Delete files','maxgalleria-media-library') ?>">
            <i class="fas fa-trash fa-2x mlf-active"></i>
          </a>
        </li>                     
      </ul>  
      
      <div id="select-all-container">
        <input type="checkbox" id="mlf-select-all">
        <label><?php esc_html_e('Select All Files','maxgalleria-media-library') ?></label>
      </div>
      
        
      <ul id="mlf-search-items">
        <li>
          <input type="search" placeholder="<?php esc_html_e('Search','maxgalleria-media-library') ?>" id="mgmlp-media-search-input" class="search gray-blue-link">
          <span id="mlf-search-clear-wraper">
            <a id="mlf-search-clear"><i class="far fa-times-circle"></i></a>
          </span>
        </li>
        <li>
          <a id="mlfp-media-search" class="gray-blue-link"><?php esc_html_e('Find','maxgalleria-media-library') ?></a>
        </li>
      </ul>
      
      <?php if($this->bda == 'on') { ?>
        <div id="mg-stb-left">
          <select id="mlf-bulk-select" class="gray-blue-link">
            <option>Bulk Actions</option>
            <option value="mgmlp-block-files">Block/Unblock File Access</option>
            <option value="mlfp-configure-private-links">Configure Blocked Download Links</option>
          </select>
          <a id="mlp-bulk-apply" class="gray-blue-link">Apply</a>
        </div>      
      <?php } ?>      
      
    </div>
    <div style="clear:both"></div>
    <div class="mg-folders-secondary-tool-bar">
    </div>
    <div id="alwrap">
      <div id="ajaxloader" style="display:none"></div>      
    </div>
    
    <div style="clear:both"></div>
    <div id="folder-message" class="folder-message-backgound"></div>

    
    <div id="add-new-area" class="input-area" style="display: none">
      <div id="dragandrophandler">
        <div><?php esc_html_e('Drag & Drop Files Here','maxgalleria-media-library') ?></div>
          <div id="upload-text"><?php esc_html_e('or select a file or image to upload:','maxgalleria-media-library') ?></div>
          <input type="file" name="fileToUpload" id="fileToUpload">
          <input type="hidden" name="folder_id" id="folder_id" value="<?php echo esc_attr($current_folder_id) ?>">
          <input type="button" value="<?php esc_html_e('Upload Image','maxgalleria-media-library') ?>" id="mgmlp_ajax_upload" name="submit_image">
      </div>
    <?php if($image_seo === 'on') { ?>
      <div id="seo-container">
        <label class="mlp-seo-label" for="mlp_title_text"><?php esc_html_e('Image Title Text:','maxgalleria-media-library') ?>&nbsp;</label><input class="seo-fields" type="text" name="mlp_title_text" id="mlp_title_text" value="<?php echo esc_attr($seo_file_title) ?>">
        <label class="mlp-seo-label" for="mlp_alt_text"><?php esc_html_e('Image ALT Text:','maxgalleria-media-library') ?>&nbsp;</label><input class="seo-fields" type="text" name="mlp_alt_text" id="mlp_alt_text" value="<?php echo esc_attr($seo_alt_text) ?>">
      </div>
    <?php } ?>
    </div>
    <div class="mlf-clearfix"></div>
    
    <div id="mgmlp-file-container">
      <?php $this->display_folder_contents ($current_folder_id); ?>
    </div>
    
  </div><!--mgmlp-library-container-->
    
</div><!--mgmlp-outer-container-->

<div id="mlf-new-folder-popup">
  <div class="mlf-popup-content">
    <h2><?php esc_html_e('New Folder','maxgalleria-media-library') ?></h2>
    <a class="close-popup" title="<?php esc_html_e('Close without saving','maxgalleria-media-library') ?>">x</a> 
    <hr>
    
    <div class="popup-content-bottom">
    <?php esc_html_e('Folder Name: ','maxgalleria-media-library') ?><input type="text" name="new-folder-name" id="new-folder-name" value="" />
    <div class="btn-wrap"><a id="mgmlp-create-new-folder" class="gray-blue-link" ><?php esc_html_e('Create Folder','maxgalleria-media-library') ?></a></div>
    </div>
        
  </div>
</div>  

<div id="mlf-rename-popup">
  <div class="mlf-popup-content">
    <h2><?php esc_html_e('Rename The Selected File','maxgalleria-media-library') ?></h2>
    <a id="close-rename-popup" title="<?php esc_html_e('Close without renaming','maxgalleria-media-library') ?>">x</a> 
    <hr>
    
    <div class="popup-content-bottom">
    <?php esc_html_e('New File Name: ','maxgalleria-media-library') ?><input type="text" name="new-file-name" id="new-file-name" value="" />
    <div class="btn-wrap"><a id="mgmlp-rename-file" class="gray-blue-link" ><?php esc_html_e('Rename File','maxgalleria-media-library') ?></a></div>
    </div>
        
  </div>
</div>  

<?php						            
  if(class_exists('MaxGalleria') || class_exists('MaxGalleriaPro')) {
    $gallery_list = $this->get_maxgalleria_galleries();
    $allowed_html = array(
      'option' => array(
        'value' => array()
      )    
    );
  ?>

<div id="mlf-add-to-gallery-popup">
  <div class="mlf-popup-content">
    <h2><?php esc_html_e('Add images to MaxGalleria Gallery','maxgalleria-media-library') ?></h2>
    <a id="close-gallery-popup" title="<?php esc_html_e('Close without renaming','maxgalleria-media-library') ?>">x</a> 
    <hr>
    
    <div class="popup-content-bottom">
      
      <select id="gallery-select">
        <option disabled ><?php esc_html_e('Select a gallery','maxgalleria-media-library') ?></option>
        <?php echo wp_kses($gallery_list, $allowed_html) ?>
      </select>
      <div class="btn-wrap"><a id="add-to-gallery" class="gray-blue-link" ><?php esc_html_e('Add Images','maxgalleria') ?></a></div>
            
    </div>
        
  </div>
</div>

<?php } ?>

<div id="mlfp-config-links-popup">
  <div class="mlf-popup-content">
    <h2><?php esc_html_e('Configure Blocked Files Links','maxgalleria-media-library') ?></h2>
    
    <div id="alwrapbda">
      <div id="ajaxloaderbda" style="display: none;"></div>
    </div>   
    
    <a id="close-links-popup" title="<?php esc_html_e('Close popup without saving','maxgalleria-media-library') ?>">x</a> 
    <hr>
    
    <div class="popup-content-bottom">
      <table id="blocked-files">
        <thead>
          <tr>
            <td class="bda-name-col"><?php esc_html_e('File','maxgalleria-media-library') ?></td>
            <td class="bda-count-col mflp-align-center"><?php esc_html_e('Count','maxgalleria-media-library') ?></td>
            <td class="bda-limit-col mflp-align-center"><?php esc_html_e('Limit','maxgalleria-media-library') ?></td>
            <td class="bda-exp-date-col mflp-align-center"><?php esc_html_e('Expiration Date','maxgalleria-media-library') ?></td>
            <td class="bda-copy-link-col"></td>
          </tr>
        </thead>
        <tbody id="blocked-links-list">          
        </tbody>
      </table>
    <hr>    
      <p>
        <a id="mlfp-save-bda-info" class="gray-blue-link"><?php esc_html_e('Save & Close','maxgalleria-media-library') ?></a>
      </p>
      <div style="clear:both"></div>
      <p id="links-config-message">
      </p>
    </div>
        
  </div>
</div>  

<script>
  
window.onerror = function(msg, url, linenumber) {
  jQuery("#folder-message").html('Javascript error : ' + msg );
  return true;
}
  
jQuery(document).ready(function(){
   
  jQuery('#mlf-add-folder').on('click', function () {
    jQuery('#mlf-new-folder-popup').fadeIn(300);
    jQuery('#new-folder-name').focus();    
  });
  
  jQuery('#new-folder-name').keydown(function (e){
    console.log('enter key press');
    if(e.keyCode == 13){                
      var new_folder_name = jQuery('#new-folder-name').val();
      console.log('new_folder_name',new_folder_name);
      create_new_folder(new_folder_name);
    }  else if(e.keyCode == 27) {
      jQuery('#mlf-new-folder-popup').fadeOut(300);      
      jQuery('#new-folder-name').val('');
    }
  });    
  
  jQuery('#mgmlp-create-new-folder').on('click', function () {
    var new_folder_name = jQuery('#new-folder-name').val();
    jQuery('#mlf-new-folder-popup').fadeOut(300);      
    create_new_folder(new_folder_name);
  });    
  
  jQuery('#cancel-button, .close-popup').on('click', function () {
    jQuery('#mlf-new-folder-popup').fadeOut(300);
  });
  
  jQuery('#mlf-refresh-folders').on('click', function () {
        
    jQuery("#ajaxloader").show();
    jQuery("#folder-message").html(mgmlp_ajax.folder_check);
    
    
    if(jQuery("#current-folder-id").val() === undefined) 
      var parent_folder = sessionStorage.getItem('folder_id');
    else
      var parent_folder = jQuery('#current-folder-id').val();
    
    jQuery.ajax({
      type: "POST",
      async: true,
      data: { action: "mlf_check_for_new_folders", parent_folder: parent_folder, nonce: mgmlp_ajax.nonce },
      url : mgmlp_ajax.ajaxurl,
      dataType: "json",
      success: function (data) {
        console.log('message',data.message);
        jQuery("#folder-tree").addClass("bound").on("select_node.jstree", show_mlp_node);							
        jQuery("#folder-message").html(data.message);
        jQuery("#ajaxloader").hide();          
        if(data.refresh) {
          jQuery('#folder-tree').jstree(true).settings.core.data = data.folders;						
          jQuery('#folder-tree').jstree(true).refresh();			
          jQuery('#folder-tree').jstree('select_node', '#' + parent_folder, true);
          jQuery('#folder-tree').jstree('toggle_expand', '#' + parent_folder, true );
        }
      },
      error: function (err)
        { alert(err.responseText);}
    });
    
  });
  
  
  jQuery(document).on("click", "#mlf-rename-mf", function () {
    console.log('mlf-rename-mf');
    var image_id = 0;
    var file_name = '';
    var found = false;
    jQuery('#new-file-name').val('');
    
    jQuery('input[type=checkbox].mgmlp-media:checked').each(function() {  
      // only get the first one
      image_id = jQuery(this).attr("id");
      file_name = jQuery(this).parent().siblings('.mediafile').text();
      file_name = file_name.substr(0, file_name.lastIndexOf('.')) || file_name;
      found = true;
      return false;
    });
    
    if(!found) {
      alert(mgmlp_ajax.nothing_selected);
      return false;
    }
    
    jQuery('#mlf-rename-popup').fadeIn(300);    
    jQuery('#new-file-name').val(file_name);
    jQuery('#new-file-name').focus();    
  });
  
  jQuery('#close-rename-popup').on('click', function () {
    jQuery('#mlf-rename-popup').fadeOut(300);
  });
    
  jQuery('#mgmlp-rename-file').on('click', function () {
    
    jQuery("#folder-message").html('');			

    if(jQuery("#current-folder-id").val() === undefined) 
      var current_folder = sessionStorage.getItem('folder_id');
    else
      var current_folder = jQuery('#current-folder-id').val();

    var image_id = 0;
    var new_file_name = jQuery('#new-file-name').val();

    new_file_name = new_file_name.trim();

    jQuery('input[type=checkbox].mgmlp-media:checked').each(function() {  
      // only get the first one
      //if(image_id === 0)
        image_id = jQuery(this).attr("id");
        return false;
    });

    if(new_file_name == "") {
      alert(mgmlp_ajax.no_blank_filename);
      return false;
    }                 

    if(new_file_name.indexOf(' ') >= 0 || new_file_name === '' ) {
      alert(mgmlp_ajax.valid_file_name);
      return false;
    }       

    jQuery("#ajaxloader").show();

    jQuery.ajax({
      type: "POST",
      async: true,
      data: { action: "maxgalleria_rename_image", image_id: image_id, new_file_name: new_file_name, nonce: mgmlp_ajax.nonce },
      url : mgmlp_ajax.ajaxurl,
      dataType: "html",
      success: function (data) {        
        jQuery('#mlf-rename-popup').fadeOut(300);  
        jQuery("#folder-message").html(data);
        jQuery('#new-file-name').val('');
        jQuery(".mgmlp-media").prop('checked', false);
        mlf_refresh(current_folder);
        jQuery('#mlf-rename-popup').fadeOut(300);
        jQuery("#ajaxloader").hide();
      },
      error: function (err) { 
        jQuery("#ajaxloader").hide();
        alert(err.responseText);
      }
    });
        
  });
  
  jQuery('#mlf-move').on('click', function () {
    var move_copy_switch = 'on';
  
    jQuery.ajax({
      type: "POST",
      async: true,
      data: { action: "mgmlp_move_copy", move_copy_switch: move_copy_switch, nonce: mgmlp_ajax.nonce },
      url : mgmlp_ajax.ajaxurl,
      dataType: "html",
      success: function (data) {
        jQuery('.fa-file-import').addClass('mlf-active');
        jQuery('.fa-clone').removeClass('mlf-active');
        jQuery('#move-or-copy-status').val(move_copy_switch);
        jQuery("#folder-message").html(mgmlp_ajax.move_mode);			        
      },
      error: function (err) { 
        alert(err.responseText);
      }
    });
  });
    
  jQuery('#mlf-copy').on('click', function () {
    
    var move_copy_switch = 'off';
  
    jQuery.ajax({
      type: "POST",
      async: true,
      data: { action: "mgmlp_move_copy", move_copy_switch: move_copy_switch, nonce: mgmlp_ajax.nonce },
      url : mgmlp_ajax.ajaxurl,
      dataType: "html",
      success: function (data) {        
        jQuery('.fa-clone').addClass('mlf-active');
        jQuery('.fa-file-import').removeClass('mlf-active');
        console.log('move_copy_switch',move_copy_switch);
        jQuery('#move-or-copy-status').val(move_copy_switch);
        jQuery("#folder-message").html(mgmlp_ajax.copy_mode);			        
      },
      error: function (err) { 
        alert(err.responseText);
      }
    });                
  });  
  
  
  jQuery('#mlf-sort-date').on('click', function () {
  
    console.log('mlf-sort-date');
    var sort_order = '0';

    if(jQuery("#current-folder-id").val() === undefined) 
      var current_folder = sessionStorage.getItem('folder_id');
    else
      var current_folder = jQuery('#current-folder-id').val();

    jQuery("#ajaxloader").show();

    jQuery.ajax({
      type: "POST",
      async: true,
      data: { action: "sort_contents", sort_order: sort_order, folder: current_folder, nonce: mgmlp_ajax.nonce },        
      url : mgmlp_ajax.ajaxurl,
      dataType: "html",
      success: function (data) {
        jQuery("#mgmlp-file-container").html(data); 
        jQuery('.fa-calendar-days').addClass('mlf-active');
        jQuery('.fa-file-image').removeClass('mlf-active');
        jQuery("#ajaxloader").hide();
      },
      error: function (err) { 
        jQuery("#ajaxloader").hide();
        alert(err.responseText);
      }
    });                
  });     
  
  jQuery('#mlf-sort-title').on('click', function () {
    
    console.log('mlf-sort-title');
  
    var sort_order = '1';

    if(jQuery("#current-folder-id").val() === undefined) 
      var current_folder = sessionStorage.getItem('folder_id');
    else
      var current_folder = jQuery('#current-folder-id').val();

    jQuery("#ajaxloader").show();

    jQuery.ajax({
      type: "POST",
      async: true,
      data: { action: "sort_contents", sort_order: sort_order, folder: current_folder, nonce: mgmlp_ajax.nonce },        
      url : mgmlp_ajax.ajaxurl,
      dataType: "html",
      success: function (data) {
        jQuery("#mgmlp-file-container").html(data); 
        jQuery('.fa-file-image').addClass('mlf-active');
        jQuery('.fa-calendar-days').removeClass('mlf-active');
        jQuery("#ajaxloader").hide();
      },
      error: function (err) { 
        jQuery("#ajaxloader").hide();
        alert(err.responseText);
      }
    });                
  });    
  
  jQuery('#mlf-sort-reverse').on('click', function () {
    
    if(jQuery("#current-folder-id").val() === undefined) 
      var current_folder = sessionStorage.getItem('folder_id');
    else
      var current_folder = jQuery('#current-folder-id').val();
        
    var sort_type = jQuery('#sort-type').val();
    console.log('sort_type start ', sort_type);
    
    sort_type = (sort_type == 'ASC') ? 'DESC' : 'ASC';    
    console.log('sort_type new ', sort_type);
        
    jQuery("#ajaxloader").show();

    jQuery.ajax({
      type: "POST",
      async: true,
      data: { action: "mlf_change_sort_type", sort_type: sort_type, folder: current_folder, nonce: mgmlp_ajax.nonce },        
      url : mgmlp_ajax.ajaxurl,
      dataType: "html",
      success: function (data) {
        jQuery("#mgmlp-file-container").html(data); 
        jQuery('#sort-type').val(sort_type);
        //jQuery('.fa-file-image').addClass('mlf-active');
        //jQuery('.fa-calendar-days').removeClass('mlf-active');
        jQuery("#ajaxloader").hide();
      },
      error: function (err) { 
        jQuery("#ajaxloader").hide();
        alert(err.responseText);
      }
    });                


  });    
    
  jQuery('#mgmlp-media-search-input').keydown(function (e){
    if(e.keyCode == 13){                
      do_mlfp_search();
    }  
  })    

  jQuery(document).on("click", "#mlfp-media-search", function () {
    do_mlfp_search();
  })    
      
  jQuery(document).on("click", "#mlf-select-all", function () {
    jQuery(".media-attachment, .mgmlp-media").prop("checked", !jQuery(".media-attachment").prop("checked"));    
  })
    
  jQuery(document).on("click", "#mlf-delete-mf", function () {
    var attachment_id = jQuery(this).closest('div[id]'); 
    console.log('attachment_id',attachment_id);
  })
    
  jQuery(document).on("click", "#sync-media", function (e) {

    if(jQuery("#current-folder-id").val() === undefined) 
      var parent_folder = sessionStorage.getItem('folder_id');
    else
      var parent_folder = jQuery('#current-folder-id').val();

    var mlp_title_text = jQuery('#mlp_title_text').val();

    var mlp_alt_text = jQuery('#mlp_alt_text').val();      

    jQuery("#ajaxloader").show();

    run_sync_process('1', parent_folder, mlp_title_text, mlp_alt_text);

    jQuery("#ajaxloader").hide();

  });
  
  jQuery('#add-mg-gallery').on('click', function () {
    console.log('add-mg-gallery');
    jQuery('#mlf-add-to-gallery-popup').fadeIn(300);
  });    
    
  jQuery('#close-gallery-popup').on('click', function () {
    jQuery('#mlf-add-to-gallery-popup').fadeOut(300);
  });
  
  
  jQuery(document).on("click", "#add-to-gallery", function (e) {

    jQuery("#folder-message").html('');			

    var gallery_image_ids = new Array();
    jQuery('input[type=checkbox].mgmlp-media:checked').each(function() {  
      gallery_image_ids[gallery_image_ids.length] = jQuery(this).attr("id");
    });

    if(gallery_image_ids.length > 0) {

      var serial_gallery_image_ids = JSON.stringify(gallery_image_ids.join());
      var gallery_id = jQuery('#gallery-select').val();
      
      console.log('serial_gallery_image_ids',serial_gallery_image_ids);

      jQuery("#ajaxloader").show();
      jQuery("#mlf-add-to-gallery-popup").fadeOut(300);

      jQuery.ajax({
        type: "POST",
        async: true,
        data: { action: "add_to_max_gallery", gallery_id: gallery_id, serial_gallery_image_ids: serial_gallery_image_ids, nonce: mgmlp_ajax.nonce },
        url : mgmlp_ajax.ajaxurl,
        dataType: "html",
        success: function (data) {
          jQuery("#ajaxloader").hide();
          jQuery("#folder-message").html(data);
          jQuery(".mgmlp-media").prop('checked', false);
        },
        error: function (err) { 
          jQuery("#ajaxloader").hide();
          alert(err.responseText);
        }
      });  
    } else {
      alert(mgmlp_ajax.no_images_selected);
    }
  });	
  
  
  jQuery('#mlf-search-clear').on('click', function () {
    jQuery('#mgmlp-media-search-input').val('');
  });	
  
  jQuery(document).on("click", "#mlp-bulk-apply", function (e) {
    e.stopImmediatePropagation();
        
    var bulk_selection = jQuery('#mlf-bulk-select').val();
    
    if(jQuery(this).hasClass("disabled-button")) {
      console.log('disabled-button');
      return false;
    }
    
    console.log('bulk_selection',bulk_selection);
          
    switch(bulk_selection) {
      
      case 'mgmlp-block-files':  
        mlfp_block_files();
        break;
        
      case 'mlfp-configure-private-links':
        configure_private_links();
        break;
        
    }  
    
  })
  
  jQuery('#close-links-popup').on('click', function (e) {
    e.stopImmediatePropagation();
    jQuery('#mlfp-config-links-popup').fadeOut(300);
  });
  
  jQuery(document).on("click", "#mlfp-save-bda-info", function (e) {
    
    e.stopImmediatePropagation();
    jQuery('#mlfp-config-links-popup').fadeOut(300);
    
    jQuery('tbody#blocked-links-list tr').each(function() {  
      var image_id = jQuery(this).attr('data-id');
      var download_limit = jQuery('#limit-'+image_id).val();
      var expiration_date = jQuery('#ex-date-'+image_id).val();
      update_bda_record(image_id, download_limit, expiration_date);
    });     
      
  });
  
  jQuery(document).on("click", ".bda-copy-link", function (e) {
    
    e.stopImmediatePropagation();
    var file_name = jQuery(this).closest('.bda-row').find('.bda-name-col').text();    
    
    var hash = jQuery(this).data('hash');  
    var url = jQuery('#mlfp-download-page').val() + "=" + hash;
    //console.log('hash',url);
    copyToClipboard(url)
    var copy_message = file_name  + ' ' + mgmlp_ajax.link_copied;
    jQuery('#links-config-message').html(copy_message);
  });
    
  jQuery(document).on("click", "#bda-update-fields", function (e) {
    e.stopImmediatePropagation();
    console.log("bda_expiration_date");
    
    var bda_limit = jQuery('#bulk-download-limit').val();
    //bda_limit = bda_limit.trim();
    console.log('bda_limit',bda_limit);
    if(bda_limit != '') {      
      jQuery('tbody#blocked-links-list tr').each(function() {  
        var image_id = jQuery(this).attr('data-id');
        jQuery('#limit-'+image_id).val(bda_limit);
      });
    }
    
    var bda_expiration_date = jQuery('#bulk-expiration-date').val();
    console.log('bda_expiration_date',bda_expiration_date);
    if(bda_expiration_date != '') {
      jQuery('tbody#blocked-links-list tr').each(function() {  
        var image_id = jQuery(this).attr('data-id');
        var expiration_date = jQuery('#ex-date-'+image_id).val(bda_expiration_date);        
      });
    }      
  });
    
}); // document ready end

// from https://stackoverflow.com/questions/51805395/navigator-clipboard-is-undefined#65996386
function copyToClipboard(textToCopy) {
  // navigator clipboard api needs a secure context (https)
  if (navigator.clipboard && window.isSecureContext) {
    // navigator clipboard api method'
    return navigator.clipboard.writeText(textToCopy);
  } else {
    let textArea = document.createElement("textarea");
    textArea.value = textToCopy;
    textArea.style.position = "fixed";
    textArea.style.left = "-999999px";
    textArea.style.top = "-999999px";
    document.body.appendChild(textArea);
    textArea.focus();
    textArea.select();
    return new Promise((res, rej) => {
      document.execCommand('copy') ? res() : rej();
      textArea.remove();
    });
  }
}

function update_bda_record(image_id, download_limit, expiration_date) {
  
  jQuery("#ajaxloaderbda").show();
    
  jQuery.ajax({
    type: "POST",
    async: true,
    data: { action: "mlfp_update_bda_record", image_id: image_id, download_limit: download_limit, expiration_date: expiration_date, nonce: mgmlp_ajax.nonce },
    url: mgmlp_ajax.ajaxurl,
    dataType: "html",
    success: function (data) {
      jQuery("#ajaxloaderbda").hide();
    },
    error: function (err){
      jQuery("#ajaxloader").hide();
      alert(err.responseText);
    }
  });
  
}  

function mlfp_block_files() {
  
  console.log('mlfp_block_files');
  
  if(jQuery("#current-folder-id").val() === undefined) 
    var current_folder = sessionStorage.getItem('folder_id');
  else
    var current_folder = jQuery('#current-folder-id').val();

  // promise array/counter will call refresh when done
  var promisesArray = [];
  var successCounter = 0;
  var promise;			    
  var file_count = 0;

  jQuery('input[type=checkbox].mgmlp-media:checked').each(function() {  

    var file_id = jQuery(this).attr("id");
    var protected = jQuery(this).attr("protected");
    console.log('file_id',file_id);
    //console.log(this);
    console.log('protected',protected);
    file_count++;
    jQuery("#ajaxloader").show();
    
    console.log('file_id',file_id,'current_folder',current_folder,'protected',protected);

    promise = 
      jQuery.ajax({
        type: "POST",
        async: true,
        data: { action: "mlfp_toggle_file_access", file_id: file_id, current_folder: current_folder, protected: protected, nonce: mgmlp_ajax.nonce },
        url: mgmlp_ajax.ajaxurl,
        dataType: "html",
        success: function (data) { 
          jQuery("#ajaxloader").hide();
          jQuery("#folder-message").html(data);      
          mlf_refresh(current_folder);
        },
        error: function (err){ 
          jQuery("#ajaxloader").hide();
          alert(err.responseText)
        }
      });  

      promise.done(function(msg) {
        successCounter++;
      });

      promise.fail(function(jqXHR) { /* error out... */ });

      promisesArray.push(promise);      

  });

  jQuery.when.apply($, promisesArray).done(function() {
    console.log('promise done');
  });
  
}  

function configure_private_links() {
  
  var count = 0;
  
  jQuery('#blocked-links-list').html('');
  
  jQuery('#mlfp-config-links-popup').fadeIn(300);
    
  jQuery('#blocked-links-list').append("<tr id='bulk-blocked-row'><td class='bda-name-col'><?php esc_html_e('Bulk Configure', 'maxgalleria-media-library' ); ?></td><td class='bda-count-col'></td><td class='bda-limit-col mflp-align-center'><input type='text' id='bulk-download-limit' class='bda-limit-input mflp-align-right' value='' ></td><td class='bda-exp-date-col'><input type='date' class='bda-exp-date-input' id='bulk-expiration-date' value=''></td><td class='bda-copy-link-col'><a id='bda-update-fields' class='gray-blue-link'>Copy Fields</a></td></tr>")
    
  jQuery('input[type=checkbox].mgmlp-media:checked').each(function() {  
    if(count > 20) {
      return false;
    }  
    var image_id = jQuery(this).attr("id");
    var title = jQuery(this).closest('.attachment-name').find('.mediafile').text();    
    //console.log('title',title);
    get_blocked_file_info(image_id, title, count);
    count++;
  });
       
}

function create_new_folder(new_folder_name) {

  jQuery("#folder-message").html('');			

  if(jQuery("#current-folder-id").val() === undefined) 
    var parent_folder = sessionStorage.getItem('folder_id');
  else
    var parent_folder = jQuery('#current-folder-id').val();

  new_folder_name = new_folder_name.trim();

  if(new_folder_name.indexOf(' ') >= 0) {
    alert(mgmlp_ajax.no_spaces);
    return false;
  }       

  if(new_folder_name.indexOf('"') >= 0) {
    alert(mgmlp_ajax.no_quotes);
    return false;
  } 

  if(new_folder_name.indexOf("'") >= 0) {
    alert(mgmlp_ajax.no_quotes);
    return false;
  } 

  if(new_folder_name == "") {
    alert(mgmlp_ajax.no_blank);
    return false;
  } 

  jQuery("#ajaxloader").show();

  jQuery.ajax({
    type: "POST",
    async: true,
    data: { action: "create_new_folder", parent_folder: parent_folder, new_folder_name: new_folder_name,   nonce: mgmlp_ajax.nonce },
    url : mgmlp_ajax.ajaxurl,
    dataType: "json",
    success: function (data) {
      jQuery("#folder-tree").addClass("bound").on("select_node.jstree", show_mlp_node);							
      jQuery('#new-folder-name').val('');	
      jQuery("#ajaxloader").hide();          
      jQuery("#folder-message").html(data.message);
      jQuery('#mlf-new-folder-popup').fadeOut(300);
      if(data.refresh) {
        jQuery('#folder-tree').jstree(true).settings.core.data = data.folders;						
        jQuery('#folder-tree').jstree(true).refresh();			
        jQuery('#folder-tree').jstree('select_node', '#' + parent_folder, true);
        jQuery('#folder-tree').jstree('toggle_expand', '#' + parent_folder, true );
      }

    },
    error: function (err)
      { alert(err.responseText);}
  });
        
}
  
function do_mlfp_search() {

  var search_value = jQuery('#mgmlp-media-search-input').val();

  search_value = search_value.trim();

  if(search_value.length < 1) {
    jQuery("#folder-message").html('<?php esc_html_e('The search text is empty.', 'maxgalleria-media-library' ); ?>');
    return false;
  } 
  jQuery("#folder-message").html('');

  var search_url = '<?php echo esc_url_raw(site_url() . '/wp-admin/admin.php?page=search-library&s=') ?>' + search_value;

  window.location.href = search_url;    
        
}

function bulk_regenerate_thumbnails() {
  
      var image_ids = new Array();
      jQuery('input[type=checkbox].mgmlp-media:checked').each(function() {   
        image_ids[image_ids.length] = jQuery(this).attr("id");
      });
			
			if(image_ids.length < 1) {
        jQuery("#folder-message").html("No files were selected.");
				return false;
			}	
			            
      var serial_image_ids = JSON.stringify(image_ids.join());
      
      console.log('serial_image_ids',serial_image_ids);
      
      jQuery("#ajaxloader").show();
      
      jQuery.ajax({
        type: "POST",
        async: true,
        data: { action: "regen_mlp_thumbnails", serial_image_ids: serial_image_ids, nonce: mgmlp_ajax.nonce },
        url : mgmlp_ajax.ajaxurl,
        dataType: "html",
        success: function (data) {
          console.log('data',data);
          jQuery(".mgmlp-media").prop('checked', false);
          jQuery("#folder-message").html(data);
          jQuery("#ajaxloader").hide();
        },
        error: function (err)
          { 
            jQuery("#ajaxloader").hide();
            alert(err.responseText);
          }
      });                
  
}

function display_protected_files() {
  jQuery(".mlfp-protected").each(function () {
    var image_element = jQuery(this).find("img.attachment-thumbnail");
    var src = image_element.attr("src");
    if(src.indexOf("protected-content") >= 0) {
      jQuery(image_element).attr("src", "");
      console.log("src", src);
      jQuery.ajax({
        type: "POST",
        async: true,
        data: { action: "mlfp_load_image", src: src, nonce: mgmlp_ajax.nonce },
        url: mgmlp_ajax.ajaxurl,
        success: function (data) {
          if(data.length > 0)
            jQuery(image_element).attr("src", data);
        },
        error: function (err){
          alert(err.responseText);
        }
      });
    }
  });  
}

function get_blocked_file_info(image_id, title, count) {
  
  jQuery("#ajaxloaderbda").show();
  
	jQuery.ajax({
		type: "POST",
		async: true,
		data: { action: "mlfp_display_bda_info", image_id: image_id, title: title, count: count, nonce: mgmlp_ajax.nonce },
		url: mgmlp_ajax.ajaxurl,
		dataType: "html",
		success: function (data) { 
      console.log('data',data);
      if(data != 'none') {
        jQuery("#ajaxloaderbda").hide();
        jQuery('#blocked-links-list').append(data);
      }                          
		},
		error: function (err){ 
			alert(err.responseText)
		}
	});
    
}

</script>   
  



