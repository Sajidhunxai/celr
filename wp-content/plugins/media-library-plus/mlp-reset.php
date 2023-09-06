<?php
/*
Plugin Name: Media Library Folders Reset
Plugin URI: http://maxgalleria.com
Description: Plugin for reseting Media Library Folders
Author: Max Foundry
Author URI: http://maxfoundry.com
Version: 8.1.0
Copyright 2015-2021 Max Foundry, LLC (http://maxfoundry.com)
Text Domain: mlp-reset
*/

if(!defined("MAXGALLERIA_MEDIA_LIBRARY_FOLDER_TABLE"))
  define("MAXGALLERIA_MEDIA_LIBRARY_FOLDER_TABLE", "mgmlp_folders");

if(!defined("MAXGALLERIA_MEDIA_LIBRARY_UPLOAD_FOLDER_ID"))
  define("MAXGALLERIA_MEDIA_LIBRARY_UPLOAD_FOLDER_ID", "mgmlp_upload_folder_id");

if(!defined("MAXGALLERIA_MEDIA_LIBRARY_PLUGIN_URL"))
  define('MAXGALLERIA_MEDIA_LIBRARY_PLUGIN_URL', rtrim(plugin_dir_url(__FILE__), '/'));

if(!defined('MLFP_BDA'))    
  define("MLFP_BDA", "mlfp-bda");

if(!defined('MLFP_BDA_USER_ROLE'))    
  define("MLFP_BDA_USER_ROLE","mlfp-bda-user-role");

if(!defined('MLFP_BDA_AUTO_PROTECT'))    
  define("MLFP_BDA_AUTO_PROTECT", "mlfp-bda-auto-protect");

if(!defined('MLFP_BDA_AUTO_PROTECT_DISABLED'))    
  define("MLFP_BDA_AUTO_PROTECT_DISABLED", "mlfp-bda-auto-protect-disabled");  

if(!defined('MLF_RESET_NONCE'))    
  define("MLF_RESET_NONCE", "mgmlp_reset_nonce");

if(!defined('MLFP_BDA_DISPLAY_FE_IMAGES'))    
  define("MLFP_BDA_DISPLAY_FE_IMAGES", "mlfp-bda-display-fe-images");

if(!defined('MLFP_BDA_PREVENT_RIGHT_CLICK'))    
  define("MLFP_BDA_PREVENT_RIGHT_CLICK", "mlfp-bda-prevent-right-click");

if(!defined('MLFP_NO_ACCESS_PAGE_TITLE'))    
  define("MLFP_NO_ACCESS_PAGE_TITLE", "mlfp-no-access-page-id-title");

if(!defined('MLFP_NO_ACCESS_PAGE_ID'))    
  define("MLFP_NO_ACCESS_PAGE_ID", "mlfp-no-access-page-id");

add_action('wp_ajax_nopriv_clean_database', 'clean_database');
add_action('wp_ajax_clean_database', 'clean_database');			

add_action('wp_ajax_nopriv_mlfr_remove_tables', 'mlfr_remove_tables');
add_action('wp_ajax_mlfr_remove_tables', 'mlfr_remove_tables');

function mlp_reset_menu() {
  add_menu_page(esc_html__('Media Library Folders Reset','mlp-reset'), esc_html__('Media Library Folders Reset','mlp-reset'), 'manage_options', 'mlp-reset', 'mlp_reset' );
  add_submenu_page('mlp-reset', esc_html__('Display Attachment URLs','mlp-reset'), esc_html__('Display Attachment URLs','mlp-reset'), 'manage_options', 'mlpr-show-attachments', 'mlpr_show_attachments');
  add_submenu_page('mlp-reset', esc_html__('Display Folder Data','mlp-reset'), esc_html__('Display Folder Data','mlp-reset'), 'manage_options', 'mlpr-show-folders', 'mlpr_show_folders');
  add_submenu_page('mlp-reset', esc_html__('Check for Folders Without Parent IDs','mlp-reset'), esc_html__('Check for Folders Without Parent IDs','mlp-reset'), 'manage_options', 'mlpr-folders-no-ids', 'mlpr_folders_no_ids');
  add_submenu_page('mlp-reset', esc_html__('Remove Other MLF Database Tables','mlp-reset'), esc_html__('Remove Other MLF Database Tables','mlp-reset'), 'manage_options', 'mlpr-remove-tables', 'mlpr_remove_tables');
  add_submenu_page('mlp-reset', esc_html__('Reset Media Library Folders Data','mlp-reset'), esc_html__('Reset Media Library Folders Data','mlp-reset'), 'manage_options', 'data-reset', 'data_reset');
}
add_action('admin_menu', 'mlp_reset_menu');

