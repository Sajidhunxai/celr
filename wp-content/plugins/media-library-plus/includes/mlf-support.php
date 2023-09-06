
<div id="wp-media-grid" class="wrap">
  
  <div class="mlfp-container">
    
    <?php echo $this->display_mlfp_header() ?>
    
    <h1 id="mlfp-page-title"><?php esc_html_e('Support', 'maxgalleria-media-library' ); ?></h1>
    <div class="mlfp-tab-section">
      
    <?php     
   //Get the active tab from the $_GET param
     $default_tab = null;
     $tab = sanitize_textarea_field(isset($_GET['tab']) ? $_GET['tab'] : $default_tab);

     ?>
     <!-- Our admin page content should all be inside .wrap -->
     <div class="wrap">
       <nav class="nav-tab-wrapper">
         <a href="?page=mlf-support8" class="nav-tab <?php if($tab===null):?>nav-tab-active<?php endif; ?>">Troubleshooting Tips</a>
         <a href="?page=mlf-support8&tab=artilces" class="nav-tab <?php if($tab==='artilces'):?>nav-tab-active<?php endif; ?>">Helpful Articles</a>
         <a href="?page=mlf-support8&tab=system-info" class="nav-tab <?php if($tab==='system-info'):?>nav-tab-active<?php endif; ?>">System Info</a>
       </nav>

       <div class="tab-content">
       <?php switch($tab) :

         case 'artilces':
           $this->support_articles();
           break;

         case 'system-info':
           $this->support_sys_info();
           break;

         default:
           $this->support_tips();
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
