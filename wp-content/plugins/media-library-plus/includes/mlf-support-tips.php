<h4><?php esc_html_e('Folder Tree Not Loading', 'maxgalleria-media-library' ); ?></h4>

<p><?php esc_html_e('Usually a Java Script error is displayed when there is a problem loading the folder tree. If this is not the case, then try a different browser, such as Chrome, as some browsers cannot handle a large number of folders in the folder tree. When there is a Java Script error, users who report this issue can usually fix it by using the Media Library Folders Pro Reset plugin that comes with Media Library Folders Pro.', 'maxgalleria-media-library' ); ?></p>
<ul>
  <li><?php esc_html_e('1. Deactivate Media Library Folders and activate Media Library Folders Reset. Open the Media Library Folders Data Reset page and click the Reset Folder Data button.', 'maxgalleria-media-library' ); ?></li>
  <li><?php esc_html_e('2. After the process has finished, reactivate Media Library Folders. This is a necessary step. It will do a fresh scan of your media library database and no changes will be made to the files or folders on your site.', 'maxgalleria-media-library' ); ?></li>
</ul>

<p><?php esc_html_e('Note that resetting the folder data is not a cure for all Media Library Folders problems; it is specifically used when the folder tree does not load.', 'maxgalleria-media-library' ); ?></p>

<p><?php esc_html_e('In rare cases, a site may have implemented a Content Security Policy that is preventing the loading of Media Library Folder\'s Javascript files. To test for this condition, go to ', 'maxgalleria-media-library' ) ?><a href="https://observatory.mozilla.org/" target="_blank">observatory.mozilla.org</a><?php esc_html_e(' enter the address of your site and scan your site. From the scanning results, check to see if there is anything under \'Content Security Policy\' If there is nothing under Content Security Policy, then this is not an issue with your site. For those sites that use a Content Security Policy normally the policy would include: ', 'maxgalleria-media-library' ) ?><em>script-src 'self'</em><?php esc_html_e(' to load local Javascript file.', 'maxgalleria-media-library' ) ?></p>

<h4><?php esc_html_e('How to Unhide a Hidden Folder', 'maxgalleria-media-library' ); ?></h4>

<ul>
  <li><?php esc_html_e('1. Go to the hidden folder via your cPanel or FTP and remove the file ‘mlpp-hidden', 'maxgalleria-media-library' ); ?>.</li>
  <li><?php esc_html_e('2. In the Media Library Folders Menu, click the Check for New folders link. This will add the folder back into Media Library Folders.', 'maxgalleria-media-library' ); ?></li>
  <li><?php esc_html_e('3. Visit the unhidden folder in Media Library Folders and click the Sync button to add contents of the folder. Before doing this, check to see that there are no thumbnail images in the current folder since these will be regenerated automatically; these usually have file names such as image-name-150×150.jpg, etc.', 'maxgalleria-media-library' ); ?></li>
  <li><?php esc_html_e('4. Repeat step 3 for each sub folder.', 'maxgalleria-media-library' ); ?></li>
</ul>

<h4><?php esc_html_e('How to Delete a Folder?', 'maxgalleria-media-library' ); ?></h4>

<p><?php esc_html_e('To delete a folder, right click (Ctrl-click with Macs) on a folder. A popup menu will appear with the options, ‘Delete this folder?’ and ‘Hide this folder?’. Click the delete option. The folder has to be empty in order to delete it. If you receive a message that the folder is not empty, use the sync function to display files that are still present in the folder.', 'maxgalleria-media-library' ); ?></p`>
<p><?php esc_html_e('In some cases if that the folder is empty and Media Library Folders is unable to delete it, delete the folder from the server either by the site’s file manager or by FTP and then delete it in Media Library Folders.', 'maxgalleria-media-library' ); ?></p>

<h4><?php esc_html_e('Folders and images added to the site by FTP are not showing up in Media Library Folders', 'maxgalleria-media-library' ); ?></h4>

<p><?php esc_html_e('Media Library Folders does not work like the file manager on your computer. It only display images and folders that have been added to the Media Library database. To display new folders that have not been added through the Media Library Folders you can click the Check for new folders option in the  Media Library Folders submenu in the Wordpress Dashboard. If you allow Wordpress to store images by year and month folders, then you should click the option once each month to add these auto-generated folders.', 'maxgalleria-media-library' ); ?></p`>

<p><?php esc_html_e('To add images that were upload to the site via the cPanel or by FTP, navigate to the folder containing the images in  Media Library Folders and click the Sync button. This will scan the folder looking images not currently found in the Media Library for that folder. The Sync function only scans the current folder. If there are subfolders, you will need to individually sync them.', 'maxgalleria-media-library' ); ?></p>

<h4><?php esc_html_e('Folders Loads Indefinitely', 'maxgalleria-media-library' ); ?></h4>

<p><?php esc_html_e('This happens when a parent folder is missing from the folder data. To fix this you will need to perform a reset of the Media Library Folders database. To do this, deactivate Media Library Folders and activate Media Library Folders Reset and select the Reset Database option. Once the reset has completed, reactivate Media Library Folders and it will do a fresh scan of the Media Library data.', 'maxgalleria-media-library' ); ?></p>