function load_mlfr_textdomain() {
  load_plugin_textdomain('mlp-reset', false, dirname(plugin_basename(__FILE__)) . '/languages/');
}

add_action('plugins_loaded', 'load_mlfr_textdomain');

function enqueue_mlfr_admin_print_styles() {		
  wp_enqueue_style('mlf8', esc_url(MAXGALLERIA_MEDIA_LIBRARY_PLUGIN_URL . '/css/mlf.css'));				
  
}

add_action('admin_print_styles', 'enqueue_mlfr_admin_print_styles');

function mlp_reset() {

	echo "<h3>" . esc_html__('Media Library Folders Reset Instructions','mlp-reset') . "</h3>";
  echo "<h4>" . esc_html__('If you need to rescan your media library, please deactivate the Media Library Folders plugin and then click Media Library Folders Reset->Media Library Folders Data Reset and click the Reset Folder Data button to erase the folder data. Then deactivate Media Library Folders Reset and reactivate Media Library Folders which will perform a fresh scan of your database.','mlp-reset') . "</h4>";
  
}

function data_reset() {
    
  ?>
  <style>
    #ajaxloader {
      top: 0 !important;
    }

  </style>

  <h2><?php esc_html_e('Media Library Folders Data Reset','mlp-reset') ?></h2>
  
  <p><?php esc_html_e('To reset the folder data used by Media Library Folders, deactivate Media Library Folders and click the Reset Folder Data button. Once completed, reactivate Media Library Folders.','mlp-reset') ?></p>
  
  <a id="mlf-clean-database" class="button">Reset Folder Data</a>
  <div id="alwrap">
    <div style="display:none" id="ajaxloader"></div>
  </div>

  <p id="reset_message"></p>
  
	<script>
	jQuery(document).ready(function(){
    
    jQuery(document).on("click", "#mlf-clean-database", function (e) {
			      
			jQuery("#reset_message").html('');			
      jQuery("#ajaxloader").show();
      
      jQuery.ajax({
        type: "POST",
        async: true,
        data: { action: 'clean_database', nonce: '<?php echo wp_create_nonce(MLF_RESET_NONCE) ?>' },
        url : '<?php echo admin_url('admin-ajax.php') ?>',
        dataType: "html",
        success: function (data) {
					jQuery("#reset_message").html(data);						
          jQuery("#ajaxloader").hide();
					
        },
        error: function (err)
          { 
            jQuery("#ajaxloader").hide();
            alert(err.responseText);
          }
      });                
    });	
    
    
	});  
  </script>   
  

  <?php
  
}

function clean_database() {  
    global $wpdb;
    $message = '';
    
    if ( !wp_verify_nonce($_POST['nonce'], MLF_RESET_NONCE)) {
      exit(esc_html__('Missing nonce! Please refresh this page.','maxgalleria-media-library'));
    }
            
    $sql = "delete from $wpdb->options where option_name = 'mgmlp_upload_folder_name'";
    $wpdb->query($sql);
    
    $sql = "delete from $wpdb->options where option_name = 'mgmlp_upload_folder_id'";
    $wpdb->query($sql);
		
    $sql = "delete from $wpdb->options where option_name = 'mgmlp_database_checked'";
    $wpdb->query($sql);
		
    $sql = "delete from $wpdb->options where option_name = 'mgmlp_postmeta_updated'";
    $wpdb->query($sql);
				        
    $message .= esc_html__('Deleteing mgmlp_folders','mlp-reset')  . "<br>";
    
    $sql = "TRUNCATE TABLE $wpdb->prefix" . "mgmlp_folders";
    $wpdb->query($sql);
    
    $sql = "DROP TABLE $wpdb->prefix" . "mgmlp_folders";    
    $wpdb->query($sql);
		
    $sql = "select ID from $wpdb->posts where post_type = 'mgmlp_media_folder'";
		
    $rows = $wpdb->get_results($sql);
		if($rows) {
      foreach($rows as $row) {
				delete_post_meta($row->ID, '_wp_attached_file');				
			}
		}
				    
    $message .= esc_html__('Removing mgmlp_media_folder posts','mlp-reset')  . "<br>";
    $sql = "delete from $wpdb->posts where post_type = 'mgmlp_media_folder'";
    $wpdb->query($sql);
    
    $message .= esc_html__('Done. You can now reactivate Media Library Folders.','mlp-reset')  . "<br>";
    echo $message;
    
    die();
  
}

