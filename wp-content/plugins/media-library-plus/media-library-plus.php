<?php
/*
Plugin Name: Media Library Folders
Plugin URI: http://maxgalleria.com
Description: Gives you the ability to adds folders and move files in the WordPress Media Library.
Version: 8.1.0
Author: Max Foundry
Author URI: http://maxfoundry.com

Copyright 2015-2022 Max Foundry, LLC (http://maxfoundry.com)
*/

if(defined('MAXGALLERIA_MEDIA_LIBRARY_VERSION_KEY')) {
   wp_die(esc_html__('You must deactivate Media Library Folders before activating Media Library Folders Pro', 'maxgalleria-media-library'));
}

include_once(__DIR__ . '/includes/attachments.php');

class MGMediaLibraryFolders {
    
  public $upload_dir;
  public $wp_version;
  public $theme_mods;
	public $uploads_folder_name;
	public $uploads_folder_name_length;
	public $uploads_folder_ID;
	public $blog_id;
	public $base_url_length;
  public $disable_scaling;
  public $current_user_can_upload;
  public $current_user_manage_options;
  public $sync_skip_webp;
  public $bda;
  public $protected_content_dir;
  public $bda_user_role;
  public $bda_folder_id;
  public $bdp_autoprotect;
  

  public function __construct() {
    
		$this->blog_id = 0;
		$this->set_global_constants();
		$this->set_activation_hooks();
		$this->setup_hooks();       
		$this->upload_dir = wp_upload_dir();  
    $this->wp_version = get_bloginfo('version'); 
	  $this->base_url_length = strlen($this->upload_dir['baseurl']) + 1;
        
    $this->uploads_folder_name = get_option(MAXGALLERIA_MEDIA_LIBRARY_UPLOAD_FOLDER_NAME, "uploads");      
    $this->uploads_folder_name_length = strlen($this->uploads_folder_name);
    $this->uploads_folder_ID = get_option(MAXGALLERIA_MEDIA_LIBRARY_UPLOAD_FOLDER_ID, 0);
    $this->sync_skip_webp = get_option(MLFP_SKIP_WEBP_FILES, 'off');    
    
    $this->bda = get_option(MLFP_BDA, 'off');    
    $this->protected_content_dir = $this->upload_dir['basedir'] . '/' . MLFP_PROTECTED_DIRECTORY;
    $this->bda_user_role = get_option(MLFP_BDA_USER_ROLE, 'admins');
    $this->bdp_autoprotect = get_option(MLFP_BDA_AUTO_PROTECT, 'off');
    $this->display_fe_protected_images = get_option(MLFP_BDA_DISPLAY_FE_IMAGES, 'off');
    $this->prevent_right_click = get_option(MLFP_BDA_PREVENT_RIGHT_CLICK, 'off');    
        
    //convert theme mods into an array
    $theme_mods = get_theme_mods();
    $this->theme_mods = json_decode(json_encode($theme_mods), true);
		        
    add_option( MAXGALLERIA_MEDIA_LIBRARY_SORT_ORDER, '0' );    
    add_option( MAXGALLERIA_MLF_SORT_TYPE, 'ASC' );    
    add_option( MAXGALLERIA_MEDIA_LIBRARY_MOVE_OR_COPY, 'on' );    
        
  }
  
  
	public function set_global_constants() {	
		define('MAXGALLERIA_MEDIA_LIBRARY_VERSION_KEY', 'maxgalleria_media_library_version');
		define('MAXGALLERIA_MEDIA_LIBRARY_VERSION_NUM', '8.1.0');
		define('MAXGALLERIA_MEDIA_LIBRARY_IGNORE_NOTICE', 'maxgalleria_media_library_ignore_notice');
		define('MAXGALLERIA_MEDIA_LIBRARY_PLUGIN_NAME', trim(dirname(plugin_basename(__FILE__)), '/'));
    if(!defined('MAXGALLERIA_MEDIA_LIBRARY_PLUGIN_DIR'))
		  define('MAXGALLERIA_MEDIA_LIBRARY_PLUGIN_DIR', WP_PLUGIN_DIR . '/' . MAXGALLERIA_MEDIA_LIBRARY_PLUGIN_NAME);
    if(!defined('MAXGALLERIA_MEDIA_LIBRARY_PLUGIN_URL'))
		  define('MAXGALLERIA_MEDIA_LIBRARY_PLUGIN_URL', plugin_dir_url('') . MAXGALLERIA_MEDIA_LIBRARY_PLUGIN_NAME);
		if(!defined('MAXGALLERIA_MEDIA_LIBRARY_NONCE'))
      define("MAXGALLERIA_MEDIA_LIBRARY_NONCE", "mgmlp_nonce");
		if(!defined('MAXGALLERIA_MEDIA_LIBRARY_POST_TYPE'))
      define("MAXGALLERIA_MEDIA_LIBRARY_POST_TYPE", "mgmlp_media_folder");
    define("MAXGALLERIA_MEDIA_LIBRARY_UPLOAD_FOLDER_NAME", "mgmlp_upload_folder_name");
    if(!defined("MAXGALLERIA_MEDIA_LIBRARY_UPLOAD_FOLDER_ID"))
      define("MAXGALLERIA_MEDIA_LIBRARY_UPLOAD_FOLDER_ID", "mgmlp_upload_folder_id");
		if(!defined('MAXGALLERIA_MEDIA_LIBRARY_FOLDER_TABLE'))
      define("MAXGALLERIA_MEDIA_LIBRARY_FOLDER_TABLE", "mgmlp_folders");    
        
		if(!defined('MAXGALLERIA_MEDIA_LIBRARY_SORT_ORDER'))
      define("MAXGALLERIA_MEDIA_LIBRARY_SORT_ORDER", "mgmlp_sort_order");
		if(!defined('NEW_MEDIA_LIBRARY_VERSION'))
      define("NEW_MEDIA_LIBRARY_VERSION", "4.0.0");
		if(!defined('MAXGALLERIA_MLP_REVIEW_NOTICE'))
      define("MAXGALLERIA_MLP_REVIEW_NOTICE", "maxgalleria_mlp_review_notice");
    define("MAXGALLERIA_MLP_FEATURE_NOTICE", "maxgalleria_mlp_feature_notice");
		if(!defined('MAXGALLERIA_MEDIA_LIBRARY_SRC_FIX'))
      define("MAXGALLERIA_MEDIA_LIBRARY_SRC_FIX", "mgmlp_src_fix");
		define("UPGRADE_TO_PRO_LINK", "https://maxgalleria.com/downloads/media-library-plus-pro/");    
    if(!defined('MAXGALLERIA_MEDIA_LIBRARY_MOVE_OR_COPY'))
      define("MAXGALLERIA_MEDIA_LIBRARY_MOVE_OR_COPY", "mgmlp_move_or_copy");
    if(!defined('MAXGALLERIA_MEDIA_LIBRARY_IMAGE_SEO'))
      define("MAXGALLERIA_MEDIA_LIBRARY_IMAGE_SEO", "mgmlp_image_seo");
    if(!defined('MAXGALLERIA_MEDIA_LIBRARY_ATL_DEFAULT'))
      define("MAXGALLERIA_MEDIA_LIBRARY_ATL_DEFAULT", "mgmlp_default_alt");
    if(!defined('MAXGALLERIA_MEDIA_LIBRARY_TITLE_DEFAULT'))
      define("MAXGALLERIA_MEDIA_LIBRARY_TITLE_DEFAULT", "mgmlp_default_title");
    //define("MAXGALLERIA_MEDIA_LIBRARY_BACKUP_TABLE", "mgmlp_old_posts");
		//define("MAXGALLERIA_MEDIA_LIBRARY_POSTMETA_UPDATED", "mgmlp_postmeta_updated");
		
		define("MLF_TS_URL", "https://wordpress.org/plugins/media-library-plus/faq/");
		define("MAXGALLERIA_MLP_DISPLAY_INFO", "mlf_display_info");
		define("MAXGALLERIA_MLP_DISABLE_FT", "mlf_disable_ft");		
		define("MAXG_SYNC_FOLDER_PATH", "mlfp_sync_folder_path");		
		define("MAXG_SYNC_FOLDER_PATH_ID", "mlfp_sync_folder_path_id");		
		define("MAXG_SYNC_FILES", "mlfp_sync_files");		
    define("MAXG_SYNC_FOLDERS", "mlfp_sync_folders");
    define("MAXG_MC_FILES", "mlfp_move_file_ids");
    define("MAXG_MC_DESTINATION_FOLDER", "mlfp_move_file_destination");
		if(!defined('MAXGALLERIA_DISABLE_SCALLING'))
      define("MAXGALLERIA_DISABLE_SCALLING", "mlfp_disable_scaling");
		if(!defined('MAXGALLERIA_MLP_ITEMS_PRE_PAGE'))
		  define("MAXGALLERIA_MLP_ITEMS_PRE_PAGE", "mlf_items_per_page");		
    define('MLF_WP_CONTENT_FOLDER_NAME', basename(WP_CONTENT_DIR));
		if(!defined('MAXGALLERIA_MLF_SORT_TYPE'))
		  define("MAXGALLERIA_MLF_SORT_TYPE", "mlf_sort_order_type");		
		if(!defined('MAXGALLERIA_POSTMETA_INDEX'))    
      define('MAXGALLERIA_POSTMETA_INDEX', 'mgmlp-index');
		if(!defined('MLFP_SKIP_WEBP_FILES'))    
      define("MLFP_SKIP_WEBP_FILES", "mlfp-skip-webp-files");
    
		if(!defined('MLFP_BDA'))    
      define("MLFP_BDA", "mlfp-bda");
		if(!defined('MLFP_PROTECTED_DIR'))    
      define("MLFP_PROTECTED_DIR", "mlfp-protected-content-dir");
		if(!defined('MLFP_BDA_DIR_LISTING'))    
      define("MLFP_BDA_DIR_LISTING", "mlfp-bda-dir-listing");
		if(!defined('MLFP_BDA_HOTLINKING'))    
      define("MLFP_BDA_HOTLINKING", "mlfp-bda-hotlinking");
		if(!defined('MLFP_PROTECTED_DIRECTORY'))    
      define("MLFP_PROTECTED_DIRECTORY", "protected-content");
		if(!defined('MAXGALLERIA_MEDIA_LIBRARY_BLOCK_ACCESS_TABLE'))    
      define("MAXGALLERIA_MEDIA_LIBRARY_BLOCK_ACCESS_TABLE", "mgmlp_block_access"); 
		if(!defined('MLFP_BDA_USER_ROLE'))    
      define("MLFP_BDA_USER_ROLE","mlfp-bda-user-role");
		if(!defined('MLFP_BDA_MEDIA'))    
      define("MLFP_BDA_MEDIA", "mlfp-bda-media");
		if(!defined('MLFP_BDA_DOWNLOAD_PAGE'))    
      define("MLFP_BDA_DOWNLOAD_PAGE", "mlfp-download-page");
		if(!defined('MLFP_BDA_AUTO_PROTECT'))    
      define("MLFP_BDA_AUTO_PROTECT", "mlfp-bda-auto-protect");    
    
		if(!defined('MLFP_BDA_DISPLAY_FE_IMAGES'))    
      define("MLFP_BDA_DISPLAY_FE_IMAGES", "mlfp-bda-display-fe-images");
		if(!defined('MLFP_BDA_PREVENT_RIGHT_CLICK'))    
      define("MLFP_BDA_PREVENT_RIGHT_CLICK", "mlfp-bda-prevent-right-click");
		if(!defined('MLFP_BDA_AUTO_PROTECT_DISABLED'))    
      define("MLFP_BDA_AUTO_PROTECT_DISABLED", "mlfp-bda-auto-protect-disabled");
		if(!defined('MLFP_NO_ACCESS_PAGE_ID'))    
      define("MLFP_NO_ACCESS_PAGE_ID", "mlfp-no-access-page-id");
		if(!defined('MLFP_NO_ACCESS_PAGE_TITLE'))    
      define("MLFP_NO_ACCESS_PAGE_TITLE", "mlfp-no-access-page-id-title");
		if(!defined('BLOCKED_IPS_TABLE'))    
      define("BLOCKED_IPS_TABLE","mgmlp_blocked_ips");
    
    if(!defined('MGMLP_FILTER_SET_UPDATE_TABLE_LINKS'))
      define('MGMLP_FILTER_SET_UPDATE_TABLE_LINKS', 'mlfp_filter_update_tables_links');
    if(!defined('MGMLP_FILTER_SET_UPDATE_TABLE_FIELDS'))
      define('MGMLP_FILTER_SET_UPDATE_TABLE_FIELDS', 'mlfp_filter_update_tables_fields');
    

		// Bring in all the actions and filters
		require_once 'includes/maxgalleria-media-library-hooks.php';    
            
  }           
          
  public function setup_hooks() {
    
    global $pagenow;
    
		add_action('init', array($this, 'load_textdomain'));
	  add_action('init', array($this, 'register_mgmlp_post_type'));
    add_action('init', array($this, 'get_upload_status'));

	  add_action('admin_init', array($this, 'ignore_notice'));
    
		add_action('admin_print_styles', array($this, 'enqueue_admin_print_styles'));
		add_action('admin_print_scripts', array($this, 'enqueue_admin_print_scripts'));
    add_action('admin_menu', array($this, 'setup_mg_media_plus'));
    
    $this->disable_scaling = get_option( MAXGALLERIA_DISABLE_SCALLING, 'off');
    if($this->disable_scaling == 'on') {
      add_filter( 'big_image_size_threshold', '__return_false' );
    } 
    		        
    add_action('wp_ajax_nopriv_create_new_folder', array($this, 'create_new_folder'));
    add_action('wp_ajax_create_new_folder', array($this, 'create_new_folder'));
    
    add_action('wp_ajax_nopriv_delete_maxgalleria_media', array($this, 'delete_maxgalleria_media'));
    add_action('wp_ajax_delete_maxgalleria_media', array($this, 'delete_maxgalleria_media'));
    
    add_action('wp_ajax_nopriv_upload_attachment', array($this, 'upload_attachment'));
    add_action('wp_ajax_upload_attachment', array($this, 'upload_attachment'));
    
    add_action('wp_ajax_nopriv_copy_media', array($this, 'copy_media'));
    add_action('wp_ajax_copy_media', array($this, 'copy_media'));
        
    add_action('wp_ajax_nopriv_move_media', array($this, 'move_media'));
    add_action('wp_ajax_move_media', array($this, 'move_media'));
    
    add_action('wp_ajax_nopriv_add_to_max_gallery', array($this, 'add_to_max_gallery'));
    add_action('wp_ajax_add_to_max_gallery', array($this, 'add_to_max_gallery'));
    
    add_action('wp_ajax_nopriv_maxgalleria_rename_image', array($this, 'maxgalleria_rename_image'));
    add_action('wp_ajax_maxgalleria_rename_image', array($this, 'maxgalleria_rename_image'));
        
    add_action('wp_ajax_nopriv_sort_contents', array($this, 'sort_contents'));
    add_action('wp_ajax_sort_contents', array($this, 'sort_contents'));
		
    add_action('wp_ajax_nopriv_mgmlp_move_copy', array($this, 'mgmlp_move_copy'));
    add_action('wp_ajax_mgmlp_move_copy', array($this, 'mgmlp_move_copy'));		
        
    add_action( 'new_folder_check', array($this,'admin_check_for_new_folders'));
    
    add_action('wp_ajax_nopriv_mlf_check_for_new_folders', array($this, 'mlf_check_for_new_folders'));
    add_action('wp_ajax_mlf_check_for_new_folders', array($this, 'mlf_check_for_new_folders'));

    add_action('wp_ajax_nopriv_mlfp_display_bda_info', array($this, 'mlfp_display_bda_info'));
    add_action('wp_ajax_mlfp_display_bda_info', array($this, 'mlfp_display_bda_info'));    
    
    //add_action( 'add_attachment', array($this,'add_attachment_to_folder'));
    
    add_filter( 'wp_generate_attachment_metadata', array($this, 'add_attachment_to_folder2'), 10, 4);    
        
    add_action( 'delete_attachment', array($this,'delete_folder_attachment'));
		
    //add_action('wp_ajax_nopriv_max_sync_contents', array($this, 'max_sync_contents'));
    //add_action('wp_ajax_max_sync_contents', array($this, 'max_sync_contents'));		
		
    add_action('wp_ajax_nopriv_mlp_tb_load_folder', array($this, 'mlp_tb_load_folder'));
    add_action('wp_ajax_mlp_tb_load_folder', array($this, 'mlp_tb_load_folder'));		
		
    add_action('wp_ajax_nopriv_mlp_load_folder', array($this, 'mlp_load_folder'));
    add_action('wp_ajax_mlp_load_folder', array($this, 'mlp_load_folder'));		
						
		add_action('wp_ajax_nopriv_mlp_display_folder_ajax', array($this, 'mlp_display_folder_contents_ajax'));
    add_action('wp_ajax_mlp_display_folder_contents_ajax', array($this, 'mlp_display_folder_contents_ajax'));		
		
    add_action('wp_ajax_nopriv_mlp_display_folder_contents_images_ajax', array($this, 'mlp_display_folder_contents_images_ajax'));
    add_action('wp_ajax_mlp_display_folder_contents_images_ajax', array($this, 'mlp_display_folder_contents_images_ajax'));		

    add_action('wp_ajax_nopriv_mlpp_hide_template_ad', array($this, 'mlpp_hide_template_ad'));
    add_action('wp_ajax_mlpp_hide_template_ad', array($this, 'mlpp_hide_template_ad'));				
		
    add_action('wp_ajax_nopriv_mlpp_create_new_ng_gallery', array($this, 'mlpp_create_new_ng_gallery'));
    add_action('wp_ajax_mlpp_create_new_ng_gallery', array($this, 'mlpp_create_new_ng_gallery'));				
			
    add_action('wp_ajax_nopriv_mg_add_to_ng_gallery', array($this, 'mg_add_to_ng_gallery'));
    add_action('wp_ajax_mg_add_to_ng_gallery', array($this, 'mg_add_to_ng_gallery'));				
		
    add_action('wp_ajax_nopriv_mgmlp_add_to_gallery', array($this, 'mgmlp_add_to_gallery'));
    add_action('wp_ajax_mgmlp_add_to_gallery', array($this, 'mgmlp_add_to_gallery'));				
		
    add_action('wp_ajax_nopriv_display_folder_nav_ajax', array($this, 'display_folder_nav_ajax'));
    add_action('wp_ajax_mgmlp_display_folder_nav_ajax', array($this, 'display_folder_nav_ajax'));				
		
    add_action('wp_ajax_nopriv_mlp_get_folder_data', array($this, 'mlp_get_folder_data'));
    add_action('wp_ajax_mlp_get_folder_data', array($this, 'mlp_get_folder_data'));		
				
    add_action('wp_ajax_nopriv_regen_mlp_thumbnails', array($this, 'regen_mlp_thumbnails'));
    add_action('wp_ajax_regen_mlp_thumbnails', array($this, 'regen_mlp_thumbnails'));				
		
		add_action( 'wp_ajax_regeneratethumbnail', array( $this, 'ajax_process_image' ) );
		$this->capability = apply_filters( 'regenerate_thumbs_cap', 'manage_options' );

    add_action('wp_ajax_nopriv_mlp_image_seo_change', array($this, 'mlp_image_seo_change'));
    add_action('wp_ajax_mlp_image_seo_change', array($this, 'mlp_image_seo_change'));				

    add_action('wp_ajax_nopriv_hide_maxgalleria_media', array($this, 'hide_maxgalleria_media'));
    add_action('wp_ajax_hide_maxgalleria_media', array($this, 'hide_maxgalleria_media'));						
		
		add_filter( 'body_class', array($this, 'mlf_body_classes'));
		add_filter( 'admin_body_class', array($this, 'mlf_body_classes'));
		
    add_action('wp_ajax_nopriv_mlf_hide_info', array($this, 'mlf_hide_info'));
    add_action('wp_ajax_mlf_hide_info', array($this, 'mlf_hide_info'));						
				
    add_action('wp_ajax_nopriv_set_floating_filetree', array($this, 'set_floating_filetree'));
    add_action('wp_ajax_set_floating_filetree', array($this, 'set_floating_filetree'));						
        
    add_action('wp_ajax_nopriv_mlfp_set_scaling', array($this, 'mlfp_set_scaling'));
    add_action('wp_ajax_mlfp_set_scaling', array($this, 'mlfp_set_scaling'));						
    
    add_action('wp_ajax_nopriv_mlfp_run_sync_process', array($this, 'mlfp_run_sync_process'));
    add_action('wp_ajax_mlfp_run_sync_process', array($this, 'mlfp_run_sync_process'));
    
    add_action('wp_ajax_nopriv_mlfp_process_mc_data', array($this, 'mlfp_process_mc_data'));
    add_action('wp_ajax_mlfp_process_mc_data', array($this, 'mlfp_process_mc_data'));				
    
    add_action('wp_ajax_nopriv_mlf_change_sort_type', array($this, 'mlf_change_sort_type'));
    add_action('wp_ajax_mlf_change_sort_type', array($this, 'mlf_change_sort_type'));
    
    add_action('wp_ajax_nopriv_mlfp_process_bdp', array($this, 'mlfp_process_bdp'));
    add_action('wp_ajax_mlfp_process_bdp', array($this, 'mlfp_process_bdp'));
    
    add_action('wp_ajax_nopriv_mlfp_save_noaccess_page', array($this, 'mlfp_save_noaccess_page'));
    add_action('wp_ajax_mlfp_save_noaccess_page', array($this, 'mlfp_save_noaccess_page'));
    
    add_action('wp_ajax_nopriv_mlfp_bdp_report', array($this, 'mlfp_bdp_report'));
    add_action('wp_ajax_mlfp_bdp_report', array($this, 'mlfp_bdp_report'));
    
    add_action('wp_ajax_nopriv_mlfp_block_new_ip', array($this, 'mlfp_block_new_ip'));
    add_action('wp_ajax_mlfp_block_new_ip', array($this, 'mlfp_block_new_ip'));    
    
    add_action('wp_ajax_nopriv_mlfp_unblock_ips', array($this, 'mlfp_unblock_ips'));
    add_action('wp_ajax_mlfp_unblock_ips', array($this, 'mlfp_unblock_ips'));    
    
    add_action('wp_ajax_nopriv_mlfp_get_block_ips', array($this, 'mlfp_get_block_ips'));
    add_action('wp_ajax_mlfp_get_block_ips', array($this, 'mlfp_get_block_ips'));    
    
    add_action('wp_ajax_nopriv_mlfp_load_image', array($this, 'mlfp_load_image'));
    add_action('wp_ajax_mlfp_load_image', array($this, 'mlfp_load_image'));
    
    add_action('wp_ajax_nopriv_mlfp_load_fe_image', array($this, 'mlfp_load_fe_image'));
    add_action('wp_ajax_mlfp_load_fe_image', array($this, 'mlfp_load_fe_image'));
    
    add_action('wp_ajax_nopriv_mlfp_toggle_file_access', array($this, 'mlfp_toggle_file_access'));
    add_action('wp_ajax_mlfp_toggle_file_access', array($this, 'mlfp_toggle_file_access'));    
    
    add_action('wp_ajax_nopriv_mlfp_update_bda_record', array($this, 'mlfp_update_bda_record'));
    add_action('wp_ajax_mlfp_update_bda_record', array($this, 'mlfp_update_bda_record'));    
    
    add_action('wp_ajax_nopriv_mflp_enable_auto_protect', array($this, 'mflp_enable_auto_protect'));
    add_action('wp_ajax_mflp_enable_auto_protect', array($this, 'mflp_enable_auto_protect'));				
    
    add_action('wp_enqueue_media', array($this, 'mlfp_enqueue_media'), 99, 1);  
    add_filter('wp_prepare_attachment_for_js', array($this, 'bda_prepare_attachment_for_js'), 10, 3);
    add_action('admin_enqueue_scripts', array($this, 'bda_add_class_to_media_library_grid_elements'));
    
    
    $this->bda = get_option(MLFP_BDA, 'off');    
    if($this->bda == 'on') {
      add_action('wp_enqueue_scripts', array($this, 'bda_enqueue_scripts'));
      add_action('wp_footer', array($this, 'mlfp_display_protected_file'));
      add_action('admin_enqueue_scripts', array($this, 'bda_load_protected_file'));      
    }
           				  
  } 
  
  public function bda_enqueue_scripts() {
    wp_enqueue_script('jquery');
  }
  
  // loades javascript file for displaying a protected image on the image edit page
  public function bda_load_protected_file() {  
    global $pagenow;
    
    if($pagenow == 'post.php') {
      
      wp_enqueue_script('jquery');
      
      wp_register_script('mlfp-lpf', MAXGALLERIA_MEDIA_LIBRARY_PLUGIN_URL . '/js/mlfp-lpf.js', array('jquery'), '', true );

      wp_localize_script('mlfp-lpf', 'lpf_ajax', 
        array('ajaxurl' => admin_url( 'admin-ajax.php'),
              'nonce'=> wp_create_nonce(MAXGALLERIA_MEDIA_LIBRARY_NONCE))
        ); 

      wp_enqueue_script('mlfp-lpf');
                  
    }
  }
    
  public function mlfp_display_protected_file() {    
    ?>
    <script type="text/javascript">
      
    jQuery(document).ready(function(){
      
      var display_protected_images = '<?php echo esc_js($this->display_fe_protected_images) ?>';
      var prevent_right_click = '<?php echo esc_js($this->prevent_right_click) ?>';
      
      if(prevent_right_click == 'on') {
        jQuery("img").mousedown(function(e){
          e.preventDefault();
        });

        // this will disable right-click on all images
        jQuery("body").on("contextmenu", function(e){
          return false;
        });
      }
            
      if(display_protected_images == 'on') {
        //console.log("display_protected_images on");
        var image_index = 1;
        jQuery("img").each(function () {
          var element = jQuery(this);
          var src = jQuery(this).attr('src');
          var clone = jQuery(this).clone();
          console.log(image_index,src);

          jQuery.ajax({
            url:src,
            type:'GET',
            async: false,
            error:function(response){
              console.log('error src', src);            
              var image_id = 'image' + image_index;
              jQuery(clone).attr('id', image_id);
              jQuery(clone).attr('src', '');
              jQuery(clone).removeAttr('srcset'); 
              // replace with new element in order to loadd the image
              jQuery(element).replaceWith(clone);

              jQuery.ajax({
                type: "POST",
                async: false,
                data: { action: "mlfp_load_image", src: src, nonce: '<?php echo wp_create_nonce(MAXGALLERIA_MEDIA_LIBRARY_NONCE) ?>' },
                url: '<?php echo admin_url('admin-ajax.php') ?>',
                success: function (data) {
                  if(data.length > 0)
                    jQuery('#'+image_id).attr("src", data);
                },
                error: function (err){
                  alert(err.responseText);
                }
              });
            }
          });
          image_index++;
        });  
      }      
    });      
    </script>
    <?php    
  }
  
  /* manually loads an image file */
  public function mlfp_load_image () {
    
    if ( !wp_verify_nonce( $_POST['nonce'], MAXGALLERIA_MEDIA_LIBRARY_NONCE)) {
      exit(esc_html__('Missing nonce! Please refresh this page.','maxgalleria-media-library'));
    }
    
		if ((isset($_POST['src'])) && (strlen(trim($_POST['src'])) > 0))
      $download_file = trim(sanitize_url($_POST['src']));
    else
      $download_file = "";
            
    if(!empty($download_file)) {      
      $file_path = $this->get_absolute_path($download_file);
      if(file_exists($file_path)) {
        $type = pathinfo($file_path, PATHINFO_EXTENSION);
        $data = file_get_contents($file_path);
        $base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);     
        echo $base64;          
      }
    }
    