<h4><?php esc_html_e('Unable to Insert files from Media Library Folders into Posts or Pages', 'maxgalleria-media-library' ); ?></h4>

<p><?php esc_html_e('For inserting images and files into posts and pages you will have to use the existing Media Library. The ability to insert items from the Media Library Folders user interface is only available in', 'maxgalleria-media-library' ); ?> <a href='http://www.maxgalleria.com/downloads/media-library-plus-pro/?utm_source=wordpress&utm_medium=mlfp&utm_content=mlpp&utm_campaign=repo'>Media Library Folders Pro</a>. <?php esc_html_e('This does not mean you cannot insert files added to Media Library Folders into any Wordpress posts or pages. Media Library Folders adds a folder user interface and file operations to the existing media library and it does not add a second media library. Since all the images are in the same media library there is no obstacle to inserting them anywhere Wordpress allows media files to be inserted. There is just no folder tree available in the media library insert window for locating images in a particular folder. We chose to include the folder tree for inserting images in posts and pages in the Pro version along with other features in order to fund the cost of providing free technical support and continued development of the plugin.', 'maxgalleria-media-library' ); ?></p>

<h4><?php esc_html_e('Unable to Update Media Library Folders Reset', 'maxgalleria-media-library' ); ?></h4>

<p><?php esc_html_e('Media Library Folders Reset is maintenance and diagnostic plugin that is included with Media Library Folders. It automatically updates when Media Library Folders is updated. There is no need to updated it  separately. Users should leave the reset plugin deactivated until it is needed in order to avoid accidentally deleting your site\'s folder data.', 'maxgalleria-media-library' ); ?></p>

<h4><?php esc_html_e('Images Not Found After Changing the Location of Uploads Folder', 'maxgalleria-media-library' ); ?></h4>

<p><?php esc_html_e('If you change the location of the uploads folder, your existing files and images will not be moved to the new location. You will need to delete them from media library and upload them again. Also you will need to perform a reset of the Media Library Folders database. To do this, deactivate Media Library Folders and activate Media Library Folders Reset and select the Reset Database option. Once the reset has completed, reactivate Media Library Folders and it will do a fresh scan of the Media Library data.', 'maxgalleria-media-library' ); ?></p>

<h4><?php esc_html_e('Difficulties Uploading or Dragging and Dropping a Large Number of Files', 'maxgalleria-media-library' ); ?></h4>

<p><?php esc_html_e('Limitations on web server processing time may cause dragging and dropping a large number of files to fail. An error is generated when it takes to longer then 30 seconds to move, copy or upload files. This time limitation can be increased by changing the max_execution_time setting in your site\'s php.ini file.', 'maxgalleria-media-library' ); ?></p>

<h4><?php esc_html_e('How to Delete a Folder?', 'maxgalleria-media-library' ); ?></h4>

<p><?php esc_html_e('To delete a folder, right click (Ctrl-click with Macs) on a folder. A popup menu will appear with the options, \'Delete this folder?\' and \'Hide this folder?\'. Click the delete option.', 'maxgalleria-media-library' ); ?></p>

<h4><?php esc_html_e('Fatal error: Maximum execution time exceeded ', 'maxgalleria-media-library' ); ?></h4>

<p><?php esc_html_e('The Maximum execution time error takes place when moving, syncing or uploading too many files at one time. The web site’s server has a setting for how long it can be busy with a task. Depending on your server, size of files and the transmission speed of your internet, you may need to reduce the number of files you upload or move at one time.', 'maxgalleria-media-library' ); ?></p>
<p><?php esc_html_e('It is possible to change the maximum execution time either with a plugin such as ', 'maxgalleria-media-library' ); ?><a href=“http://wordpress.org/plugins/wp-maximum-execution-time-exceeded/” target=“_blank”>WP Maximum Execution Time Exceeded</a><?php esc_html_e(' or by editing your site’s .htaccess file and adding this line:', 'maxgalleria-media-library' ); ?></p>
<p><?php esc_html_e('php_value max_execution_time 300', 'maxgalleria-media-library' ); ?></p>
<p><?php esc_html_e('Which will raise the maximum execution time to five minutes.', 'maxgalleria-media-library' ); ?></p>

<h4><?php esc_html_e('How to Upload Multiple Files', 'maxgalleria-media-library' ); ?></h4>
<p><?php esc_html_e('Users can upload multiple files by using drag and drop. When the Add Files button is click it revels the file upload area either single or multiple files can be highlight can be dragged from you computer’s file manager and dropped into the file uploads areas.', 'maxgalleria-media-library' ); ?></p>

<h4><?php esc_html_e('Cannot Rename or Move a Folder', 'maxgalleria-media-library' ); ?></h4>
<p><?php esc_html_e('Because most images and files in the media library have corresponding links embedded in site’s posts and pages, Media Library Folders does not allow folders to be rename or moved in order to prevent breaking these links. Rather, to rename or move a folder, one needs to create a new folder and move the files from the old folder to the new. During the move process, Media Library Folders will scan the sites standard posts and pages for any links matching the old address of the images or files and update them to the new address.', 'maxgalleria-media-library' ); ?></p>