function mlfr_table_exist($table) {

  global $wpdb;

  $sql = "SHOW TABLES LIKE '{$table}'";
  
  $rows = $wpdb->get_results($sql);
  
  if($rows) 
    return true;
  else
    return false;
}

function mlpr_show_attachments () {
  global $wpdb;
  
  if(!mlfr_table_exist($wpdb->prefix . 'mgmlp_folders')) {
    echo "<p>" . esc_html__("The mgmlp_folders table does not exists. Please activate Media Library Folders to create the table.",'mlp-reset') . "</p>";
    return;
  }
  
  $sql = "select count(*) from {$wpdb->prefix}posts where post_type = 'attachment' ";
  
  $count = $wpdb->get_var($sql);  
		
  $uploads_path = wp_upload_dir();
	
  $sql = "SELECT ID, pm.meta_value as attached_file, folder_id
FROM {$wpdb->prefix}posts
LEFT JOIN {$wpdb->prefix}postmeta AS pm ON pm.post_id = {$wpdb->prefix}posts.ID
LEFT JOIN {$wpdb->prefix}mgmlp_folders ON ({$wpdb->prefix}posts.ID = {$wpdb->prefix}mgmlp_folders.post_id)
WHERE post_type = 'attachment' 
AND pm.meta_key = '_wp_attached_file'
ORDER by folder_id";
		
	echo '<h2>' . esc_html__('Attachment URLs','mlp-reset') . '</h2>';

  echo '<p>' . esc_html( __('Number of attachments','mlp-reset') . " $count") . "</p>";

  $rows = $wpdb->get_results($sql);
	?>
	<table>
		<tr>
			<th><?php esc_html_e('Attachment ID','mlp-reset'); ?></th>
			<th><?php esc_html_e('Attachment URL','mlp-reset'); ?></th>
			<th><?php esc_html_e('Folder ID','mlp-reset'); ?></th>
		</tr>	
    
  <?php  
  
  foreach($rows as $row) {
		$image_location = $uploads_path['baseurl'] . "/" . $row->attached_file;
	  ?>
		<tr>
			<td><?php echo esc_html($row->ID); ?></td>	
			<td><?php echo esc_html($image_location); ?></td>	
			<td><?php echo esc_html($row->folder_id); ?></td>	
		</tr>
    <?php				
  }    
	?>
	</table>
  <?php
}

function mlpr_show_folders() {
  global $wpdb;
  
  if(!mlfr_table_exist($wpdb->prefix . 'mgmlp_folders')) {
    echo "<p>" . esc_html__("The mgmlp_folders table does not exists. Please activate Media Library Folders to create the table.",'mlp-reset') . "</p>"; 
    return;
  }  
	
  $sql = "select count(*) from {$wpdb->prefix}posts where post_type = 'mgmlp_media_folder' ";
  
  $count = $wpdb->get_var($sql);    
	
	echo '<h2>' . esc_html__('Folder URLs','mlp-reset') . '</h2>';
  
  $upload_dir = wp_upload_dir();  
  
  $upload_dir1 = $upload_dir['basedir'];
  
  echo esc_html__('Uploads folder: ','mlp-reset') . $upload_dir1 . '<br>';
        
  echo esc_html__('Uploads URL: ','mlp-reset') . $upload_dir['baseurl'] . '<br>';
  
  echo esc_html__('Number of folders: ','mlp-reset') . $count . '<br><br>';

  $folder_table = $wpdb->prefix . MAXGALLERIA_MEDIA_LIBRARY_FOLDER_TABLE;
            	
  $sql = "select distinct ID, post_title, $folder_table.folder_id, pm.meta_value as attached_file
from $wpdb->prefix" . "posts
LEFT JOIN $folder_table ON ($wpdb->prefix" . "posts.ID = $folder_table.post_id)
LEFT JOIN {$wpdb->prefix}postmeta AS pm ON pm.post_id = {$wpdb->prefix}posts.ID
where post_type = 'mgmlp_media_folder' 
order by ID";
		  
  $rows = $wpdb->get_results($sql);
	
	?>
	<table>
		<tr>
			<th><?php esc_html_e('Folder ID','mlp-reset'); ?></th>
			<th><?php esc_html_e('Folder Name','mlp-reset'); ?></th>
			<th><?php esc_html_e('Folder URL','mlp-reset'); ?></th>
			<th><?php esc_html_e('Parent ID','mlp-reset'); ?></th>
		</tr>	
    
  <?php  
  foreach($rows as $row) {
		$image_location = $upload_dir['baseurl'] . "/" . $row->attached_file;
	  ?>
		<tr>
			<td><?php esc_html_e($row->ID); ?></td>	
			<td><?php esc_html_e($row->post_title); ?></td>	
			<td><?php esc_html_e($image_location); ?></td>	
			<td><?php esc_html_e($row->folder_id); ?></td>	
		</tr>
    <?php		
  }	
	?>
	</table>
  <br><br>
  <?php
	
  echo "<br><br>" . esc_html($folder_table) . "<br><br>";
  
  $sql = "select distinct post_id, folder_id from $folder_table order by post_id";
  
  $rows = $wpdb->get_results($sql);
  
  foreach($rows as $row) {
    echo esc_html("$row->post_id $row->folder_id") . "<br>";
  }
  		  
}