    die();
  }
  
  /* manually load image on the front end of the site */
  public function mlfp_load_fe_image () {
    
    if ( !wp_verify_nonce( $_POST['nonce'], MAXGALLERIA_MEDIA_LIBRARY_NONCE)) {
      exit(esc_html__('Missing nonce! Please refresh this page.','maxgalleria-media-library'));
    }
    
		if ((isset($_POST['src'])) && (strlen(trim($_POST['src'])) > 0))
      $download_file = trim(sanitize_url($_POST['src']));
    else
      $download_file = "";
    
		if ((isset($_POST['image_id'])) && (strlen(trim($_POST['image_id'])) > 0))
      $image_id = trim(sanitize_url($_POST['image_id']));
    else
      $image_id = "";  
                        
    if(!empty($download_file)) {
      $file_path = $this->get_absolute_path($download_file);
      if(file_exists($file_path)) {
        $type = pathinfo($file_path, PATHINFO_EXTENSION);
        $data = file_get_contents($file_path);
        $base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);     
        echo $base64;          
      }
    } else {
      echo null;
    }
    
    die();
  }
  
  public function get_attachment_author($image_id) {
    
    global $wpdb;
    
    $sql = $wpdb->prepare("select post_author from $wpdb->posts where ID = %s", $image_id);
    
    $post_author = $wpdb->get_var($sql);
    
    return $post_author;
    
  }
  
  public function mlfp_remove_protected_folders() {
    
    if ( !wp_verify_nonce( $_POST['nonce'], MAXGALLERIA_MEDIA_LIBRARY_NONCE)) {
      exit(esc_html__('Missing nonce! Please refresh this page.','maxgalleria-media-library'));
    } 
    
    $this->remove_protected_folders();
        
    die();
  }
  
  public function remove_protected_folders() {
    
    $folders = array();
            
    $protected_content_path = $this->get_absolute_path($this->protected_content_dir);    
        
    $iterator = new RecursiveDirectoryIterator($protected_content_path);

    $iterator->setFlags(RecursiveDirectoryIterator::SKIP_DOTS);

    $directories = new ParentIterator($iterator);

    // check for empty folders
    foreach (new RecursiveIteratorIterator($directories, RecursiveIteratorIterator::SELF_FIRST) as $dir) {      
      $folder_path = $dir->getPathname();
      $sub_iterator = new FilesystemIterator($folder_path);
      if(!$sub_iterator->valid()) {
        $folders[] = $folder_path;
      }
    }
    
    // delete the empty folders
    foreach($folders as $folder) {
      if(file_exists($folder)) {
        rmdir($folder);
      }
    }          
  }
    
  
  
 	public function set_activation_hooks() {
		register_activation_hook(__FILE__, array($this, 'do_activation'));
		register_deactivation_hook(__FILE__, array($this, 'do_deactivation'));
	}
  
  public function do_activation($network_wide) {
	  $this->activate();
	}
	
	public function do_deactivation($network_wide) {	
    $this->deactivate();
	}
  
	public function activate() {
    update_option(MAXGALLERIA_MEDIA_LIBRARY_VERSION_KEY, MAXGALLERIA_MEDIA_LIBRARY_VERSION_NUM);
    //update_option('uploads_use_yearmonth_folders', 1);    
    $this->add_folder_table();
    $this->add_block_access_table();
    $this->add_blocked_ips_table();    
		//update_option('mgmlp_database_checked', 'off', true);
		
    if ( 'impossible_default_value_1234' === get_option( MAXGALLERIA_MEDIA_LIBRARY_UPLOAD_FOLDER_NAME, 'impossible_default_value_1234' ) ) {
      $this->scan_attachments();
      $this->admin_check_for_new_folders(true);
		  update_option(MAXGALLERIA_MEDIA_LIBRARY_SRC_FIX, true);
    } 
		
    $current_user_id = get_current_user_id();     
    $havemeta = get_user_meta( $current_user_id, MAXGALLERIA_MLP_FEATURE_NOTICE, true );
    if ($havemeta === '') {
      $review_date = date('Y-m-d', strtotime("+1 days"));        
      update_user_meta( $current_user_id, MAXGALLERIA_MLP_FEATURE_NOTICE, $review_date );      
    }
    
    if($this->bda == 'on') {
      add_filter('mod_rewrite_rules', array( $this, 'mlfp_update_htaccess'));
      flush_rewrite_rules();
    }      
				
    if ( ! wp_next_scheduled( 'new_folder_check' ) )
      wp_schedule_event( time(), 'daily', 'new_folder_check' );
            
	}
  
  public function deactivate() {
    
  }
	
	public function check_for_old_multisite() {
		
		$content_folder = apply_filters( 'mlfp_content_folder', 'wp-content');
		$upload_sites = ABSPATH . $content_folder . DIRECTORY_SEPARATOR . "uploads";
    		
		if(!file_exists($upload_sites)) 
			return false;
		else
			return true;
	}
  
  
  public function enqueue_admin_print_styles() {
    
    //error_log("enqueue_admin_print_styles");
    
    global $pagenow;

    if(isset($_REQUEST['page'])) {
      
      //error_log("page " . $_REQUEST['page']);
      	
      // on these pages load our styles and scripts
      if($_REQUEST['page'] == 'mlf-folders8'              
        || $_REQUEST['page'] == 'search-library'         
				|| $_REQUEST['page'] == 'mlf-thumbnails'
				|| $_REQUEST['page'] == 'mlf-image-seo' 
				|| $_REQUEST['page'] == 'mlf-settings8'
				|| $_REQUEST['page'] == 'mlf-support8') {
                
        wp_enqueue_style('mlf8', MAXGALLERIA_MEDIA_LIBRARY_PLUGIN_URL . '/css/mlf.css');				
        wp_enqueue_style('mlfp-fontawesome', MAXGALLERIA_MEDIA_LIBRARY_PLUGIN_URL . '/libs/fontawesome-free-6.0.0-web/css/all.min.css');        
                
        if($_REQUEST['page'] === 'mlf-folders8' || 
           $_REQUEST['page'] === 'mlf-thumbnails' ||
           $_REQUEST['page'] === 'search-library') {

          //wp_enqueue_style('jstree-style', esc_url(MAXGALLERIA_MEDIA_LIBRARY_PLUGIN_URL . '/js/jstree/themes/default/style.css'));    		
          wp_enqueue_style('mlf8-jstree-style', MAXGALLERIA_MEDIA_LIBRARY_PLUGIN_URL . '/js/jstree/themes/default/style.min.css');    		

          wp_enqueue_script('jquery');

          wp_enqueue_script('jquery-ui');
          wp_enqueue_script('jquery-ui-core');
          wp_enqueue_script('jquery-ui-progressbar');
          wp_enqueue_script('jquery-ui-draggable');
          wp_enqueue_script('jquery-ui-droppable');

          wp_enqueue_script('jquery-ui-widget');
          wp_enqueue_script('jquery-ui-mouse');
          wp_enqueue_script('jquery-ui-position');
          wp_enqueue_script('jquery-ui-resizable');
          wp_enqueue_script('jquery-ui-selectable');
          wp_enqueue_script('jquery-ui-sortable');

          wp_register_script('jstree', MAXGALLERIA_MEDIA_LIBRARY_PLUGIN_URL . '/js/jstree/jstree.min.js', array('jquery'));
          wp_enqueue_script('jstree');
        }
        
        if($_REQUEST['page'] == 'mlf-settings8' && isset($_REQUEST['tab']) && $_REQUEST['tab'] == 'access') {
          wp_enqueue_script('jquery');
          wp_enqueue_style('select2', MAXGALLERIA_MEDIA_LIBRARY_PLUGIN_URL . '/js/select2/css/select2.min.css', false, null );      
          wp_enqueue_script('select2', MAXGALLERIA_MEDIA_LIBRARY_PLUGIN_URL . '/js/select2/js/select2.min.js');    		        
        }          
                        
      } else if ($_REQUEST['page'] === 'mlp-upgrade-to-pro') {
        wp_enqueue_style('media-library-upgrade-to-pro', esc_url(MAXGALLERIA_MEDIA_LIBRARY_PLUGIN_URL . '/css/upgrade-to-pro.css'));
			}      
									
    }    
    
  }
  
  public function enqueue_admin_print_scripts() {
    
    global $pagenow, $current_screen;

    if(isset($_REQUEST['page'])) {
      
     // error_log("page " . $_REQUEST['page']);
      	
      // on these pages load our styles and scripts
      if($_REQUEST['page'] == 'mlf-folders8'              
        || $_REQUEST['page'] == 'search-library'         
				|| $_REQUEST['page'] == 'mlf-thumbnails'
				|| $_REQUEST['page'] == 'mlf-image-seo' 
				|| $_REQUEST['page'] == 'mlf-settings8'
				|| $_REQUEST['page'] == 'mlf-support8') {
                     
          wp_enqueue_script('jquery');
          wp_enqueue_script('jquery-migrate', esc_url(ABSPATH . WPINC . '/js/jquery/jquery-migrate.min.js'), array('jquery'));            

          wp_register_script( 'loader-folders', esc_url(MAXGALLERIA_MEDIA_LIBRARY_PLUGIN_URL . '/js/mgmlp-loader.js'), array( 'jquery' ), '', true );

          wp_localize_script( 'loader-folders', 'mgmlp_ajax', 
                array( 'ajaxurl' => admin_url( 'admin-ajax.php' ),
                       'confirm_file_delete' => esc_html__('Are you sure you want to delete the selected files?', 'maxgalleria-media-library' ),
                       'nothing_selected' => esc_html__('No items were selected.', 'maxgalleria-media-library' ),
                       'no_images_selected' => esc_html__('No images were selected.', 'maxgalleria-media-library' ),
                       'no_quotes' => esc_html__('Folder names cannot contain single or double quotes.', 'maxgalleria-media-library' ),
                       'no_spaces' => esc_html__('Folder names cannot contain spaces.', 'maxgalleria-media-library' ),
                       'no_blank' => esc_html__('The folder name cannot be blank.' ),
                       'no_blank_filename' => esc_html__('The new file name cannot be blank.' ),                  
                       'valid_file_name' => esc_html__('Please enter a valid file name with no spaces.', 'maxgalleria-media-library' ),
                       'move_mode' => esc_html__('Drag and drop is set for moving files', 'maxgalleria-media-library' ),
                       'copy_mode' => esc_html__('Drag and drop is set for copying files', 'maxgalleria-media-library' ),
                       'folder_check' => esc_html__('Checking for new folders...', 'maxgalleria-media-library' ),
                       'bda_user_role' => $this->bda_user_role,  
                       'link_copied' => esc_html__('download link has been copied to the clipboard', 'maxgalleria-media-library'),
                       'nonce'=> wp_create_nonce(MAXGALLERIA_MEDIA_LIBRARY_NONCE))
                     ); 

          wp_enqueue_script('loader-folders');

          //error_log("loader-folders loaded");
      }
    }
    
    if (isset( $current_screen ) && 'upload' === $current_screen->base) {
      $uploads_js_ver  = date("ymd-Gis", filemtime(MAXGALLERIA_MEDIA_LIBRARY_PLUGIN_DIR . '/js/uploads-media.js'));            
      wp_register_script( 'uploads-media', MAXGALLERIA_MEDIA_LIBRARY_PLUGIN_URL . '/js/uploads-media.js', array( 'jquery', 'media-models', ), $uploads_js_ver, true );
      wp_enqueue_script('uploads-media');
    }
  }
  
  public function mlfp_enqueue_media() {
    
    wp_enqueue_style('bda-media', MAXGALLERIA_MEDIA_LIBRARY_PLUGIN_URL . '/css/bda-media.css');
             
    $media_js_ver  = date("ymd-Gis", filemtime(MAXGALLERIA_MEDIA_LIBRARY_PLUGIN_DIR . '/js/mlfp-media.js'));            
    wp_register_script( 'mlfp-media', MAXGALLERIA_MEDIA_LIBRARY_PLUGIN_URL . '/js/mlfp-media.js', array( 'jquery', 'media-models', ), $media_js_ver, true );

    wp_localize_script( 'mlfp-media', 'mlfpmedia', $this->media_localize());

    wp_enqueue_script('mlfp-media');
    
    if($this->bda == 'on') {
      wp_enqueue_script('jquery');
      wp_enqueue_script('bda-media', MAXGALLERIA_MEDIA_LIBRARY_PLUGIN_URL . '/js/bda-media.js', array('jquery')); 
    }
    
  }
  
  public function media_localize() {
    
    $upload_id = get_option(MAXGALLERIA_MEDIA_LIBRARY_UPLOAD_FOLDER_ID );        
    $theme = get_option('template');
    $user = wp_get_current_user();    
        
    return array( 
      'ajaxurl' => admin_url( 'admin-ajax.php' ),
      'nonce'=> wp_create_nonce(MAXGALLERIA_MEDIA_LIBRARY_NONCE),
      'upload_message' => esc_html__('Select the folder where you wish to view or upload files.', 'maxgalleria-media-library'),
      'uploads_folder_id' => $upload_id,
      'bda' => $this->bda,
      'bda_user_role' => $this->bda_user_role,  
      'display_btn_text' => esc_html__('Display Blocked Files', 'maxgalleria-media-library'),
      'theme' => get_option('template'),
		  'gutenberg' => $this->gutenberg_active(),
      'location' => esc_html__('Location: ', 'maxgalleria-media-library')
    );   
    
  }  
  
  public function gutenberg_active() {
      // Gutenberg plugin is installed and activated.
      $gutenberg = ! ( false === has_filter( 'replace_editor', 'gutenberg_init' ) );

      // Block editor since 5.0.
      $block_editor = version_compare( $GLOBALS['wp_version'], '5.0-beta', '>' );

      if ( ! $gutenberg && ! $block_editor ) {
        return false;
      }

      if ( $this->classic_editor_plugin_active() ) {
        $editor_option = get_option( 'classic-editor-replace' );
        $block_editor_active = array( 'no-replace', 'block' );

        return in_array( $editor_option, $block_editor_active, true );
      }

      return true;
  }

  public function classic_editor_plugin_active() {
    if ( ! function_exists( 'is_plugin_active' ) ) {
      include_once ABSPATH . 'wp-admin/includes/plugin.php';
    }
  }  
   
  public function setup_mg_media_plus() {
		add_menu_page(esc_html__('Media Library Folders','maxgalleria-media-library'), esc_html__('Media Library Folders','maxgalleria-media-library'), 'upload_files', 'mlf-folders8', array($this, 'mlf_folders'), 'dashicons-admin-media', 11 );				
    add_submenu_page('mlf-folders8', esc_html__('Folders & Files','maxgalleria-media-library'), esc_html__('Folders & Files','maxgalleria-media-library'), 'upload_files', 'mlf-folders8' );    
    add_submenu_page('mlf-folders8', esc_html__('Thumbnails','maxgalleria-media-library'), esc_html__('Thumbnails','maxgalleria-media-library'), 'manage_options', 'mlf-thumbnails', array($this, 'mlfp_thumbnails'));
    add_submenu_page('mlf-folders8', esc_html__('Image SEO','maxgalleria-media-library'), esc_html__('Image SEO','maxgalleria-media-library'), 'upload_files', 'mlf-image-seo', array($this, 'mlfp_image_seo'));
    add_submenu_page('mlf-folders8', esc_html__('Settings','maxgalleria-media-library'), esc_html__('Settings','maxgalleria-media-library'), 'manage_options', 'mlf-settings8', array($this, 'mlfp_settings8'));
    add_submenu_page('mlf-folders8', esc_html__('Support','maxgalleria-media-library'), esc_html__('Support','maxgalleria-media-library'), 'manage_options', 'mlf-support8', array($this, 'mlfp_support'));
    add_submenu_page('mlf-folders8', esc_html__('Upgrade to Pro','maxgalleria-media-library'), esc_html__('Upgrade to Pro','maxgalleria-media-library'), 'upload_files', 'mlp-upgrade-to-pro', array($this, 'mlp_upgrade_to_pro'));		    
    add_submenu_page(null, esc_html__('Search Library','maxgalleria-media-library'), esc_html__('Search Library','maxgalleria-media-library'), 'upload_files', 'search-library', array($this, 'search_library'));
  }  
  
  public function mlf_folders() {
	  require_once 'includes/media-folders.php';
	}  

  public function mlfp_thumbnails() {
	  require_once 'includes/mlf-thumbnails.php';
  }
  
  public function mlfp_image_seo() {
	  require_once 'includes/mlf-image-seo.php';
  }
  
  public function mlfp_settings8() {
	  require_once 'includes/mlf-settings.php';
  }
            
  public function mlfp_support() {
	  require_once 'includes/mlf-support.php';
  }
  
  public function media_library() {
	  require_once 'includes/media-library.php';
  }
  
  public function support_tips() {
	  require_once 'includes/mlf-support-tips.php';
  }
  
  public function support_articles() {
	  require_once 'includes/mlf-support-articles.php';
  }
  
  public function support_sys_info() {
	  require_once 'includes/mlf-support-sys-info.php';
  }
  
  public function block_access_settings() {
	  require_once 'includes/mlfp-bda-options.php';	 		
	}  
  
      
  public function display_mlfp_header() {
    
    $html = '';
    
		$html .= '<div class="mgmlp-header">' . PHP_EOL;
                      
    $html .= '  <div id="mlfp-logo-container">' . PHP_EOL;
    $html .= '    <img id="mlpf-logo" src="' . esc_url(MAXGALLERIA_MEDIA_LIBRARY_PLUGIN_URL . '/images/mlp_logo.png') . '" alt="media library folders pro logo" width="100" height="100">' . PHP_EOL;
    $html .= '  </div>' . PHP_EOL;
          
    $html .= '  <div id="mlfp-link-container">' . PHP_EOL;
    $html .= '    <div id="mlfp-links">' . PHP_EOL;
    $html .= '      <div>' . esc_html__('Brought to you by ', 'maxgalleria-media-library') .' <a target="_blank" href="http://maxfoundry.com">MaxFoundry</a></div>' . PHP_EOL;
    $html .= '      <div>' . esc_html__('Makers of', 'maxgalleria-media-library') . ' <a target="_blank"  href="http://maxbuttons.com/">MaxButtons</a>, <a target="_blank" href="http://maxbuttons.com/product-category/button-packs/">WordPress Buttons</a> ' . esc_html__('and', 'maxgalleria-media-library') . ' <a target="_blank" href="http://maxgalleria.com/">MaxGalleria</a></div>' . PHP_EOL;
    $html .= '    </div>' . PHP_EOL;
          
    $html .= '    <div id="mlfp-support">' . PHP_EOL;
    $html .= '      <div><strong>Quick Support</strong></div>' . PHP_EOL;
    $html .= '      <div>' . esc_html__('Click here to', 'maxgalleria-media-library') . '&nbsp;<a href="' . MLF_TS_URL . '" target="_blank">' . esc_html__('Fix Common Problems', 'maxgalleria-media-library') . '</a></div>' . PHP_EOL;
    $html .= '      <div>' . esc_html__('Need more help? Check out our ', 'maxgalleria-media-library') . ' <a href="https://wordpress.org/support/plugin/media-library-plus" target="_blank">' . esc_html__('Support Forums', 'maxgalleria-media-library') . '</a></div>' . PHP_EOL;
    $html .= '      <div>' . esc_html__('Or Email Us at', 'maxgalleria-media-library' ) . ' <a href="mailto:support@maxfoundry.com">support@maxfoundry.com</a></div>' . PHP_EOL;
    $html .= '    </div>' . PHP_EOL;
    
    $html .= '  </div>' . PHP_EOL;
    
                   
    $html .= '</div>' . PHP_EOL;
    
    return $html;
    
  }
      
	function mlf_body_classes( $classes ) {
		$locale = "locale-" . str_replace('_','-', strtolower(get_locale()));
		if(is_array($classes))
		  $classes[] = $locale;
		else
			$classes .= " " . $locale;
		return $classes;
	}	
						  
  public function delete_folder_attachment ($postid) {    
    global $wpdb;
    $table = $wpdb->prefix . MAXGALLERIA_MEDIA_LIBRARY_FOLDER_TABLE;
    $where = array( 'post_id' => $postid );
    $wpdb->delete( $table, $where );    
  }

    // in case an image is uploaded in the WP media library we
  // need to add a record to the mgmlp_folders table
  public function add_attachment_to_folder ($post_id) {
    
    $folder_id = $this->get_default_folder($post_id); //for non pro version
    if($folder_id !== false) {
      $this->add_new_folder_parent($post_id, $folder_id);
    }  
  }
  
public function add_attachment_to_folder2( $metadata, $attachment_id ) {
    
  $folder_id = $this->get_default_folder($attachment_id);
  if($folder_id !== false && $folder_id != null) {
    $this->add_new_folder_parent($attachment_id, $folder_id);
    
    error_log("bdp_autoprotect " . $this->bdp_autoprotect);    
    if($this->bdp_autoprotect == 'on') {
      $message = $this->move_to_protected_folder($attachment_id, $folder_id, 0);
    }    

  }
  return $metadata;
}  

