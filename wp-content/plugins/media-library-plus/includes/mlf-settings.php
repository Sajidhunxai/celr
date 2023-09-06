
<div id="wp-media-grid" class="wrap">
  
  <div class="mlfp-container">
    
    <?php echo $this->display_mlfp_header() ?>
    
    <h1 id="mlfp-page-title"><?php esc_html_e('Settings', 'maxgalleria-media-library' ); ?></h1>
    <div class="mlfp-tab-section">
      
 <?php     
//Get the active tab from the $_GET param
  $default_tab = null;
  $tab = sanitize_textarea_field(isset($_GET['tab']) ? $_GET['tab'] : $default_tab);

  ?>
  <!-- Our admin page content should all be inside .wrap -->
  <div class="wrap">
    <nav class="nav-tab-wrapper">
      <a href="?page=mlf-settings8" class="nav-tab <?php if($tab==='options'):?>nav-tab-active<?php endif; ?>">Options</a>
      <a href="?page=mlf-settings8&tab=access" class="nav-tab <?php if($tab==='access'):?>nav-tab-active<?php endif; ?>"><?php esc_html_e('Block Direct Access', 'maxgalleria-media-library' ); ?></a>
      <a id="mlfp-help"><img id="mlfp-help-icon" src="<?php echo esc_url(MAXGALLERIA_MEDIA_LIBRARY_PLUGIN_URL . "/images/mlfp-help.png") ?>" alt="help icon" width:="28" height="28"></a>
    </nav>
    <div id="mlfp-help-panel" style="display:none">
      <div id="mlfp-help-panel-inner" >
        
        <?php switch($tab) :
          case 'access':
            $this->bda_help();
            break;
          
          default:
            ?>
              <div><?php esc_html_e('Settings Help', 'maxgalleria-media-library' ); ?></div>        
              <p><strong><?php esc_html_e('Number of images to display.', 'maxgalleria-media-library'); ?></strong> <?php esc_html_e('This option sets the number of images displayed in the Folders and Files page. By default, it is set at 500 images but it can be changed to a higher or lower amount.', 'maxgalleria-media-library' ); ?></p>
              <p><strong><?php esc_html_e('Disable large image scaling.', 'maxgalleria-media-library'); ?></strong> <?php esc_html_e('By default, WordPress will scale down very large images so they will take up less space and load faster. Checking this option will turn this feature off.', 'maxgalleria-media-library' ); ?></p>
              <p><strong><?php esc_html_e("Add an index to the postmeta table","maxgalleria-media-library")?></strong>, <?php esc_html_e("For sites with a large number of media files, check this option to create a new index fro the postmeta table to speed by the loading of the Media Library Folders Pro page.","maxgalleria-media-library")?></p>
              <p><strong><?php esc_html_e("Skip .webp images when syncing media library files","maxgalleria-media-library")?></strong>, <?php esc_html_e("For sites that automatically generate .webp versions of their image files, check this option to prevent the .webp files from being added to the media library when syncing.","maxgalleria-media-library")?></p>        
            <?php
            break;
          endswitch; ?>
                                  
      </div>
    </div>    

    <div id="mlfp-help-panel" style="display:none">
      <div id="mlfp-help-panel-inner" >
        
        <?php switch($tab) :
          case 'access':
            $this->bda_help();
            break;
          default:
            ?>
            <div><?php esc_html_e('Settings Help', 'maxgalleria-media-library' ); ?></div>

            <p><strong><?php esc_html_e('Number of images to display.', 'maxgalleria-media-library'); ?></strong> <?php esc_html_e('This option sets the number of images displayed in the Folders and Files page. By default, it is set at 500 images but it can be changed to a higher or lower amount.', 'maxgalleria-media-library' ); ?></p>
            <p><strong><?php esc_html_e('Disable large image scaling.', 'maxgalleria-media-library'); ?></strong> <?php esc_html_e('By default, WordPress will scale down very large images so they will take up less space and load faster. Checking this option will turn this feature off.', 'maxgalleria-media-library' ); ?></p>
            <p><strong><?php esc_html_e("Add an index to the postmeta table","maxgalleria-media-library")?></strong>, <?php esc_html_e("For sites with a large number of media files, check this option to create a new index fro the postmeta table to speed by the loading of the Media Library Folders Pro page.","maxgalleria-media-library")?></p>
            <p><strong><?php esc_html_e("Skip .webp images when syncing media library files","maxgalleria-media-library")?></strong>, <?php esc_html_e("For sites that automatically generate .webp versions of their image files, check this option to prevent the .webp files from being added to the media library when syncing.","maxgalleria-media-library")?></p>
            <?php
            break;
          endswitch; ?>
                
      </div>
    </div>


    <div class="tab-content" style="padding-right: 20px;">
    <?php switch($tab) :
      case 'access':
        $this->block_access_settings();
        break;
      default:
        $this->mlpp_settings();
        break;
      endswitch; ?>
    </div>
        
  </div>
  
  
      
    </div><!--mlfp-tab-section-->
    
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