function get_parent_by_name($sub_folder) {

  global $wpdb;

  $sql = "SELECT post_id FROM {$wpdb->prefix}postmeta where meta_key = '_wp_attached_file' and `meta_value` = '$sub_folder'";

  return $wpdb->get_var($sql);
}

function add_new_folder_parent($record_id, $parent_folder) {

  global $wpdb;    
  $table = $wpdb->prefix . MAXGALLERIA_MEDIA_LIBRARY_FOLDER_TABLE;

  $new_record = array( 
    'post_id'   => $record_id, 
    'folder_id' => $parent_folder 
  );

  $wpdb->insert( $table, $new_record );

}

function mlpr_folders_no_ids() {
  
  global $wpdb;
  ?>
    <h3><?php echo esc_html__('Checking for files without folder IDs','mlp-reset') ?></h3>
  <?php
  
  $uploads_folder_id = get_option(MAXGALLERIA_MEDIA_LIBRARY_UPLOAD_FOLDER_ID );

  $folders = $wpdb->prefix . MAXGALLERIA_MEDIA_LIBRARY_FOLDER_TABLE;
  
  $sql = "SELECT ID, pm.meta_value AS attached_file FROM {$wpdb->prefix}posts
 LEFT JOIN $folders ON {$wpdb->prefix}posts.ID = {$folders}.post_id
 JOIN {$wpdb->prefix}postmeta AS pm ON pm.post_id = {$wpdb->prefix}posts.ID
 WHERE post_type = 'attachment' 
 AND folder_id IS NULL
 AND pm.meta_key = '_wp_attached_file'";
  
  $rows = $wpdb->get_results($sql);
  if($rows) {
    ?>
      <p><?php esc_html_e('The following files with missing folder IDs were found:','mlp-reset') ?></p>
      <ul>
    <?php  
    foreach($rows as $row) {
      // get the parent ID
      $folder_path = dirname($row->attached_file);
      if($folder_path != "")
        $folder_id = get_parent_by_name($folder_path);
      else
        $folder_id = $uploads_folder_id;
      if($folder_id !== NULL) {
        // if parent ID is found
        add_new_folder_parent($row->ID, $folder_id);
        echo "<li>{$row->attached_file} " . esc_html__('Fixed','mlp-reset') . "</li>" . PHP_EOL;
      } else {
        echo "<li>{$row->attached_file} " . esc_html__(' Parent folder not found.','mlp-reset') . "</li>" . PHP_EOL;        
      }  
    }
    ?>
      </ul>
    <?php
  } else {
    ?>
      <p><?php esc_html_e('No files with missing folder IDs were found.','mlp-reset') ?></p>
    <?php
  }  
}