public function get_parent_by_name($sub_folder) {
    
  global $wpdb;

  $sql = "SELECT post_id FROM {$wpdb->prefix}postmeta where meta_key = '_wp_attached_file' and `meta_value` = '$sub_folder'";

  return $wpdb->get_var($sql);
}
  
  public function get_default_folder($post_id) {
    
    $attached_file = get_post_meta($post_id, '_wp_attached_file', true);
    $folder_path = dirname($attached_file);
    
    $upload_folder_id = get_option(MAXGALLERIA_MEDIA_LIBRARY_UPLOAD_FOLDER_ID);
    
    if($folder_path == '.') {
      $folder_id = $upload_folder_id;
    } else {
      $folder_id = $this->get_parent_by_name($folder_path);
    }
    return $folder_id;
  }

  public function register_mgmlp_post_type() {
    
		$args = apply_filters(MGMLP_FILTER_POST_TYPE_ARGS, array(
			'public' => false,
			'publicly_queryable' => false,
			'show_ui' => false,
      'show_in_nav_menus' => false,
      'show_in_admin_bar' => false,
			'show_in_menu' => false,
			'query_var' => true,
			'hierarchical' => true,
			'supports' => false,
			'exclude_from_search' => true
		));
		
		register_post_type(MAXGALLERIA_MEDIA_LIBRARY_POST_TYPE, $args);
    
  }
  
  public function add_folder_table () {
    global $wpdb;
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

    $table = $wpdb->prefix . MAXGALLERIA_MEDIA_LIBRARY_FOLDER_TABLE;
    $sql = "CREATE TABLE IF NOT EXISTS " . $table . " ( 
  `post_id` bigint(20) NOT NULL,
  `folder_id` bigint(20) NOT NULL,
  PRIMARY KEY (`post_id`)
) DEFAULT CHARSET=utf8;";	
 
    dbDelta($sql);
    
  }
  
  public function add_block_access_table() {
    global $wpdb;
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

    $table = $wpdb->prefix . MAXGALLERIA_MEDIA_LIBRARY_BLOCK_ACCESS_TABLE;
    $sql = "CREATE TABLE IF NOT EXISTS `" . $table . "` ( 
  `attachment_id` bigint(20) NOT NULL,
  `hash_id` varchar(256) NULL,
  `time` datetime NULL,
  `block` tinyint(4) NULL,
  `count` mediumint(9) NULL,
  `download_limit` mediumint(9) NULL,
  `expiration_date` date NULL,
  PRIMARY KEY (`attachment_id`)
  ) DEFAULT CHARSET=utf8;";
    
    dbDelta($sql);
    
  }
  
  public function add_blocked_ips_table() {
    global $wpdb;
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

    $table = $wpdb->prefix . BLOCKED_IPS_TABLE;
    $sql = "CREATE TABLE IF NOT EXISTS `" . $table . "` ( 
    `ip_id` bigint(20) NOT NULL AUTO_INCREMENT,
    `address` varchar(16) NOT NULL,
    PRIMARY KEY (`ip_id`)
    ) DEFAULT CHARSET=utf8;";
    
    dbDelta($sql);
    
  }  
    
  public function upload_attachment () {   
    global $is_IIS;
                  
    if ( !wp_verify_nonce( $_POST['nonce'], MAXGALLERIA_MEDIA_LIBRARY_NONCE)) {
      exit(esc_html__('missing nonce!','maxgalleria-media-library'));
    }
    
    if ((isset($_POST['folder_id'])) && (strlen(trim($_POST['folder_id'])) > 0))
      $folder_id = trim(sanitize_text_field($_POST['folder_id']));
    else
      $folder_id = 0;
    
    if ((isset($_POST['title_text'])) && (strlen(trim($_POST['title_text'])) > 0))
      $seo_title_text = trim(sanitize_text_field($_POST['title_text']));
    else
      $seo_title_text = "";
		
    if ((isset($_POST['alt_text'])) && (strlen(trim($_POST['alt_text'])) > 0))
      $alt_text = trim(sanitize_text_field($_POST['alt_text']));
    else
      $alt_text = "";
		
    $destination = $this->get_folder_path($folder_id);
        
    if(isset($_FILES['file'])){
      if ( 0 < $_FILES['file']['error'] ) {
         echo esc_html('Error: ' . $_FILES['file']['error'] . '<br>');
      } else {


        if(!defined('ALLOW_UNFILTERED_UPLOADS')) {  
          $wp_filetype = wp_check_filetype_and_ext($_FILES['file']['tmp_name'], $_FILES['file']['name'] );

          //error_log(print_r($wp_filetype,true));

          if ($wp_filetype['ext'] === false) {
            ?>
            <script>
            jQuery("#folder-message").html("<span class='mlp-warning'><?php echo esc_html($_FILES['file']['name'] . esc_html__(' file\'s type is invalid.', 'maxgalleria-media-library')); ?></span>");
            </script>
            <?php            
            exit;
          }
        }  

        // insure it has a unique name
        $title_text = $_FILES['file']['name'];    
        $new_filename = wp_unique_filename( $destination, $_FILES['file']['name'], null );

        if ($is_IIS || strtoupper(substr(PHP_OS, 0, 3)) == 'WIN' || strtoupper(substr(PHP_OS, 0, 13)) == 'MICROSOFT-IIS' )
          $destination = rtrim($destination, '\\');

        $filename = $destination . DIRECTORY_SEPARATOR . $new_filename;

        if(file_exists($destination)) {
          if( move_uploaded_file($_FILES['file']['tmp_name'], $filename) ) {

            // Set correct file permissions.
            $stat = stat( dirname( $filename ));
            $perms = $stat['mode'] & 0000664;
            @ chmod( $filename, $perms );

            $attach_id = $this->add_new_attachment($filename, $folder_id, $title_text, $alt_text, $seo_title_text);
            
            error_log("bdp_autoprotect " . $this->bdp_autoprotect);
            if($this->bdp_autoprotect == 'on') {
              $message = $this->move_to_protected_folder($attach_id, $folder_id, 0);
            }
            
            $this->display_folder_contents ($folder_id);

          }
        } else {
          ?>
          <script>
            jQuery("#folder-message").html("<span class='mlp-warning'><?php esc_html__(' Unable to move the file to the destination folder; the folder may not exist.', 'maxgalleria-media-library') ?></span>");
          </script>
          <?php
        }
      }
    }    
    die();
  }
      
  public function add_new_attachment($filename, $folder_id, $title_text="", $alt_text="", $seo_title_text="") {
    
    global $is_IIS;
    $parent_post_id = 0;
    $exif_data = array();
    $ImageDescription = "";
    
    //error_log("add_new_attachment, add_new_attachment, $folder_id");

    //remove_action( 'add_attachment', array($this,'add_attachment_to_folder'));
    remove_filter( 'wp_generate_attachment_metadata', array($this, 'add_attachment_to_folder2'));    

    // Check the type of file. We'll use this as the 'post_mime_type'.
    $filetype = wp_check_filetype( basename( $filename ), null );
    
    if(isset($filetype['type'])) {
      if($filetype['type'] == 'image/jpeg') {
        if(extension_loaded("exif")) {
          $exif_data = exif_read_data($filename);
        }  
      }
    }
    
    // Get the path to the upload directory.
    $wp_upload_dir = wp_upload_dir();
    
    $file_url = $this->get_file_url_for_copy($filename);
		
    $image_seo = get_option(MAXGALLERIA_MEDIA_LIBRARY_IMAGE_SEO, 'off');
    
    if(isset($filetype['type']) && $filetype['type'] == 'image/jpeg') {
      if(isset($exif_data['FileName'])) {
        $title_text = $exif_data['FileName']; 
      }  
      if(isset($exif_data['ImageDescription'])) {
        $ImageDescription = $exif_data['ImageDescription'];
      }  
    }

		// remove the extention from the file name
		$position = strpos($title_text, '.');
		if($position)
			$title_text	= substr ($title_text, 0, $position);
				
		if($image_seo === 'on') {
			
			$folder_name = $this->get_folder_name($folder_id);
			
			$file_name = $this->remove_extension(basename($filename));
			
      $file_name = str_replace('-', ' ', $file_name);      
			
			$new_file_title = $seo_title_text;
			
			$new_file_title = str_replace('%foldername', $folder_name, $new_file_title );			
			
			$new_file_title = str_replace('%filename', $file_name, $new_file_title );			
									
			$default_alt = $alt_text;
			
			$default_alt = str_replace('%foldername', $folder_name, $default_alt );			
			
			$default_alt = str_replace('%filename', $file_name, $default_alt );			
						
		} else {
      //$new_file_title	= preg_replace( '/\.[^.]+$/', '', basename( $filename ) );
			$new_file_title	= $title_text;
		}
				            
    // Prepare an array of post data for the attachment.
    $attachment = array(
      'guid'           => $file_url, 
      'post_mime_type' => $filetype['type'],
      'post_title'     => $new_file_title,
  		'post_parent'    => 0,
      'post_content'   => '',
      'post_excerpt'  => $ImageDescription, 
      'post_status'    => 'inherit'
    );
    
    // Insert the attachment.
    if (! ($attach_id = get_file_attachment_id($filename))) {
      $attach_id = wp_insert_attachment( $attachment, $filename, $parent_post_id );
    }

		if($image_seo == 'on') 
		  update_post_meta($attach_id, '_wp_attachment_image_alt', $default_alt);			
		
    // Make sure that this file is included, as wp_generate_attachment_metadata() depends on it.
    require_once( ABSPATH . 'wp-admin/includes/image.php' );

    // Generate the metadata for the attachment (if it doesn't already exist), and update the database record.
    if (! wp_get_attachment_metadata($attach_id, TRUE)) {
      if ($is_IIS || strtoupper(substr(PHP_OS, 0, 3)) == 'WIN' || strtoupper(substr(PHP_OS, 0, 13)) == 'MICROSOFT-IIS' )
        $attach_data = wp_generate_attachment_metadata( $attach_id, addslashes($filename));
      else
        $attach_data = wp_generate_attachment_metadata( $attach_id, $filename );

      wp_update_attachment_metadata( $attach_id, $attach_data );
    }
   
    if($this->is_windows()) {
      
      // get the uploads dir name
      $basedir = $this->upload_dir['baseurl'];
      $uploads_dir_name_pos = strrpos($basedir, '/');
      $uploads_dir_name = substr($basedir, $uploads_dir_name_pos+1);
    
      //find the name and cut off the part with the uploads path
      $string_position = strpos($filename, $uploads_dir_name);
      $uploads_dir_length = strlen($uploads_dir_name) + 1;
      $uploads_location = substr($filename, $string_position+$uploads_dir_length);
      $uploads_location = str_replace('\\','/', $uploads_location);   
			$uploads_location = ltrim($uploads_location, '/');
      
      // put the short path into postmeta
	    $media_file = get_post_meta( $attach_id, '_wp_attached_file', true );
    
      if($media_file !== $uploads_location )
        update_post_meta( $attach_id, '_wp_attached_file', $uploads_location );
    }

    $this->add_new_folder_parent($attach_id, $folder_id );
    //add_action( 'add_attachment', array($this,'add_attachment_to_folder'));
    add_filter( 'wp_generate_attachment_metadata', array($this, 'add_attachment_to_folder2'), 10, 4);    
    
    return $attach_id;
    
  }
  
  public function update_links($rename_image_location, $rename_destination) {
    
    global $wpdb;
    
    $table_list = apply_filters( MGMLP_FILTER_SET_UPDATE_TABLE_LINKS, '');
    $field_list = apply_filters( MGMLP_FILTER_SET_UPDATE_TABLE_FIELDS, '');
    
    $table_list = str_replace(' ', '', $table_list);
    $field_list = str_replace(' ', '', $field_list);
        
    if(!empty($table_list)) {
      $table_list = "$wpdb->posts," . $table_list;
      $tables = explode(',', $table_list);     
    } else {
      $tables = array("$wpdb->posts"); 
    }
    
    if(!empty($field_list)) {
      $field_list = "post_content," . $field_list;
      $fields = explode(',', $field_list);     
    } else {
      $fields = array("post_content"); 
    }
    
    if(is_array($table_list) || is_array($field_list)) {
      if(count($table_list) != count($field_list)) {
        error_log(__('An unequal number of items were sent to update_links function.','maxgalleria-media-library'));
        return;
      }  
    }
    
    $pairs = array_combine($tables, $fields);
    
    foreach($pairs as $key => $value) {    
      $replace_sql = "UPDATE $key SET `$value` = REPLACE (`$value`, '$rename_image_location', '$rename_destination');";          
      $result = $wpdb->query($replace_sql);
      //error_log("replace_sql $replace_sql");

      $replace_sql = str_replace ( '/', '\\/', $replace_sql);
      $result = $wpdb->query($replace_sql);
    }
    
  }
  
  public function remove_extension($file_name) {
    $position = strrpos($file_name, '.');
    if($position === false)
      return $file_name;
    else
      return substr($file_name, 0, $position);
  }
  
  public function scan_attachments () {
    
    global $wpdb;
            
    $uploads_path = wp_upload_dir();
    
    if(!$uploads_path['error']) {
			      
      //find the uploads folder
      $base_url = $uploads_path['baseurl'];
      $last_slash = strrpos($base_url, '/');
      $uploads_dir = substr($base_url, $last_slash+1);
			$this->uploads_folder_name = $uploads_dir;
			$this->uploads_folder_name_length = strlen($uploads_dir);
            
      update_option(MAXGALLERIA_MEDIA_LIBRARY_UPLOAD_FOLDER_NAME, $uploads_dir);
                              
      //create uploads parent media folder      
      $uploads_parent_id = $this->add_media_folder($uploads_dir, 0, $base_url);
      update_option(MAXGALLERIA_MEDIA_LIBRARY_UPLOAD_FOLDER_ID, $uploads_parent_id);
      
      $baseurl = $this->upload_dir['baseurl'];
      // use for comparisons 
      $uploads_base_url = rtrim($baseurl, '/');
      $baseurl = rtrim($baseurl, '/') . '/';
      
      $sql = "SELECT ID, pm.meta_value as attached_file 
FROM {$wpdb->prefix}posts
LEFT JOIN {$wpdb->prefix}postmeta AS pm ON pm.post_id = {$wpdb->prefix}posts.ID
WHERE post_type = 'attachment' 
AND pm.meta_key = '_wp_attached_file'
ORDER by ID";
			
      $rows = $wpdb->get_results($sql);
      
      $current_folder = "";
            
      $parent_id = $uploads_parent_id;
            
      if($rows) {
        foreach($rows as $row) {
					
				if( strpos($row->attached_file, "http:") !== false || 
						strpos($row->attached_file, "https:") !== false || 
						strpos($row->attached_file, "'") !== false)  {
				} else {
									
						$image_location = $baseurl . ltrim($row->attached_file, '/');
            
            // check for and add files in the uploads or root media library folder
            $uploads_location = $this->strip_base_file($image_location);
            if($uploads_base_url == $uploads_location) {
              $this->add_new_folder_parent($row->ID, $uploads_parent_id);
              continue;
            }  
            
            //check for protected folders
            if(!empty($image_location)) {
              if(strpos($image_location, MLFP_PROTECTED_DIRECTORY)) {
                $image_location = str_replace(MLFP_PROTECTED_DIRECTORY . '/', '', $image_location );              
              }
            }            
																          
            $sub_folders = $this->get_folders($image_location);
            $attachment_file = array_pop($sub_folders);  

            $uploads_length = strlen($uploads_dir);
            $new_folder_pos = strpos($image_location, $uploads_dir ); 
            $folder_path = substr($image_location, 0, $new_folder_pos+$uploads_length );

            foreach($sub_folders as $sub_folder) {
              
              // check for URL path in database
              $folder_path = $folder_path . '/' . $sub_folder;

              $new_parent_id = $this->folder_exist($folder_path);														
              if($new_parent_id === false) {
                if($this->is_new_top_level_folder($uploads_dir, $sub_folder, $folder_path)) {
                  $parent_id = $this->add_media_folder($sub_folder, $uploads_parent_id, $folder_path); 
                } else {
                  $parent_id = $this->add_media_folder($sub_folder, $parent_id, $folder_path); 
                }  
              } else {
                $parent_id = $new_parent_id;
              }  
            }          

            $this->add_new_folder_parent($row->ID, $parent_id );
				  } // test for http
        } //foreach         
        
      } //rows  
			//if ( ! wp_next_scheduled( 'new_folder_check' ) )
			//	wp_schedule_event( time(), 'daily', 'new_folder_check' );
            
    }
		
//		echo "done";
//		die();
        
  }
  
  public function strip_base_file($url){
    $parts = explode("/", $url);
    if(count($parts) < 4) return $url . "/";
    if(strpos(end($parts), ".") !== false){ 
        array_pop($parts); 
    }else if(end($parts) !== ""){ 
      $parts[] = ""; 
    }
    
    return implode("/", $parts);
  }  
  	       
  private function is_new_top_level_folder($uploads_dir, $folder_name, $folder_path) {
    
    $needle = $uploads_dir . '/' . $folder_name;
    if(strpos($folder_path . '/' , $needle . '/'))        
      return true;
    else
      return false;   
  }

  private function get_folders($path) {
    $sub_folders = explode('/', $path);
    while( $sub_folders[0] !== $this->uploads_folder_name ) {
      array_shift($sub_folders);
    } 
    
    if($sub_folders[0] === $this->uploads_folder_name) 
      array_shift($sub_folders);
      
    return $sub_folders;
  }
  
  private function get_folders2($path) {
    $sub_folders = explode('/', $path);
    foreach($sub_folders as $id => $folderName)
      if ( $folderName === $this->uploads_folder_name )
        return array_slice($sub_folders, $id+1);
    return array();
  }  
  
  private function folder_exist($folder_path) {
    
    global $wpdb;    
    
		$relative_path = substr($folder_path, $this->base_url_length);
		$relative_path = ltrim($relative_path, '/');
    
		$sql = "SELECT ID FROM {$wpdb->prefix}posts
LEFT JOIN {$wpdb->prefix}postmeta AS pm ON pm.post_id = ID
WHERE pm.meta_value = '$relative_path' 
and pm.meta_key = '_wp_attached_file'";

    $row = $wpdb->get_row($sql);
    if($row === null)
      return false;
    else
      return $row->ID;
             
  }
  
  public function add_media_folder($folder_name, $parent_folder, $base_path ) {
    
    global $wpdb;    
    $table = $wpdb->prefix . "posts";	    
		
    $new_folder_id = $this->mpmlp_insert_post(MAXGALLERIA_MEDIA_LIBRARY_POST_TYPE, $folder_name, $base_path, 'publish' );

		$attachment_location = substr($base_path, $this->base_url_length);
		$attachment_location = ltrim($attachment_location, '/');
				
		update_post_meta($new_folder_id, '_wp_attached_file', $attachment_location);
        		
    $this->add_new_folder_parent($new_folder_id, $parent_folder);
        
    return $new_folder_id;
        
  }
  
  private function add_new_folder_parent($record_id, $parent_folder) {
    
    global $wpdb;    
    $table = $wpdb->prefix . MAXGALLERIA_MEDIA_LIBRARY_FOLDER_TABLE;
    
    // check for existing record  
    $sql = "select post_id from $table where post_id = $record_id";
    
    if($wpdb->get_var($sql) == NULL) {
    
      $new_record = array( 
			  'post_id'   => $record_id, 
			  'folder_id' => $parent_folder 
			);
      
      $wpdb->insert( $table, $new_record );
    }
      
  }
  
	public function load_textdomain() {
		load_plugin_textdomain('maxgalleria-media-library', false, dirname(plugin_basename(__FILE__)) . '/languages/');
	}
  
	public function ignore_notice() {
		if (current_user_can('install_plugins')) {
			global $current_user;
			
			if (isset($_GET['maxgalleria-media-library-ignore-notice']) && $_GET['maxgalleria-media-library-ignore-notice'] == 1) {
				add_user_meta($current_user->ID, MAXGALLERIA_MEDIA_LIBRARY_IGNORE_NOTICE, true, true);
			}
		}
	}

	public function show_mlp_admin_notice() {
    global $current_user;  
    
    if(isset($_REQUEST['page'])) {
          
      if($_REQUEST['page'] == 'media-library-folders' 
          || $_REQUEST['page'] === 'mlf-support8' 
          || $_REQUEST['page'] === 'mlf-settings8' 
          || $_REQUEST['page'] === 'mlf-image-seo' 
          || $_REQUEST['page'] === 'mlf-thumbnails' 
          || $_REQUEST['page'] === 'search-library' ) {

        
        $features = get_user_meta( $current_user->ID, MAXGALLERIA_MLP_FEATURE_NOTICE, true );
        $review = get_user_meta( $current_user->ID, MAXGALLERIA_MLP_REVIEW_NOTICE, true );
        if( $review !== 'off' || $features !== 'off') {
          if($features === '') {
            $features_date = date('Y-m-d', strtotime("+30 days"));        
            update_user_meta( $current_user->ID, MAXGALLERIA_MLP_FEATURE_NOTICE, $features_date );
          }
          if($review === '') {
            //show review notice after three days
            $review_date = date('Y-m-d', strtotime("+3 days"));        
            update_user_meta( $current_user->ID, MAXGALLERIA_MLP_REVIEW_NOTICE, $review_date );

            //show notice if not found
            //add_action( 'admin_notices', array($this, 'mlp_review_notice' ));            
          } else if( $review !== 'off') {
            $now = date("Y-m-d"); 
            $review_time = strtotime($review);
            $features_time = strtotime($features);
            $now_time = strtotime($now);
            
            if($now_time > $features_time && $features !== 'off')
              add_action( 'admin_notices', array($this, 'mlp_features_notice' ));            
            else if($now_time > $review_time)
              add_action( 'admin_notices', array($this, 'mlp_review_notice' ));
          } else if( $features !== 'off') {
            $now = date("Y-m-d"); 
            $features_time = strtotime($features);
            $now_time = strtotime($now);
            if($now_time > $features_time && $features !== 'off')
              add_action( 'admin_notices', array($this, 'mlp_features_notice' ));                        
          }
        }
      }
    }
	}
  
  /* if no upload fold id, check the folder table */
  private function fetch_uploads_folder_id() {
    global $wpdb;
    $sql = "SELECT * FROM {$wpdb->prefix}mgmlp_folders order by folder_id limit 1";
    $row = $wpdb->get_row($sql);
    if($row) {
      return $row->post_id;
    } else {
      return false;
    }
  }
          
  private function lookup_uploads_folder_name($current_folder_id) {
    global $wpdb;
    $sql = "SELECT post_title FROM {$wpdb->prefix}posts where ID = $current_folder_id";
    $folder_name = $wpdb->get_var($sql);
    return $folder_name;
  }  

  public function get_maxgalleria_galleries() {
    
    global $wpdb;
    
    $sql = "select ID, post_title 
	from {$wpdb->prefix}posts 
  LEFT JOIN {$wpdb->prefix}postmeta ON({$wpdb->prefix}posts.ID = {$wpdb->prefix}postmeta.post_id)
	where post_type = 'maxgallery' and post_status = 'publish'
  and {$wpdb->prefix}postmeta.meta_key = 'maxgallery_type'
	and {$wpdb->prefix}postmeta.meta_value = 'image'
	order by LOWER(post_name)";
  
    //error_log($sql);
  
    $gallery_list = "";
    $rows = $wpdb->get_results($sql);

    if($rows) {
      foreach ($rows as $row) {
        $gallery_list .='<option value="' . esc_attr($row->ID) . '">' . esc_html($row->post_title) . '</option>' . PHP_EOL;
      }
    }
    return $gallery_list;
  }  
  
  public function display_folder_contents ($current_folder_id, $image_link = true, $folders_path = '', $echo = true) {
    
    $folders_found = false;
    $images_found = false;
		$output = "";
    
    $sort_order = get_option(MAXGALLERIA_MEDIA_LIBRARY_SORT_ORDER);
    $sort_type = trim(get_option( MAXGALLERIA_MLF_SORT_TYPE ));    
  
    switch($sort_order) {
      default:
      case '0': //order by date
        $order_by = "post_date $sort_type";
        break;
      
      case '1': //order by name
        $order_by = "LOWER(post_title) $sort_type";
        break;      
    }
		
		if($image_link)
			$image_link = "1";
		else				
			$image_link = "0";
								
    // build the Javascript code to load the folder contents
		$output .= '<script type="text/javascript">' . PHP_EOL;
    $output .= '	jQuery(document).ready(function() {' . PHP_EOL;		
    $output .= '	var mif_visible = (jQuery("#mgmlp-media-search-input").is(":visible")) ? false : true;' . PHP_EOL;		
		$output .= '    jQuery.ajax({' . PHP_EOL;
		$output .= '      type: "POST",' . PHP_EOL;
		$output .= '      async: true,' . PHP_EOL;
		$output .= '      data: { action: "mlp_display_folder_contents_ajax", current_folder_id: "' . esc_attr($current_folder_id) . '", image_link: "' . esc_attr($image_link) . '", mif_visible: mif_visible, nonce: mgmlp_ajax.nonce },' . PHP_EOL;
    $output .= '      url: mgmlp_ajax.ajaxurl,' . PHP_EOL;
		$output .= '      dataType: "html",' . PHP_EOL;
		$output .= '      success: function (data) ' . PHP_EOL;
		$output .= '        {' . PHP_EOL;
		$output .= '          jQuery("#mgmlp-file-container").html(data);' . PHP_EOL;    
		$output .= '          jQuery("li a.media-attachment").draggable({' . PHP_EOL;
		$output .= '          	cursor: "move",' . PHP_EOL;
    $output .= '            cursorAt: { left: 25, top: 25 },' . PHP_EOL;
		$output .= '          helper: function() {' . PHP_EOL;
		$output .= '          	var selected = jQuery(".mg-media-list input:checked").parents("li");' . PHP_EOL;
		$output .= '          	if (selected.length === 0) {' . PHP_EOL;
		$output .= '          		selected = jQuery(this);' . PHP_EOL;
		$output .= '          	}' . PHP_EOL;
		$output .= '          	var container = jQuery("<div/>").attr("id", "draggingContainer");' . PHP_EOL;
		$output .= '          	container.append(selected.clone());' . PHP_EOL;
		$output .= '          	return container;' . PHP_EOL;
		$output .= '          }' . PHP_EOL;
		
		$output .= '          });' . PHP_EOL;
    $output .= '          display_protected_files();' . PHP_EOL;
		
		$output .= '          jQuery(".media-link").droppable( {' . PHP_EOL;
		$output .= '          	  accept: "li a.media-attachment",' . PHP_EOL;
		$output .= '          		hoverClass: "droppable-hover",' . PHP_EOL;
		$output .= '          		drop: handleDropEvent' . PHP_EOL;
		$output .= '          });' . PHP_EOL;
		
    $output .= '        },' . PHP_EOL;
		$output .= '          error: function (err)' . PHP_EOL;
		$output .= '	      { alert(err.responseText)}' . PHP_EOL;
		$output .= '	   });' . PHP_EOL;
		
		if($folders_path !== '') {
		  $output .= '   jQuery("#mgmlp-breadcrumbs").html("'. esc_html__('Location:','maxgalleria-media-library') . " " . addslashes($folders_path) .'");' . PHP_EOL;
		}
				
    $output .= '	});' . PHP_EOL;
    $output .= '</script>' . PHP_EOL;
		
    add_filter( 'wp_kses_allowed_html', array($this, 'kses_mlf_add_allowed_html'), 10, 4);    
		if($echo) {
			echo wp_kses_post($output);
    } else {
			return wp_kses_post($output);
    }  
    remove_filter( 'wp_kses_allowed_html', array($this, 'kses_mlf_add_allowed_html'));    
				
	}
  
  public function kses_mlf_add_allowed_html($html, $context) {                
    if($context == 'post') {
      $new_html = array(
        'script' => array(
          'type' => 1
        )
      );
      
      $html = array_merge($html, $new_html);
    }
    return $html;
  }
  	
	public function in_array_r($needle, $haystack, $strict = false) {
    foreach ($haystack as $item) {
			if (($strict ? $item === $needle : $item == $needle) || (is_array($item) && $this->in_array_r($needle, $item, $strict))) {
				return true;
			}
		}
    return false;
  }

	public function mlp_display_folder_contents_ajax() {
    
    global $wpdb;
		    
    //$folders_found = false;
    
    $sort_order = get_option(MAXGALLERIA_MEDIA_LIBRARY_SORT_ORDER);
    $sort_type = trim(get_option( MAXGALLERIA_MLF_SORT_TYPE ));    
    
    switch($sort_order) {
      default:
      case '0': //order by date
        $order_by = "post_date $sort_type";
        break;
      
      case '1': //order by name
        $order_by = "LOWER(attached_file) $sort_type";
        break;      
    }
    
    if ( !wp_verify_nonce( $_POST['nonce'], MAXGALLERIA_MEDIA_LIBRARY_NONCE)) {
      exit(esc_html__('missing nonce! Please refresh this page.','maxgalleria-media-library'));
    } 
    
    if ((isset($_POST['current_folder_id'])) && (strlen(trim($_POST['current_folder_id'])) > 0))
      $current_folder_id = trim(sanitize_text_field($_POST['current_folder_id']));
		else
			$current_folder_id = 0;
		
    if ((isset($_POST['image_link'])) && (strlen(trim($_POST['image_link'])) > 0))
      $image_link = trim(sanitize_text_field($_POST['image_link']));
		else
			$image_link = "0";
    
    if ((isset($_POST['display_type'])) && (strlen(trim($_POST['display_type'])) > 0))
      $display_type = trim(sanitize_text_field($_POST['display_type']));
		else
			$display_type = 0;
    
    if ((isset($_POST['mif_visible'])) && (strlen(trim($_POST['mif_visible'])) > 0))
      $mif_visible = trim(sanitize_text_field($_POST['mif_visible']));
		else
			$mif_visible = false;
		
		if($mif_visible === 'true')
			$mif_visible = true;
				
    $folder_table = $wpdb->prefix . MAXGALLERIA_MEDIA_LIBRARY_FOLDER_TABLE;
				
		$this->display_folder_nav($current_folder_id, $folder_table);
		
		$this->display_files($image_link, $current_folder_id, $folder_table, $display_type, $order_by, $mif_visible );
		
		die();
		
	}
	
	public function mlp_display_folder_contents_images_ajax() {
    
    global $wpdb;
		        
    $sort_order = get_option(MAXGALLERIA_MEDIA_LIBRARY_SORT_ORDER);
    $sort_type = trim(get_option( MAXGALLERIA_MLF_SORT_TYPE ));    
    
    switch($sort_order) {
      default:
      case '0': //order by date
        $order_by = "post_date $sort_type";
        break;
      
      case '1': //order by name
        $order_by = "LOWER(attached_file) $sort_type";
        break;      
    }
    
    if ( !wp_verify_nonce( $_POST['nonce'], MAXGALLERIA_MEDIA_LIBRARY_NONCE)) {
      exit(esc_html__('missing nonce! Please refresh this page.','maxgalleria-media-library'));
    } 
		
    if ((isset($_POST['current_folder_id'])) && (strlen(trim($_POST['current_folder_id'])) > 0))
      $current_folder_id = trim(sanitize_text_field($_POST['current_folder_id']));
		else
			$current_folder_id = 0;
		
    if ((isset($_POST['image_link'])) && (strlen(trim($_POST['image_link'])) > 0))
      $image_link = trim(sanitize_text_field($_POST['image_link']));
		else
			$image_link = "0";
		
    if ((isset($_POST['display_type'])) && (strlen(trim($_POST['display_type'])) > 0))
      $display_type = trim(sanitize_text_field($_POST['display_type']));
		else
			$display_type = 0;
		
    $folder_table = $wpdb->prefix . MAXGALLERIA_MEDIA_LIBRARY_FOLDER_TABLE;
			
		$this->display_files($image_link, $current_folder_id, $folder_table, $display_type, $order_by );
		
		die();
		
	}
	
	public function display_folder_nav_ajax () {
    
    if ( !wp_verify_nonce( $_POST['nonce'], MAXGALLERIA_MEDIA_LIBRARY_NONCE)) {
      exit(esc_html__('missing nonce! Please refresh this page.','maxgalleria-media-library'));
    } 
		
    if ((isset($_POST['current_folder_id'])) && (strlen(trim($_POST['current_folder_id'])) > 0))
      $current_folder_id = trim(sanitize_text_field($_POST['current_folder_id']));
		else
			$current_folder_id = 0;
				
    $folder_table = $wpdb->prefix . MAXGALLERIA_MEDIA_LIBRARY_FOLDER_TABLE;
				
		$this->display_folder_nav($current_folder_id, $folder_table);
		
		die();
						
	}
	
	public function mlp_get_folder_data() {
				
    if ( !wp_verify_nonce( $_POST['nonce'], MAXGALLERIA_MEDIA_LIBRARY_NONCE)) {
      exit(esc_html__('missing nonce! Please refresh this page.','maxgalleria-media-library'));
    } 
				
    if ((isset($_POST['current_folder_id'])) && (strlen(trim($_POST['current_folder_id'])) > 0)) 
      $current_folder_id = trim(sanitize_text_field($_POST['current_folder_id']));
		else
		  $current_folder_id = get_option(MAXGALLERIA_MEDIA_LIBRARY_UPLOAD_FOLDER_ID );        								
				
		$folders = array();
		$folders = $this->get_folder_data($current_folder_id);
					
		echo json_encode($folders);
		
		die();
			
	}
	
	public function get_folder_data($current_folder_id) {
		
    global $wpdb;
				
		$folder_parents = $this->get_parents($current_folder_id);
		$folder_table = $wpdb->prefix . MAXGALLERIA_MEDIA_LIBRARY_FOLDER_TABLE;
		
			$sql = "select ID, post_title, $folder_table.folder_id
from {$wpdb->prefix}posts
LEFT JOIN $folder_table ON({$wpdb->prefix}posts.ID = $folder_table.post_id)
where post_type = '" . MAXGALLERIA_MEDIA_LIBRARY_POST_TYPE ."' 
order by folder_id";
						
			$add_child = array();
			$folders = array();
			$first = true;
			$rows = $wpdb->get_results($sql);            
			if($rows) {
				foreach($rows as $row) {
          
          // do not show protected content folder
          if($row->post_title == MLFP_PROTECTED_DIRECTORY)
            continue;                  
          
						//$max_id = -1;

						//if($row->ID > $max_id)
						//	$max_id = $row->ID;
						$folder = array();
						$folder['id'] = esc_attr($row->ID);
						if($row->folder_id === '0') {
							$folder['parent'] = '#';
						} else {
              if(!$row->folder_id)
						    continue;
						  // check if parent folder even exists
						  $sql = "select ID from {$wpdb->prefix}posts
						    where ID = " . esc_attr($row->folder_id) . " and post_type = '".MAXGALLERIA_MEDIA_LIBRARY_POST_TYPE."'";
						  if (count($wpdb->get_results($sql)) == 0)
						    continue;
						  $folder['parent'] = esc_attr($row->folder_id);
						}

						$folder['text'] = esc_html($row->post_title);
						$state = array();
					if($row->folder_id === '0') {
						$state['opened'] = true;
						$state['disabled'] = false;
						$state['selected'] = true;
					} else if($this->in_array_r($row->ID, $folder_parents))	{
						$state['opened'] = true;
					} else if($this->uploads_folder_ID === $row->ID) {	
						$state['opened'] = true;
					}	else {
						$state['opened'] = false;
					}	
					if($row->ID === $current_folder_id) {
						$state['opened'] = true;
						$state['selected'] = true;
					} else
						$state['selected'] = false;
					$state['disabled'] = false;
					$folder['state'] = $state;
					
					$a_attr  = array();
					$a_attr['href'] = "#" . esc_attr($row->ID);
					$a_attr['target'] = '_self';

					$folder['a_attr'] = $a_attr;
					
					$add_child[] = $row->ID;
					$child_index = array_search($row->folder_id, $add_child);
					if($child_index !== false)
						unset($add_child[$child_index]);

					$folders[] = $folder;
				}

			}

			return $folders;
		
	}
  
  public function new_folder_check() {
    
    $currnet_date_time = date('Y-m-d H:i:s');
    
    $currnet_date_time_seconds = strtotime($currnet_date_time);
    
    $folder_check = get_option('mlf-folder-check', $currnet_date_time);
    if($currnet_date_time == $folder_check) {
			update_option('mlf-folder-check', $currnet_date_time, true);
      return;
    }  
    
    $folder_check_seconds = strtotime($folder_check . ' +1 hour');
        
    if($folder_check_seconds < $currnet_date_time_seconds) {
      $this->admin_check_for_new_folders(true);
      if($this->bda == 'on') {
        $this->remove_protected_folders();
      }        
			update_option('mlf-folder-check', $currnet_date_time, true);
    }		
    
  }
  	
	public function display_folder_nav($current_folder_id, $folder_table ) {
	
    global $wpdb;
    		
    if(!defined('SKIP_AUTO_FOLDER_CHECK'))
      $this->new_folder_check();
    
    $folder_parents = $this->get_parents($current_folder_id);
    $folder_table = $wpdb->prefix . MAXGALLERIA_MEDIA_LIBRARY_FOLDER_TABLE;
						
    $sql = "select ID, post_title, $folder_table.folder_id
from {$wpdb->prefix}posts
LEFT JOIN $folder_table ON({$wpdb->prefix}posts.ID = $folder_table.post_id)
where post_type = '" . MAXGALLERIA_MEDIA_LIBRARY_POST_TYPE ."' 
order by folder_id";
						
					$folders = array();
					$folders = $this->get_folder_data($current_folder_id);
					
					?>
			
<script>
	var mlp_busy = false;
  var folders = <?php echo json_encode($folders); ?>;
	jQuery(document).ready(function(){		
		jQuery("#scanning-message").hide();		
		jQuery("#ajaxloadernav").show();		
    jQuery('#folder-tree').jstree({ 'core' : {
        'multiple' : false,
				'data' : folders,
				'check_callback' : true
			},
			'force_text' : true,
			'themes' : {
				'responsive' : false,
				'variant' : 'small',
				'stripes' : true
			},		
			'types' : {
				'default' : { 'icon' : 'folder' },
        'file' : { 'icon' :'folder'},
				'valid_children' : {'icon' :'folder'}	 
			},
			'sort' : function(a, b) {
				return this.get_type(a).toLowerCase() === this.get_type(b).toLowerCase() ? (this.get_text(a).toLowerCase() > this.get_text(b).toLowerCase() ? 1 : -1) : (this.get_type(a).toLowerCase() >= this.get_type(b).toLowerCase() ? 1 : -1);
			},			
				"contextmenu":{
				  "select_node":false,
					"items": function($node) {
						 var tree = jQuery("#tree").jstree(true);
						 return {
							 "Remove": {
								 "separator_before": false,
								 "separator_after": false,
								 "label": "<?php esc_html_e('Delete this folder?','maxgalleria-media-library'); ?>",
								 "action": function (obj) { 
										var delete_ids = new Array();
										delete_ids[delete_ids.length] = jQuery($node).attr('id');
										
										var folder_id = jQuery('#folder_id').val();      
										var to_delete = jQuery($node).attr('id');
										var parent_id = jQuery($node).attr('parent');
										
										if(confirm("<?php esc_html_e('Are you sure you want to delete the selected folder?','maxgalleria-media-library'); ?>")) {	
											var serial_delete_ids = JSON.stringify(delete_ids.join());
											jQuery("#ajaxloader").show();
											jQuery.ajax({
												type: "POST",
												async: true,
												data: { action: "delete_maxgalleria_media", serial_delete_ids: serial_delete_ids, parent: parent_id, nonce: mgmlp_ajax.nonce },
												url : mgmlp_ajax.ajaxurl,
												dataType: "json",
												success: function (data) {
													
													jQuery("#folder-message").html(data.message);
													if(data.refresh) {
														jQuery('#folder-tree').jstree(true).settings.core.data = data.folders;
														jQuery('#folder-tree').jstree(true).refresh();			
														setTimeout(function() { jQuery('#folder-tree').jstree('select_node', '#' + parent_id); }, 4000);
														jQuery("#folder-message").html('');
														jQuery("#current-folder-id").val(parent_id);
													}																																																															
													jQuery("#ajaxloader").hide();            
												},
												error: function (err)
													{ alert(err.responseText);}
											});
									} 
								}
							},
							 "Hide": {
								 "separator_before": false,
								 "separator_after": false,
								 "label": "<?php esc_html_e('Hide this folder? This will remove the folder contents from the media library database.','maxgalleria-media-library'); ?>",
								 "action": function (obj) { 
										var folder_id = jQuery('#folder_id').val();      
										var to_hide = jQuery($node).attr('id');

								    if(confirm("<?php esc_html_e('Are you sure you want to hide the selected folder and all its sub folders and files?','maxgalleria-media-library'); ?>")) {
											//var serial_delete_ids = JSON.stringify(delete_ids.join());
											jQuery("#ajaxloader").show();
											jQuery.ajax({
												type: "POST",
												async: true,
												data: { action: "hide_maxgalleria_media", folder_id: to_hide, nonce: mgmlp_ajax.nonce },
												url : mgmlp_ajax.ajaxurl,
												dataType: "html",
												success: function (data) {
													jQuery("#folder-message").html(data);
													jQuery("#ajaxloader").hide();            
												},
												error: function (err)
													{ alert(err.responseText);}
											});
									} 
								}
							}
						}; // end context menu
					}					
			},						
			'plugins' : [ 'sort', 'types', 'contextmenu' ],
		});
		
		// for changing folders
		if(!jQuery("ul#folder-tree.jstree").hasClass("bound")) {
      jQuery("#folder-tree").addClass("bound").on("select_node.jstree", show_mlp_node);		
		}	
				
		jQuery('#folder-tree').droppable( {
				accept: 'li a.media-attachment',
				hoverClass: 'jstree-anchor',
				drop: handleTreeDropEvent
		});
	
		jQuery('#folder-tree').on('copy_node.jstree', function (e, data) {
			 //console.log(data.node.data.more); 
		});
		
		jQuery("#ajaxloadernav").hide();		
	});  
	
	
function show_mlp_node (e, data) {

	if(!window.mlp_busy) {
		window.mlp_busy = true;

    // opens the closed node
    jQuery("#folder-tree").jstree("toggle_node", data.node.id);

    var folder = data.node.id;

    jQuery("#ajaxloader").show();

    jQuery.ajax({
      type: "POST",
      async: true,
      data: { action: "mlp_load_folder", folder: folder, nonce: mgmlp_ajax.nonce },
      url : mgmlp_ajax.ajaxurl,
      dataType: "html",
      success: function (data) {
        jQuery("#mgmlp-file-container").html(data);						
        jQuery("#ajaxloader").hide();          
        jQuery("#current-folder-id").val(folder);
        jQuery("#folder_id").val(folder);
        jQuery("#mlf-select-all").prop('checked', false); // reset select all checkbox
        sessionStorage.setItem('folder_id', folder);

        jQuery("li a.media-attachment").draggable({
          cursor: "move",
          helper: function() {
            var selected = jQuery(".mg-media-list input:checked").parents("li");
            if (selected.length == 0) {
              selected = jQuery(this);
            }
            var container = jQuery("<div/>").attr("id", "draggingContainer");
            container.append(selected.clone());
            return container;
          }		
        });

        jQuery(".media-link").droppable( {
          accept: "li a.media-attachment",
          hoverClass: "droppable-hover",
          drop: handleDropEvent
        });					
      },
      error: function (err) { 
        alert(err.responseText);
      }
    });

		window.mlp_busy = false;
	}	
}
	
function handleTreeDropEvent(event, ui ) {
		
	var target=event.target || event.srcElement;
	//console.log(target);
	
	var move_ids = new Array();
	var items = ui.helper.children();
	items.each(function() {  
		move_ids[move_ids.length] = jQuery(this).find( "a.media-attachment" ).attr("id");
	});
	
	if(move_ids.length < 2) {
	  move_ids = new Array();
		move_ids[move_ids.length] =  ui.draggable.attr("id");
	}	
		
	//var serial_copy_ids = JSON.stringify(move_ids.join());
	var folder_id = jQuery(target).attr("aria-activedescendant");	
	var current_folder = jQuery("#current-folder-id").val();      
	
	var action_name = 'move_media';
	var operation_type = jQuery('#move-or-copy-status').val();
	if(operation_type == 'on')
		action_name = 'move_media';
	else
		action_name = 'copy_media';

  console.log('action_name',action_name);

	jQuery("#ajaxloader").show();
			
  var serial_copy_ids = JSON.stringify(move_ids.join());

  process_mc_data('1', folder_id, action_name, current_folder, serial_copy_ids);
      						
} 

function delete_current_folder(node) {
	var folder_id = jQuery(target).attr("aria-activedescendant");	
	//console.log(folder_id);
}

function process_mc_data(phase, folder_id, action_name, parent_folder, serial_copy_ids) {
  
	jQuery.ajax({
		type: "POST",
		async: true,
		data: { action: "mlfp_process_mc_data", phase: phase, folder_id: folder_id, action_name: action_name, current_folder: parent_folder, serial_copy_ids: serial_copy_ids, nonce: mgmlp_ajax.nonce },
		url: mgmlp_ajax.ajaxurl,
		dataType: "json",
		success: function (data) { 
			if(data != null && data.phase != null) {
			  jQuery("#folder-message").html(data.message);
        process_mc_data(data.phase, folder_id, action_name, parent_folder, null);
      } else {        
			  jQuery("#folder-message").html(data.message);        
        if(action_name == 'move_media')
				  mlf_refresh_folders(parent_folder);
		    jQuery("#ajaxloader").hide();
				return false;
      }      
		},
		error: function (err){ 
		  jQuery("#ajaxloader").hide();
			alert(err.responseText);
		}    
	});																											
  
}
</script>
  <?php
							
	}
  
	public function display_files($image_link, $current_folder_id, $folder_table, $display_type, $order_by, $mif_visible = false) {
    
    global $wpdb;
    $images_found = false;
    $images_pre_page = get_option(MAXGALLERIA_MLP_ITEMS_PRE_PAGE, '500');
    if(empty($images_pre_page))
      $images_pre_page = '500';
    $author_class = '';
    
    $allowed_html = array(
      'input' => array(
        'type' => array(),
        'class' => array(),
        'id' => array(),
        'protected' => array(),
        'value' => array()
      )    
    );
    
    $blocked_image_url = '';  
    $block_access_table = $wpdb->prefix . MAXGALLERIA_MEDIA_LIBRARY_BLOCK_ACCESS_TABLE;    
    $current_user = get_current_user_id();
    $is_admin = current_user_can('administrator');
    
		if($image_link === "1")
			$image_link = true;
		else
			$image_link = false;
						
            ?>
            <ul class="mg-media-list">
            <?php  
            
    if($this->bda == 'on') {

            $sql = "select ID, post_title, post_author, $folder_table.folder_id, pm.meta_value as attached_file, IFNULL($block_access_table.block,0) as block
from {$wpdb->prefix}posts 
LEFT JOIN $folder_table ON({$wpdb->prefix}posts.ID = $folder_table.post_id)
LEFT JOIN {$wpdb->prefix}postmeta AS pm ON (pm.post_id = {$wpdb->prefix}posts.ID) 
LEFT JOIN $block_access_table ON($wpdb->posts.ID = $block_access_table.attachment_id)
where post_type = 'attachment' 
and folder_id = '$current_folder_id'
AND pm.meta_key = '_wp_attached_file' 
order by $order_by limit 0, $images_pre_page";
      
    } else {           
            						
            $sql = "select ID, post_title, post_author, $folder_table.folder_id, pm.meta_value as attached_file, 0 as block
from {$wpdb->prefix}posts 
LEFT JOIN $folder_table ON({$wpdb->prefix}posts.ID = $folder_table.post_id)
LEFT JOIN {$wpdb->prefix}postmeta AS pm ON (pm.post_id = {$wpdb->prefix}posts.ID) 
where post_type = 'attachment' 
and folder_id = '$current_folder_id'
AND pm.meta_key = '_wp_attached_file' 
order by $order_by limit 0, $images_pre_page";
    }

            //error_log($sql);

            $rows = $wpdb->get_results($sql);            
            if($rows) {
              $images_found = true;
              foreach($rows as $row) {
								
                if($row->block == 1) {
                  if(($this->bda_user_role == 'admins' && $is_admin) || $this->bda_user_role == 'authors' && $current_user == $row->post_author) {
                    if($current_user == $row->post_author)
                      $author_class = 'author';
                    else
                      $author_class = '';
                    if(wp_attachment_is_image($row->ID)) {
                      $blocked_image_url = wp_get_attachment_thumb_url($row->ID);
                    } else {  
                      $ext = pathinfo($row->attached_file, PATHINFO_EXTENSION);
                      $blocked_image_url = $this->get_file_thumbnail($ext);
                      $image_file_type = false;                    
                    }
                  } else {
                    $blocked_image_url = '';
                  }                  
                } else {
                  // use wp_get_attachment_image to get the PDF preview
                  $thumbnail_html = "";
                  $thumbnail_html = wp_get_attachment_image( $row->ID);
                  if(!$thumbnail_html){
                    $thumbnail = wp_get_attachment_thumb_url($row->ID);                
                    if($thumbnail === false) {
                      $thumbnail = esc_url(MAXGALLERIA_MEDIA_LIBRARY_PLUGIN_URL . "/images/file.jpg");
                    }  
                    $thumbnail_html = "<img alt='' src='$thumbnail' />";
                  }
                }
                                
                $checkbox = sprintf("<input type='checkbox' class='mgmlp-media' id='%s' value='%s' protected='%s' />", $row->ID, $row->ID, $row->block );
								if($image_link && $mif_visible)
                  $class = "media-attachment no-pointer"; 
								else if($image_link)
                  $class = "media-attachment"; 
								else
                  $class = "tb-media-attachment"; 
                
								// for WP 4.6 use /wp-admin/post.php?post=
								if( version_compare($this->wp_version, NEW_MEDIA_LIBRARY_VERSION, ">") )
                  $media_edit_link = "/wp-admin/post.php?post=" . $row->ID . "&action=edit";
								else
                  $media_edit_link = "/wp-admin/upload.php?item=" . $row->ID;
									
                $image_location = $this->build_location_url($row->attached_file);
								                
                $filename = pathinfo($image_location, PATHINFO_BASENAME);
                
                $protcted_class = ($row->block == 1) ? 'mlfp-protected' : '';
                //error_log($blocked_image_url);
								                
                ?>
                <li id='<?php echo esc_attr($row->ID) ?>'>
                  <?php if($row->block == 1) { ?>
                    <a id='<?php echo esc_attr($row->ID) ?>' target='_blank' class='<?php echo esc_attr($class) ?> <?php echo esc_attr($protcted_class) ?> <?php echo esc_attr($author_class) ?>' href='<?php echo esc_url_raw(site_url() . $media_edit_link) ?>' title='<?php echo esc_attr($filename) ?>'><img width='80' height='80' type='bda' src='<?php echo esc_url_raw($blocked_image_url) ?>' class='attachment-thumbnail size-thumbnail' alt='' loading='lazy' /></a>
                  <?php } else { ?>  
                    <a id='<?php echo esc_attr($row->ID) ?>' target='_blank' class='<?php echo esc_attr($class) ?>' href='<?php echo esc_url_raw(site_url() . $media_edit_link) ?>'><?php echo wp_kses_post($thumbnail_html) ?></a>
                  <?php } ?>  
                  <div class='attachment-name'><label><span class='image_select'><?php echo wp_kses($checkbox, $allowed_html) ?></span><span class="mediafile"><?php echo esc_html($filename) ?></span></label></div>
                </li>
                <?php
                                 								
              }      
            }
            ?>
            </ul>
						
            <script>
              jQuery(document).ready(function(){
                jQuery("#folder-message").html("");
                jQuery("li a.media-attachment").draggable({
                  cursor: "move",
                  helper: function() {
                    var selected = jQuery(".mg-media-list input:checked").parents("li");
                    if (selected.length === 0) {
                      selected = jQuery(this);
                    }
                    var container = jQuery("<div/>").attr("id", "draggingContainer");
                    container.append(selected.clone());
                    return container;
                  }
                });
              });
            </script>				
            <?php
            if(!$images_found) { 
              ?>
              <p style='text-align:center'><?php esc_html_e('No files were found.','maxgalleria-media-library') ?></p>
              <?php
            }  
						
		
		
	}
  
  private function get_folder_path($folder_id) {
    
    global $wpdb;    
   $sql = "select meta_value as attached_file
from {$wpdb->prefix}postmeta 
where post_id = $folder_id
AND meta_key = '_wp_attached_file'";
				
    $row = $wpdb->get_row($sql);
		
    //$image_location = $this->upload_dir['baseurl'] . '/' . $row->attached_file;		
		$baseurl = $this->upload_dir['baseurl'];
		$baseurl = rtrim($baseurl, '/') . '/';
		$image_location = $baseurl . ltrim($row->attached_file, '/');
    $absolute_path = $this->get_absolute_path($image_location);
		
    return $absolute_path;
      
  }
  
  private function get_subfolder_path($folder_id) {
      
    global $wpdb;    
		
    $sql = "select meta_value as attached_file
from {$wpdb->prefix}postmeta 
where post_id = $folder_id    
AND meta_key = '_wp_attached_file'";
		
    $row = $wpdb->get_row($sql);
		
		$baseurl = $this->upload_dir['baseurl'];
		$baseurl = rtrim($baseurl, '/') . '/';
		$image_location = $baseurl . ltrim($row->attached_file, '/');
			
    $postion = strpos($image_location, $this->uploads_folder_name);
    $path = substr($image_location, $postion+$this->uploads_folder_name_length );
    return $path;
      
  }
  
  private function get_folder_name($folder_id) {
    global $wpdb;    
    $sql = "select post_title from $wpdb->prefix" . "posts where ID = $folder_id";    
    $row = $wpdb->get_row($sql);
    return $row->post_title;
  }
    
  private function get_parents($current_folder_id) {

    global $wpdb;    
    $folder_id = $current_folder_id;    
    $parents = array();
    $folder_table = $wpdb->prefix . MAXGALLERIA_MEDIA_LIBRARY_FOLDER_TABLE;
		$not_found = false;
    
    while($folder_id !== '0' || !$not_found ) {    
      
      $sql = "select post_title, ID, $folder_table.folder_id 
from $wpdb->prefix" . "posts 
LEFT JOIN $folder_table ON ($wpdb->prefix" . "posts.ID = $folder_table.post_id)
where ID = $folder_id";    
      
      $row = $wpdb->get_row($sql);
			
			if($row) {      
				$folder_id = $row->folder_id;
				$new_folder = array();
				$new_folder['name'] = $row->post_title;
				$new_folder['id'] = $row->ID;
				$parents[] = $new_folder;      
			} else {
				$not_found = true;
			}              
    }
    
    $parents = array_reverse($parents);
        
    return $parents;
    
  }  

  private function get_parent($folder_id) {
    
    global $wpdb;    
    $folder_table = $wpdb->prefix . MAXGALLERIA_MEDIA_LIBRARY_FOLDER_TABLE;
    
    $sql = "select folder_id from $folder_table where post_id = $folder_id";    
    
    $row = $wpdb->get_row($sql);
		if($row)        
      return $row->folder_id;
    else
			return $this->uploads_folder_ID;
  }
  
  public function create_new_folder() {
        
    global $wpdb;
    
    if ( !wp_verify_nonce( $_POST['nonce'], MAXGALLERIA_MEDIA_LIBRARY_NONCE)) {
      exit(esc_html__('missing nonce! Please refresh this page.','maxgalleria-media-library'));
    } 

    if ((isset($_POST['parent_folder'])) && (strlen(trim($_POST['parent_folder'])) > 0))
      $parent_folder_id = trim(sanitize_text_field($_POST['parent_folder']));
    
    
    if ((isset($_POST['new_folder_name'])) && (strlen(trim($_POST['new_folder_name'])) > 0))
      $new_folder_name = trim(sanitize_text_field($_POST['new_folder_name']));
    
      $sql = "select meta_value as attached_file
from {$wpdb->prefix}postmeta 
where post_id = $parent_folder_id    
AND meta_key = '_wp_attached_file'";
		
    $row = $wpdb->get_row($sql);
		
		$baseurl = $this->upload_dir['baseurl'];
		$baseurl = rtrim($baseurl, '/') . '/';
		$image_location = $baseurl . ltrim($row->attached_file, '/');
				        
    $absolute_path = $this->get_absolute_path($image_location);
		$absolute_path = rtrim($absolute_path, '/') . '/';
		//$this->write_log("absolute_path $absolute_path");
        
    $new_folder_path = $absolute_path . $new_folder_name ;
		//$this->write_log("new_folder_path $new_folder_path");
    
    $new_folder_url = $this->get_file_url_for_copy($new_folder_path);
		//$this->write_log("new_folder_url $new_folder_url");
		
		//$this->write_log("Trying to create directory at $new_folder_path, $parent_folder_id, $new_folder_url");
    
    if(!file_exists($new_folder_path)) {
      if(mkdir($new_folder_path)) {
        if(defined('FS_CHMOD_DIR'))
			    @chmod($new_folder_path, FS_CHMOD_DIR);
        else  
			    @chmod($new_folder_path, 0755);
        //if($this->add_media_folder($new_folder_name, $parent_folder_id, $new_folder_url)){
				$new_folder_id = $this->add_media_folder($new_folder_name, $parent_folder_id, $new_folder_url);
				if($new_folder_id) {
					
          $message = esc_html__('The folder was created.','maxgalleria-media-library');
					$folders = $this->get_folder_data($parent_folder_id);
					$data = array ('message' => esc_html($message), 'folders' => $folders, 'refresh' => true, 'new_folder' => esc_attr($new_folder_id));
					echo json_encode($data);
					
        } else {					
          $message = esc_html__('There was a problem creating the folder.','maxgalleria-media-library');
					$data = array ('message' => esc_html($message),  'refresh' => false );
					echo json_encode($data);
				}	
      }
    } else {
			$message = esc_html__('The folder already exists.','maxgalleria-media-library');
			$data = array ('message' => esc_html($message),  'refresh' => false );
			echo json_encode($data);
		}	
    die();
  }

  public function get_absolute_path($url) {
		
		global $blog_id, $is_IIS;
		
		$baseurl = $this->upload_dir['baseurl'];

		if(is_multisite()) {
			$url_slug = "site" . $blog_id . "/";
			$baseurl = str_replace($url_slug, "", $baseurl);
			if(strpos($url, MLF_WP_CONTENT_FOLDER_NAME) === false)
			  $url = str_replace($url_slug, "wp-content/uploads/sites/" . $blog_id . "/" , $url);
			else
			  $url = str_replace($url_slug, "", $url);
		}
		
    $file_path = str_replace( $baseurl, $this->upload_dir['basedir'], $url ); 
    // fix the slashes
    if(strpos($this->upload_dir['basedir'], '\\') !== false)
      $file_path = str_replace('/', '\\', $file_path);
    				
		//first attempt failed; try again
		if((strpos($file_path, "http:") !== false) || (strpos($file_path, "https:") !== false)) {	
			//$this->write_log("absolute path, second attempt $file_path");
			$baseurl = $this->upload_dir['baseurl'];
			$base_length = strlen($baseurl);
			//compare the two urls
			$url_stub = substr($url, 0, $base_length);
			if(strcmp($url_stub, $baseurl) === 0) {			
				$non_base_file = substr($url, $base_length);
				$file_path = $this->upload_dir['basedir'] . DIRECTORY_SEPARATOR . $non_base_file;			
			} else {
				//$this->write_log("url_stub $url_stub");
				//$this->write_log("baseurl $baseurl");
        $new_msg = esc_html__('The URL to the folder or image is not correct: ','maxgalleria-media-library') . esc_url_raw($url);
				//$this->write_log($new_msg);
				echo esc_html($new_msg);
			}
		}
		    
    // are we on windows?
    if ($is_IIS || strtoupper(substr(PHP_OS, 0, 3)) == 'WIN' || strtoupper(substr(PHP_OS, 0, 13)) == 'MICROSOFT-IIS' ) {
      $file_path = str_replace('/', '\\', $file_path);
    }
						
    return $file_path;
  }
  
  public function is_windows() {
		global $is_IIS;
    if ($is_IIS || strtoupper(substr(PHP_OS, 0, 3)) == 'WIN' || strtoupper(substr(PHP_OS, 0, 13)) == 'MICROSOFT-IIS' )
      return true;
    else
      return false;      
  }
  
  public function get_file_url($path) {
		global $is_IIS;
    
    if ($is_IIS || strtoupper(substr(PHP_OS, 0, 3)) == 'WIN' || strtoupper(substr(PHP_OS, 0, 13)) == 'MICROSOFT-IIS' ) {
      
      $base_url = $this->upload_dir['baseurl'];
      
      $position = strpos($path, $this->uploads_folder_name);
      $relative_path = substr($path, $position+$this->uploads_folder_name_length+1);

      $file_url = $base_url . '/' . $relative_path;
      $file_url = str_replace('\\', '/', $file_url);      
              
    }
    else {
      $file_url = str_replace( $this->upload_dir['basedir'], $this->upload_dir['baseurl'], $path );          
    }
    return $file_url;    
  }
  
  public function get_file_url_for_copy($path) {
		global $is_IIS;
    
    if ($is_IIS || strtoupper(substr(PHP_OS, 0, 3)) == 'WIN' || strtoupper(substr(PHP_OS, 0, 13)) == 'MICROSOFT-IIS' ) {
      
      $base_url = $this->upload_dir['baseurl'];
      
      // replace any slashes in the dir path when running windows
      $base_upload_dir1 = $this->upload_dir['basedir'];
      $base_upload_dir2 = str_replace('/','\\', $base_upload_dir1);      
      $file_url = str_replace( $base_upload_dir2, $base_url, $path ); 
      $file_url = str_replace('\\',   '/', $file_url);      
      
    }
    else {
      $file_url = str_replace( $this->upload_dir['basedir'], $this->upload_dir['baseurl'], $path );          
    }
    return $file_url;
    
  }
  
  public function delete_maxgalleria_media() {
        
    global $wpdb, $is_IIS;
    $delete_ids = array();
    $folder_deleted = true;
    $message = "";
    
    if ( !wp_verify_nonce( $_POST['nonce'], MAXGALLERIA_MEDIA_LIBRARY_NONCE)) {
      exit( esc_html__('missing nonce! Please refresh this page.','maxgalleria-media-library'));
    } 
    
    if ((isset($_POST['serial_delete_ids'])) && (strlen(trim($_POST['serial_delete_ids'])) > 0)) {
      $delete_ids = trim(stripslashes(sanitize_text_field($_POST['serial_delete_ids'])));
      $delete_ids = str_replace('"', '', $delete_ids);
      $delete_ids = explode(",",$delete_ids);
    }  
    else
      $delete_ids = '';
		
    if ((isset($_POST['parent_id'])) && (strlen(trim($_POST['parent_id'])) > 0))
      $parent_folder = trim(sanitize_text_field($_POST['parent_id']));
		else
			$parent_folder = "0";
		
		            
    foreach( $delete_ids as $delete_id) {
      
      // prevent uploads folder from being deleted
      if(intval($delete_id) == intval($this->uploads_folder_ID)) {
				$message = esc_html__('The uploads folder cannot be deleted.','maxgalleria-media-library');
				$data = array ('message' => esc_html($message), 'refresh' => false );
        echo json_encode($data);
        die();
      }
			
			if(is_numeric($delete_id)) {

        $sql = "select post_title, post_type, pm.meta_value as attached_file 
from {$wpdb->prefix}posts 
LEFT JOIN {$wpdb->prefix}postmeta AS pm ON (pm.post_id = {$wpdb->prefix}posts.ID) 
where ID = $delete_id 
AND pm.meta_key = '_wp_attached_file'";

				$row = $wpdb->get_row($sql);

				$baseurl = $this->upload_dir['baseurl'];
				$baseurl = rtrim($baseurl, '/') . '/';
				$image_location = $baseurl . ltrim($row->attached_file, '/');
				
				$folder_path = $this->get_absolute_path($image_location);
				$table = $wpdb->prefix . MAXGALLERIA_MEDIA_LIBRARY_FOLDER_TABLE;
				$del_post = array('post_id' => $delete_id);                        

				if($row->post_type === MAXGALLERIA_MEDIA_LIBRARY_POST_TYPE) { //folder

					$sql = "SELECT COUNT(*) FROM $table where folder_id = $delete_id";
					$row_count = $wpdb->get_var($sql);

					if($row_count > 0) {
						$message = esc_html__('The folder, ','maxgalleria-media-library'). $row->post_title . esc_html__(', is not empty. Please delete or move files from the folder','maxgalleria-media-library') . PHP_EOL;      
						
						$data = array ('message' => esc_html($message), 'refresh' => false );
						echo json_encode($data);
						
						die();
					}  
					
			    //$parent_folder =  $this->get_parent($delete_id);
          
          if(file_exists($folder_path)) {
            if(is_dir($folder_path)) {  //folder
              @chmod($folder_path, 0777);
              $this->remove_hidden_files($folder_path);
              if($this->is_dir_empty($folder_path)) {
                if(!rmdir($folder_path)) {
                  $message = esc_html__('The folder could not be deleted.','maxgalleria-media-library');
                }  
              } else {
                $message = esc_html__('The folder is not empty and could not be deleted.','maxgalleria-media-library');
                $folder_deleted = false;                                  
              }         
            }          
          }                                    
					
          if($folder_deleted) {
            wp_delete_post($delete_id, true);
            $wpdb->delete( $table, $del_post );
            $message = esc_html__('The folder was deleted.','maxgalleria-media-library');
          }
					$folders = $this->get_folder_data($parent_folder);
					$data = array ('message' => esc_html($message), 'folders' => $folders, 'refresh' => $folder_deleted );
					echo json_encode($data);
									
					die();
				}
				else {
          //error_log("delete_id $delete_id");
          $attached_file = get_post_meta($delete_id, '_wp_attached_file', true);
          $metadata = wp_get_attachment_metadata($delete_id);                               
          $baseurl = $this->upload_dir['baseurl'];
          $baseurl = rtrim($baseurl, '/') . '/';
          $image_location = $baseurl . ltrim($row->attached_file, '/');
          $image_path = $this->get_absolute_path($image_location);
          $path_to_thumbnails = pathinfo($image_path, PATHINFO_DIRNAME);          
          
					if( wp_delete_attachment( $delete_id, true ) !== false ) {
						$wpdb->delete( $table, $del_post );						
						$message = esc_html__('The file(s) were deleted','maxgalleria-media-library') . PHP_EOL;						
            
            //error_log("unlink image_path $image_path");            
            if(file_exists($image_path))
              unlink($image_path);
            if(isset($metadata['sizes'])) {
              foreach($metadata['sizes'] as $source_path) {
                $thumbnail_file = $path_to_thumbnails . DIRECTORY_SEPARATOR . $source_path['file'];

                if ($is_IIS || strtoupper(substr(PHP_OS, 0, 3)) == 'WIN' || strtoupper(substr(PHP_OS, 0, 13)) == 'MICROSOFT-IIS' )
                  $thumbnail_file = str_replace('/', '\\', $thumbnail_file);

                if(file_exists($thumbnail_file))
                  unlink($thumbnail_file);
              }  
            }
                        
					} else {
            $message = esc_html__('The file(s) were not deleted','maxgalleria-media-library') . PHP_EOL;
					} 
				} 
			}
    }

		$files = $this->display_folder_contents ($parent_folder, true, "", false);
		$refresh = true;
		$data = array ('message' => esc_html($message), 'files' => $files, 'refresh' => $refresh );
		echo json_encode($data);						
    die();
  }

  public function remove_hidden_files($directory) {
    $files = array_diff(scandir($directory), array('.','..'));
    foreach ($files as $file) {
      unlink("$directory/$file");
    }    
  }
  
  public function is_dir_empty($directory) {
    $filehandle = opendir($directory);
    while (false !== ($entry = readdir($filehandle))) {
      if ($entry != "." && $entry != "..") {
        closedir($filehandle);
        return false;
      }
    }
    closedir($filehandle);
    return true;
  }  
      
  public function get_image_sizes() {
    global $_wp_additional_image_sizes;
    $sizes = array();
    $rSizes = array();
    foreach (get_intermediate_image_sizes() as $s) {
      $sizes[$s] = array(0, 0);
      if (in_array($s, array('thumbnail', 'medium', 'large'))) {
        $sizes[$s][0] = get_option($s . '_size_w');
        $sizes[$s][1] = get_option($s . '_size_h');
      } else {
        if (isset($_wp_additional_image_sizes) && isset($_wp_additional_image_sizes[$s]))
          $sizes[$s] = array($_wp_additional_image_sizes[$s]['width'], $_wp_additional_image_sizes[$s]['height'],);
      }
    }
		
		foreach ($sizes as $size => $atts) {
			$rSizes[] = implode('x', $atts);
		}

    return $rSizes;
  }  
    
  public function add_to_max_gallery () {
        
    global $wpdb, $maxgalleria;
    
    if ( !wp_verify_nonce( $_POST['nonce'], MAXGALLERIA_MEDIA_LIBRARY_NONCE)) {
      exit(esc_html__('missing nonce! Please refresh this page.','maxgalleria-media-library'));
    } 
    
    if ((isset($_POST['serial_gallery_image_ids'])) && (strlen(trim($_POST['serial_gallery_image_ids'])) > 0))
      $serial_gallery_image_ids = stripslashes(trim(sanitize_text_field($_POST['serial_gallery_image_ids'])));
    else
      $serial_gallery_image_ids = "";
    
    $serial_gallery_image_ids = str_replace('"', '', $serial_gallery_image_ids);    
    
    $serial_gallery_image_ids = explode(',', $serial_gallery_image_ids);
        
    if ((isset($_POST['gallery_id'])) && (strlen(trim($_POST['gallery_id'])) > 0))
      $gallery_id = trim(sanitize_text_field($_POST['gallery_id']));
    else
      $gallery_id = 0;
    
    foreach( $serial_gallery_image_ids as $attachment_id) {
      
      if(wp_attachment_is_image($attachment_id)) {     
              
        // check for image already in the gallery
        $sql = "SELECT ID FROM $wpdb->prefix" . "posts where post_parent = $gallery_id and post_type = 'attachment' and ID = $attachment_id";

        $row = $wpdb->get_row($sql);

        if($row === null) {

          $menu_order = $maxgalleria->common->get_next_menu_order($gallery_id);      

          $attachment = get_post( $attachment_id, ARRAY_A );

          // assign a new value for menu_order
          //$menu_order = $maxgalleria->common->get_next_menu_order($gallery_id);
          $attachment[ 'menu_order' ] = $menu_order;

          //If the attachment doesn't have a post parent, simply change it to the attachment we're working with and be done with it      
          // assign a new value for menu_order
          if( empty( $attachment[ 'post_parent' ] ) ) {
            wp_update_post(
              array(
                'ID' => $attachment[ 'ID' ],
                'post_parent' => $gallery_id,
                'menu_order' => $menu_order
              )
            );
            $result = $attachment[ 'ID' ];
          } else {
            //Else, unset the attachment ID, change the post parent and insert a new attachment
            unset( $attachment[ 'ID' ] );
            $attachment[ 'post_parent' ] = $gallery_id;
            $new_attachment_id = wp_insert_post( $attachment );

            //Now, duplicate all the custom fields. (There's probably a better way to do this)
            $custom_fields = get_post_custom( $attachment_id );

            foreach( $custom_fields as $key => $value ) {
              //The attachment metadata wasn't duplicating correctly so we do that below instead
              if( $key != '_wp_attachment_metadata' )
                update_post_meta( $new_attachment_id, $key, $value[0] );
            }

            //Carry over the attachment metadata
            $data = wp_get_attachment_metadata( $attachment_id );
            wp_update_attachment_metadata( $new_attachment_id, $data );

            $result = $new_attachment_id;

          } 
        }
      
      } 
                  
    }// foreach
        
    echo esc_html__('The images were added.','maxgalleria-media-library') . PHP_EOL;              
        
    die();
    
  }
  
  public function search_media () {
    
    global $wpdb;
    
    if ( !wp_verify_nonce( $_POST['nonce'], MAXGALLERIA_MEDIA_LIBRARY_NONCE)) {
      exit(esc_html__('missing nonce! Please refresh this page.','maxgalleria-media-library'));
    } 
    
    if ((isset($_POST['search_value'])) && (strlen(trim($_POST['search_value'])) > 0))
      $search_value = trim(sanitize_text_field($_POST['search_value']));
    else
      $search_value = "";
    
	$sql = $wpdb->prepare("select ID, post_title, post_name, pm.meta_value as attached_file from {$wpdb->prefix}posts 
			LEFT JOIN {$wpdb->prefix}mgmlp_folders ON( {$wpdb->prefix}posts.ID = {$wpdb->prefix}mgmlp_folders.post_id) 
      LEFT JOIN {$wpdb->prefix}postmeta AS pm ON (pm.post_id = {$wpdb->prefix}posts.ID)
      where post_type= 'attachment' and pm.meta_key = '_wp_attached_file' and post_title like '%%%s%%'", $search_value);
    
    $rows = $wpdb->get_results($sql);
    
    if($rows) {
        foreach($rows as $row) {
          $thumbnail = wp_get_attachment_thumb_url($row->ID);
          if($thumbnail !== false)
            $ext = pathinfo($thumbnail, PATHINFO_EXTENSION);
          else {
						
						$baseurl = $this->upload_dir['baseurl'];
						$baseurl = rtrim($baseurl, '/') . '/';
						$image_location = $baseurl . ltrim($row->attached_file, '/');
												
            $ext_pos = strrpos($image_location, '.');
            $ext = substr($image_location, $ext_pos+1);
            $thumbnail = MAXGALLERIA_MEDIA_LIBRARY_PLUGIN_URL . "/images/file.jpg";
          }
          ?>
          <li>
            <a class='media-attachment' href='<?php echo esc_url_raw(site_url() . "/wp-admin/upload.php?item=" . $row->ID ) ?>'><img alt='<?php echo esc_html($row->post_title . '.' . $ext) ?>' src='<?php echo esc_url($thumbnail) ?>' /></a>
            <div class='attachment-name'><?php echo esc_html($row->post_title . '.' . $ext) ?></div>
          </li>
          <?php
        }      
      
    }
    else {
      echo esc_html__('No files were found matching that name.','maxgalleria-media-library') . PHP_EOL;                      
    }
    
    die();    
  }
  
  public function search_library() {
    
    global $wpdb;
    $block_access_table = $wpdb->prefix . MAXGALLERIA_MEDIA_LIBRARY_BLOCK_ACCESS_TABLE;    
    $current_user = get_current_user_id();
    $author_class = '';
    
    if ((isset($_GET['s'])) && (strlen(trim($_GET['s'])) > 0))
      $search_string = trim(sanitize_text_field($_GET['s']));
    else
      $search_string = '';
        
    echo '<div id="wp-media-grid" class="wrap">' . PHP_EOL;
    //empty h2 for where WP notices will appear
    echo '  <h2></h2>' . PHP_EOL;
//    echo '  <div class="media-plus-toolbar wp-filter"><div class="media-toolbar-secondary">' . PHP_EOL;
    echo '  <div class="media-plus-toolbar wp-filter">' . PHP_EOL;
    echo '<div id="mgmlp-title-area">' . PHP_EOL;
		echo '  <h2 class="mgmlp-title">'. __('Media Library Folders Search Results','maxgalleria-media-library') . '</h2>' . PHP_EOL;
    echo '  <div>' . PHP_EOL;
    echo '    <p><a href="' . site_url() . '/wp-admin/admin.php?page=mlf-folders8">Back to Media Library Folders</a></p>' . PHP_EOL;
    echo '    <p><input type="search" placeholder="Search" id="mgmlp-media-search-input" class="search" value="'.$search_string.'"> <a id="mlfp-media-search-2" class="gray-blue-link" >' .  __('Search','maxgalleria-media-library') . '</a></p>' . PHP_EOL;            
    echo '  </div>' . PHP_EOL;
    echo '</div>' . PHP_EOL;
		echo '<div style="clear:both;"></div>' . PHP_EOL;
    echo "<div id='search-instructions'>". __('Click on an image to go to its folder or a on folder to view its contents.','maxgalleria-media-library')."</div>";		
    if ((isset($_GET['s'])) && (strlen(trim($_GET['s'])) > 0)) {
      echo "<h4>" . __('Search results for: ','maxgalleria-media-library') . $search_string ."</h4>" . PHP_EOL;			
      
      echo '<ul class="mg-media-list search-results">' . PHP_EOL;
            
      $folder_table = $wpdb->prefix . MAXGALLERIA_MEDIA_LIBRARY_FOLDER_TABLE;
            
    if($this->bda == 'on') {
        
      $sql = $wpdb->prepare("(select {$wpdb->prefix}posts.ID, post_author, post_title, {$wpdb->prefix}mgmlp_folders.folder_id, pm.meta_value as attached_file, 'a' as item_type, 0 as block
from {$wpdb->prefix}posts
LEFT JOIN {$wpdb->prefix}mgmlp_folders ON($wpdb->posts.ID = {$wpdb->prefix}mgmlp_folders.post_id)
LEFT JOIN {$wpdb->prefix}postmeta AS pm ON (pm.post_id = {$wpdb->prefix}posts.ID)
LEFT JOIN {$wpdb->prefix}users AS us ON ({$wpdb->prefix}posts.post_author = us.ID) 
LEFT JOIN $block_access_table ON($block_access_table.attachment_id = {$wpdb->prefix}posts.ID)
where post_type = 'mgmlp_media_folder' and pm.meta_key = '_wp_attached_file' and SUBSTRING_INDEX(pm.meta_value, '/', -1) like '%%%s%%')
union all
(select $wpdb->posts.ID, post_author, post_title, {$wpdb->prefix}mgmlp_folders.folder_id, pm.meta_value as attached_file, 'b' as item_type, IFNULL($block_access_table.block,0) as block
from $wpdb->posts 
LEFT JOIN {$wpdb->prefix}mgmlp_folders ON( $wpdb->posts.ID = {$wpdb->prefix}mgmlp_folders.post_id) 
LEFT JOIN {$wpdb->prefix}postmeta AS pm ON (pm.post_id = {$wpdb->prefix}posts.ID) 
LEFT JOIN {$wpdb->prefix}users AS us ON ({$wpdb->prefix}posts.post_author = us.ID) 
LEFT JOIN $block_access_table ON($block_access_table.attachment_id = {$wpdb->prefix}posts.ID)
where post_type = 'attachment' and pm.meta_key = '_wp_attached_file' and SUBSTRING_INDEX(pm.meta_value, '/', -1) like '%%%s%%') order by attached_file", $search_string, $search_string);

    } else {
      
      $sql = $wpdb->prepare("(select {$wpdb->prefix}posts.ID, post_author, post_title, {$wpdb->prefix}mgmlp_folders.folder_id, pm.meta_value as attached_file, 'a' as item_type, 0 as block
from {$wpdb->prefix}posts
LEFT JOIN {$wpdb->prefix}mgmlp_folders ON($wpdb->posts.ID = {$wpdb->prefix}mgmlp_folders.post_id)
LEFT JOIN {$wpdb->prefix}postmeta AS pm ON (pm.post_id = {$wpdb->prefix}posts.ID)
LEFT JOIN {$wpdb->prefix}users AS us ON ({$wpdb->prefix}posts.post_author = us.ID) 
where post_type = 'mgmlp_media_folder' and pm.meta_key = '_wp_attached_file' and SUBSTRING_INDEX(pm.meta_value, '/', -1) like '%%%s%%')
union all
(select $wpdb->posts.ID, post_author, post_title, {$wpdb->prefix}mgmlp_folders.folder_id, pm.meta_value as attached_file, 'b' as item_type, 0 as block
from $wpdb->posts 
LEFT JOIN {$wpdb->prefix}mgmlp_folders ON( $wpdb->posts.ID = {$wpdb->prefix}mgmlp_folders.post_id) 
LEFT JOIN {$wpdb->prefix}postmeta AS pm ON (pm.post_id = {$wpdb->prefix}posts.ID) 
LEFT JOIN {$wpdb->prefix}users AS us ON ({$wpdb->prefix}posts.post_author = us.ID) 
where post_type = 'attachment' and pm.meta_key = '_wp_attached_file' and SUBSTRING_INDEX(pm.meta_value, '/', -1) like '%%%s%%') order by attached_file", $search_string, $search_string);
      
    }
                      
      //error_log("grid " . $sql);  
        
      $rows = $wpdb->get_results($sql);

      if($rows) {
        foreach($rows as $row) {
          
          if($row->item_type == 'a')
            $class = "media-folder"; 
          else
            $class = "media-attachment";
          
          $thumbnail = wp_get_attachment_thumb_url($row->ID);
          if($this->bda == 'on' && $row->block == 1) {
            $thumbnail = $this->get_absolute_path($thumbnail);
            if($this->bda_user_role == 'authors' && $current_user == $row->post_author)
              $author_class = 'author';
            else 
              $author_class = '';
          } 
          if($thumbnail !== false)
            $ext = pathinfo($thumbnail, PATHINFO_EXTENSION);
          else {
						
            //$image_location = $this->upload_dir['baseurl'] . '/' . $row->attached_file;
						$baseurl = $this->upload_dir['baseurl'];
						$baseurl = rtrim($baseurl, '/') . '/';
						$image_location = $baseurl . ltrim($row->attached_file, '/');
												
            $ext_pos = strrpos($image_location, '.');
            $ext = substr($image_location, $ext_pos+1);
            if($row->item_type == 'b')
              $thumbnail = MAXGALLERIA_MEDIA_LIBRARY_PLUGIN_URL . "/images/file.jpg";
            else
              $thumbnail = MAXGALLERIA_MEDIA_LIBRARY_PLUGIN_URL . "/images/folder.jpg";
          }
          
          echo "<li>" . PHP_EOL;
          if($row->item_type == 'a') {
            echo "   <a class='$class' href='" . site_url() . "/wp-admin/admin.php?page=mlf-folders8&media-folder=" . $row->ID . "'><img alt='' src='$thumbnail' /></a>" . PHP_EOL;
          } else {
            if($row->block == 1) {
              echo "   <a class='$class mlfp-protected $author_class' data-id='$row->ID' href='" . site_url() . "/wp-admin/admin.php?page=mlf-folders8&media-folder=" . $row->folder_id . "'><img class='attachment-thumbnail' alt='' src='$thumbnail' /></a>" . PHP_EOL;
            } else {
              echo "   <a class='$class' data-id='$row->ID' href='" . site_url() . "/wp-admin/admin.php?page=mlf-folders8&media-folder=" . $row->folder_id . "'><img class='attachment-thumbnail' alt='' src='$thumbnail' /></a>" . PHP_EOL;
            }  
          }  
          echo "   <div class='attachment-name'>$row->post_title</div>" . PHP_EOL;
          echo "</li>" . PHP_EOL;              
          
        }
      }

      echo "</ul>" . PHP_EOL;
    }
    echo '</div>' . PHP_EOL;    
    
    ?>
        
    <script>                        
    jQuery(document).ready(function(){
      console.log('document ready');
      jQuery('#mgmlp-media-search-input').keydown(function (e){
        if(e.keyCode == 13){

          var search_value = jQuery('#mgmlp-media-search-input').val();                    

          var home_url = "<?php echo site_url(); ?>"; 

          window.location.href = home_url + '/wp-admin/admin.php?page=search-library&' + 's=' + search_value;

        }  
      });
            
      jQuery(document).on("click", "#mlfp-media-search-2", function () {
      
        var search_value = jQuery('#mgmlp-media-search-input').val();
        
        var home_url = "<?php echo site_url(); ?>"; 
        
        window.location.href = home_url + '/wp-admin/admin.php?page=search-library&' + 's=' + search_value;
      });
      
        <?php if($this->bda == 'on') { ?>
        
          console.log('bda_user_role', mgmlp_ajax.bda_user_role);
          if(mgmlp_ajax.bda_user_role == 'authors')
            var selector_class = '.mlfp-protected.author';
          else
            var selector_class = '.mlfp-protected';
          
            jQuery(selector_class).each(function () {
              
              var image_element = jQuery(this).find('img.attachment-thumbnail');
              var src = image_element.attr('src');
              // remove the source URL and avoid the 404 error
              jQuery(image_element).attr('src', '');
              console.log('src', src);

              jQuery.ajax({
                type: 'POST',
                async: true,
                data: { action: 'mlfp_load_image', src: src, nonce: mgmlp_ajax.nonce },
                url: mgmlp_ajax.ajaxurl,
                success: function (data) {
                  if(data.length > 0)
                    jQuery(image_element).attr('src', data);
                },
                error: function (err){
                  alert(err.responseText);
                }
              });

          });
        <?php } ?>      
      });      
    </script>          
    <?php
  }  
              
  public function maxgalleria_rename_image() {
    
    global $wpdb, $blog_id, $is_IIS;
    
    if ( !wp_verify_nonce( $_POST['nonce'], MAXGALLERIA_MEDIA_LIBRARY_NONCE)) {
      exit(esc_html__('missing nonce! Please refresh this page.','maxgalleria-media-library'));
    } 
    
    if ((isset($_POST['image_id'])) && (strlen(trim($_POST['image_id'])) > 0))
      $file_id = trim(sanitize_text_field($_POST['image_id']));
    else
      $file_id = "";
    
    if ((isset($_POST['new_file_name'])) && (strlen(trim($_POST['new_file_name'])) > 0))
      $new_file_name = trim(sanitize_text_field($_POST['new_file_name']));
    else
      $new_file_name = "";
    
    if($new_file_name === '') {
      echo esc_html__('Invalid file name.','maxgalleria-media-library');
      die();
    }
    
    if(preg_match('^[\w,\s\-_]+\.[A-Za-z]{3}$^', $new_file_name)) {
      echo esc_html__('Invalid file name.','maxgalleria-media-library');
      die();      
    }
          
    if (preg_match("/\\s/", $new_file_name)) {
			echo esc_html__('The file name cannot contain spaces or tabs.','maxgalleria-media-library'); 
			die();            
    }
		
		$new_file_name = sanitize_file_name($new_file_name);
    
    $sql = $wpdb->prepare("select ID, pm.meta_value as attached_file, post_title, post_name
from {$wpdb->prefix}posts 
LEFT JOIN {$wpdb->prefix}postmeta AS pm ON (pm.post_id = {$wpdb->prefix}posts.ID) 
where ID = %s
AND pm.meta_key = '_wp_attached_file'", $file_id);

    $row = $wpdb->get_row($sql);
    if($row) {
			
      $image_location = $this->build_location_url($row->attached_file);
      
      $alt_text = get_post_meta($file_id, '_wp_attachment_image_alt', true);
      			
      $full_new_file_name = $new_file_name . '.' . pathinfo($image_location, PATHINFO_EXTENSION);
      $destination_path = $this->get_absolute_path(pathinfo($image_location, PATHINFO_DIRNAME));
						
      $new_file_name = wp_unique_filename( $destination_path, $full_new_file_name, null );

			$new_file_title = $this->remove_extension($new_file_name);
      
      $old_file_path = $this->get_absolute_path($image_location);
						
      $new_file_url = pathinfo($image_location, PATHINFO_DIRNAME) . DIRECTORY_SEPARATOR . $new_file_name;

			if(is_multisite()) {
				$url_slug = "site" . $blog_id . "/";
				$new_file_url = str_replace($url_slug, "", $new_file_url);
			}
									
      $new_file_path = $this->get_absolute_path($new_file_url);
                  
      if($this->is_windows()) {
        $old_file_path = str_replace('\\', '/', $old_file_path);      
        $new_file_path = str_replace('\\', '/', $new_file_path);      
      }
						
			$rename_image_location = $this->get_base_file($image_location);
			$rename_destination = $this->get_base_file($new_file_url);			
      
      $position = strrpos($image_location, '.');
      
      $image_location_no_extension = substr($image_location, 0, $position);
			            
      if(rename($old_file_path, $new_file_path )) {

        //$old_file_path = str_replace('.', '*.', $old_file_path );
        
        $metadata = wp_get_attachment_metadata($file_id);                               
        $path_to_thumbnails = pathinfo($old_file_path, PATHINFO_DIRNAME);

        foreach($metadata['sizes'] as $source_path) {
          $thumbnail_file = $path_to_thumbnails . DIRECTORY_SEPARATOR . $source_path['file'];
          
          if ($is_IIS || strtoupper(substr(PHP_OS, 0, 3)) == 'WIN' || strtoupper(substr(PHP_OS, 0, 13)) == 'MICROSOFT-IIS' )
            $thumbnail_file = str_replace('/', '\\', $thumbnail_file);
          
          if(file_exists($thumbnail_file))
            unlink($thumbnail_file);
        }  
                      
        $table = $wpdb->prefix . "posts";
        $data = array('guid' => $new_file_url, 
                      'post_title' => $new_file_title,
                      'post_name' => $new_file_name                
                );
        $where = array('ID' => $file_id);
        $wpdb->update( $table, $data, $where);
        
        $table = $wpdb->prefix . "postmeta";
        $where = array('post_id' => $file_id);
        $wpdb->delete($table, $where);
                
        // get the uploads dir name
        $basedir = $this->upload_dir['baseurl'];
        $uploads_dir_name_pos = strrpos($basedir, '/');
        $uploads_dir_name = substr($basedir, $uploads_dir_name_pos+1);

        //find the name and cut off the part with the uploads path
        $string_position = strpos($new_file_url, $uploads_dir_name);
        $uploads_dir_length = strlen($uploads_dir_name) + 1;
        $uploads_location = substr($new_file_url, $string_position+$uploads_dir_length);
        if($this->is_windows()) 
          $uploads_location = str_replace('\\','/', $uploads_location);      
								
				$uploads_location = ltrim($uploads_location, '/');
        update_post_meta( $file_id, '_wp_attached_file', $uploads_location );
        if(strlen(trim($alt_text)) > 0)
          update_post_meta( $file_id, '_wp_attachment_image_alt', $alt_text );
        $attach_data = wp_generate_attachment_metadata( $file_id, $new_file_path );
        wp_update_attachment_metadata( $file_id, $attach_data );
														
          if(class_exists( 'SiteOrigin_Panels')) {                  
            $this->update_serial_postmeta_records($rename_image_location, $rename_destination);                  
          }
          
          // update postmeta records for beaver builder
          if(class_exists( 'FLBuilderLoader')) {

            $sql = "SELECT ID FROM {$wpdb->prefix}posts WHERE post_content LIKE '%$rename_image_location%'";

            $records = $wpdb->get_results($sql);
            foreach($records as $record) {

              $this->update_bb_postmeta($record->ID, $rename_image_location, $rename_destination);

            }
            // clearing BB caches
            if ( class_exists( 'FLBuilderModel' ) && method_exists( 'FLBuilderModel', 'delete_asset_cache_for_all_posts' ) ) {
              FLBuilderModel::delete_asset_cache_for_all_posts();
            }
            if ( class_exists( 'FLCustomizer' ) && method_exists( 'FLCustomizer', 'clear_all_css_cache' ) ) {
              FLCustomizer::clear_all_css_cache();
            }

          }

				$replace_sql = "UPDATE {$wpdb->prefix}posts SET `post_content` = REPLACE (`post_content`, '$rename_image_location', '$rename_destination');";
          
        $replace_sql = str_replace ( '/', '\/', $replace_sql);
				$result = $wpdb->query($replace_sql);
        
        // for updating wp pagebuilder
        if(defined('WPPB_LICENSE')) {
          $this->update_wppb_data($image_location_no_extension, $new_file_url);          
        }
        
        // for updating themify images
        if(function_exists('themify_builder_activate')) {
          $this->update_themify_data($image_location_no_extension, $new_file_url);
        }
                
        // for updating elementor background images
        if(is_plugin_active("elementor/elementor.php")) {
          $this->update_elementor_data($file_id, $image_location_no_extension, $new_file_url);          
        }
                				
				echo esc_html__('Updating attachment links, please wait...The file was renamed','maxgalleria-media-library');
      }
    }
    
    die();
  }
  
	public function build_location_url($attached_file) {					
		return rtrim($this->upload_dir['baseurl'], '/') . '/' . ltrim($attached_file, '/');
	}					
    
  // saves the sort selection
  public function sort_contents() {
    
    if ( !wp_verify_nonce( $_POST['nonce'], MAXGALLERIA_MEDIA_LIBRARY_NONCE)) {
      exit(esc_html__('missing nonce!','maxgalleria-media-library'));
    } 
    
    if ((isset($_POST['sort_order'])) && (strlen(trim($_POST['sort_order'])) > 0))
      $sort_order = trim(sanitize_text_field($_POST['sort_order']));
    else
      $sort_order = "0";
    
    if ((isset($_POST['folder'])) && (strlen(trim($_POST['folder'])) > 0))
      $current_folder_id = trim(sanitize_text_field($_POST['folder']));
    else
      $current_folder_id = "";
		        
    update_option( MAXGALLERIA_MEDIA_LIBRARY_SORT_ORDER, $sort_order );  
        
		if($current_folder_id != "") {		
		  $this->display_folder_contents ($current_folder_id, true);
		}
                    
    die();
  }
  
  public function mlf_change_sort_type() {
    
    if ( !wp_verify_nonce( $_POST['nonce'], MAXGALLERIA_MEDIA_LIBRARY_NONCE)) {
      exit(esc_html__('missing nonce!','maxgalleria-media-library'));
    } 
    
    if ((isset($_POST['sort_type'])) && (strlen(trim($_POST['sort_type'])) > 0))
      $sort_type = trim(sanitize_text_field($_POST['sort_type']));
    else
      $sort_type = "ASC";
    
    if ((isset($_POST['folder'])) && (strlen(trim($_POST['folder'])) > 0))
      $current_folder_id = trim(sanitize_text_field($_POST['folder']));
    else
      $current_folder_id = "";
		        
    update_option( MAXGALLERIA_MLF_SORT_TYPE, $sort_type );  
        
		if($current_folder_id != "") {		
		  $this->display_folder_contents ($current_folder_id, true);
		}
                    
    die();
  }
  
	public function mgmlp_move_copy(){

    if ( !wp_verify_nonce( $_POST['nonce'], MAXGALLERIA_MEDIA_LIBRARY_NONCE)) {
      exit(esc_html__('missing nonce! Please refresh this page.','maxgalleria-media-library'));
    } 
    
    if ((isset($_POST['move_copy_switch'])) && (strlen(trim($_POST['move_copy_switch'])) > 0))
      $move_copy_switch = trim(sanitize_text_field($_POST['move_copy_switch']));
    else
      $move_copy_switch = 'on';
				    
    update_option( MAXGALLERIA_MEDIA_LIBRARY_MOVE_OR_COPY, $move_copy_switch );  
		
		die();
		
	}
  
  public function run_on_deactivate() {
    wp_clear_scheduled_hook('new_folder_check');
  }
  
  public function mlf_check_for_new_folders(){
    
    if ( !wp_verify_nonce( $_POST['nonce'], MAXGALLERIA_MEDIA_LIBRARY_NONCE)) {
      exit(esc_html__('missing nonce!','maxgalleria-media-library'));
    }
    
    if ((isset($_POST['parent_folder'])) && (strlen(trim($_POST['parent_folder'])) > 0))
      $parent_folder_id = trim(sanitize_text_field($_POST['parent_folder']));
    
    //error_log("parent_folder_id $parent_folder_id");
            
    $message = $this->admin_check_for_new_folders(true);
    
    $folders = $this->get_folder_data($parent_folder_id);
    $data = array ('message' => esc_html($message), 'folders' => $folders, 'refresh' => true );
    echo json_encode($data);
        
    die();
    
  }
  
  public function admin_check_for_new_folders($noecho = null) {
    
		global $blog_id, $is_IIS;
		$skip_path = "";
    //$uploads_path = wp_upload_dir();
    $message = esc_html__('Added','maxgalleria-media-library');
    $first = true;
    
    if(!$this->upload_dir['error']) {
      
      $uploads_folder = get_option(MAXGALLERIA_MEDIA_LIBRARY_UPLOAD_FOLDER_NAME, "uploads");      
      $uploads_folder_id = get_option(MAXGALLERIA_MEDIA_LIBRARY_UPLOAD_FOLDER_ID );
      $uploads_length = strlen($uploads_folder);
						
			$folders_to_hide = explode("\n", file_get_contents( MAXGALLERIA_MEDIA_LIBRARY_PLUGIN_DIR .'/folders_to_hide.txt'));
      
      //find the uploads folder
      $uploads_url = $this->upload_dir['baseurl'];
			$upload_path = $this->upload_dir['basedir'];
      $folder_found = false;
			
			//not sure if this is still needed
			//$this->mlp_remove_slashes();
      
      if(!$noecho)
        echo esc_html( __('Scanning for new folders in ','maxgalleria-media-library') . " $upload_path") . "<br>";      
      $objects = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($upload_path), RecursiveIteratorIterator::SELF_FIRST, RecursiveIteratorIterator::CATCH_GET_CHILD);
      foreach($objects as $name => $object){
        if(is_dir($name)) {
          $dir_name = pathinfo($name, PATHINFO_BASENAME);
          if ($dir_name[0] !== '.' && strpos($dir_name, "'") === false ) { 
						if( empty($skip_path) || (strpos($name, $skip_path) === false)) {
						
							// no match, set it back to empty
							$skip_path = "";
						
            if(!is_multisite()) {
							$upload_pos = strpos($name, $uploads_folder);
							$url = $uploads_url . substr($name, ($upload_pos+$uploads_length));

							// fix slashes if running windows
              if ($is_IIS || strtoupper(substr(PHP_OS, 0, 3)) == 'WIN' || strtoupper(substr(PHP_OS, 0, 13)) == 'MICROSOFT-IIS' ) {
								$url = str_replace('\\', '/', $url);      
							}

							if($this->folder_exist($url) === false) {
								if(!in_array($dir_name, $folders_to_hide)) {
		              if(!file_exists($name . DIRECTORY_SEPARATOR . 'mlpp-hidden' )){
										$folder_found = true;
										if(!$noecho)
											echo esc_html( __('Adding','maxgalleria-media-library') . " " . esc_url($url)) . "<br>";
                    else {
                      $seprator = ($first) ? ' ':', ';
                      $first = false;
                      $message .= $seprator . esc_url($url); 
                    }  
										$parent_id = $this->find_parent_id($url);
                    if($parent_id)
										  $this->add_media_folder($dir_name, $parent_id, $url);
									} else {
										$skip_path = $name;
									}
								} else {
									$skip_path = $name;									
								}
							}
						} else {
							if($blog_id === '1') {
								if(strpos($name,"uploads/sites") !== false)
									continue;
								
								$upload_pos = strpos($name, $uploads_folder);
								$url = $uploads_url . substr($name, ($upload_pos+$uploads_length));

								// fix slashes if running windows
                if ($is_IIS || strtoupper(substr(PHP_OS, 0, 3)) == 'WIN' || strtoupper(substr(PHP_OS, 0, 13)) == 'MICROSOFT-IIS' ) {
									$url = str_replace('\\', '/', $url);      
								}

								if($this->folder_exist($url) === false) {
								  if(!in_array($dir_name, $folders_to_hide)) {
		                if(!file_exists($name . DIRECTORY_SEPARATOR . 'mlpp-hidden' )){
											$folder_found = true;
											if(!$noecho)
												echo esc_html( __('Adding','maxgalleria-media-library') . " " . esc_url($url)) . "<br>";
                      else {
                        $seprator = ($first) ? ' ':', ';
                        $first = false;
                        $message .= $seprator . esc_url($url); 
                      }  
											$parent_id = $this->find_parent_id($url);
                      if($parent_id)
											  $this->add_media_folder($dir_name, $parent_id, $url);
											} else {
												$skip_path = $name;									
											}
										} else {
											$skip_path = $name;									
										}
									}																
							} else {
								if(strpos($name,"uploads/sites/$blog_id") !== false) {
									$upload_pos = strpos($name, $uploads_folder);
									$url = $uploads_url . substr($name, ($upload_pos+$uploads_length));

									// fix slashes if running windows
                  if ($is_IIS || strtoupper(substr(PHP_OS, 0, 3)) == 'WIN' || strtoupper(substr(PHP_OS, 0, 13)) == 'MICROSOFT-IIS' ) {
										$url = str_replace('\\', '/', $url);      
									}

										if($this->folder_exist($url) === false) {											
											if(!in_array($dir_name, $folders_to_hide)) {
												if(!file_exists($name . DIRECTORY_SEPARATOR . 'mlpp-hidden' )){																						
													$folder_found = true;
													if(!$noecho)
														echo esc_html( __('Adding','maxgalleria-media-library') . " " . esc_url($url)) . "<br>";
                          else {
                            $seprator = ($first) ? ' ':', ';
                            $first = false;
                            $message .= $seprator . esc_url($url); 
                          }  
													$parent_id = $this->find_parent_id($url);
											    if($parent_id)
													  $this->add_media_folder($dir_name, $parent_id, $url);              
												}
											} else {
												$skip_path = $name;									
											}
										}																
                  }
                }
              }
            }  
          }
        }  
      }      
      if(!$folder_found) {
          $message = esc_html__('No new folders were found.','maxgalleria-media-library');
        if(!$noecho)
          echo $message . "<br>";
      }  
    } 
    else {
      $message = esc_html("error: " . $this->upload_dir['error']);
      if(!$noecho)
        echo $message . "<br>";
    }
    return $message;
  }
		
	public function new_folder_search($name, $uploads_folder, $uploads_length, $dir_name, $noecho) {
		global $is_IIS;
		$folder_found = false;
		$upload_pos = strpos($name, $uploads_folder);
		$url = $uploads_url . substr($name, ($upload_pos+$uploads_length));

		// fix slashes if running windows
    if ($is_IIS || strtoupper(substr(PHP_OS, 0, 3)) == 'WIN' || strtoupper(substr(PHP_OS, 0, 13)) == 'MICROSOFT-IIS' ) {
			$url = str_replace('\\', '/', $url);      
		}

		if($this->folder_exist($url) === false) {
			$folder_found = true;
			if(!$noecho) {
				echo esc_html( __('Adding','maxgalleria-media-library') . " " . esc_url($url)) . "<br>";
			}	
			$parent_id = $this->find_parent_id($url);
			if($parent_id)
			  $this->add_media_folder($dir_name, $parent_id, $url);              
		}
		return $folder_found;
	}
  
  private function find_parent_id($base_url) {
    
    global $wpdb;    
    $last_slash = strrpos($base_url, '/');
    $parent_dir = substr($base_url, 0, $last_slash);
		
		// get the relative path
		$parent_dir = substr($parent_dir, $this->base_url_length);		
		
    $sql = "SELECT ID FROM {$wpdb->prefix}posts
LEFT JOIN {$wpdb->prefix}postmeta AS pm ON pm.post_id = ID
WHERE pm.meta_value = '$parent_dir' 
and pm.meta_key = '_wp_attached_file'";
		
    $row = $wpdb->get_row($sql);
    if($row) {
      $parent_id = $row->ID;
    }
    else
      $parent_id = $this->uploads_folder_ID; //-1;

    return $parent_id;
  }
    
  private function mpmlp_insert_post( $post_type, $post_title, $guid, $post_status ) {
    global $wpdb;
    
    $user_id = get_current_user_id();
    $post_date = current_time('mysql');
    
    $post = array(
      'post_content'   => '',
      'post_name'      => $post_title, 
      'post_title'     => $post_title,
      'post_status'    => $post_status,
      'post_type'      => $post_type,
      'post_author'    => $user_id,
      'ping_status'    => 'closed',
      'post_parent'    => 0,
      'menu_order'     => 0,
      'to_ping'        => '',
      'pinged'         => '',
      'post_password'  => '',
      'guid'           => $guid,
      'post_content_filtered' => '',
      'post_excerpt'   => '',
      'post_date'      => $post_date,
      'post_date_gmt'  => $post_date,
      'comment_status' => 'closed'
    );      
        
    
    $table = $wpdb->prefix . "posts";	    
    $wpdb->insert( $table, $post );
        
    return $wpdb->insert_id;  
  }
  
  public function mlp_set_review_notice_true() {
    
    $current_user_id = get_current_user_id(); 
    
    update_user_meta( $current_user_id, MAXGALLERIA_MLP_REVIEW_NOTICE, "off" );
        
    $request = sanitize_url($_SERVER["HTTP_REFERER"]);
    
    echo "<script>window.location.href = '" . esc_url_raw($request) . "'</script>";             
    
    
	}
  
  public function mlp_set_feature_notice_true() {
    
    $current_user_id = get_current_user_id(); 
    
    update_user_meta( $current_user_id, MAXGALLERIA_MLP_FEATURE_NOTICE, "off" );
    
    $request = sanitize_url($_SERVER["HTTP_REFERER"]);
    
    echo "<script>window.location.href = '" . esc_url_raw($request) . "'</script>";             
    
	}
    
	public function mlp_set_review_later() {
    
    $current_user_id = get_current_user_id(); 
    
    $review_date = date('Y-m-d', strtotime("+14 days"));
        
    update_user_meta( $current_user_id, MAXGALLERIA_MLP_REVIEW_NOTICE, $review_date );
    
    $request = sanitize_url($_SERVER["HTTP_REFERER"]);
    
    echo "<script>window.location.href = '" . esc_url_raw($request) . "'</script>";             
    
	}
  
  public function mlp_features_notice() {
    if( current_user_can( 'manage_options' ) ) {  ?>
      <div class="updated notice maxgalleria-mlp-notice">         
        <div id='mlp_logo'></div>
        <div id='maxgalleria-mlp-notice-3'><p id='mlp-notice-title'><?php esc_html_e('Is there a feature you would like for us to add to', 'maxgalleria-media-library' ); ?><br><?php esc_html_e('Media Library Folders Pro? Let us know.', 'maxgalleria-media-library' ); ?></p>
        <p><?php esc_html_e('Send your suggestions to', 'maxgalleria-media-library' ); ?> <a href="mailto:support@maxfoundry.com">support@maxfoundry.com</a>.</p>

        </div>
        <a class="dashicons dashicons-dismiss close-mlp-notice" href="<?php echo esc_url_raw(admin_url() . "admin.php?page=mlp-feature-notice") ?>"></a>          
      </div>
    <?php     
    }
  }
  
  public function mlp_review_notice() {
    if( current_user_can( 'manage_options' ) ) {  ?>
      <div class="updated notice maxgalleria-mlp-notice">         
        <div id='mlp_logo'></div>
        <div id='maxgalleria-mlp-notice-3'><p id='mlp-notice-title'><?php esc_html_e( 'Rate us Please!', 'maxgalleria-media-library' ); ?></p>
        <p><?php esc_html_e( 'Your rating is the simplest way to support Media Library Folders Pro. We really appreciate it!', 'maxgalleria-media-library' ); ?></p>

        <ul id="mlp-review-notice-links">
          <li> <span class="dashicons dashicons-smiley"></span><a href="<?php echo esc_url_raw( admin_url() . "admin.php?page=mlp-review-notice") ?>"><?php esc_html_e( "I've already left a review", "maxgalleria-media-library" ); ?></a></li>
          <li><span class="dashicons dashicons-calendar-alt"></span><a href="<?php echo esc_url_raw( admin_url() . "admin.php?page=mlp-review-later") ?>"><?php esc_html_e( "Maybe Later", "maxgalleria-media-library" ); ?></a></li>
          <li><span class="dashicons dashicons-external"></span><a target="_blank" href="https://wordpress.org/support/plugin/media-library-plus/reviews/?filter=5"><?php esc_html_e( "Sure! I'd love to!", "maxgalleria-media-library" ); ?></a></li>
        </ul>
        </div>
        <a class="dashicons dashicons-dismiss close-mlp-notice" href="<?php echo esc_url_raw( admin_url() . "admin.php?page=mlp-review-notice") ?>"></a>          
      </div>
    <?php     
    }
  }
  			  
	public function max_sync_contents($parent_folder) {
        
    global $wpdb;
		global $blog_id;
		global $is_IIS;
		$skip_path = "";
		$last_new_folder_id = 0;
		
    $files_added = 0;
		$alt_text = "";
		$default_title = "";
		$default_alt = "";
		$folders_found = false;
    $existing_folders = false;
				    				    
    if(!is_numeric($parent_folder))
      die();
    
		$uploads_folder = get_option(MAXGALLERIA_MEDIA_LIBRARY_UPLOAD_FOLDER_NAME, "uploads");      
		$uploads_length = strlen($uploads_folder);		
		$uploads_url = $this->upload_dir['baseurl'];
		$upload_path = $this->upload_dir['basedir'];

		$folders_to_hide = explode("\n", file_get_contents(MAXGALLERIA_MEDIA_LIBRARY_PLUGIN_DIR .'/folders_to_hide.txt'));
		
    $sql = "select meta_value as attached_file
from {$wpdb->prefix}postmeta
where post_id = $parent_folder    
and meta_key = '_wp_attached_file'";	

    $current_row = $wpdb->get_row($sql);

		$baseurl = rtrim($uploads_url, '/') . '/';
		
		if(!is_multisite()) {
			$image_location = $baseurl . ltrim($current_row->attached_file, '/');      
      $folder_path = $this->get_absolute_path($image_location);
		} else {
      $folder_path = $this->get_absolute_path($baseurl);		
		}	
		
		//not sure if this is still needed
		//$this->mlp_remove_slashes();
		
		$folders_array = array();
		$folders_array[] = $parent_folder;
    
    $file_names = array_diff(scandir($folder_path), array('..', '.'));
    				    						
    // check for new folders		
    foreach ($file_names as $file_name) {
			$name = $folder_path . DIRECTORY_SEPARATOR . $file_name;      
			if(is_dir($name)) {
        //error_log($name);
				$dir_name = pathinfo($name, PATHINFO_BASENAME);
				if ($dir_name[0] !== '.' && strpos($dir_name, "'") === false ) { 
					if( empty($skip_path) || (strpos($name, $skip_path) === false)) {

						// no match, set it back to empty
						$skip_path = "";

						if(!is_multisite()) {

							$upload_pos = strpos($name, $uploads_folder);
							$url = $uploads_url . substr($name, ($upload_pos+$uploads_length));

							// fix slashes if running windows
              if ($is_IIS || strtoupper(substr(PHP_OS, 0, 3)) == 'WIN' || strtoupper(substr(PHP_OS, 0, 13)) == 'MICROSOFT-IIS' ) {
								$url = str_replace('\\', '/', $url);      
							}

							$existing_folder_id = $this->folder_exist($url);
							if($existing_folder_id === false) {
								if(!in_array($dir_name, $folders_to_hide)) {
									if(!file_exists($name . DIRECTORY_SEPARATOR . 'mlpp-hidden' )){
									$folders_found = true;
									$parent_id = $this->find_parent_id($url);
									$last_new_folder_id = $this->add_media_folder($dir_name, $parent_id, $url);
									$files_added++;								
									} else {
										$skip_path = $name;
									}
								} else {
									$skip_path = $name;			
								}
							} else {
                $existing_folders = true;
							}
						} else {
							if($blog_id === '1') {
								if(strpos($name,"uploads/sites") !== false)
									continue;

								$upload_pos = strpos($name, $uploads_folder);
								$url = $uploads_url . substr($name, ($upload_pos+$uploads_length));

								// fix slashes if running windows
                if ($is_IIS || strtoupper(substr(PHP_OS, 0, 3)) == 'WIN' || strtoupper(substr(PHP_OS, 0, 13)) == 'MICROSOFT-IIS' ) {
									$url = str_replace('\\', '/', $url);      
								}

							  $existing_folder_id = $this->folder_exist($url);
								if($existing_folder_id === false) {
									if(!in_array($dir_name, $folders_to_hide)) {
										if(!file_exists($name . DIRECTORY_SEPARATOR . 'mlpp-hidden' )){
											$folders_found = true;
											$parent_id = $this->find_parent_id($url);
											$last_new_folder_id = $this->add_media_folder($dir_name, $parent_id, $url);
											$files_added++;								
										} else {
											$skip_path = $name;
										}
									} else {
										$skip_path = $name;									
									}
								}	else {
                  $existing_folders = true;
								}					
							} else {
								if(strpos($name,"uploads/sites/$blog_id") !== false) {
									
									$upload_pos = strpos($name, $uploads_folder);
																		
									$url = $uploads_url . substr($name, ($upload_pos+$uploads_length));

									// fix slashes if running windows
                  if ($is_IIS || strtoupper(substr(PHP_OS, 0, 3)) == 'WIN' || strtoupper(substr(PHP_OS, 0, 13)) == 'MICROSOFT-IIS' ) {
										$url = str_replace('\\', '/', $url);      
									}
									$existing_folder_id = $this->folder_exist($url);
									if($existing_folder_id === false) {
										$folders_found = true;
										$parent_id = $this->find_parent_id($url);
										$last_new_folder_id = $this->add_media_folder($dir_name, $parent_id, $url);              
										$files_added++;								
									} else {
                    $existing_folders = true;
									}																
								}
							}
						}
					}  
				}				
			}
		} // end foreach		
    
		$user_id = get_current_user_id();
  	update_user_meta($user_id, MAXG_SYNC_FOLDERS, $folders_array);
				
    if($folders_found || $existing_folders) {
      return true;
    } else {
      return false;
    }  
    
	}
  	
	public function get_base_file($file_path) {
		
		$dot_position = strrpos($file_path, '.' );		
    if($dot_position === false)
      return $file_path;
    else
		  return substr($file_path, 0, $dot_position);
	}
				
	private function is_base_file($file_path, $file_array) {
		
		$dash_position = strrpos($file_path, '-' );
		$x_position = strrpos($file_path, 'x', $dash_position);
		$dot_position = strrpos($file_path, '.' );
		
		if(($dash_position) && ($x_position)) {
			$base_file = substr($file_path, 0, $dash_position) . substr($file_path, $dot_position );
			if(in_array($base_file, $file_array))
				return false;
			else 
				return true;
		} else 
			return true;
				
	}
	
	private function search_folder_attachments($file_path, $attachments){

		$found = false;
    if($attachments) {
      foreach($attachments as $row) {
        $current_file_path = pathinfo(get_attached_file($row->ID), PATHINFO_BASENAME);
        if(strpos($current_file_path, '-scaled.') !== false)
          $current_file_path = str_replace ('-scaled', '', $current_file_path);
        //error_log("$current_file_path $file_path");
				if($current_file_path === $file_path) {
					$found = true;
					break;
				} else {
        }
      }			
    }
		return $found; 
	}
	
	public function write_log ( $log )  {
		if(!defined('HIDE_WRITELOG_MESSAGES')) {
			if ( true === WP_DEBUG ) {
				if ( is_array( $log ) || is_object( $log ) ) {
					error_log( print_r( $log, true ) );
				} else {
					error_log( $log );
				}
			}
		}
  }
	
	public function mlp_load_folder() {
    
    global $wpdb;
		
    if ( !wp_verify_nonce( $_POST['nonce'], MAXGALLERIA_MEDIA_LIBRARY_NONCE)) {
      exit(esc_html__('missing nonce!','maxgalleria-media-library'));
    }
    
    if ((isset($_POST['folder'])) && (strlen(trim($_POST['folder'])) > 0))
      $current_folder_id = trim(sanitize_textarea_field($_POST['folder']));
    else
      $current_folder_id = "";
    
    if(!is_numeric($current_folder_id))
      die();

		$folder_location = $this->get_folder_path($current_folder_id);

		$folders_path = "";
		$parents = $this->get_parents($current_folder_id);

		$folder_count = count($parents);
		$folder_counter = 0;        
		$current_folder_string = site_url() . "/wp-content";
		foreach( $parents as $key => $obj) { 
			$folder_counter++;
			if($folder_counter === $folder_count)
				$folders_path .= $obj['name'];      
			else
				$folders_path .= '<a folder="' . $obj['id'] . '\' class="media-link\'>' . $obj['name'] . '</a>/';      
			$current_folder_string .= '/' . $obj['name'];
		}
		
		$this->display_folder_contents ($current_folder_id, true, $folders_path);
						
	  die();
		
	}
	
	public function mlp_upgrade_to_pro() {
		?>
	
<div class="utp-body"> 			
  <div class="top-section">
    <div class="container">
      <div class="row">
        <div class="width-50">
          <h1><?php esc_html_e('Media Library Folders: Update to PRO','maxgalleria-media-library') ?></h1>
          <a href="<?php echo esc_url(UPGRADE_TO_PRO_LINK); ?>" class="big-pluspro-btn"><?php esc_html_e('Buy Now','maxgalleria-media-library') ?></a>
          <a class="simple-btn block" href="<?php echo esc_url("https://maxgalleria.com/media-library-plus/") ?>"><?php esc_html_e('Click here to learn about the Media Library Folders','maxgalleria-media-library') ?></a>
        </div>
        <div class="width-50">
          <strong>
            <i><?php esc_html_e('Brought to you by','maxgalleria-media-library') ?> <img src="<?php echo esc_url(MAXGALLERIA_MEDIA_LIBRARY_PLUGIN_URL . "/images/logo-mf.png") ?>" alt="logo" /><br><?php esc_html_e('Upgrade to Media Library Folders Pro today!','maxgalleria-media-library') ?> <a class="simple-btn" href="<?php echo esc_url(UPGRADE_TO_PRO_LINK) ?>"><?php esc_html_e('Click Here','maxgalleria-media-library') ?></a></i>
          </strong>
        </div>
        <div class="mlf-clearfix"></div>
      </div>
    </div>
		<img id="mlpp-logo" alt="Media Library Folders Pro Logo" src="<?php echo esc_url(MAXGALLERIA_MEDIA_LIBRARY_PLUGIN_URL ."/images/mlfp.png") ?>" width="235" height="235" >
  </div>
  
  <div class="section features-section">
    <div class="features">
      <div class="container">
        <h2><?php esc_html_e('Features','maxgalleria-media-library') ?></h2>
        <div class="row">
          <div class="width-50">
            <ul>
              <li><span><?php esc_html_e('Add images to your posts and pages','maxgalleria-media-library') ?></span></li>
              <li><span><?php esc_html_e('File Name View Mode','maxgalleria-media-library') ?></span></li>
              <li><span><?php esc_html_e('Thumbnail Management','maxgalleria-media-library') ?></span></li>							
              <li><span><?php esc_html_e('Add Images to WooCommerce Product Gallery','maxgalleria-media-library') ?></span></li>							
              <li><span><?php esc_html_e('Export the media library from one Wordpress site and import it into another','maxgalleria-media-library') ?></span></li>
              <li><span><?php esc_html_e('Front End Upload to a Specific Folder','maxgalleria-media-library') ?></span></li>							
              <li><span><?php esc_html_e('Bulk Move of media files','maxgalleria-media-library') ?></span></li>							
              <li><span><?php esc_html_e('Organize Nextgen Galleries','maxgalleria-media-library') ?></span></li>
            </ul>
          </div>
          <div class="width-50">
            <ul>
              <li><span><?php esc_html_e('Supports Advanced Custom Fields','maxgalleria-media-library') ?></span></li>
              <li><span><?php esc_html_e('Multisite Supported','maxgalleria-media-library') ?></span></li>
              <li><span><?php esc_html_e('Category Interchangability with Enhanced Media Library','maxgalleria-media-library') ?></span></li>							
              <li><span><?php esc_html_e('Embed PDF files in a page via a shortcode and Embed PDF file shortcode generator','maxgalleria-media-library') ?></span></li>							
              <li><span><?php esc_html_e('Media Library Maintenance and Bulk File Import','maxgalleria-media-library') ?></span></li>							
              <li><span><?php esc_html_e('Jetpack and the Wordpress Gallery Shortcode Generator','maxgalleria-media-library') ?></span></li>
              <li><span><?php esc_html_e('Block Direct Access for Selected Files','maxgalleria-media-library') ?></span></li>
            </ul>
          </div>
          <div class="mlf-clearfix"></div>
        </div>
      </div>
    </div>
  </div>


  <div class="section price-section">
    <div class="container">
      <div class="prices">
        <h3>$49</h3>
        <div class="descr">
          <img src="<?php echo esc_url(MAXGALLERIA_MEDIA_LIBRARY_PLUGIN_URL . "/images/icons/benefits.png") ?>" class=" img-responsive" alt="ico">
          <p>            
            <?php esc_html_e('Includes 1 Year Support','maxgalleria-media-library') ?>
            <br>           
            <?php esc_html_e('and Updates','maxgalleria-media-library') ?>
          </p>
        </div>
        <a href="<?php echo esc_url(UPGRADE_TO_PRO_LINK) ?>" class="text-uppercase big-pluspro-btn">Buy MLFP</a>
      </div>
    </div>
  </div>

  <div class="section options-section">
    <div class="option">
      <div class="container">
        <div class="row">
          <div class="width-100">
            
            <p class="mflp-into">
              <?php esc_html_e('MLF Pro integrates with post and page editor pages to let you select <br>and add images to your posts and pages for the editor.','maxgalleria-media-library') ?>
            </p>            
            <h4>
              <?php esc_html_e('Add Images to Your Posts and Pages','maxgalleria-media-library') ?>
            </h4>
            <p>
              <?php esc_html_e('Media Library Folders Pro helps you organize your WordPress Media Library including functions for using and managing your files and images, thubnails, image categorizes and media library maintenance.','maxgalleria-media-library') ?>
            </p>
            <p>
              <?php esc_html_e('Media Library Folders Pro lets you create MaxGalleria and NextGEN Galleries directly from your MLF folders. This is where your images are so it is a logical place to select them and build your Gallery.','maxgalleria-media-library') ?>
            </p>
            <img class="img-responsive" src="<?php echo esc_url(MAXGALLERIA_MEDIA_LIBRARY_PLUGIN_URL . "/images/assets/new-add-images.png") ?>" alt="img" />
          </div>
        </div>
      </div>
    </div>

    <div class="option">
      <div class="container">
        <div class="row">
          <div class="width-100">
            <h4>
              <?php esc_html_e('File Name View Mode','maxgalleria-media-library') ?>
            </h4>
            <p>
              <?php esc_html_e('When you are dealing with large image libraries the wait time can be quite long in WordPress Media Library.  In order to speed the process of image selection we have built a file name view mode options into Media Library Folders Pro.','maxgalleria-media-library') ?>
            </p>
            <p>
              <?php esc_html_e('This mode let\’s you see all of the file names in a folder quickly and then click on specific files to see their images.','maxgalleria-media-library') ?>
            </p>
            <img class="img-responsive" src="<?php echo esc_url(MAXGALLERIA_MEDIA_LIBRARY_PLUGIN_URL . "/images/assets/file-name.png") ?>" alt="img" />
          </div>
        </div>
      </div>
    </div>

    <div class="option">
      <div class="container">
        <div class="row">
          <div class="width-100">
            <h4>
             <?php esc_html_e('Thumbnail Management','maxgalleria-media-library') ?>
            </h4>
            <p>
             <?php esc_html_e('Reduce the number of image thumbnail files generated by WordPress.','maxgalleria-media-library') ?>
            </p>
            <img class="img-responsive" src="<?php echo esc_url(MAXGALLERIA_MEDIA_LIBRARY_PLUGIN_URL . "/images/assets/thumbnail-management.png") ?>" alt="img" />
          </div>
        </div>
      </div>
    </div>
    
    <div class="option">
      <div class="container">
        <div class="row">
          <div class="width-100">
            <h4>
              <?php esc_html_e('Media Library Maintenance','maxgalleria-media-library') ?>
            </h4>
            <p>
              <?php esc_html_e('Over time a site\'s media library often builds up a number of unneeded files, especially auto generated extra thumbnail file sizes that are no longer necessary due to theme or plugin changes or perhaps multiple thumbnail regenerations.','maxgalleria-media-library') ?>
            </p>
            <p>
              <?php esc_html_e('Media Library Maintenance allows site administrators to find, view and remove or import these uncatalogued files.','maxgalleria-media-library') ?>
            </p>
            <img class="img-responsive" src="<?php echo esc_url(MAXGALLERIA_MEDIA_LIBRARY_PLUGIN_URL . "/images/assets/maintenance.png") ?>" alt="img" />
          </div>
        </div>
      </div>
    </div>

    <div class="option">
      <div class="container">
        <div class="row">
          <div class="width-100">
            <h4>
               <?php esc_html_e('Bulk File Import','maxgalleria-media-library') ?>
            </h4>
            <p>
              <?php esc_html_e('Our Media Library Maintenance feature makes it easy to bulk import images and files into the Media Library.','maxgalleria-media-library') ?>
            </p>
          </div>
        </div>
      </div>
    </div>

    <div class="option">
      <div class="container">
        <div class="row">
          <div class="width-100">
            <h4>
             <?php esc_html_e('Assign and Group Images by Categories','maxgalleria-media-library') ?>
            </h4>
            <p>
              <?php esc_html_e('Media Library Folders Pro implements image categories which are compatible with categories created by the Enhanced Media Library plugin.','maxgalleria-media-library') ?>
            </p>
            <img class="img-responsive" src="<?php echo esc_url(MAXGALLERIA_MEDIA_LIBRARY_PLUGIN_URL . "/images/assets/image-categories.png") ?>" alt="img" />
          </div>
        </div>
      </div>
    </div>

    <div class="option">
      <div class="container">
        <div class="row">
          <div class="width-100">
            <h4>
             Import/Export & Backup
            </h4>
            <p>
              The Import/Export feature allows an administrator to export a sites media library from one site to another. 
            </p>
            <img class="img-responsive" src="<?php echo esc_url(MAXGALLERIA_MEDIA_LIBRARY_PLUGIN_URL . "/images/assets/import-export.png") ?>" alt="img" />
          </div>
        </div>
      </div>
    </div>

    <div class="option">
      <div class="container">
        <div class="row">
          <div class="width-100">
            <h4>
             <?php esc_html_e('File Replacement','maxgalleria-media-library') ?>
            </h4>
            <p>
              <?php esc_html_e('Replace an existing file with another one of the same type in the Media Library','maxgalleria-media-library') ?>
            </p>
            <p>
              <?php esc_html_e('You can export and download the contents of your media library from one WordPress site and then upload and import it into the media library of another WordPress site.','maxgalleria-media-library') ?> 
            </p>
            <img class="img-responsive" src="<?php echo esc_url(MAXGALLERIA_MEDIA_LIBRARY_PLUGIN_URL . "/images/assets/file-replacement.png") ?>" alt="img" />
          </div>
        </div>
      </div>
    </div>
    
    <div class="option">
      <div class="container">
        <div class="row">
          <div class="width-100">
            <h4>
             <?php esc_html_e('Block Direct Access for Selected Files','maxgalleria-media-library') ?>
            </h4>
            <p>
              <?php esc_html_e('Select the files to be protected','maxgalleria-media-library') ?>
            </p>
            <p>
              <?php esc_html_e('Generate and configure download links for protected files','maxgalleria-media-library') ?> 
            </p>
            <img class="img-responsive" src="<?php echo esc_url(MAXGALLERIA_MEDIA_LIBRARY_PLUGIN_URL . "/images/assets/block-direct-access.png") ?>" alt="img" />
          </div>
        </div>
      </div>
    </div>
    
    
    <div class="option">
      <div class="container">
        <div class="row">
          <div class="width-100">
            <h4>
             <?php esc_html_e('Frontend Upload','maxgalleria-media-library') ?>
            </h4>
            <p>
              <?php esc_html_e('Frontend uploading of files is available via a shortcode.','maxgalleria-media-library') ?>
            </p>
            <p>
              <?php esc_html_e('Allows your signed in users to upload files to specified folders without needing to grant them access to your dashboard or media library.','maxgalleria-media-library') ?>
            </p>
            <img class="img-responsive" src="<?php echo esc_url(MAXGALLERIA_MEDIA_LIBRARY_PLUGIN_URL . "/images/assets/frontend-upload.png") ?>" alt="img" />
          </div>
        </div>
      </div>
    </div>        
    
    <div class="option">
      <div class="container">
        <div class="row">
          <div class="width-100">
            <h4>
             <?php esc_html_e('Embed PDF, Audio or Video Files','maxgalleria-media-library') ?>
            </h4>
            <p>
              <?php esc_html_e('Media Library Folders Pro allows the embedding of PDF, audio or video files into posts and pages via a shortcode and a builtin embed file shortcode genreator.','maxgalleria-media-library') ?>
            </p>
            <img class="img-responsive" src="<?php echo esc_url(MAXGALLERIA_MEDIA_LIBRARY_PLUGIN_URL . "/images/assets/embed-file.png") ?>" alt="img" />
          </div>
        </div>
      </div>
    </div>

    <div class="option">
      <div class="container">
        <div class="row">
          <div class="width-100">
            <h4>
             <?php esc_html_e('Create Audio and Video Playlists','maxgalleria-media-library') ?>
            </h4>
            <p>
              <?php esc_html_e('Use Media Library Folders Pro\'s playlist shortcode generator to create your own audio or video playlists.','maxgalleria-media-library') ?>
            </p>
            <img class="img-responsive" src="<?php echo esc_url(MAXGALLERIA_MEDIA_LIBRARY_PLUGIN_URL . "/images/assets/audio-playlist-generator.png") ?>" alt="img" />
          </div>
        </div>
      </div>
    </div>

    <div class="option">
      <div class="container">
        <div class="row">
          <div class="width-100">
            <h4>
              <?php esc_html_e('NextGEN Galleries','maxgalleria-media-library') ?>
            </h4>
            <p>
              <?php esc_html_e('Media Library Folders Pro lets you create a NextGEN gallery from the Media Library Pro Plus directory. We recommend using this capability when creating new NextGEN galleries.','maxgalleria-media-library') ?>
            </p>
            <img class="img-responsive" src="<?php echo esc_url(MAXGALLERIA_MEDIA_LIBRARY_PLUGIN_URL . "/images/assets/nextgen.png") ?>" alt="img" />
          </div>
        </div>
      </div>
    </div>


  </div>

  <div class="section options-section last-section">
    <div class="container">
      <h4>
        <?php esc_html_e('Get Media Library Folders Pro','maxgalleria-media-library') ?>
      </h4>
      <a href="<?php echo esc_url(UPGRADE_TO_PRO_LINK) ?>" class="text-uppercase big-pluspro-btn"><?php esc_html_e('Get MLF Pro','maxgalleria-media-library') ?></a>
    </div>
  </div>
</div>			
			
		<?php	
		
  }
			
	public function wp_get_attachment( $attachment_id ) {

		$attachment = get_post( $attachment_id );

		$base_url = $this->upload_dir['baseurl'];
    $attached_file = get_post_meta( $attachment_id, '_wp_attached_file', true );
		$base_url = rtrim($base_url, '/') . '/';
		$image_location = $base_url . ltrim($attached_file, '/');
		
		$available_sizes = array();
		
		if (wp_attachment_is_image($attachment_id)) {
			foreach ( $this->image_sizes as $size ) {
				$image = wp_get_attachment_image_src( $attachment_id, $size );
								
				if(!empty( $image ) && ( true == $image[3] || 'full' == $size )) {
					$available_sizes[$size] = $image[1] . " x " . $image[2];
				}	
			}
		} else {
			$available_sizes["full"] = "full";
		}
	
		
		$image_data = array(
				'id' => $attachment_id,
				'alt' => get_post_meta( $attachment->ID, '_wp_attachment_image_alt', true ),
				'caption' => $attachment->post_excerpt,
				'description' => $attachment->post_content,
				'href' => get_permalink( $attachment->ID ),
				'src' => $image_location,
				'title' => $attachment->post_title,
				'available_sizes'	=> $available_sizes
		);
		
		return $image_data;
	}
				
	
	public function mlpp_hide_template_ad() {
		
    if ( !wp_verify_nonce( $_POST['nonce'], MAXGALLERIA_MEDIA_LIBRARY_NONCE)) {
      exit(esc_html__('missing nonce! Please refresh this page.','maxgalleria-media-library'));
    } 
		
    update_option('mlpp_show_template_ad', "off");
		
		die();
	}
	
		public function mlpp_settings() {
		
		global $current_user;
		?>	
		
		<div styel="clear:both"></div>
		
		<?php
      $meta_index = get_option( MAXGALLERIA_POSTMETA_INDEX, 'off');        
      $this->disable_scaling = get_option( MAXGALLERIA_DISABLE_SCALLING, 'off');
      $images_pre_page = get_option(MAXGALLERIA_MLP_ITEMS_PRE_PAGE, '500');
      $this->sync_skip_webp = get_option(MLFP_SKIP_WEBP_FILES, 'off');    
      
      if($images_pre_page == '')
        $images_pre_page = 100;
    ?>
		    
		<p>
			<label><?php esc_html_e('Number of images to display:', 'maxgalleria-media-library'); ?></label>
			<input type="text" id="images-pre-page" name="images-pre-page" value="<?php echo esc_attr($images_pre_page) ?>" style="width: 50px" autocomplete=off>
		</p>
    
		<p>
			<input type="checkbox" name="disable_scaling" id="disable_scaling" value="" <?php esc_attr(checked($this->disable_scaling, 'on')) ?>>
			<label><?php esc_html_e('Disable large image scaling', 'maxgalleria-media-library'); ?></label>			
		</p>
		<p>
			<input type="checkbox" name="meta_index" id="meta_index" value="" <?php checked($meta_index, 'on') ?>>
			<label><?php  esc_html_e('Add an index to the postmeta table. <em>Recommend for sites with a high number of media files. Uncheck to remove the index.</em>', 'maxgalleria-media-library'); ?></label>			
		</p>
    <p>
			<input type="checkbox" name="skip_webp" id="skip_webp" value="" <?php checked($this->sync_skip_webp, 'on') ?>>
			<label><?php  esc_html_e('Skip WEBP images when syncing media library files. <em>For sites where WEBP files are automatically generated.</em>', 'maxgalleria-media-library'); ?></label>			      
    </p>
		<p>
      <a class="button-primary" id="mlfp-update-settings"><?php esc_html_e('Update Settings','maxgalleria-media-library'); ?></a>			
		</p>
        
		<div id="saving-message"></div>
    
		
<script>
	jQuery(document).ready(function(){
		    
    jQuery(document).on("click","#mlfp-update-settings",function(){
      
			var images_per_page = jQuery("#images-pre-page").val();
			var scaling_status = jQuery("#disable_scaling").is(":checked");
      var meta_index = jQuery("#meta_index").is(":checked");      
      var skip_webp = jQuery("#skip_webp").is(":checked");      
            
			jQuery("#saving-message").html('');
			
			jQuery.ajax({
				type: "POST",
				async: true,
				data: { action: "mlfp_set_scaling", scaling_status: scaling_status, images_per_page: images_per_page, meta_index: meta_index, skip_webp: skip_webp, nonce: mgmlp_ajax.nonce },
				url: mgmlp_ajax.ajaxurl,
				dataType: "html",
				success: function (data) { 
					jQuery("#saving-message").html(data);
          window.location.reload();                    
				},
				error: function (err){ 
					jQuery("#gi-ajax-loader").hide();
					alert(err.responseText)
				}
			});
    
		});
        	
	});  
</script>  		
		
		<?php 
	}
			
	public function regen_mlp_thumbnails() {
    
    global $wpdb, $is_IIS;
        
    if ( !wp_verify_nonce( $_POST['nonce'], MAXGALLERIA_MEDIA_LIBRARY_NONCE)) {
      exit( esc_html__('missing nonce! Please refresh this page.','maxgalleria-media-library'));
    } 
		    
    if ((isset($_POST['serial_image_ids'])) && (strlen(trim($_POST['serial_image_ids'])) > 0))
      $image_ids = trim(stripslashes(sanitize_text_field($_POST['serial_image_ids'])));
    else
      $image_ids = "";
    
    //error_log("image_ids $image_ids");
				        
    $image_ids = str_replace('"', '', $image_ids);    
    
    $image_ids = explode(',', $image_ids);
    
    //error_log(print_r($image_ids, true));
		
		$counter = 0;
		
		foreach( $image_ids as $image_id) {
			
			// check if the file is an image
			if(wp_attachment_is_image(intval($image_id))) {
        
        //error_log("is image");
			
				// get the image path
				$image_path = get_attached_file( $image_id );
        
        $scaled_position = strpos($image_path, '-scaled');
        
        if($scaled_position != false) {
          $temp_path = substr($image_path, 0, $scaled_position);
          $temp_path .= substr($image_path, $scaled_position+7);
          $image_path = $temp_path;
        }

				// get the name of the file
				$base_name = wp_basename( $image_path );

				// set the time limit o five minutes
				@set_time_limit( 300 ); 

				// regenerate the thumbnails
        if ($is_IIS || strtoupper(substr(PHP_OS, 0, 3)) == 'WIN' || strtoupper(substr(PHP_OS, 0, 13)) == 'MICROSOFT-IIS' ) {
          $this->remove_existing_thumbnails($image_id, addslashes($image_path));
				  $metadata = wp_generate_attachment_metadata( $image_id, addslashes($image_path));
        } else {
          $this->remove_existing_thumbnails($image_id, $image_path);
				  $metadata = wp_generate_attachment_metadata( $image_id, $image_path );
        }

				// check for errors
				if (is_wp_error($metadata)) {
					echo esc_html__('Error: ','maxgalleria-media-library') . "$base_name ". $metadata->get_error_message();
					continue;
				}	
				if(empty($metadata)) {
					printf( esc_html__('Unknown error with %s','maxgalleria-media-library'), $base_name);
					continue;
				}	

				// update the meta data
				wp_update_attachment_metadata( $image_id, $metadata );
				$counter++;

			} else {
        error_log("not an image");        
      }		
		}
				
    printf( esc_html__('Thumbnails have been regenerated for %d image(s)','maxgalleria-media-library'), $counter);		
		die();
	}
  
  public function remove_existing_thumbnails($image_id, $image_path) {
    
    global $is_IIS;
    
    $metadata = wp_get_attachment_metadata($image_id);
    
    if ($is_IIS || strtoupper(substr(PHP_OS, 0, 3)) == 'WIN' || strtoupper(substr(PHP_OS, 0, 13)) == 'MICROSOFT-IIS' )
      $seprator_position = strrpos($image_path, '\\');
    else
      $seprator_position = strrpos($image_path, '/');
    
    $image_path = substr($image_path, 0, $seprator_position);

    if(isset($metadata['sizes'])) {
      foreach($metadata['sizes'] as $source_path) {
        $thumbnail_file = $image_path . DIRECTORY_SEPARATOR . $source_path['file'];

        if ($is_IIS || strtoupper(substr(PHP_OS, 0, 3)) == 'WIN' || strtoupper(substr(PHP_OS, 0, 13)) == 'MICROSOFT-IIS' )
          $thumbnail_file = str_replace('/', '\\', $thumbnail_file);

        if(file_exists($thumbnail_file))
          unlink($thumbnail_file);        
      }  
    }    
  }
  
  public function regenerate_interface() {
		global $wpdb;
    
    $allowed_html = array(
      'a' => array(
        'href' => array(),
        'id' => array()
      )    
    );                      

		?>

      <div id="message" class="updated fade" style="display:none"></div>

      <div id="wp-media-grid" class="wrap">                
        <!--empty h1 for where WP notices will appear--> 
				<h1></h1>
        <div class="media-plus-toolbar"><div class="media-toolbar-secondary">  
            
				<div id="mgmlp-header">		
					<div id='mgmlp-title-area'>
						<h2 class='mgmlp-title'><?php esc_html_e('Regenerate Thumbnails', 'maxgalleria-media-library' ); ?></h2>  

					</div> <!-- mgmlp-title-area -->
					<div id="new-top-promo">
						<a id="mf-top-logo" target="_blank" href="http://maxfoundry.com"><img alt="maxfoundry logo" src="<?php echo esc_url(MAXGALLERIA_MEDIA_LIBRARY_PLUGIN_URL . "/images/mf-logo.png") ?>" width="140" height="25" ></a>
						<p class="center-text"><?php esc_html_e('Makers of', 'maxgalleria-media-library' ); ?> <a target="_blank"  href="http://maxbuttons.com/">MaxButtons</a>, <a target="_blank" href="http://maxbuttons.com/product-category/button-packs/">WordPress Buttons</a> <?php esc_html_e('and', 'maxgalleria-media-library' ); ?> <a target="_blank" href="http://maxgalleria.com/">MaxGalleria</a></p>
				    <p class="center-text-no-ital"><?php esc_html_e('Click here to', 'maxgalleria-media-library' ); ?> <a href="<?php echo esc_url(MLF_TS_URL) ?>" target="_blank"><?php esc_html_e('Fix Common Problems', 'maxgalleria-media-library'); ?></a></p>
						<p class="center-text-no-ital"><?php esc_html_e('Need help? Click here for', 'maxgalleria-media-library' ); ?> <a href="https://wordpress.org/support/plugin/media-library-plus" target="_blank"><?php esc_html_e('Awesome Support!', 'maxgalleria-media-library' ); ?></a></p>
						<p class="center-text-no-ital"><?php esc_html_e('Or Email Us at', 'maxgalleria-media-library' ); ?> <a href="mailto:support@maxfoundry.com">support@maxfoundry.com</a></p>
					</div>
					
				</div><!--mgmlp-header-->
        <div class="mlf-clearfix"></div>  


<?php

		// If the button was clicked
		if ( ! empty( $_POST['regenerate-thumbnails'] ) || ! empty( $_REQUEST['ids'] ) ) {
			// Capability check
			if ( ! current_user_can( $this->capability ) )
				wp_die( esc_html__( 'Cheatin&#8217; uh?' ) );
      
      if($this->bdp_autoprotect == 'on') {
        $this->bdp_autoprotect = 'off';
        update_option(MLFP_BDA_AUTO_PROTECT, 'off');                  
        update_option(MLFP_BDA_AUTO_PROTECT_DISABLED, 'on');                  
      }
      
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

	<p><input type="submit" class="button hide-if-no-js" name="regenerate-thumbnails" id="regenerate-thumbnails" value="<?php esc_html_e( 'Regenerate All Thumbnails', 'maxgalleria-media-library' ) ?>" /></p>

	<noscript><p><em><?php esc_html_e( 'You must enable Javascript in order to proceed!', 'maxgalleria-media-library' ) ?></em></p></noscript>

	</form>
<?php
		} // End if button
?>
			</div>
		</div>
	</div>

<?php
	}

	// Process a single image ID (this is an AJAX handler)
	public function ajax_process_image() {
    
    global $is_IIS;
    
		@error_reporting( 0 ); // Don't break the JSON result

		header( 'Content-type: application/json' );

		$id = (int) $_REQUEST['id'];
		$image = get_post( $id );

		if ( ! $image || 'attachment' != $image->post_type || 'image/' != substr( $image->post_mime_type, 0, 6 ) )
			die( json_encode( array( 'error' => sprintf( esc_html__( 'Failed resize: %s is an invalid image ID.', 'maxgalleria-media-library' ), esc_html( $_REQUEST['id'] ) ) ) ) );

		if ( ! current_user_can( $this->capability ) )
			$this->die_json_error_msg( $image->ID, esc_html__( "Your user account doesn't have permission to resize images", 'maxgalleria-media-library' ) );

		$fullsizepath = get_attached_file( $image->ID );
    
    $scaled_position = strpos($fullsizepath, '-scaled');

    if($scaled_position != false) {
      $temp_path = substr($fullsizepath, 0, $scaled_position);
      $temp_path .= substr($fullsizepath, $scaled_position+7);
      //error_log("temp_path $temp_path");
      $fullsizepath = $temp_path;
    }
    
		if ( false === $fullsizepath || ! file_exists( $fullsizepath ) )
			$this->die_json_error_msg( $image->ID, sprintf( esc_html__( 'The originally uploaded image file cannot be found at %s', 'maxgalleria-media-library' ), '<code>' . esc_html( $fullsizepath ) . '</code>' ) );

		@set_time_limit( 900 ); // 5 minutes per image should be PLENTY

    if ($is_IIS || strtoupper(substr(PHP_OS, 0, 3)) == 'WIN' || strtoupper(substr(PHP_OS, 0, 13)) == 'MICROSOFT-IIS' ) {
      $this->remove_existing_thumbnails($image->ID, addslashes($fullsizepath));
		  $metadata = wp_generate_attachment_metadata( $image->ID, addslashes($fullsizepath));
    } else {
      $this->remove_existing_thumbnails($image->ID, $fullsizepath);
		  $metadata = wp_generate_attachment_metadata( $image->ID, $fullsizepath );
    }  

		if ( is_wp_error( $metadata ) )
			$this->die_json_error_msg( $image->ID, $metadata->get_error_message() );
		if ( empty( $metadata ) )
			$this->die_json_error_msg( $image->ID, esc_html__( 'Unknown failure reason.', 'maxgalleria-media-library' ) );

		// If this fails, then it just means that nothing was changed (old value == new value)
		wp_update_attachment_metadata( $image->ID, $metadata );

		die( json_encode( array( 'success' => sprintf( esc_html__( '&quot;%1$s&quot; (ID %2$s) was successfully resized in %3$s seconds.', 'maxgalleria-media-library' ), esc_html( get_the_title( $image->ID ) ), $image->ID, timer_stop() ) ) ) );
	}

	// Helper to make a JSON error message
	public function die_json_error_msg( $id, $message ) {
		die( json_encode( array( 'error' => sprintf( esc_html__( '&quot;%1$s&quot; (ID %2$s) failed to resize. The error message was: %3$s', 'maxgalleria-media-library' ), esc_html( get_the_title( $id ) ), $id, $message ) ) ) );
	}

	// Helper function to escape quotes in strings for use in Javascript
	public function esc_quotes( $string ) {
		return str_replace( '"', '\"', $string );
	}
  
  public function mflp_enable_auto_protect() {
    
    if ( !wp_verify_nonce( $_POST['nonce'], MAXGALLERIA_MEDIA_LIBRARY_PRO_NONCE)) {
      exit(__('missing nonce!','maxgalleria-media-library'));
    }
    
    if(get_option(MLFP_BDA_AUTO_PROTECT_DISABLED, 'off') == 'on') {
      $this->bdp_autoprotect = 'on';
      update_option(MLFP_BDA_AUTO_PROTECT, 'on');                  
      update_option(MLFP_BDA_AUTO_PROTECT_DISABLED, 'off');                  
    }
    
    die();
  }  
		
	public function mlp_image_seo_change() {
    		
    if ( !wp_verify_nonce( $_POST['nonce'], MAXGALLERIA_MEDIA_LIBRARY_NONCE)) {
      exit(esc_html__('missing nonce! Please refresh this page.','maxgalleria-media-library'));
    } 
		    
    if ((isset($_POST['checked'])) && (strlen(trim($_POST['checked'])) > 0))
      $checked = trim(sanitize_text_field($_POST['checked']));
    else
      $checked = "off";
		
    if ((isset($_POST['default_alt'])) && (strlen(trim($_POST['default_alt'])) > 0))
      $default_alt = trim(sanitize_text_field($_POST['default_alt']));
    else
      $default_alt = "";
		
    if ((isset($_POST['default_title'])) && (strlen(trim($_POST['default_title'])) > 0))
      $default_title = trim(sanitize_text_field($_POST['default_title']));
    else
      $default_title = "";
    
    //error_log("default_title $default_title");
		
    update_option(MAXGALLERIA_MEDIA_LIBRARY_IMAGE_SEO, $checked );		
		
    update_option(MAXGALLERIA_MEDIA_LIBRARY_ATL_DEFAULT, $default_alt );		
		
    update_option(MAXGALLERIA_MEDIA_LIBRARY_TITLE_DEFAULT, $default_title );		
		
		echo esc_html__('The Image SEO settings have been updated ','maxgalleria-media-library');
				
		die();
		
		
	}
	
	public function locaton_without_basedir($image_location, $uploads_dir, $upload_length) {
		
		$position = strpos($image_location, $uploads_dir);
		return substr($image_location, $position+$upload_length );
		
	}
				
	public function get_browser() {
		// http://www.php.net/manual/en/function.get-browser.php#101125.
		// Cleaned up a bit, but overall it's the same.

		$user_agent = $_SERVER['HTTP_USER_AGENT'];
		$browser_name = 'Unknown';
		$platform = 'Unknown';
		$version= "";

		// First get the platform
		if (preg_match('/linux/i', $user_agent)) {
			$platform = 'Linux';
		}
		elseif (preg_match('/macintosh|mac os x/i', $user_agent)) {
			$platform = 'Mac';
		}
		elseif (preg_match('/windows|win32/i', $user_agent)) {
			$platform = 'Windows';
		}
		
		// Next get the name of the user agent yes seperately and for good reason
		if (preg_match('/MSIE/i', $user_agent) && !preg_match('/Opera/i', $user_agent)) {
			$browser_name = 'Internet Explorer';
			$browser_name_short = "MSIE";
		}
		elseif (preg_match('/Firefox/i', $user_agent)) {
			$browser_name = 'Mozilla Firefox';
			$browser_name_short = "Firefox";
		}
		elseif (preg_match('/Chrome/i', $user_agent)) {
			$browser_name = 'Google Chrome';
			$browser_name_short = "Chrome";
		}
		elseif (preg_match('/Safari/i', $user_agent)) {
			$browser_name = 'Apple Safari';
			$browser_name_short = "Safari";
		}
		elseif (preg_match('/Opera/i', $user_agent)) {
			$browser_name = 'Opera';
			$browser_name_short = "Opera";
		}
		elseif (preg_match('/Netscape/i', $user_agent)) {
			$browser_name = 'Netscape';
			$browser_name_short = "Netscape";
		}
		
		// Finally get the correct version number
		$known = array('Version', $browser_name_short, 'other');
		$pattern = '#(?<browser>' . join('|', $known) . ')[/ ]+(?<version>[0-9.|a-zA-Z.]*)#';
		if (!preg_match_all($pattern, $user_agent, $matches)) {
			// We have no matching number just continue
		}
		
		// See how many we have
		$i = count($matches['browser']);
		if ($i != 1) {
			// We will have two since we are not using 'other' argument yet
			// See if version is before or after the name
			if (strripos($user_agent, "Version") < strripos($user_agent, $browser_name_short)){
				$version= $matches['version'][0];
			}
			else {
				$version= $matches['version'][1];
			}
		}
		else {
			$version= $matches['version'][0];
		}
		
		// Check if we have a number
		if ($version == null || $version == "") { $version = "?"; }
		
		return array(
			'user_agent' => $user_agent,
			'name' => $browser_name,
			'version' => $version,
			'platform' => $platform,
			'pattern' => $pattern
		);
	}
	
	public function mlp_support() {
	  require_once MAXGALLERIA_MEDIA_LIBRARY_PLUGIN_DIR . '/includes/mlf_support.php';	 		
	}
	
	public  function mlp_remove_slashes() {

		global $wpdb;
			
    $sql = "select ID, pm.meta_value, pm.meta_id
from {$wpdb->prefix}posts 
LEFT JOIN {$wpdb->prefix}postmeta AS pm ON pm.post_id = {$wpdb->prefix}posts.ID
where post_type = 'attachment' 
or post_type = '" . MAXGALLERIA_MEDIA_LIBRARY_POST_TYPE . "'
and pm.meta_key = '_wp_attached_file'
group by ID
order by meta_id";


		//error_log($sql);

		$rows = $wpdb->get_results($sql);

		if($rows) {
			foreach($rows as $row) {
				if($row->meta_value !== '') {
					if( $row->meta_value[0] == "/") {
						$new_meta = $row->meta_value;
						$new_meta = ltrim($new_meta, '/');
						update_post_meta($row->ID, '_wp_attached_file', $new_meta);							
					}	
				}
			}
		}	
	}
	
	public function hide_maxgalleria_media() {
    
    //error_log("hide_maxgalleria_media");
		
    global $wpdb;
    
    if ( !wp_verify_nonce( $_POST['nonce'], MAXGALLERIA_MEDIA_LIBRARY_NONCE)) {
      exit( esc_html__('missing nonce! Please refresh this page.','maxgalleria-media-library'));
    }  
    
		if ((isset($_POST['folder_id'])) && (strlen(trim($_POST['folder_id'])) > 0))
      $folder_id = trim(sanitize_text_field($_POST['folder_id']));
    else
      $folder_id = "";

    // prevent hiding of the uploads folder and sub folders  
    if(intval($folder_id) == intval($this->uploads_folder_ID)) {
      echo esc_html__('The uploads folder cannot be hidden.','maxgalleria-media-library');
      die();
    }
			
		if($folder_id !== '') {
			
			$folder_table = $wpdb->prefix . MAXGALLERIA_MEDIA_LIBRARY_FOLDER_TABLE;			
			$parent_folder =  $this->get_parent($folder_id);
			
		  $sql = "select meta_value as attached_file
from {$wpdb->prefix}postmeta
where post_id = $folder_id
and meta_key = '_wp_attached_file';";
	
			$row = $wpdb->get_row($sql);
			if($row) {
				
				$basedir = $this->upload_dir['basedir'];
				$basedir = rtrim($basedir, '/') . '/';
				$skip_folder_file = $basedir . ltrim($row->attached_file, '/') . DIRECTORY_SEPARATOR . "mlpp-hidden";
				file_put_contents($skip_folder_file, '');
				
				$this->remove_children($folder_id);
				$del_post = array('post_id' => $folder_id);                        
				$this->mlf_delete_post($folder_id, false); //delete the post record
				$wpdb->delete( $folder_table, $del_post ); // delete the folder table record
								
			}
			
			echo esc_html__('The selected folder, subfolders and thier files have been hidden.','maxgalleria-media-library');
			echo "<script>window.location.href = '" . esc_url_raw(site_url() . '/wp-admin/admin.php?page=mlf-folders8&media-folder=' . $parent_folder) . "'</script>";
					
		}	
		
		die();
	}
		
	private function remove_children($folder_id) {
		
    global $wpdb;
		
		if($folder_id !== 0) {
			
			$folder_table = $wpdb->prefix . MAXGALLERIA_MEDIA_LIBRARY_FOLDER_TABLE;
							
		  $sql = "select post_id
from $folder_table 
where folder_id = $folder_id";
		
			$rows = $wpdb->get_results($sql);
			if($rows) {
				foreach($rows as $row) {

					$this->remove_children($row->post_id);
				  $del_post = array('post_id' => $row->post_id);                        
					$this->mlf_delete_post($row->post_id, false); //delete the post record
					$wpdb->delete( $folder_table, $del_post ); // delete the folder table record
								
				}
			}	
		}	
	}

	// modifed version of wp_delete_post
	private function mlf_delete_post( $postid = 0, $force_delete = false ) {
		global $wpdb;

		if ( !$post = $wpdb->get_row($wpdb->prepare("SELECT * FROM $wpdb->posts WHERE ID = %d", $postid)) )
			return $post;
    
		if ( !$force_delete && ( $post->post_type == 'post' || $post->post_type == 'page') && get_post_status( $postid ) != 'trash' && EMPTY_TRASH_DAYS )
			return wp_trash_post( $postid );

		delete_post_meta($postid,'_wp_trash_meta_status');
		delete_post_meta($postid,'_wp_trash_meta_time');

		wp_delete_object_term_relationships($postid, get_object_taxonomies($post->post_type));

		$parent_data = array( 'post_parent' => $post->post_parent );
		$parent_where = array( 'post_parent' => $postid );

		if ( is_post_type_hierarchical( $post->post_type ) ) {
			// Point children of this page to its parent, also clean the cache of affected children.
			$children_query = $wpdb->prepare( "SELECT * FROM $wpdb->posts WHERE post_parent = %d AND post_type = %s", $postid, $post->post_type );
			$children = $wpdb->get_results( $children_query );
			if ( $children ) {
				$wpdb->update( $wpdb->posts, $parent_data, $parent_where + array( 'post_type' => $post->post_type ) );
			}
		}

		// Do raw query. wp_get_post_revisions() is filtered.
		$revision_ids = $wpdb->get_col( $wpdb->prepare( "SELECT ID FROM $wpdb->posts WHERE post_parent = %d AND post_type = 'revision'", $postid ) );
		// Use wp_delete_post (via wp_delete_post_revision) again. Ensures any meta/misplaced data gets cleaned up.
		foreach ( $revision_ids as $revision_id )
			wp_delete_post_revision( $revision_id );

		// Point all attachments to this post up one level.
		$wpdb->update( $wpdb->posts, $parent_data, $parent_where + array( 'post_type' => 'attachment' ) );

		wp_defer_comment_counting( true );

		$comment_ids = $wpdb->get_col( $wpdb->prepare( "SELECT comment_ID FROM $wpdb->comments WHERE comment_post_ID = %d", $postid ));
		foreach ( $comment_ids as $comment_id ) {
			wp_delete_comment( $comment_id, true );
		}

		wp_defer_comment_counting( false );

		$post_meta_ids = $wpdb->get_col( $wpdb->prepare( "SELECT meta_id FROM $wpdb->postmeta WHERE post_id = %d ", $postid ));
		foreach ( $post_meta_ids as $mid )
			delete_metadata_by_mid( 'post', $mid );

		$result = $wpdb->delete( $wpdb->posts, array( 'ID' => $postid ) );
		if ( ! $result ) {
			return false;
		}

		if ( is_post_type_hierarchical( $post->post_type ) && $children ) {
			foreach ( $children as $child )
				clean_post_cache( $child );
		}

		wp_clear_scheduled_hook('publish_future_post', array( $postid ) );

		return $post;
	}
	
	public function mlf_hide_info() {
				
    if ( !wp_verify_nonce( $_POST['nonce'], MAXGALLERIA_MEDIA_LIBRARY_NONCE)) {
      exit(esc_html__('missing nonce! Please refresh this page.','maxgalleria-media-library'));
    } 
		
    $current_user_id = get_current_user_id(); 
            
    update_user_meta( $current_user_id, MAXGALLERIA_MLP_DISPLAY_INFO, 'off' );
				
	}
	  
	public function mlfp_set_scaling() {
    
    if ( !wp_verify_nonce( $_POST['nonce'], MAXGALLERIA_MEDIA_LIBRARY_NONCE)) {
      exit(esc_html__('missing nonce! Please refresh this page.','maxgalleria-media-library'));
    } 

		if ((isset($_POST['scaling_status'])) && (strlen(trim($_POST['scaling_status'])) > 0))
      $scaling_status = trim(sanitize_text_field($_POST['scaling_status']));
    else
      $scaling_status = "";
        
		if ((isset($_POST['images_per_page'])) && (strlen(trim($_POST['images_per_page'])) > 0))
      $images_per_page = trim(sanitize_text_field($_POST['images_per_page']));
    else
      $images_per_page = "";
    
		if ((isset($_POST['meta_index'])) && (strlen(trim($_POST['meta_index'])) > 0))
      $meta_index = trim(sanitize_text_field($_POST['meta_index']));
    else
      $meta_index = "";    

		if ((isset($_POST['skip_webp'])) && (strlen(trim($_POST['skip_webp'])) > 0))
      $skip_webp = trim(sanitize_text_field($_POST['skip_webp']));
    else
      $skip_webp = "";    
    
    $this->mlf_option_true_update(MAXGALLERIA_DISABLE_SCALLING, $scaling_status);        
          
		update_option(MAXGALLERIA_MLP_ITEMS_PRE_PAGE, $images_per_page, true);
    
    if($meta_index == 'true') {
      if(get_option(MAXGALLERIA_POSTMETA_INDEX) == 'off') {
        $this->add_postmeta_index();
        update_option(MAXGALLERIA_POSTMETA_INDEX, 'on', true);
      }  
    } else {
      if(get_option(MAXGALLERIA_POSTMETA_INDEX) == 'on') {
        $this->remove_postmeta_index();
        update_option(MAXGALLERIA_POSTMETA_INDEX, 'off', true);
      }
    }
                
    $this->mlf_option_true_update(MLFP_SKIP_WEBP_FILES, $skip_webp);    
      
    echo esc_html__('The settings were updated.','maxgalleria-media-library');
		die();
	}
  
  public function mlf_option_true_update($option_id, $option_value) {
    if($option_value == 'true') {
      update_option($option_id, 'on', true);
    } else {
      update_option($option_id, 'off', true);
    }    
  }
  
  public function add_postmeta_index() {
    
    global $wpdb;
    
    $sql = "ALTER TABLE $wpdb->postmeta ADD INDEX mg_meta_value (meta_key ASC, meta_value(255) ASC);";
    
    //error_log($sql);
    
    $wpdb->get_results($sql);
    
  }
  
  public function remove_postmeta_index() {
    
    global $wpdb;    
    
    $sql = "DROP INDEX mg_meta_value ON $wpdb->postmeta";
    
    //error_log($sql);    
    
    $wpdb->get_results($sql);    
    
  }
      
  public function max_discover_files($parent_folder) {
    
    //error_log("max_discover_files parent_folder $parent_folder");
    
    global $wpdb, $is_IIS;
    $user_id = get_current_user_id();
    $files_to_add = array();
    $files_count = 0;
            
		$folder_table = $wpdb->prefix . MAXGALLERIA_MEDIA_LIBRARY_FOLDER_TABLE;    
      
    $sql = "select ID, pm.meta_value as attached_file, post_title, $folder_table.folder_id 
from $wpdb->prefix" . "posts 
LEFT JOIN $folder_table ON($wpdb->prefix" . "posts.ID = $folder_table.post_id)
LEFT JOIN {$wpdb->prefix}postmeta AS pm ON (pm.post_id = {$wpdb->prefix}posts.ID) 
where post_type = 'attachment' 
and folder_id = '$parent_folder' 
and pm.meta_key = '_wp_attached_file'	
order by post_title";
    
    $attachments = $wpdb->get_results($sql);
		
    $sql = "select meta_value as attached_file
from {$wpdb->prefix}postmeta
where post_id = $parent_folder    
and meta_key = '_wp_attached_file'";	

    $current_row = $wpdb->get_row($sql);
		
		$baseurl = $this->upload_dir['baseurl'];
		$baseurl = rtrim($baseurl, '/') . '/';
		$image_location = $baseurl . ltrim($current_row->attached_file, '/');
    
    //error_log("image location 1" . $image_location);
    
    //str_replace('localhost', 'www.localhost', $image_location);
    
    //error_log("image location 2" . $image_location);
		
    $folder_path = $this->get_absolute_path($image_location);
        
    //error_log($folder_path);
    
    update_user_meta($user_id, MAXG_SYNC_FOLDER_PATH_ID, $parent_folder);
    
    if ($is_IIS || strtoupper(substr(PHP_OS, 0, 3)) == 'WIN' || strtoupper(substr(PHP_OS, 0, 13)) == 'MICROSOFT-IIS' )
      update_user_meta($user_id, MAXG_SYNC_FOLDER_PATH, str_replace('\\', '\\\\', $folder_path));
    else
      update_user_meta($user_id, MAXG_SYNC_FOLDER_PATH, $folder_path);
    
    $folder_contents = array_diff(scandir($folder_path), array('..', '.'));
						
    foreach ($folder_contents as $file_path) {
      			
			if($file_path !== '.DS_Store' && $file_path !== '.htaccess') {
				$new_attachment = $folder_path . DIRECTORY_SEPARATOR . $file_path;                
        if(!$this->is_webp($new_attachment) || ($this->is_webp($new_attachment) && $this->sync_skip_webp == 'off')) {  
          if(!strpos($new_attachment, '-uai-')) {  // skip thumbnails created by the Uncode theme
            if(!strpos($new_attachment, '-scaled.')) {  // skip scaled images
              if(!strpos($new_attachment, '-pdf.jpg')) {  // skip pdf thumbnails
                if(!is_dir($new_attachment)) {
                  if($this->is_base_file($file_path, $folder_contents)) {				
                    if(!$this->search_folder_attachments($file_path, $attachments)) {

                      $old_attachment_name = $new_attachment;
                      $new_attachment = pathinfo($new_attachment, PATHINFO_DIRNAME) . DIRECTORY_SEPARATOR . sanitize_file_name(pathinfo($new_attachment, PATHINFO_FILENAME) . "." . strtolower(pathinfo($new_attachment, PATHINFO_EXTENSION)));

                      if(rename($old_attachment_name, $new_attachment)) {	
                        $files_to_add[] = basename($new_attachment);
                        $files_count++;
                      } else {
                        $files_to_add[] = basename($old_attachment_name);
                        $files_count++;
                      }
                    }	
                  }
                } 
              }
            }
          }
        }
			}		
		}
    
    if(is_array($files_to_add)) {
      update_user_meta($user_id, MAXG_SYNC_FILES, $files_to_add);
    }
    if($files_count > 0)
      return '3'; // add the files
    else
      return '2'; // check next folder
   		
  }
  
  public function is_webp($new_attachment ) {
    if(strpos($new_attachment, '.webp') !== false)
      return true;
    else
      return false;
  }
  
  public function mlfp_run_sync_process() {
    
    global $wpdb;
		$user_id = get_current_user_id();
    $message = "";
    $folders_array = array();
    
    if ( !wp_verify_nonce( $_POST['nonce'], MAXGALLERIA_MEDIA_LIBRARY_NONCE)) {
      exit(esc_html__('missing nonce! Please refresh this page.','maxgalleria-media-library'));
    }
        
		if ((isset($_POST['phase'])) && (strlen(trim($_POST['phase'])) > 0))
      $phase = trim(sanitize_text_field($_POST['phase']));
    else
      $phase = "";
    
		if ((isset($_POST['parent_folder'])) && (strlen(trim($_POST['parent_folder'])) > 0))
      $parent_folder = trim(sanitize_text_field($_POST['parent_folder']));
    else
      $parent_folder = "";

		if ((isset($_POST['mlp_title_text'])) && (strlen(trim($_POST['mlp_title_text'])) > 0))
      $mlp_title_text = trim(sanitize_text_field($_POST['mlp_title_text']));
    else
      $mlp_title_text = "";

		if ((isset($_POST['mlp_alt_text'])) && (strlen(trim($_POST['mlp_alt_text'])) > 0))
      $mlp_alt_text = trim(sanitize_text_field($_POST['mlp_alt_text']));
    else
      $mlp_alt_text = "";
    
    $next_phase = '1';
    
    switch($phase) {
      // find folders
      case '1':
        $next_phase = '2';
        $this->max_sync_contents($parent_folder);
        break;
      
      // for each folder. get the folder ids
      case '2':
        
		    $folders_array = get_user_meta($user_id, MAXG_SYNC_FOLDERS, true);
                
        if(is_array($folders_array)) {
          $next_folder = array_pop($folders_array);				
        } else {
          $next_folder = $folders_array;
        }  
        
        if($next_folder != "") {
          $message = esc_html__("Scanning for new files and folders...please wait.",'maxgalleria-media-library');        
          $this->max_discover_files($next_folder);
          update_user_meta($user_id, MAXG_SYNC_FOLDERS, $folders_array);
          $next_phase = '3';          
        } else {
          $message = esc_html__("Syncing finished.",'maxgalleria-media-library');        
          delete_user_meta($user_id, MAXG_SYNC_FOLDERS);
          delete_user_meta($user_id, MAXG_SYNC_FILES);          
          delete_user_meta($user_id, MAXG_SYNC_FOLDER_PATH_ID);          
          delete_user_meta($user_id, MAXG_SYNC_FOLDER_PATH);          
          $next_phase = null;          
        }                
        break;
                      
      // add each file
      case '3':
        $files_to_add = get_user_meta($user_id, MAXG_SYNC_FILES, true);        
        
        if(is_array($files_to_add)) {
          $next_file = array_pop($files_to_add);
        } else {
          $next_file = $files_to_add;
        }
        
        if($next_file != "") {

          $next_phase = '3';          
          
          $wp_filetype = wp_check_filetype_and_ext($next_file, $next_file );

          if ($wp_filetype['ext'] !== false) {      
            $message = esc_html__("Adding ",'maxgalleria-media-library') . $next_file;
            $this->mlfp_process_sync_file($next_file, $mlp_title_text, $mlp_alt_text);
          } else {
            $message = $next_file . esc_html__(" is not an allowed file type. It was not added.",'maxgalleria-media-library');            
          }
          update_user_meta($user_id, MAXG_SYNC_FILES, $files_to_add);            

        } else {
          $next_phase = '2';          
          delete_user_meta($user_id, MAXG_SYNC_FILES);          
        }        
        break;
    }  
    $phase = $next_phase;
    
	  $data = array('phase' => $phase, 'message' => esc_html($message));								
		echo json_encode($data);						
    die();
  }
  
  public function mlfp_process_sync_file($next_file, $mlp_title_text, $mlp_alt_text) {
    
    global $wpdb;
		$user_id = get_current_user_id();
      
		if($next_file != "") {
  
      $parent_folder = get_user_meta($user_id, MAXG_SYNC_FOLDER_PATH_ID, true);

      $folder_path = get_user_meta($user_id, MAXG_SYNC_FOLDER_PATH, true);

      $new_attachment = $folder_path . DIRECTORY_SEPARATOR . $next_file;
      
			$new_file_title = preg_replace( '/\.[^.]+$/', '', $next_file);	      

      $attach_id = $this->add_new_attachment($new_attachment, $parent_folder, $new_file_title, $mlp_alt_text, $mlp_title_text);
      
    }       
  }
  
  public function mlfp_save_mc_data($serial_copy_ids, $folder_id, $user_id) {
    
    global $is_IIS; 
                
  	update_user_meta($user_id, MAXG_MC_FILES, $serial_copy_ids);
    
    $destination_folder = $this->get_folder_path($folder_id);
        
    if ($is_IIS || strtoupper(substr(PHP_OS, 0, 3)) == 'WIN' || strtoupper(substr(PHP_OS, 0, 13)) == 'MICROSOFT-IIS' )
      update_user_meta($user_id, MAXG_MC_DESTINATION_FOLDER, str_replace('\\', '\\\\', $destination_folder));
    else
      update_user_meta($user_id, MAXG_MC_DESTINATION_FOLDER, $destination_folder);
    
  }
  
  public function mlfp_process_mc_data() {
    
		$user_id = get_current_user_id();
    $message = "";
    $next_phase = '2';
    
    if ( !wp_verify_nonce( $_POST['nonce'], MAXGALLERIA_MEDIA_LIBRARY_NONCE)) {
      exit(esc_html__('missing nonce! Please refresh this page.','maxgalleria-media-library'));
    }
    
		if ((isset($_POST['phase'])) && (strlen(trim($_POST['phase'])) > 0))
      $phase = trim(sanitize_textarea_field($_POST['phase']));
    else
      $phase = "";
    
    if ((isset($_POST['folder_id'])) && (strlen(trim($_POST['folder_id'])) > 0))
      $folder_id = trim(sanitize_textarea_field($_POST['folder_id']));
    else
      $folder_id = "";
    
    if ((isset($_POST['current_folder'])) && (strlen(trim($_POST['current_folder'])) > 0))
      $current_folder = trim(sanitize_textarea_field($_POST['current_folder']));
    else
      $current_folder = "";
    
    if ((isset($_POST['action_name'])) && (strlen(trim($_POST['action_name'])) > 0))
      $action_name = trim(sanitize_textarea_field($_POST['action_name']));
    else
      $action_name = "";    
    
    if ((isset($_POST['serial_copy_ids'])) && (strlen(trim($_POST['serial_copy_ids'])) > 0))
      $serial_copy_ids = trim(sanitize_textarea_field($_POST['serial_copy_ids']));
    else
      $serial_copy_ids = "";
		
          
    switch($phase) {
      
      case '1':
        
        $serial_copy_ids = str_replace('"', '', $serial_copy_ids);    

        $serial_copy_ids = explode(',', $serial_copy_ids);
    
        $this->mlfp_save_mc_data($serial_copy_ids, $folder_id, $user_id);
        
        $next_phase = '2';
        
        break;
      
      case '2':
        
        $files_to_move = get_user_meta($user_id, MAXG_MC_FILES, true);        

        if(is_array($files_to_move)) {
          $next_id = array_pop($files_to_move);
        } else {
          $next_id = $files_to_move;
          $files_to_move = "";
        }

        if($next_id != "") {
          if($action_name == 'copy_media') {
            $message = $this->move_copy_file(true, $next_id, $folder_id, $current_folder, $user_id);
          } else {
            $message = $this->move_copy_file(false, $next_id, $folder_id, $current_folder, $user_id);
          }  
          update_user_meta($user_id, MAXG_MC_FILES, $files_to_move);                     
        } else {
          $next_phase = null;
          delete_user_meta($user_id, MAXG_MC_FILES);          
          if($action_name == 'copy_media')          		
            $message = esc_html__("Finished copying files. ",'maxgalleria-media-library');
          else
            $message = esc_html__("Finished moving files. ",'maxgalleria-media-library');
        }  
        break;
    }
    $phase = $next_phase;
       
	  $data = array('phase' => $phase, 'message' => esc_html($message));								
    
		echo json_encode($data);						
    
    die();
  }
  
  public function move_copy_file($copy, $copy_id, $folder_id, $current_folder, $user_id) {
    
    global $wpdb, $is_IIS;
		$message = "";
		$files = "";
		$refresh = false;
    $scaled = false;
    
    $destination = get_user_meta($user_id, MAXG_MC_DESTINATION_FOLDER, true);
    
    $sql = "select meta_value as attached_file
from {$wpdb->prefix}postmeta 
where post_id = $copy_id    
AND meta_key = '_wp_attached_file'";

    $row = $wpdb->get_row($sql);

    $baseurl = $this->upload_dir['baseurl'];
    $baseurl = rtrim($baseurl, '/') . '/';
    $image_location = $baseurl . ltrim($row->attached_file, '/');
    
    if(strpos($image_location, '-scaled.' ) !== false) {
      $scaled = true;
    }  

    $image_path = $this->get_absolute_path($image_location);

    $destination_path = $this->get_absolute_path($destination);

    $folder_basename = basename($destination_path);
    
    $basename = pathinfo($image_path, PATHINFO_BASENAME);

    $destination_name = $destination_path . DIRECTORY_SEPARATOR . $basename;
    
    $copy_status = true;

    if(file_exists($image_path)) {
      if(!is_dir($image_path)) {
        if(file_exists($destination_path)) {
          if(is_dir($destination_path)) {

            if($copy) {

              if($scaled) {
                $full_scaled_image_path = str_replace('-scaled*', '', $image_path);
                if(file_exists($full_scaled_image_path)) {
                  $image_path = $full_scaled_image_path;
                  $full_scaled_image = substr($full_scaled_image_path, strrpos($full_scaled_image_path, '/')+1);
                  $destination_name = $destination_path . DIRECTORY_SEPARATOR . $full_scaled_image;                  
                }
              }

              if(copy($image_path, $destination_name )) {                                          

                $destination_url = $this->get_file_url($destination_name);
                $title_text = get_the_title($copy_id);
                $alt_text = get_post_meta($copy_id, '_wp_attachment_image_alt');										
                $attach_id = $this->add_new_attachment($destination_name, $folder_id, $title_text, $alt_text);
                if($attach_id === false){
                  $copy_status = false; 
                }  
              }
              else {
                echo esc_html__('Unable to copy the file; please check the folder and file permissions.','maxgalleria-media-library') . PHP_EOL;
                $copy_status = false; 
              }
              //move
            } else {
              if(rename($image_path, $destination_name )) {

                // check current theme customizer settings for the file
                // and update if found
                $update_theme_mods = false;
                $move_image_url = $this->get_file_url_for_copy($image_path);
                $move_destination_url = $this->get_file_url_for_copy($destination_name);
                $key = array_search ($move_image_url, $this->theme_mods, true);
                if($key !== false ) {
                  set_theme_mod( $key, $move_destination_url);
                  $update_theme_mods = true;                      
                }
                if($update_theme_mods) {
                  $theme_mods = get_theme_mods();
                  $this->theme_mods = json_decode(json_encode($theme_mods), true);
                  $update_theme_mods = false;
                }

                $image_path = str_replace('.', '*.', $image_path );
                //error_log("image_path $image_path");
                $metadata = wp_get_attachment_metadata($copy_id);                               
                $path_to_thumbnails = pathinfo($image_path, PATHINFO_DIRNAME);
                //error_log("path_to_thumbnails $path_to_thumbnails");
                
                if($scaled) {
                  $full_scaled_image_path = str_replace('-scaled*', '', $image_path);
                  $full_scaled_image = substr($full_scaled_image_path, strrpos($full_scaled_image_path, '/')+1);
                  $scaled_image_destination = $destination_path . DIRECTORY_SEPARATOR . $full_scaled_image;
                  if(file_exists($full_scaled_image_path))
                    rename($full_scaled_image_path, $scaled_image_destination);  
                }
                
                if(isset($metadata['sizes'])) {
                  
                  foreach($metadata['sizes'] as $source_path) {
                    $thumbnail_file = $path_to_thumbnails . DIRECTORY_SEPARATOR . $source_path['file'];
                    $thumbnail_destination = $destination_path . DIRECTORY_SEPARATOR . $source_path['file'];
		                if(file_exists($thumbnail_file)) {
                      rename($thumbnail_file, $thumbnail_destination);

                      // check current theme customizer settings for the fileg
                      // and update if found
                      $update_theme_mods = false;
                      $move_source_url = $this->get_file_url_for_copy($source_path);
                      $move_thumbnail_url = $this->get_file_url_for_copy($thumbnail_destination);
                      $key = array_search ($move_source_url, $this->theme_mods, true);
                      if($key !== false ) {
                        set_theme_mod( $key, $move_thumbnail_url);
                        $update_theme_mods = true;                      
                      }
                      if($update_theme_mods) {
                        $theme_mods = get_theme_mods();
                        $this->theme_mods = json_decode(json_encode($theme_mods), true);
                        $update_theme_mods = false;
                      }
                    } else if(defined('MLF_CHECK_THUMBNAILFILE_MOVE')) {
                      error_log("$thumbnail_file not found");
                    }
                  }
                  
                }
                
                $destination_url = $this->get_file_url($destination_name);

                // update posts table
                $table = $wpdb->prefix . "posts";
                $data = array('guid' => $destination_url );
                $where = array('ID' => $copy_id);
                $wpdb->update( $table, $data, $where);

                // update folder table
                $table = $wpdb->prefix . MAXGALLERIA_MEDIA_LIBRARY_FOLDER_TABLE;
                $data = array('folder_id' => $folder_id );
                $where = array('post_id' => $copy_id);
                $wpdb->update( $table, $data, $where);

                // get the uploads dir name
                $basedir = $this->upload_dir['baseurl'];
                $uploads_dir_name_pos = strrpos($basedir, '/');
                $uploads_dir_name = substr($basedir, $uploads_dir_name_pos+1);

                //find the name and cut off the part with the uploads path
                $string_position = strpos($destination_name, $uploads_dir_name);
                $uploads_dir_length = strlen($uploads_dir_name) + 1;
                $uploads_location = substr($destination_name, $string_position+$uploads_dir_length);
                if($this->is_windows()) 
                  $uploads_location = str_replace('\\','/', $uploads_location);      

                // update _wp_attached_file

                $uploads_location = ltrim($uploads_location, '/');
                update_post_meta( $copy_id, '_wp_attached_file', $uploads_location );

                // update _wp_attachment_metadata
                if ($is_IIS || strtoupper(substr(PHP_OS, 0, 3)) == 'WIN' || strtoupper(substr(PHP_OS, 0, 13)) == 'MICROSOFT-IIS' )
                  $attach_data = wp_generate_attachment_metadata( $copy_id, addslashes($destination_name));										
                else
                  $attach_data = wp_generate_attachment_metadata( $copy_id, $destination_name );										
                wp_update_attachment_metadata( $copy_id,  $attach_data );

                // update posts and pages
                $replace_image_location = $this->get_base_file($image_location);
                $replace_destination_url = $this->get_base_file($destination_url);
                                
                if(class_exists( 'SiteOrigin_Panels')) {                  
                  $this->update_serial_postmeta_records($replace_image_location, $replace_destination_url);                  
                }
                
                // update postmeta records for beaver builder
                if(class_exists( 'FLBuilderLoader')) {
                  $sql = "SELECT ID FROM {$wpdb->prefix}posts WHERE post_content LIKE '%$replace_image_location%'";
                  
                  $records = $wpdb->get_results($sql);
                  foreach($records as $record) {
                    
                    $this->update_bb_postmeta($record->ID, $replace_image_location, $replace_destination_url);
                                        
                  }
                  // clearing BB caches
                  if ( class_exists( 'FLBuilderModel' ) && method_exists( 'FLBuilderModel', 'delete_asset_cache_for_all_posts' ) ) {
                    FLBuilderModel::delete_asset_cache_for_all_posts();
                  }
                  if ( class_exists( 'FLCustomizer' ) && method_exists( 'FLCustomizer', 'clear_all_css_cache' ) ) {
                    FLCustomizer::clear_all_css_cache();
                  }
                  
                }
                                               
                $replace_sql = "UPDATE {$wpdb->prefix}posts SET `post_content` = REPLACE (`post_content`, '$replace_image_location', '$replace_destination_url');";
                $result = $wpdb->query($replace_sql);
                
                $replace_sql = str_replace ( '/', '\/', $replace_sql);
                //error_log($replace_sql);
                $result = $wpdb->query($replace_sql);
                
                // for updating wp pagebuilder
                if(defined('WPPB_LICENSE')) {
                  $this->update_wppb_data($replace_image_location, $destination_url);
                }
                                
                // for updating themify images
                if(function_exists('themify_builder_activate')) {
                  $this->update_themify_data($replace_image_location, $destination_url);
                }                                
                
                // for updating elementor background images
                if(is_plugin_active("elementor/elementor.php")) {
                  $this->update_elementor_data($copy_id, $replace_image_location, $destination_url);
                }
                                
                $message .= esc_html__('Updating attachment links, please wait...','maxgalleria-media-library') . PHP_EOL;
                $files = $this->display_folder_contents ($current_folder, true, "", false);
                $refresh = true;
              }                                   
              else {
                $message .= esc_html( __('Unable to move ','maxgalleria-media-library') . $basename . __('; please check the folder and file permissions.','maxgalleria-media-library') . PHP_EOL);
                $copy_status = false; 
              }
            } 
          }
          else {
            $message .= esc_html( __('The destination is not a folder: ','maxgalleria-media-library') . $destination_path . PHP_EOL);
            $copy_status = false; 
          }
        }
        else {
          $message .= esc_html( __('Cannot find destination folder: ','maxgalleria-media-library') . $destination_path . PHP_EOL);
          $copy_status = false; 
        }
      }   
      else {
        $message .= esc_html__('Coping or moving a folder is not allowed.','maxgalleria-media-library') . PHP_EOL;
        $copy_status = false; 
      }
    }
    else {
      $message .= esc_html( __('Cannot find the file: ','maxgalleria-media-library') . $image_path . ". " . PHP_EOL);
      //$this->write_log("Cannot find the file: $image_path");
      $copy_status = false; 
    }        
  
    if($copy) {
      if($copy_status)
        $message .= esc_html($basename . __(' was copied to ','maxgalleria-media-library') . $folder_basename . PHP_EOL);      
      else
        $message .= esc_html($basename . __(' was not copied.','maxgalleria-media-library') . PHP_EOL);      
    }
    else {
      if($copy_status)
        $message .= esc_html($basename . __(' was moved to ','maxgalleria-media-library') . $folder_basename . PHP_EOL);      
      else
        $message .= esc_html($basename . __(' was not moved.','maxgalleria-media-library') . PHP_EOL);      
    }

    return $message;
    
  }
  
  public function update_wppb_data($replace_image_location, $destination_url) {
    
    global $wpdb;
    $save = false;
    $table = $wpdb->prefix . "postmeta";
    
    $position = strrpos($destination_url, '.');    
    $url_without_extension = substr($destination_url, 0, $position);    
        
    $base_file_name = basename($replace_image_location);
    
    $sql = "select post_id, meta_id, meta_value from wp_postmeta where meta_key = '_wppb_content' and meta_value like '%{$base_file_name}%'";
    //error_log($sql);
    
    $rows = $wpdb->get_results($sql);
    if($rows) {
      foreach($rows as $row) {        
        $jarrays = json_decode($row->meta_value, true);
        $this->wppb_recursive_find_and_update($jarrays, $replace_image_location, $destination_url, $url_without_extension);
        //error_log(print_r($jarrays, true));
        
        $jarrays = json_encode($jarrays);
        $data = array('meta_value' => $jarrays);
        $where = array('meta_id' => $row->meta_id);
        $wpdb->update($table, $data, $where);
      }
    }  
  }
  
  public function wppb_recursive_find_and_update(&$jarrays, $replace_image_location, $destination_url ) {
    
    foreach($jarrays as $key => &$value) {
      if(is_array($value)) {
        $this->wppb_recursive_find_and_update($value, $replace_image_location, $destination_url);
      } else {
        if($key == 'url' && strpos($value, $replace_image_location) !== false) {            
          $value = $destination_url;
        }          
      }
    }
  }
          
  public function update_themify_data($replace_image_location, $destination_url) {
    
    global $wpdb;
    $save = false;
    $table = $wpdb->prefix . "postmeta";
    
    $position = strrpos($destination_url, '.');    
    $url_without_extension = substr($destination_url, 0, $position);    
        
    $base_file_name = basename($replace_image_location);
    
    $sql = "select post_id, meta_id, meta_value from {$table} where meta_key = '_themify_builder_settings_json' and meta_value like '%$base_file_name%'";
    
    $rows = $wpdb->get_results($sql);
    if($rows) {
      foreach($rows as $row) {        
        $jarrays = json_decode($row->meta_value, true);
        $this->recursive_find_and_update($jarrays, $replace_image_location, $destination_url, $url_without_extension);
        
        $jarrays = json_encode($jarrays);
        $data = array('meta_value' => $jarrays);
        $where = array('meta_id' => $row->meta_id);
        $wpdb->update($table, $data, $where);
      }
    }      
  }
  
  public function recursive_find_and_update(&$jarrays, $replace_image_location, $destination_url, $url_without_extension) {
            
    foreach($jarrays as $key => &$value) {
      if(is_array($value)) {
        $this->recursive_find_and_update($value, $replace_image_location, $destination_url, $url_without_extension);
      } else {
        if($key == 'url_image' && strpos($value, $replace_image_location) !== false) {            
          $value = $destination_url;
        } else if($key == 'img_url_slider' && strpos($value, $replace_image_location) !== false) {            
          $value = $destination_url;            
        } else if($key == 'content_text' && strpos($value, $replace_image_location) !== false ) {
          $content_text = $value;
          $value = str_replace($replace_image_location, $url_without_extension, $content_text);      
        }          
      }
    }
  }
    
  public function update_elementor_data($image_id, $replace_image_location, $replace_destination_url) {
    
    global $wpdb;
    $save = false;
    
    $base_file_name = basename($replace_image_location);
    
    $sql = "select post_id, meta_id, meta_value from {$wpdb->prefix}postmeta where meta_key = '_elementor_data' and meta_value like '%$base_file_name%'";
    
    $rows = $wpdb->get_results($sql);
    if($rows) {
      foreach($rows as $row) {
        
        // check for serialized data
        $data = @unserialize($row->meta_value);
        if($data === false)
          $jarrays = json_decode($row->meta_value, true);
        else {
          $jarrays = $data; 
        }
        
        if(is_array($jarrays)) {          
          foreach($jarrays as &$jarray) {
            if($this->search_elementor_array($image_id, $jarray, $replace_image_location, $replace_destination_url, $row->post_id))
              $save = true;
          }
        } else {
            //error_log("is not an array");
        }
        if($save) {
          update_post_meta($row->post_id, '_elementor_data', $jarrays);
        }
        $this->update_elemenator_css_file($row->post_id, $replace_image_location, $replace_destination_url);
      }
    }
  }
  
  public function search_elementor_array($image_id, &$jarray, $replace_image_location, $replace_destination_url, $post_id) {
    
    $save = false;
    if(array_key_exists('settings', $jarray)) {
      if(array_key_exists('background_background', $jarray['settings'])) {
        if($jarray['settings']['background_background'] == 'classic') {
          if(array_key_exists('id', $jarray['settings']['background_image'])) {
            if($jarray['settings']['background_image']['id'] == $image_id) {
              $jarray['settings']['background_image']['url'] = $replace_destination_url;
              $save = true;              
            }              
          }          
        }        
      }
    }    
  }
  
  public function update_elemenator_css_file($post_id, $replace_image_location, $replace_destination_url) {
    
    $css_file_path = trailingslashit($this->upload_dir['basedir']) . "elementor/css/post-{$post_id}.css";
    
    $position = strrpos($replace_destination_url, '.');
    
    $url_without_extension = substr($replace_destination_url, 0, $position);
    
    if(file_exists($css_file_path)) {
        
      $css = file_get_contents($css_file_path);

      $css = str_replace($replace_image_location, $url_without_extension, $css);

      file_put_contents($css_file_path, $css);
    }
        
  }
  
  public function update_bb_postmeta($post_id, $replace_image_location, $replace_destination_url) {
      
    $this->update_bb_postmeta_item('_fl_builder_draft', $post_id, $replace_image_location, $replace_destination_url);
    $this->update_bb_postmeta_item('_fl_builder_data', $post_id, $replace_image_location, $replace_destination_url);
    
  }
  
  public function update_bb_postmeta_item($metakey, $post_id, $replace_image_location, $replace_destination_url) {
    
    $save = false;
    $builder_info = json_decode(json_encode(get_post_meta($post_id, $metakey, true)));
    $builder_info = $this->objectToArray($builder_info);
    
    if(is_array($builder_info)){
      foreach ($builder_info as $key => &$info_head) {
        foreach ($info_head as $info_key => &$info_value) {
          if(is_array($info_value)) {
            foreach ($info_value as $data_key => &$data_value) {
              if(!is_array($data_value)) {
                if($data_key == 'photo_src' || $data_key == 'text') {
                  $save = true;
                  $data_value = str_replace($replace_image_location, $replace_destination_url, $data_value);
                }
              } else {  
                foreach ($data_value as $next_key => &$next_value) {
                  if(!is_array($next_value)) {
                    if($next_key == 'url') {
                      $save = true;
                      $next_value = str_replace($replace_image_location, $replace_destination_url, $next_value);
                    }                  
                  } else {
                    foreach ($next_value as $sizes_key => &$sizes_value) {
                      if(is_array($sizes_value)) {
                        foreach ($sizes_value as $final_key => &$final_value) {
                          if(!is_array($final_value)) {
                            if($final_key == 'url') {
                              $save = true;
                              $final_value = str_replace($replace_image_location, $replace_destination_url, $final_value);
                            }                            
                          }
                        }
                      }
                    }
                  }  
                }  
              }
            }  
          }
        }
      }
    }
        
    if($save) {
      $builder_info = $this->arrayToObject($builder_info);
      $builder_info = serialize($builder_info);
      update_post_meta($post_id, $metakey, $builder_info);
    }
    
  }
        
  function objectToArray( $object ) {
    if( !is_object( $object ) && !is_array( $object )){
        return $object;
    }
    if( is_object( $object ) ){
        $object = get_object_vars( $object );
    }
    return array_map( array($this, 'objectToArray'), $object );
  }  
  
  public function arrayToObject($d){
    if (is_array($d)){
      return (object) array_map(array($this, 'arrayToObject'), $d);
    } else {
      return $d;
    }
  }  
            
  public function get_upload_status() {
    $data = get_userdata(get_current_user_id());
    if (!is_object($data) || !isset($data->allcaps['upload_files']))
      $this->current_user_can_upload = false;
    else
      $this->current_user_can_upload = $data->allcaps['upload_files'];
  }  
  
  public function update_serial_postmeta_records($replace_image_location, $replace_destination_url) {
    
    global $wpdb;
    
    // = instead oflike?   
    $sql = "SELECT * FROM {$wpdb->prefix}postmeta WHERE meta_key = 'panels_data' and meta_value like '%$replace_image_location%'";
    
    $widgets = array('text','content','url','mp4','m4v','webm','ogv','flv');

    $records = $wpdb->get_results($sql);
    foreach($records as $record) {
                  
      $data = unserialize($record->meta_value);
      
      if (isset($data['widgets']) && is_array($data['widgets'])) {
        
        for ($index = 0; $index < count($data['widgets']); $index++) {  
          
          foreach($widgets as $widget) {
            
            if(isset($data['widgets'][$index][$widget])) {
              
              if(is_string($data['widgets'][$index][$widget])) {
                $text = $data['widgets'][$index][$widget];
                //error_log("$widget: $text");
                $data['widgets'][$index][$widget] = str_replace($replace_image_location, $replace_destination_url, $text);
                //error_log($data['widgets'][$index][$widget]);
              }
            }
            
          }
          
        }
        
      }
            
		  update_post_meta($record->post_id, $record->meta_key, $data);												      
    }        
  }
  
  public function get_ajax_paramater($parameter_name, $default = '') {

    if ((isset($_POST[$parameter_name])) && (strlen(trim($_POST[$parameter_name])) > 0))
      $return_value = trim(sanitize_text_field($_POST[$parameter_name]));
    else
      $return_value = $default;

    return $return_value;

  }  
    
  public function mlfp_process_bdp() {
    
    $message = "";
    
    if ( !wp_verify_nonce( $_POST['nonce'], MAXGALLERIA_MEDIA_LIBRARY_NONCE)) {
      exit(esc_html__('Missing nonce! Please refresh this page.','maxgalleria-media-library'));
    }
    //error_log("mlfp_process_bdp 1");
    
    $activate_mlfp_bdp = $this->get_ajax_paramater('activate_mlfp_bdp');
    $disable_listing = $this->get_ajax_paramater('disable_listing');
    $disable_hotlinking = $this->get_ajax_paramater('disable_hotlinking');
    $auto_protect = $this->get_ajax_paramater('auto_protect');
    $display_fe_protected_images = $this->get_ajax_paramater('display_fe_protected_images');
    $prevent_right_click = $this->get_ajax_paramater('prevent_right_click');
    $bda_role = $this->get_ajax_paramater('bda_role');
    $no_access_page_id = $this->get_ajax_paramater('no_access_page_id');
    $no_access_page_name = $this->get_ajax_paramater('no_access_page_name');
                  
    if($this->bda == 'off' && $activate_mlfp_bdp == 'true') {
      //error_log("mlfp_process_bdp 2");
                  
      $this->protected_content_dir = $this->upload_dir['basedir'] . '/' . MLFP_PROTECTED_DIRECTORY;
      
		  $content_folder = apply_filters( 'mlfp_content_folder', 'wp-content');
      
      $position = strpos($this->protected_content_dir, $content_folder);
        
      $bda_path = substr($this->protected_content_dir, $position);
            
		  if(!file_exists($this->protected_content_dir)) {        
        if(mkdir($this->protected_content_dir)) {
          //error_log("created " . $this->protected_content_dir);
          if(defined('FS_CHMOD_DIR'))
            @chmod($this->protected_content_dir, FS_CHMOD_DIR);
          else  
            @chmod($this->protected_content_dir, 0775);
            //@chmod($this->protected_content_dir, 0755);
          
		      if(file_exists($this->protected_content_dir)) {  
            
            $this->bda_folder_id = $this->add_media_folder(MLFP_PROTECTED_DIRECTORY, $this->uploads_folder_ID, $this->protected_content_dir); 
            update_option(MLFP_PROTECTED_DIR, $this->protected_content_dir); 
            
            $skip_folder_file = $this->protected_content_dir . DIRECTORY_SEPARATOR . "mlpp-hidden";

            file_put_contents($skip_folder_file, '');                        
            
            $message .= esc_html__('Procteced-content folder added.','maxgalleria-media-library') . '<br>';
          }
        }
      }
      
      $message .= esc_html__('Block Direct Access activated.','maxgalleria-media-library');
      
      $this->bda = 'on';
      update_option(MLFP_BDA, $this->bda);      
      add_filter('mod_rewrite_rules', array( $this, 'mlfp_update_htaccess'));
      flush_rewrite_rules();
      
      // check for download page
      if(!get_page_by_path("mlfp-download")) {
        $wordpress_page = array(
          'post_title'    => esc_html__('MLFP Download','maxgalleria-media-library'),
          'post_content'  => '',
          'post_status'   => 'publish',
          'post_author'   => 1,
          'post_type' => 'page'
        );
        $download_page_id =  wp_insert_post($wordpress_page); 
        update_option(MLFP_BDA_DOWNLOAD_PAGE, $download_page_id);              
      }
            
    } else if($this->bda == 'on' && $activate_mlfp_bdp == 'false') {
      $this->bda = 'off';
      update_option(MLFP_BDA, 'off');   
      if($post = get_page_by_path('mlfp-download')) {
        wp_delete_post($post->ID, true);
      }  
      $message .= esc_html__('Block Direct Access deactivated.','maxgalleria-media-library');
      
      remove_filter('mod_rewrite_rules', array( $this, 'mlfp_update_htaccess'));
      flush_rewrite_rules();
      //error_log("removing bda rules");
    }
    
    if($disable_listing == 'true') {
      update_option(MLFP_BDA_DIR_LISTING, 'on');            
    } else {
      update_option(MLFP_BDA_DIR_LISTING, 'off');      
    }
    
    if($disable_hotlinking == 'true') {
      update_option(MLFP_BDA_HOTLINKING, 'on');                  
    } else {
      update_option(MLFP_BDA_HOTLINKING, 'off');                        
    }
        
    if($auto_protect == 'true') {
      update_option(MLFP_BDA_AUTO_PROTECT, 'on');                  
    } else {
      update_option(MLFP_BDA_AUTO_PROTECT, 'off');                        
    }
    
    if($display_fe_protected_images == 'true') {
      update_option(MLFP_BDA_DISPLAY_FE_IMAGES, 'on');                  
    } else {
      update_option(MLFP_BDA_DISPLAY_FE_IMAGES, 'off');                        
    }
    
    if($prevent_right_click == 'true') {
      update_option(MLFP_BDA_PREVENT_RIGHT_CLICK, 'on');                  
    } else {
      update_option(MLFP_BDA_PREVENT_RIGHT_CLICK, 'off');                        
    }    
    
    if(!empty($bda_role)) {
      update_option(MLFP_BDA_USER_ROLE, $bda_role);                        
    }  
        
    echo $message;
    
    die();
    
  }
  
  public function mlfp_save_noaccess_page() {
        
    if ( !wp_verify_nonce( $_POST['nonce'], MAXGALLERIA_MEDIA_LIBRARY_NONCE)) {
      exit(esc_html__('Missing nonce! Please refresh this page.','maxgalleria-media-library'));
    }
    $no_access_page_id = $this->get_ajax_paramater('no_access_page_id');
    $no_access_page_name = $this->get_ajax_paramater('no_access_page_name');
                      
    if(!empty($no_access_page_id))      
      update_option(MLFP_NO_ACCESS_PAGE_ID, $no_access_page_id); 
    
    if(!empty($no_access_page_name))      
      update_option(MLFP_NO_ACCESS_PAGE_TITLE, $no_access_page_name);
    
    echo '';
    
    die();
  }
    
  public function mlfp_update_htaccess($rules) {
    
    //error_log("mlfp_update_htaccess");
    
    global $wpdb;
    $endpoint = "mlfp_bdp";
    $display_listing = '';
    $bdp_rules = '';
            
    $bdp_rules .= "# Block Direct Access Rewrite Rules" . PHP_EOL;
    $bdp_rules .= "RewriteRule private/([a-zA-Z0-9]+)$ index.php?{$endpoint}=$1 [L]" . PHP_EOL;
    $bdp_rules .= "RewriteCond %{REQUEST_FILENAME} -s"  . PHP_EOL;
    $bdp_rules .= "RewriteCond %{HTTP_USER_AGENT} !facebookexternalhit/[0-9]" . PHP_EOL;
    $bdp_rules .= "RewriteCond %{HTTP_USER_AGENT} !Googlebot/[0-9]" . PHP_EOL;
    $bdp_rules .= "RewriteCond %{HTTP_USER_AGENT} !Twitterbot/[0-9]" . PHP_EOL;
		$directAccessPath = str_replace( trailingslashit( site_url() ), '', 'index.php' ) . "?{$endpoint}=$1&block_access=true [QSA,L]" . PHP_EOL;
		$upload_dir_url = str_replace( "https", "http", wp_upload_dir()['baseurl'] );
		$site_url       = str_replace( "https", "http", site_url() );    
    $bdp_rules .= "RewriteRule " . str_replace(trailingslashit($site_url), '', $upload_dir_url) . "/" . MLFP_PROTECTED_DIRECTORY . "(\/[A-Za-z0-9_@.\/&+-]+)+\.([A-Za-z0-9_@.\/&+-]+)$ " . $directAccessPath  . PHP_EOL;
    
    if(get_option(MLFP_BDA_HOTLINKING) == 'on') {     
      $domain = home_url( '/', is_ssl() ? 'https' : 'http' );      
      $bdp_rules .= "# Block Direct Access Prevent Hotlinking Rules" . PHP_EOL;
      $bdp_rules .= "RewriteCond %{HTTP_REFERER} !^$" . PHP_EOL;
      $bdp_rules .= "RewriteCond %{HTTP_REFERER} !^{$domain} [NC]" . PHP_EOL;
      $bdp_rules .= "RewriteRule \.(gif|jpg|jpeg|bmp|zip|rar|mp3|flv|swf|xml|png|css|pdf)$ - [F]" . PHP_EOL;
      $bdp_rules .= "# Block Direct Access Prevent Hotlinking Rules End" . PHP_EOL;
    }
    
    if(get_option(MLFP_BDA_DIR_LISTING) == 'on') {
      $display_listing = "Options -Indexes" . PHP_EOL;
    }
    
    $bdp_rules .= "# Block Direct Access Rewrite Rules End"  . PHP_EOL;
    
    return $bdp_rules . $rules . $display_listing;
        
  }
    
  public function mlfp_toggle_file_access() {
        
    if ( !wp_verify_nonce( $_POST['nonce'], MAXGALLERIA_MEDIA_LIBRARY_NONCE)) {
      exit(esc_html__('missing nonce!','maxgalleria-media-library'));
    }
    
    $next_id = $this->get_ajax_paramater('file_id', '');
        
    $current_folder = $this->get_ajax_paramater('current_folder', '');
        
    $protected = intval($this->get_ajax_paramater('protected', '0'));
                        
    if($next_id != "") {
      $message = $this->move_to_protected_folder($next_id, $current_folder, $protected);
    } else {
      $message = esc_html__("Finished moving files to protected folder. ",'maxgalleria-media-library');
    }  
        
    echo $message;
    
    die();
    
  }
  
  public function move_to_protected_folder($next_id, $current_folder, $protected) {
        
    require_once( ABSPATH . 'wp-admin/includes/image.php' );
        
    global $wpdb, $is_IIS;
		$message = "";
		$files = "";
		$refresh = false;
    $scaled = false;
    
    $sql = "select meta_value as attached_file
from $wpdb->postmeta 
where post_id = $next_id    
AND meta_key = '_wp_attached_file'";

    //error_log($sql);
    
    $row = $wpdb->get_row($sql);

    if(!$row) {
      return esc_html__('Could not find the selected file.','maxgalleria-media-library') . PHP_EOL;
    }

    remove_filter( 'wp_generate_attachment_metadata', array($this, 'add_attachment_to_folder2'));   

    $baseurl = $this->upload_dir['baseurl'];
    $baseurl = rtrim($baseurl, '/') . '/';
    $image_location = $baseurl . ltrim($row->attached_file, '/');
    
    //error_log("image_location $image_location");
    if(strpos($image_location, '-scaled.' ) !== false) {
      $scaled = true;
    }

    $image_path = $this->get_absolute_path($image_location);
    //error_log("image_path $image_path");
    
    if($protected == 0 ) {
      $destination_path = $this->protected_content_dir;   
      
      //error_log('Protecting file ' . $row->attached_file);
      
      $destination_name = $destination_path . DIRECTORY_SEPARATOR . $row->attached_file;
      //error_log("destination_name $destination_name");
      $position = strrpos($destination_name, '/');
      $destination_folder = substr($destination_name, 0, $position);
      
    } else {      
      $destination_path = $this->get_folder_path($current_folder);
      $destination_folder = $destination_path;
      
      //error_log('Unprotecting file ' . $row->attached_file);
      
      $basename = pathinfo($row->attached_file, PATHINFO_BASENAME);
            
      $destination_name = $destination_path . DIRECTORY_SEPARATOR . $basename;        
      
    }
    //error_log("destination_path $destination_path");
    
    
    if(!file_exists($destination_folder)) {
      if($this->make_dir_path($destination_folder)) {
        if(defined('FS_CHMOD_DIR'))
			    @chmod($destination_folder, FS_CHMOD_DIR);
        else  
			    @chmod($destination_folder, 0755);
      }  
    }

    $copy_status = true;
    
    if(file_exists($image_path)) {
      if(!is_dir($image_path)) {
        if(file_exists($destination_path)) {
          if(is_dir($destination_path)) {
            //error_log("rename $image_path, $destination_name");  
            //return false;
            if(rename($image_path, $destination_name )) {

              $image_path = str_replace('.', '*.', $image_path );
              $metadata = wp_get_attachment_metadata($next_id);                               
              $path_to_thumbnails = pathinfo($image_path, PATHINFO_DIRNAME);

              if($scaled) {
                $full_scaled_image_path = str_replace('-scaled*', '', $image_path);
                //error_log("full_scaled_image_path $full_scaled_image_path");
                $full_scaled_image = substr($full_scaled_image_path, strrpos($full_scaled_image_path, '/')+1);
                //error_log("full_scaled_image $full_scaled_image");
                $scaled_image_destination = $destination_folder . DIRECTORY_SEPARATOR . $full_scaled_image;
                //error_log("scaled_image_destination $scaled_image_destination");
                if(file_exists($full_scaled_image_path))
                  rename($full_scaled_image_path, $scaled_image_destination);  
              }

              if(isset($metadata['sizes'])) {

                foreach($metadata['sizes'] as $source_path) {
                  $thumbnail_file = $path_to_thumbnails . DIRECTORY_SEPARATOR . $source_path['file'];
                  $thumbnail_destination = $destination_folder . DIRECTORY_SEPARATOR . $source_path['file'];
                  //error_log("thumbnail_file $thumbnail_file");
                  if(file_exists($thumbnail_file)) {
                    unlink($thumbnail_file);
                    
                  }
                }
              }

              $destination_url = $this->get_file_url($destination_name);
              
              // update block_access table
              if($protected == 0) {
                $this->add_bda_record($next_id);
                $message = esc_html__('The file', 'maxgalleria-media-library') . esc_html(" $row->attached_file ") . esc_html__('is protected.', 'maxgalleria-media-library');
              } else {
                $this->remove_bda_record($next_id);
                $message = esc_html__('The file', 'maxgalleria-media-library') . esc_html(" $row->attached_file ") . esc_html__('is unprotected.', 'maxgalleria-media-library');
              }  
              //error_log("block_access_table updated $protected");
              
              //add postmete record

              // get the uploads dir name
              $basedir = $this->upload_dir['baseurl'];
              $uploads_dir_name_pos = strrpos($basedir, '/');
              $uploads_dir_name = substr($basedir, $uploads_dir_name_pos+1);

              //find the name and cut off the part with the uploads path
              $string_position = strpos($destination_name, $uploads_dir_name);
              $uploads_dir_length = strlen($uploads_dir_name) + 1;
              $uploads_location = substr($destination_name, $string_position+$uploads_dir_length);
              if($this->is_windows()) 
                $uploads_location = str_replace('\\','/', $uploads_location);      

              // update _wp_attached_file

              $uploads_location = ltrim($uploads_location, '/');
              update_post_meta( $next_id, '_wp_attached_file', $uploads_location );
              //error_log("new file location $uploads_location");
              
              // update _wp_attachment_metadata
              if ($is_IIS || strtoupper(substr(PHP_OS, 0, 3)) == 'WIN' || strtoupper(substr(PHP_OS, 0, 13)) == 'MICROSOFT-IIS' )
                $attach_data = wp_generate_attachment_metadata( $next_id, addslashes($destination_name));										
              else
                $attach_data = wp_generate_attachment_metadata( $next_id, $destination_name );										
              wp_update_attachment_metadata( $next_id,  $attach_data );

              // update posts and pages
              $replace_image_location = $this->get_base_file($image_location);
              $replace_destination_url = $this->get_base_file($destination_url);

              if(class_exists( 'SiteOrigin_Panels')) {                  
                $this->update_serial_postmeta_records($replace_image_location, $replace_destination_url);                  
              }

              // update postmeta records for beaver builder
              if(class_exists( 'FLBuilderLoader')) {
                $sql = "SELECT ID FROM {$wpdb->prefix}posts WHERE post_content LIKE '%$replace_image_location%'";
                //error_log($sql);

                $records = $wpdb->get_results($sql);
                foreach($records as $record) {

                  $this->update_bb_postmeta($record->ID, $replace_image_location, $replace_destination_url);

                  //}
                  // clearing BB caches
                  //error_log("check for cache");
                  if ( class_exists( 'FLBuilderModel' ) && method_exists( 'FLBuilderModel', 'delete_asset_cache_for_all_posts' ) ) {
                    FLBuilderModel::delete_asset_cache_for_all_posts();
                    //error_log("delete_asset_cache_for_all_posts");
                  }

                  if ( class_exists( 'FLBuilderModel' ) && method_exists( 'FLBuilderModel', 'delete_all_asset_cache' ) ) {
                    FLBuilderModel::delete_all_asset_cache( $record->ID );
                    //error_log("delete_all_asset_cache");
                  }  

                  if ( class_exists( 'FLCustomizer' ) && method_exists( 'FLCustomizer', 'clear_all_css_cache' ) ) {
                    FLCustomizer::clear_all_css_cache();
                    //error_log("clear_all_css_cache");
                  }
                }
                wp_cache_flush();

              }

              $this->update_links($replace_image_location, $replace_destination_url);                

              // for updating wp pagebuilder
              if(defined('WPPB_LICENSE')) {
                $this->update_wppb_data($replace_image_location, $destination_url);
              }

              // for updating themify images
              if(function_exists('themify_builder_activate')) {
                $this->update_themify_data($replace_image_location, $destination_url);
              }

              // for updating elementor background images
              if(is_plugin_active("elementor/elementor.php")) {
                $this->update_elementor_data($next_id, $replace_image_location, $destination_url);
              }

              //$message .= __('Updating attachment links, please wait...','maxgalleria-media-library') . PHP_EOL;
              $files = $this->display_folder_contents ($current_folder, true, "", false);
              $refresh = true;
            } else {
              //$message .= sprintf(__("Could not move %s to the protected folder",'maxgalleria-media-library'), $row->attached_file) . PHP_EOL;
            }                                           
          }
        }
      }
    }  
    
    add_filter( 'wp_generate_attachment_metadata', array($this, 'add_attachment_to_folder2'), 10, 4);    

    return $message;
    
  }

  public function is_file_protected($file_id) {

    global $wpdb;

    $sql = "select block from wp_mgmlp_block_access where attachment_id = $file_id";

    $row = $wpdb->get_row($sql);

    if($row) {
      if($row->block == 1)
        return true;
      else
        return false;
    } else {
      return false;
    }
    
  }

  public function file_exists($file_id) {

    global $wpdb;

    $sql = "select post_title from $wpdb->posts where ID = $file_id and post_type = 'attachment'";

    $row = $wpdb->get_row($sql);

    if($row) {
      return true;
    } else {
      return false;
    }
  }  
  
  public function make_dir_path($path) {
    return (file_exists($path) || mkdir($path, 0775, true));
  }
  
  public function add_bda_record($attachment_id) {
    global $wpdb;
    $block_access_table = $wpdb->prefix . MAXGALLERIA_MEDIA_LIBRARY_BLOCK_ACCESS_TABLE;
    $time = date('D n/j/Y g:i a');
    
    $data = array(
      'attachment_id'=> $attachment_id,
      'hash_id' => md5(rand()),  
      'time' => $time,
      'block' => 1
    );
    
    $wpdb->replace($block_access_table, $data);
    update_post_meta($attachment_id, MLFP_BDA_MEDIA, TRUE);
  }  
  
  public function remove_bda_record($attachment_id) {
    global $wpdb;
    $block_access_table = $wpdb->prefix . MAXGALLERIA_MEDIA_LIBRARY_BLOCK_ACCESS_TABLE;
    $where = array('attachment_id' => $attachment_id);
    $wpdb->delete($block_access_table,$where);
    delete_post_meta($attachment_id, MLFP_BDA_MEDIA);
  }
  
  public function bda_help() {
    ?>
    <p><strong><?php esc_html_e("Block Direct Access","maxgalleria-media-library")?></strong></p>
    <p><?php esc_html_e("The Block Direct Access feature allows an administrator to block viewing and download of selected media files. Files to be blocked are moved to a protected folder in the media library and their embedded links in any posts or pages are automatically updated.","maxgalleria-media-library")?></p>
    <p><?php esc_html_e("Blocked file links can be generated and configured to limit the number of downloads or to expire with an expiration date on the Library page.","maxgalleria-media-library")?></p>
    <p><?php esc_html_e("To activate this feature, check the 'Activate Block Direct Access' checkbox and click the Update Settings button.","maxgalleria-media-library")?></p>
    <p><?php esc_html_e("You can also check the 'Prevent Directory Listing' and the 'Prevent Hotlinking'. Hotlinking is the practice and linking to files hosted on a different website.","maxgalleria-media-library")?></p>
    <p><?php esc_html_e("The viewing of protected files on the front end of the site can be enabled by check the 'Display Protected Images on the Front End of the Site' option, but this may not work in some browsers. Also the ability to copy and save images displayed in a browser can be disable using the 'Disable Image Copy and Right Click' option.","maxgalleria-media-library")?></p>
    <p><?php esc_html_e("Also either Administrators or the Author, the user who upload the protected file, can be set to view the protected files from Media Library Folders Pro.","maxgalleria-media-library")?></p>    
    <p><strong><?php esc_html_e("Block Access to Private Download Links","maxgalleria-media-library")?></strong>, <?php esc_html_e("IP addresses to private links to protected files can be blocked by adding them to the Block Access to Private Download Links.","maxgalleria-media-library")?></p>    
    <p><strong><?php esc_html_e("Custom No Access Page,","maxgalleria-media-library")?></strong>, <?php esc_html_e("A page can be created to display a no access message in place of the site's 404 page. Under Custom No Access Page, an administrator can select and set the page to be used for this purpose.","maxgalleria-media-library")?></p>
    <p>&nbsp;</p>
    <p>&nbsp;</p>
    <p>&nbsp;</p>
    <?php
  }
  
  public function mlp_generate_file_link() {
    
    $message = "";
    
    if ( !wp_verify_nonce( $_POST['nonce'], MAXGALLERIA_MEDIA_LIBRARY_NONCE)) {
      exit(esc_html__('Missing nonce! Please refresh this page.','maxgalleria-media-library'));
    }
          
		if ((isset($_POST['image_id'])) && (strlen(trim($_POST['image_id'])) > 0))
      $image_id = intval(trim(sanitize_text_field($_POST['image_id'])));
    else
      $image_id = 0;
        
		if ((isset($_POST['title'])) && (strlen(trim($_POST['title'])) > 0))
      $title = trim(sanitize_text_field($_POST['title']));
    else
      $title = "";
    
    
		if ((isset($_POST['current_user'])) && (strlen(trim($_POST['current_user'])) > 0))
      $current_user = intval(trim(sanitize_text_field($_POST['current_user'])));
    else
      $current_user = 0;
    
    if($image_id != 0) {
      
      if(!$this->is_protected_file($image_id)) {
        $message = $title . esc_html__(' is not a protected file','maxgalleria-media-library');
      } else {
        $download_link = $this->get_private_link($image_id, $current_user);
        $message = esc_html($title) . " - " . esc_url($download_link);
      }              
    }
    
    echo $message;
    die();
    
  }
  
  public function is_protected_file($image_id) {
    
    global $wpdb;
    
    $table = $wpdb->prefix . MAXGALLERIA_MEDIA_LIBRARY_BLOCK_ACCESS_TABLE;
    
    $sql = "select time from $table where attachment_id = $image_id";
    
    //error_log($sql);
    
    if($wpdb->get_var($sql) != NULL)
      return true;
    else
      return false;    
  }
  
  public function get_hash_id($image_id) {
    
    global $wpdb;
    
    $table = $wpdb->prefix . MAXGALLERIA_MEDIA_LIBRARY_BLOCK_ACCESS_TABLE;
        
    $sql = "select hash_id from $table where attachment_id = $image_id";
    
    //error_log($sql);
    
    $hash_id = $wpdb->get_var($sql);
    
    return $hash_id;
  }
  
  public function get_private_link($image_id, $current_user) {
    
    $hash = $this->get_hash_id($image_id);
    
    //$this->add_hash_id($image_id, $hash);
    
    $download_page = get_permalink(get_option(MLFP_BDA_DOWNLOAD_PAGE));
    
    return esc_url(add_query_arg('download', $hash, $download_page));         
  }
  
  public function add_hash_id($image_id, $hash) {
    
    global $wpdb;
    
    $table = $wpdb->prefix . MAXGALLERIA_MEDIA_LIBRARY_BLOCK_ACCESS_TABLE;
    
    $data = array('hash_id' => $hash);
    
    $where = array('attachment_id' => $image_id);
    
    $wpdb->update($table, $data, $where);
        
  }
  
  public function mlfp_display_bda_info() {
    
    if ( !wp_verify_nonce( $_POST['nonce'], MAXGALLERIA_MEDIA_LIBRARY_NONCE)) {
      exit(esc_html__('Missing nonce! Please refresh this page.','maxgalleria-media-library'));
    }
          
    $image_id = $this->get_ajax_paramater('image_id', 0);
    
    $title = $this->get_ajax_paramater('title', '');
    
    $count = $this->get_ajax_paramater('count', ''); 
    
    //error_log("$image_id, $title, $count");
    
    $row = $this->get_bda_file_info($image_id, $title, $count);
    
    if($row)    
      echo $row;
    else
      echo "none";
    
    die();
    
  }
  
  public function get_bda_file_info($image_id, $title, $count) {
    
    global $wpdb;
    
    $table = $wpdb->prefix . MAXGALLERIA_MEDIA_LIBRARY_BLOCK_ACCESS_TABLE;

    $sql = "select * from $table where attachment_id = $image_id";
    //error_log($sql);
    $row = $wpdb->get_row($sql);

    if($count % 2)
      $row_color = "";
    else
      $row_color = "gray-row";
              
    if($row) {
      $download_count = ($row->count) ? $row->count : '0';
      $download_limit = ($row->download_limit) ? $row->download_limit : '0';
      $line = "<tr class='bda-row $row_color' data-id='$image_id'><td class='bda-name-col'>$title</td><td class='bda-count-col mflp-align-center'>$download_count</td><td class='bda-limit-col mflp-align-center'><input type='text' id='limit-{$image_id}' class='bda-limit-input mflp-align-right' value='$download_limit' ></td><td class='bda-exp-date-col'><input type='date' class='bda-exp-date-input' id='ex-date-{$image_id}' value='$row->expiration_date'></td><td class='bda-copy-link-col'><a class='bda-copy-link gray-blue-link' data-hash='$row->hash_id'>" . __('Copy Link','maxgalleria-media-library') . "</a></td>\r\n";
      return $line; 
    } else {
      return false;
    }   
  }
  
  public function mlfp_update_bda_record() {
    
    global $wpdb;
    
    if ( !wp_verify_nonce( $_POST['nonce'], MAXGALLERIA_MEDIA_LIBRARY_NONCE)) {
      exit(__('Missing nonce! Please refresh this page.','maxgalleria-media-library'));
    }
    
    $image_id = $this->get_ajax_paramater('image_id', 0);
    
    $download_limit = $this->get_ajax_paramater('download_limit', 0);
    
    $expiration_date = $this->get_ajax_paramater('expiration_date', 0);

    $this->update_bda_record($image_id, $download_limit, $expiration_date);
                  
    die();
    
  }

  public function update_bda_record($image_id, $download_limit, $expiration_date) {
    
    global $wpdb;
    
    $table = $wpdb->prefix . MAXGALLERIA_MEDIA_LIBRARY_BLOCK_ACCESS_TABLE;
    
    $data = array(
      'download_limit' => $download_limit,
      'expiration_date' => $expiration_date
    );
    
    $where = array('attachment_id' => $image_id);
    
    $wpdb->update($table, $data, $where);
    
  }
  
  public function copy_template_to_theme() {
        
		// Copy gallery post type template file to theme directory
    $source = MAXGALLERIA_MEDIA_LIBRARY_PLUGIN_DIR . '/page-mlfp-download.php';
    $destination = $this->get_theme_dir() . '/page-mlfp-download.php';
    if(!defined('PRESERVE_MLFP_TEMPLATE')) {
      copy($source, $destination);
    }  
    else if(!file_exists($destination)) {
      copy($source, $destination);
    }
		flush_rewrite_rules();    
  }

	public function get_theme_dir() {
    if(is_child_theme())
		  return WP_CONTENT_DIR . '/themes/' . get_stylesheet();
    else
		  return WP_CONTENT_DIR . '/themes/' . get_template();
	}
  
  
  public function mlfp_bdp_report() {
    
    global $wpdb;
    $images_found = false;
    $items_per_page = 10;
    
    if ( !wp_verify_nonce( $_POST['nonce'], MAXGALLERIA_MEDIA_LIBRARY_NONCE)) {
      exit(esc_html__('Missing nonce! Please refresh this page.','maxgalleria-media-library'));
    }
    
    $page_id = intval($this->get_ajax_paramater('page_id', 0));
        
    $table = $wpdb->prefix . MAXGALLERIA_MEDIA_LIBRARY_BLOCK_ACCESS_TABLE;    
    
    $offset = $page_id * $items_per_page;
    
    $sql = $wpdb->prepare("SELECT SQL_CALC_FOUND_ROWS pm.meta_value AS attached_file, ba.count, ba.download_limit, ba.expiration_date 
FROM $table AS ba
LEFT JOIN $wpdb->postmeta AS pm ON pm.post_id = ba.attachment_id
WHERE pm.meta_key = '_wp_attached_file' limit %d, %d", $offset, $items_per_page);
    
    //error_log($sql);
    
    $rows = $wpdb->get_results($sql);
    
    $count = $wpdb->get_row("select FOUND_ROWS()", ARRAY_A);
    $total_images = $count['FOUND_ROWS()'];
    $total_number_pages = ceil($total_images / $items_per_page);
        
    if($rows) {
      $images_found = true;      
      echo "<table id='protected-files'>" . PHP_EOL;
      echo "  <thead>" . PHP_EOL;
      echo "    <tr>" . PHP_EOL;
      echo "      <td class='pf-name'>". esc_html__('File','maxgalleria-media-library')."</td>" . PHP_EOL;
      echo "      <td class='pf-count'>". esc_html__('Download count','maxgalleria-media-library')."</td>" . PHP_EOL;
      echo "      <td class='pf-limit'>". esc_html__('Download limit','maxgalleria-media-library')."</td>" . PHP_EOL;
      echo "      <td class='pf-expiration'>". esc_html__('Expirtation Date','maxgalleria-media-library')."</td>" . PHP_EOL;
      echo "    </tr>" . PHP_EOL;
      echo "  </thead>" . PHP_EOL;
      echo "  <tbody>" . PHP_EOL;
      foreach($rows as $row) {
        echo "    <tr>" . PHP_EOL;
        echo "      <td class='pf-name'>" . esc_html($row->attached_file) ."</td>" . PHP_EOL;
        $count = ($row->count) ? $row->count : '0';
        echo "      <td class='pf-count'>".  esc_html($count) ."</td>" . PHP_EOL;
        $download_limit = ($row->download_limit) ? $row->download_limit : 'none';
        echo "      <td class='pf-limit'>" . esc_html($download_limit) ."</td>" . PHP_EOL;
        //error_log($row->attached_file . " expiration_date " . $row->expiration_date);
        //$expiration_date = ($row->expiration_date == '0000-00-00') ? 'none' : date("m/d/Y", strtotime($row->expiration_date));
        if($row->expiration_date == null || $row->expiration_date == '0000-00-00')
          $expiration_date = 'none';
        else 
          $expiration_date = date("m/d/Y", strtotime($row->expiration_date));
        echo "      <td class='pf-expiration'>" . esc_html($expiration_date) ." </td>" . PHP_EOL;
        echo "    </tr>" . PHP_EOL;
      }  
      echo "  <tbody>" . PHP_EOL;
      echo "<table>" . PHP_EOL;
    } else {
      echo "<p style='text-align:center'>" . esc_html__('No files were found.','maxgalleria-media-library')  . "</p>";      
    }
    
    if($images_found) {      
      $last_page = $total_number_pages-1;      
      $previous_page = $page_id - 1;
      $next_page = $page_id + 1;
      echo "<div class='mlfp-page-nav'>" . PHP_EOL;
      if($page_id > 0)	
        echo "<a id='mlfp-previous' page-id='" . esc_attr($previous_page) . "' style='float:left;cursor:pointer'>< " . esc_html__( 'Previous', 'maxgalleria-media-library' ) ."</a>" . PHP_EOL;
      if($page_id < $total_number_pages-1 && $total_images > $items_per_page)
        echo "<a id='mlfp-next' page-id='" . esc_attr($next_page) ."' style='float:right;cursor:pointer'>" . esc_html__( 'Next', 'maxgalleria-media-library' ) ." ></a>" . PHP_EOL;
      echo "</div>" . PHP_EOL;      
    }
      
    die();
    
  }
  
  public function mlfp_block_new_ip() {
    
    global $wpdb;
    $message = '';
    $result = '';
    $new_id = 0;
    
    if ( !wp_verify_nonce( $_POST['nonce'], MAXGALLERIA_MEDIA_LIBRARY_NONCE)) {
      exit(esc_html__('Missing nonce! Please refresh this page.','maxgalleria-media-library'));
    }
        
    $new_block_ip = $this->get_ajax_paramater('new_block_ip');
    
    if(filter_var($new_block_ip, FILTER_VALIDATE_IP)) {
      
      $table = $wpdb->prefix . BLOCKED_IPS_TABLE;

      $sql = $wpdb->prepare("select * from $table where address = '%s'", $new_block_ip);

      $row = $wpdb->get_row($sql);
      if($row) {
        $message = esc_html__('The address is already in the blocked IPs list.','maxgalleria-media-library');
        $result = false;
      } else {
        $data = array('address' => $new_block_ip);
        $retval = $wpdb->insert($table, $data);
        $new_id = $wpdb->insert_id;
        //error_log("$new_block_ip retval $retval");
        $message = esc_html__('The address has been added to the blocked IPs list.','maxgalleria-media-library');
        $result = true;
      }
            
    } else {
        $message = esc_html__('The IP address is not valid.','maxgalleria-media-library');
        $result = false;      
    }
        
    $return = array('message' => $message, 'result' => $result, 'id' => $new_id);
    
	  echo json_encode($return);
    
    die();
  }
  
  public function get_blocked_ips() {
    global $wpdb;
    $buffer = '';
    $table = $wpdb->prefix . BLOCKED_IPS_TABLE;
    $sql = "select * from $table order by address";
    //error_log($sql);
    $rows = $wpdb->get_results($sql);
    if($rows) {
      foreach($rows as $row) {
        $buffer .= '<option value="' . esc_attr($row->ip_id) . '">' . esc_html($row->address) . '</option>' . PHP_EOL ;
      }
    }  
      
    return $buffer;      
  }
  
  public function mlfp_get_block_ips() {
    
    if ( !wp_verify_nonce( $_POST['nonce'], MAXGALLERIA_MEDIA_LIBRARY_NONCE)) {
      exit(esc_html__('Missing nonce! Please refresh this page.','maxgalleria-media-library'));
    }
    
    $data = $this->get_blocked_ips();
    
    echo $data;
    
    die();
  }
  
  public function mlfp_unblock_ips() {
    
    global $wpdb;
    $updated = false;
    $message = esc_html__('No IP addresses were removed  from the blocked IPs lists','maxgalleria-media-library');
    
    if ( !wp_verify_nonce( $_POST['nonce'], MAXGALLERIA_MEDIA_LIBRARY_NONCE)) {
      exit(esc_html__('Missing nonce! Please refresh this page.','maxgalleria-media-library'));
    }
    
    if ((isset($_POST['serial_ips'])) && (strlen(trim($_POST['serial_ips'])) > 0)) {
      $unblock_ips = trim(stripslashes(sanitize_text_field($_POST['serial_ips'])));
      //error_log("unblock_ips 1: $unblock_ips");
      $unblock_ips = str_replace('"', '', $unblock_ips);
      //error_log("unblock_ips 2: $unblock_ips");
      $unblock_ips = explode(",",$unblock_ips);
    }  
    else
      $unblock_ips = '';
    
    $table = $wpdb->prefix . BLOCKED_IPS_TABLE;    
    
    //error_log(print_r($unblock_ips, true));
    foreach($unblock_ips as $unblock_ip) {  
      $updated = true;
      $where = array('ip_id' => $unblock_ip);
      $wpdb->delete($table, $where);
      $message = esc_html__('The IP addresses were unblocked.','maxgalleria-media-library');
    }
        
    $return = array('message' => $message, 'result' => $updated);
    
	  echo json_encode($return);
    
    die();
  }  
  
  public function get_all_pages() {
    global $wpdb; 
    $sql = "select ID, post_title  FROM $wpdb->posts where post_status = 'publish' and post_type = 'page' order by post_title";
    $pages = $wpdb->get_results($sql);
    return $pages;
  }  
  
  public function bda_prepare_attachment_for_js( $response, $attachment, $meta ) {

    $current_user = get_current_user_id();
    $attach_arr = $this->objectToArray($attachment);
    $blocked = get_post_meta($attach_arr['ID'], MLFP_BDA_MEDIA, true );
    
    if($blocked == '1' && $current_user == $attach_arr['post_author'])
      $response['customClass'] = "mlfp-protected author";
    else if($blocked == '1')
      $response['customClass'] = "mlfp-protected";
    else
      $response['customClass'] = "";

    return $response;

  }

  public function bda_add_class_to_media_library_grid_elements() {

    $currentScreen = get_current_screen();

    if('upload' === $currentScreen->id) :

      global $mode;

      wp_enqueue_script('jquery');
      wp_enqueue_script('bda-media', MAXGALLERIA_MEDIA_LIBRARY_PLUGIN_URL . '/js/bda-media.js', array('jquery')); 

    endif;

  }
    
}

$mg_media_library_folders = new MGMediaLibraryFolders();