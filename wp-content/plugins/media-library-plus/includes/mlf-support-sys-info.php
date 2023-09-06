<?php
  $theme = wp_get_theme();
  $browser = $this->get_browser();
?>

<div id="support-info">
  <h4><?php esc_html_e('You may be asked to provide the information below to help troubleshoot your issue.', 'maxgalleria-media-library') ?></h4>
  <textarea class="system-info" readonly="readonly" wrap="off">
----- Begin System Info -----

WordPress Version:      <?php echo esc_html(get_bloginfo('version') . "\n"); ?>
PHP Version:            <?php echo esc_html(PHP_VERSION . "\n"); ?>
PHP OS:                 <?php echo esc_html(PHP_OS . "\n"); ?>
MySQL Version:          <?php 
														global $wpdb;
														$mysql_version = $wpdb->db_version();

														echo esc_html($mysql_version . "\n"); 
?>
Web Server:             <?php echo esc_html($_SERVER['SERVER_SOFTWARE'] . "\n"); ?>

WordPress URL:          <?php echo esc_html(get_bloginfo('wpurl') . "\n"); ?>
Home URL:               <?php echo esc_html(get_bloginfo('url') . "\n"); ?>
WP-contents folder:     <?php echo esc_html(WP_CONTENT_DIR . "\n\n");  ?>
<?php 
  $upload_dir = wp_upload_dir();    
?>
Uploads Path:           <?php echo esc_html($upload_dir['path'] . "\n"); ?>
Uploads URL:            <?php echo esc_html($upload_dir['url'] . "\n"); ?>
Uploads Sub Directory:  <?php echo esc_html($upload_dir['subdir'] . "\n"); ?>
Uploads Base Directory: <?php echo esc_html($upload_dir['basedir'] . "\n"); ?>
Uploads Base URL:       <?php echo esc_html($upload_dir['baseurl'] . "\n"); ?>

PHP cURL Support:       <?php echo esc_html((function_exists('curl_init')) ? 'Yes' . "\n" : 'No' . "\n"); ?>
PHP GD Support:         <?php echo esc_html((function_exists('gd_info')) ? 'Yes' . "\n" : 'No' . "\n"); ?>
PHP Memory Limit:       <?php echo esc_html(ini_get('memory_limit') . "\n"); ?>
PHP Post Max Size:      <?php echo esc_html(ini_get('post_max_size') . "\n"); ?>
PHP Upload Max Size:    <?php echo esc_html(ini_get('upload_max_filesize') . "\n"); ?>

WP_DEBUG:               <?php echo esc_html(defined('WP_DEBUG') ? WP_DEBUG ? 'Enabled' . "\n" : 'Disabled' . "\n" : 'Not set' . "\n") ?>
Multi-Site Active:      <?php echo esc_html(is_multisite() ? 'Yes' . "\n" : 'No' . "\n") ?>

Operating System:       <?php echo esc_html($browser['platform'] . "\n"); ?>
Browser:                <?php echo esc_html($browser['name'] . ' ' . $browser['version'] . "\n"); ?>
User Agent:             <?php echo esc_html($browser['user_agent'] . "\n"); ?>

Active Theme:
- <?php echo esc_html($theme->get('Name')) ?> <?php echo esc_html($theme->get('Version') . "\n"); ?>
  <?php echo esc_html($theme->get('ThemeURI') . "\n"); ?>

Active Plugins:
<?php
$plugins = get_plugins();
$active_plugins = get_option('active_plugins', array());

foreach ($plugins as $plugin_path => $plugin) {
	
	// Only show active plugins
	if (in_array($plugin_path, $active_plugins)) {
		echo esc_html('- ' . $plugin['Name'] . ' ' . $plugin['Version'] . "\n");
	
		if (isset($plugin['PluginURI'])) {
			echo esc_html('  ' . $plugin['PluginURI'] . "\n");
		}
		
		echo esc_html("\n");
	}
}
?>
----- End System Info -----
  </textarea>

</div>