function mlpr_remove_tables() {
  
  ?>
  
	<h3><?php esc_html_e('Remove Other MLF Database Tables & Settings','mlp-reset'); ?></h3>
  
  <p><?php esc_html_e('To remove the auxiliary tables added by Media Library Folders, in order to completely uninstall the plugin, select the tables to be remove and click the \'Remove Selected Tables\' button. All data stored in the selected tables will be lost.','mlp-reset'); ?></p>
  
  <p><?php esc_html_e('Before deleting the block direct access tables, be sure to unblock currently blocked files and deactivate Block Direct Access in Media Library Folders Settings.','mlp-reset'); ?></p>
  
  <div><input type="checkbox" id="mlfr-bda" name="bda" value="" ><label for="bda"><?php esc_html_e('Block Direct Access Table','mlp-reset'); ?></label></div>    
  <div><input type="checkbox" id="mlfr-bda-ips" name="bda-ips" value="" ><label for="bda-ips"><?php esc_html_e('Blocked IPs Table','mlp-reset'); ?></label></div>  
    
  <p>
    <a id="remove-tables" class="button-primary"><?php esc_html_e('Remove Selected Tables','mlp-reset'); ?></a>
    <img id="mlfr-ajaxloader" alt="loading GIF" src="<?php echo MAXGALLERIA_MEDIA_LIBRARY_PLUGIN_URL; ?>/images/ajax-loader.gif" style="position: relative;top: 10px;left: 10px; display:none;" width="32" height="32">    
  </p>
  <p id="return-message"></p>
  
  <script>
	jQuery(document).ready(function(){
    
    //jQuery("#remove-tables").click(function () {
    jQuery(document).on("click","#remove-tables",function(){
      
      var mlfr_bda = jQuery('#mlfr-bda:checkbox:checked').length > 0;
      var mlfr_bda_ips = jQuery('#mlfr-bda-ips:checkbox:checked').length > 0;
            
      if(mlfr_bda == false && mlfr_bda_ips == false ) {
        jQuery("#return-message").html("<?php esc_html_e('No items were selected.','mlp-reset'); ?>");
        jQuery("#mlfr-ajaxloader").hide();
        return false;
      }      

      if(confirm("<?php esc_html_e('Are you sure you want to remove the selected tables, data and settings?','mlp-reset'); ?>")) {
        
        jQuery("#return-message").html("");
        jQuery("#mlfr-ajaxloader").show();
                                
        jQuery.ajax({
          type: "POST",
          async: true,
          data: { action: "mlfr_remove_tables", 
            mlfr_bda: mlfr_bda,
            mlfr_bda_ips: mlfr_bda_ips,
            nonce: '<?php echo wp_create_nonce(MLF_RESET_NONCE); ?>' },
          url: '<?php echo admin_url( 'admin-ajax.php' ); ?>',
          dataType: "html",
          success: function (data) {
            jQuery("#return-message").html(data);            
						jQuery("#mlfr-bda").prop('checked', false);  
						jQuery("#mlfr-bda-ips").prop('checked', false);  
            jQuery("#mlfr-ajaxloader").hide();
          },
          error: function (err) { 
            jQuery("#mlfr-ajaxloader").hide();
            alert(err.responseText);
          }
        });  
                
      }     
    
	  });  
	
	});  
  </script>  

  <?php
    
}


function mlfr_remove_tables () {
      
  global $wpdb;
  $message = '';
   
    
  if ( !wp_verify_nonce( $_POST['nonce'], MLF_RESET_NONCE)) {
    exit(__('missing nonce! Please refresh this page.','mlp-reset'));
  }
  
  $mlfr_bda = false;
  $mlfr_bda_ips = false;
  
  if ((isset($_POST['mlfr_bda'])) && (strlen(trim($_POST['mlfr_bda'])) > 0))
    $mlfr_bda = trim(sanitize_text_field($_POST['mlfr_bda']));
  
  if ((isset($_POST['mlfr_bda_ips'])) && (strlen(trim($_POST['mlfr_bda_ips'])) > 0))
    $mlfr_bda_ips = trim(sanitize_text_field($_POST['mlfr_bda_ips']));
  
  if($mlfr_bda == 'true') {
    $message .= mlfr_remove_db_table("mgmlp_block_access");
    
    delete_option(MLFP_BDA);
    delete_option(MLFP_BDA_USER_ROLE);
    delete_option(MLFP_BDA_AUTO_PROTECT);
    delete_option(MLFP_BDA_AUTO_PROTECT_DISABLED);
    delete_option(MLFP_BDA_DISPLAY_FE_IMAGES);
    delete_option(MLFP_BDA_PREVENT_RIGHT_CLICK);
    delete_option(MLFP_NO_ACCESS_PAGE_TITLE);
    delete_option(MLFP_NO_ACCESS_PAGE_ID);
    
    $download_page = get_page_by_path("mlfp-download");
    
    if($download_page)
      wp_delete_post($download_page->ID);
    
    $download_template = mlf_get_theme_dir() . '/page-mlfp-download.php';
    
    if(file_exists($download_template))
      unlink($download_template);

  }  
  
  if($mlfr_bda_ips == 'true')
    $message .= "<br>" . mlfr_remove_db_table("mgmlp_blocked_ips");
  
  echo $message;
  
  die();
}

function mlfr_remove_db_table ($table) {
  
  global $wpdb;
  
  $table_name = $wpdb->prefix . $table; 
  
  $sql = "DROP TABLE $table_name";
  //error_log($sql);
  
  $result = $wpdb->query($sql);
  if ($wpdb->last_error) {
    return $wpdb->last_error;
  } else {
    return $table . esc_html__(' was deleted.','mlp-reset');
  }

}

function mlf_get_theme_dir() {
  if(is_child_theme())
    return WP_CONTENT_DIR . '/themes/' . get_stylesheet();
  else
    return WP_CONTENT_DIR . '/themes/' . get_template();
}


