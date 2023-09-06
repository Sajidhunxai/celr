
<div id="wp-media-grid" class="wrap">
  <script>
    // deterime what browser we are using
    var doc = document.documentElement;
    doc.setAttribute('data-useragent', navigator.userAgent);
  </script>
  
  
  <div class="mlfp-container">
    
    <?php echo $this->display_mlfp_header() ?>
    
    <h1 id="mlfp-page-title"><?php esc_html_e('Folders & Files', 'maxgalleria-media-library' ); ?></h1>
    <div class="mlfp-tab-section">
      
 <?php     
//Get the active tab from the $_GET param
  $default_tab = null;
  $tab = sanitize_textarea_field(isset($_GET['tab']) ? $_GET['tab'] : $default_tab);

  ?>
  <!-- Our admin page content should all be inside .wrap -->
  <div class="wrap">
    <nav class="nav-tab-wrapper">
      <a href="?page=mlf-folders8" class="nav-tab <?php if($tab===null):?>nav-tab-active<?php endif; ?>">Library</a>
      <a id="mlfp-help"><img id="mlfp-help-icon" src="<?php echo esc_url(MAXGALLERIA_MEDIA_LIBRARY_PLUGIN_URL . "/images/mlfp-help.png") ?>" alt="help icon" width:="28" height="28"></a>
    </nav>

    <div id="mlfp-help-panel-container">
    <div id="mlfp-help-panel" style="display:none">
      <div id="mlfp-help-panel-inner" >
        <div><?php esc_html_e('Library Help', 'maxgalleria-media-library' ); ?></div>
        
        <p><?php esc_html_e('Media Library Folders provides additional functionality to the standard Wordpress media library. The media library is basically a database of files that have been imported into Wordpress. It is not a live picture of files on your server. Before files can be viewed in the media library they have to be process and added to the database. New folders created manually on the server also have to be added to the database before that can be used by Media Library Folders.', 'maxgalleria-media-library' ); ?></p>
        
        <p><strong><?php esc_html_e('Basic Operations', 'maxgalleria-media-library' ); ?></strong></p>
        
        <p><i class="fa-solid fa-folder-plus mlf-help-icon"></i> <?php esc_html_e('Add Folder, Creates a new folder. Folder names cannot contain spaces.', 'maxgalleria-media-library' ); ?></p>
        <p><i class="fa-solid fa-upload mlf-help-icon"></i> <?php esc_html_e('Upload Files, Opens the file upload area. Here you can upload individual files using the browse button to find and select a file on your computer. Multiple files can be uploaded using drag and drop; open your file manger, select highlight multiple files, drag them to the Drag and Drop box on this page and release them. The files will be uploaded to the currently selected folder. To close the file upload area, click the Upload Files icon.', 'maxgalleria-media-library' ); ?></p>
        <p><i class="fa-solid fa-arrows-rotate mlf-help-icon"></i> <?php esc_html_e('Refresh Folders, Media Library Folders periodically checks for new folders. Clicking this icon will immediate check and add new folders to the folder tree.', 'maxgalleria-media-library' ); ?></p>
        <p><i class="fa-solid fa-file-import mlf-help-icon"></i> <?php esc_html_e('Move Files, By default, files can be moved from one folder to another by dragging a file to a folder. Note that the mouse pointer is slightly offset so be sure to place the mouse, not the image, over the destination folder before releasing. Media files that are embedded in standard posts and pages will be updated when a file is moved. However this some themes and page builders may store embedded links in a non standard format. Media Library Folders has attentional code for handling such non standard formats but it is a good practices on a new install of Media Library Folders to test the updating of embedded links by move a file and checking the page where the files is embedded to see that it was correctly update to the new link.', 'maxgalleria-media-library' ); ?></p>
        <p><i class="fa-solid fa-clone mlf-help-icon"></i> <?php esc_html_e('Copy Files, It is possible to switch the drag and drop operation from moving files to copying files but clicking on the copy icon. To switch back to moving files, click the move icon.', 'maxgalleria-media-library' ); ?></p>
        <p><i class="fa-solid fa-calendar-days mlf-help-icon"></i> <?php esc_html_e('Order by Date, Files can be sorted by the date they were added to Wordpress or by their title. Clicking on the Calendar icon will order the files by date.', 'maxgalleria-media-library' ); ?></p>
        <p><i class="fa-solid fa-file-image mlf-help-icon"></i> <?php esc_html_e('Sort by Title, Order files by their title. The title of a file can be changed by clicking on an image which will open the file\'s edit media page in a new tab.', 'maxgalleria-media-library' ); ?></p>
        <p><i class="fa-solid fa-arrows-up-down mlf-help-icon"></i> <?php esc_html_e('Reverse Order, Reverses the order of files displayed.', 'maxgalleria-media-library' ); ?></p>
        <p><i class="fas fa-bolt mlf-help-icon"></i> Sync, <?php esc_html_e('The sync function scans the current folder and adds any new files or folders it finds. Files uploaded to a folder by FTP can be added to the media library using this feature.', 'maxgalleria-media-library' ); ?></p>        
        <p><i class="fa-solid fa-pen mlf-help-icon"></i> <?php esc_html_e('Rename a File, To rename a file, check the check box of the file to be renamed and click the Rename icon. This action will display the Rename The Selected File box where you can enter a new name for the file. After entering the new file name, click the Rename File button. Note the the file\'s extension cannot be changes and if multiple files are selected, only the name of the first file selected can be changed. Folders cannot be renamed as that would break embedded links in posts and page; instead renaming a folder, create a new folder with the desired name and move the files to the new folder which will update any embedded links to the new location.', 'maxgalleria-media-library' ); ?></p>
        <p><i class="fa-solid fa-images mlf-help-icon"></i> <?php esc_html_e('Add Images to MaxGalleria Gallery, The icon for adding images to a MaxGalleria gallery is only visible when MaxGalleria or MaxGalleria Pro is installed and activated. This feature allows one to select one or more images in a folder and add them to an existing MaxGalleria gallery. To add images, select the images to add by checking their checkboxes Then click the image gallery icon. In the Add images to MaxGalleria Gallery box the appears, select the name of the gallery where to add the images and click the Add Images button. This function only works for images and not videos.', 'maxgalleria-media-library' ); ?></p>
        <p><i class="far fa-object-group mlf-help-icon"></i> <?php esc_html_e('Regenerate Thumbnails, To regenerate thumbnails for one or more files, check the check boxes of the images to regenerate and then click the regenerate thumbnails icon.', 'maxgalleria-media-library' ); ?></p>
        <p><i class="fas fa-trash mlf-help-icon"></i> <?php esc_html_e('Delete Files, To delete one or more files, check the check boxes of the files to delete and then click the delete files icon.', 'maxgalleria-media-library' ); ?></p>
        <p><?php esc_html_e('Search/Find, To find a particular file or folder by typing in a file or folder name and pressing Enter, or by clicking the Find button. The search results page will display files that match the search text and clicking on an item on the search results page will open the related folder and display its contents.', 'maxgalleria-media-library' ); ?></p>
        <p><?php esc_html_e('Edit a file, To edit a media file\'s data, such as it title, alt text, caption or description, click on an image and the file\'s edit media page will open in a new browser tab.', 'maxgalleria-media-library' ); ?></p>
        <p><strong><?php esc_html_e('Folder Operations', 'maxgalleria-media-library' ); ?></strong></p>         
        <p><?php esc_html_e('Hide a Folder,  To hide a folder, that is to remove it from the folder tree and the database but not from the server, right click on a folder you want to hide (CTRL-click on a Mac). Choose the option “Hide this folder?”. Clicking on this option will remove the folder, its sub folders and files from the Media Library but not from your server. This action will also write a file to the folder with the name “mlpp-hidden”. As long as that file is present, Media Library Plus will skip over this folder when checking for folders and files to add to the Media Library.', 'maxgalleria-media-library' ); ?></p> 
        <p><?php esc_html_e('Delete a Folder, Right click on a folder you want to delete (CTRL-click on a Mac). This action will display a popup menu. Click “Delete this Folder?” and if the folder is empty, then it will be removed. If you get a message that the folder is not empty, view that folder and click the Sync button to add any files on the server that are not in the media library. Then you can remove the files and delete the folder as explained above.', 'maxgalleria-media-library' ); ?></p> 
        <p><?php esc_html_e('Select/Deselect All Files, to select or deselect at the files currently displayed, check the Select All Files checkbox.', 'maxgalleria-media-library'); ?></p>
        <p><?php esc_html_e('Select a Group of Files, To select a group of adjacent files, check the checkbox of the first file in the group and the while holding down the Shift key, click the last file in the group.', 'maxgalleria-media-library' ); ?></p>
        <p><strong><?php esc_html_e("Bulk Actions","maxgalleria-media-library") ?></strong></p>
        <p><?php esc_html_e("To use a bulk action, select an action from the dropdown list and click the Apply button.","maxgalleria-media-library") ?></p>
        <p><?php esc_html_e("Block/Unblock File Access, When the Block Direct Access feature is turned on, files in Media Library Folders Pro can be protected from direct access by checking the check boxes of one or more file's, selecting Block/Unblock File Access from the Bulk Actions dropdown list and clicking the Apply button. This action will move the file and its thumbnail files from the current folder to a folder under the protected-content folder though it still appears in the current folder. Links to media files embedded in posts and pages will be updated with the new file address. If a file is protected, it will be displayed surrounded by a red border in the media library and in Media Library Folders Pro.","maxgalleria-media-library") ?></p>
        <p><?php esc_html_e("In the standard Wordpress media library and media library popups, blocked files can be displayed by clicking the Display Blocked Files button.","maxgalleria-media-library") ?></p>
        <p><?php esc_html_e("Note that the action of displaying blocked files does not preform as fast as displaying unblocked as this is a manual process. The same applies to blocking or unblocking a target number of files at one time. It usually works better to block or unblock files in small batches rather than in one large group.","maxgalleria-media-library") ?></p>
        <p><?php esc_html_e("The same process can be used to unblock files. Check the check box of one or more protected files, select Block/Unblock File Access from the Bulk Actions dropdown list and clicking the Apply button which will move the selected files back to the current folder and links to those files embedded in posts and pages will be updated.","maxgalleria-media-library") ?></p>
        <p><?php esc_html_e("Configure Blocked Download Links, Links to download block files can be configured and generated via the Configure Blocked Download Links popup window. To view select one or more blocked files, up to twenty at at time, select Configure Blocked Download Links from the bulk actions dropdown list and click the Apply button. Any none blocked files selected will not appear in the list of files in the popup.","maxgalleria-media-library") ?></p>
        <p><?php esc_html_e("By default blocked file have a zero limit, meaning there is no downloading limit and no expiration date. A count of the number of times a file has been downloaded is also displayed. For any file a limit to the number of downloads and/or an expiration date can be entered. Or to enter the same download limit and expiration date for all files displayed in the list, use the bulk configure fields at the top of the list and click the Copy Fields button.","maxgalleria-media-library") ?></p>
        <p><?php esc_html_e("To copy a file's download link to the clipboard, chick the Copy Link button. To save the download limits and expiration dates, click the Save & Close button. To close without saving, click the close icon in the top right corner of the popup.","maxgalleria-media-library") ?></p>
        <p><?php esc_html_e("Note, for the download links to work, Media Library Folders Pro creates a new page on the site,  MLFP Download and copies a template file, page-mlfp-download.php, to the your site's current theme folder. The MLFP Download page should not be deleted. And if the theme is updated, be sure it contains a copy of the page-mlfp-download.php file which can be found in the Media Library Folders Pro plugin folder, media-library-plus-pro.","maxgalleria-media-library") ?></p>
           
      </div>
    </div>
    </div>
    
    <div class="tab-content">
        <?php $this->media_library(); ?> 
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


