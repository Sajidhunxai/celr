<style>
  .mlfp-download-message {
    color: white;
    background: red;
    font-size: 16px;
    padding: 15px;
    position: absolute;
    top: 50%;
    left: 50%;
    -ms-transform: translateX(-50%) translateY(-50%);
    -webkit-transform: translate(-50%,-50%);
    transform: translate(-50%,-50%);    
  }
  .mlfp-download-message.success {
    background: #00CCFF;    
  }
</style>

<?php
if(!defined('MAXGALLERIA_MEDIA_LIBRARY_BLOCK_ACCESS_TABLE'))
  define("MAXGALLERIA_MEDIA_LIBRARY_BLOCK_ACCESS_TABLE", "mgmlp_block_access"); 

if(!defined('BLOCKED_IPS_TABLE'))
  define("BLOCKED_IPS_TABLE","mgmlp_blocked_ips");

$uploads_dir = wp_upload_dir();

$currnet_date_time = strtotime("now");

$not_found = false;

// check for blocked IP address
$ip_address = $_SERVER['REMOTE_ADDR'];
// test ip
//$ip_address = '171.93.72.77';

$ip_table = $wpdb->prefix . BLOCKED_IPS_TABLE;    
$prepare_sql = "select ip_id from $ip_table where address = '%s'";
$sql = $wpdb->prepare($prepare_sql, $ip_address);

$row = $wpdb->get_row($sql);
if($row) {
  $no_access_page_id = get_option("mlfp-no-access-page-id", 0);
  if($no_access_page_id != 0) {
    wp_redirect(get_permalink($no_access_page_id));
  } else { // display 404 page if the no access page has been added
    global $wp_query;
    $wp_query->set_404();
    status_header(404);
    get_template_part(404); 
  }  
  exit();
}

if (isset($_GET['download'])) {
  $hash_id = sanitize_text_field($_GET['download']);
  //$hash_id = filter_input (INPUT_GET, 'download', FILTER_SANITIZE_STRING);
  
  if(!empty($hash_id)) {
    global $wpdb;
    $table = $wpdb->prefix . MAXGALLERIA_MEDIA_LIBRARY_BLOCK_ACCESS_TABLE;    
    
    $prepare_sql = "select attachment_id, count, download_limit, expiration_date, pm.meta_value AS attached_file 
from $table as ba
JOIN $wpdb->postmeta AS pm ON pm.post_id = ba.attachment_id
where hash_id = '%s'
AND pm.meta_key = '_wp_attached_file'";
    
    $sql = $wpdb->prepare($prepare_sql, $hash_id);
    $row = $wpdb->get_row($sql);
    if($row) {
      $expiration_date = strtotime($row->expiration_date);
      if(($row->download_limit == null || $row->download_limit == 0) || $row->download_limit > $row->count) {
        if($row->expiration_date == null || $row->expiration_date == '0000-00-00' || $expiration_date > $currnet_date_time) {
          echo '<p class="mlfp-download-message success">Download successful.</p>';
          $download_file = $uploads_dir['path'] . '/' . $row->attached_file;

          ob_clean();
          
          $content_type = mime_content_type($download_file);
          $fp = @fopen($download_file, 'rb'); 
          $fsize = filesize($download_file);
          $path_parts = pathinfo($download_file);

          if (strstr($_SERVER['HTTP_USER_AGENT'], "MSIE")) {
            header('Content-Type: "$content_type"');
            header('Content-Disposition: attachment; filename="'.$path_parts["basename"].'"');
            header('Expires: 0');
            header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
            header("Content-Transfer-Encoding: binary");
            header('Pragma: public');
            header("Content-Length: ".filesize(trim($download_file)));
          } else {
            header('Content-Type: "$content_type"');
            header('Content-Disposition: attachment; filename="'.$path_parts["basename"].'"');
            header("Content-Transfer-Encoding: binary");
            header('Expires: 0');
            header('Pragma: no-cache');
            header("Content-Length: ".filesize(trim($download_file)));
          }
          ob_end_clean(); 
          fpassthru($fp);
          fclose($fp);

          $data = array('count' => $row->count+1);
          $where = array('attachment_id' => $row->attachment_id);        
          $wpdb->update($table, $data, $where);
          } else {
            echo '<p class="mlfp-download-message">This download link has expired</p>' , PHP_EOL;
            $not_found = true;
          }
        } else {        
          echo '<p class="mlfp-download-message">The download limit for this file has already been reached.</p>' , PHP_EOL;
          $not_found = true;
        }
    } else {
      $not_found = true;      
    }
    if($not_found) {
      $no_access_page_id = get_option("mlfp-no-access-page-id", 0);
      wp_redirect(get_permalink($no_access_page_id));
    }        
  }
}
exit();
?>