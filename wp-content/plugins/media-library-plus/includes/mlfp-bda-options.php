    <?php
      $bdp_checked = ($this->bda == 'on') ? 'checked' : '';
      $bdp_listing = (get_option(MLFP_BDA_DIR_LISTING, 'off') == 'on') ? 'checked' : '';
      $bdp_hotlinking = (get_option(MLFP_BDA_HOTLINKING, 'off') == 'on') ? 'checked' : '';
      $bdp_autoprotect = (get_option(MLFP_BDA_AUTO_PROTECT, 'off') == 'on') ? 'checked' : '';
      $display_fe_protected_images = (get_option(MLFP_BDA_DISPLAY_FE_IMAGES, 'off') == 'on') ? 'checked' : '';
      $prevent_right_click = (get_option(MLFP_BDA_PREVENT_RIGHT_CLICK, 'off') == 'on') ? 'checked' : '';
      $this->bda_user_role = get_option(MLFP_BDA_USER_ROLE, 'admins');      
      
      $no_access_page_id = get_option(MLFP_NO_ACCESS_PAGE_ID, 0);
      $no_access_page_name = get_option(MLFP_NO_ACCESS_PAGE_TITLE, '');
            
      if($this->bda == 'on')
        $status =  esc_html__('Block Direct Access is on', 'maxgalleria-media-library' );
      else
        $status =  esc_html__('Block Direct Access is off', 'maxgalleria-media-library' );
    ?>

    <div id="bda-warp">
      
      <div id="albdawrap">
        <div style="display:none" id="ajaxloaderbda"></div>
      </div>

      <p id="bda-message"><?php echo esc_html($status) ?></p>  
            
      <div class="bda-settings-row">
        
        <div class='bda-column'>
          <input type="hidden" id="ip-changes" value="false">

          <p>
            <label class="switch"><input id="activate-mlfp-bdp" class="bda-option" type="checkbox" <?php echo esc_attr($bdp_checked) ?> ><span class="slider round"></span></label>&nbsp;<span class="space-label"><?php esc_html_e('Activate Block Direct Access', 'maxgalleria-media-library' ); ?></span>
          </p>                    
          <p>
            <label class="switch"><input id="disable-listing" class="bda-option" type="checkbox" <?php echo esc_attr($bdp_listing) ?> ><span class="slider round"></span></label>&nbsp;<span class="space-label"><?php esc_html_e('Prevent Directory Listing', 'maxgalleria-media-library' ); ?></span>
          </p>
          <p>
            <label class="switch"><input id="disable-hotlinking" class="bda-option" type="checkbox" <?php echo esc_attr($bdp_hotlinking) ?> ><span class="slider round"></span></label>&nbsp;<span class="space-label"><?php esc_html_e('Prevent Hotlinking', 'maxgalleria-media-library' ); ?></span>
          </p>
          <p>
            <label class="switch"><input id="auto-protect" class="bda-option" type="checkbox" <?php echo esc_attr($bdp_autoprotect) ?> ><span class="slider round"></span></label>&nbsp;<span class="space-label"><?php esc_html_e('Auto Protect New Uploads', 'maxgalleria-media-library' ); ?></span>
          </p>
          <p>
            <label class="switch"><input id="display_fe_protected_images" class="bda-option" type="checkbox" <?php echo esc_attr($display_fe_protected_images) ?> ><span class="slider round"></span></label>&nbsp;<span class="space-label"><?php esc_html_e('Display Protected Images on the Front End of the Site', 'maxgalleria-media-library' ); ?> <sup>*</sup></span>
          </p>
          <p>
            <label class="switch"><input id="prevent_right_click" class="bda-option" type="checkbox" <?php echo esc_attr($prevent_right_click) ?> ><span class="slider round"></span></label>&nbsp;<span class="space-label"><?php esc_html_e('Disable Image Copy and Right Click', 'maxgalleria-media-library' ); ?> <sup>*</sup></span>
          </p>
          <p>* <?php esc_html_e('Many not work in all browsers, such as Safari.', 'maxgalleria-media-library' ); ?></p>

          <?php $admins_selected = ($this->bda_user_role == "admins") ? 'selected' : '' ?>
          <?php $authors_selected = ($this->bda_user_role == "authors") ? 'selected' : '' ?>

          <p><?php esc_html_e('User roles who can view protected files:','maxgalleria-media-library'); ?>
            <select id="bdp-user-roles" class="bda-option">
              <option value="admins" <?php echo esc_attr($admins_selected) ?>><?php esc_html_e('Administrators','maxgalleria-media-library'); ?></option>
              <option value="authors" <?php echo esc_attr($authors_selected) ?>><?php esc_html_e('File Authors','maxgalleria-media-library'); ?></option>
            </select>
          </p>

          <p>
            <a class="button-primary" id="update-bda-settings"><?php esc_html_e('Update Settings','maxgalleria-media-library'); ?></a>			
          </p>

        
        </div><!--bda-column-->
        <div class='bda-column'>
          <fieldset id="bda-blocked-ids">
            <legend><?php esc_html_e('Block Access to Private Download Links', 'maxgalleria-media-library' ); ?></legend>
            
            <div id="ip-section">
              
              <div class="bda-settings-row">
                <?php $ip_addresses = $this->get_blocked_ips(); ?>
                <div class="bda-column-left">
                  <select id="blocked-ip-list" size="10" multiple>
                    <?php echo $ip_addresses ?>
                  </select>  
                </div>  
                
                <div class="bda-column-right">
                  <div id="ip-row-1">
                    <label id="ip-label"><?php esc_html_e('IP Address', 'maxgalleria-media-library' ); ?></label>
                    <input type="text" id="new-block-ip" value="">
                    <a id="add-new-ip" class="button"><?php esc_html_e('Add IP Address', 'maxgalleria-media-library' ); ?></a>
                  </div>
                  
                  <div id="ip-row-2">
                    <a id="remove-ips" class="button"><?php esc_html_e('Remove Selected IP Addresses', 'maxgalleria-media-library' ); ?></a>                                      
                  </div>
                  
                </div>  
                                
              </div>  
              
            </div>

          </fieldset>
          
          <?php $pages = $this->get_all_pages() ?>
          <div id="custom-404-page-section">
            <p id="no-access-title">Custom No Access Page</p>
            <select id="selectPage" style="width: 200px;">
               <option value="0">-- Select Page --</option>
               <?php 
                 foreach($pages as $page)
                 echo '<option value="' . esc_attr($page->ID) .'">' . esc_html($page->post_title) .'</option>';
               ?>
            </select>
            <input type="button" value="Seleted Page" id="select-page-btn" class="button">
            <div id="result"><?php esc_html_e('Page', 'maxgalleria-media-library' ); ?>: <?php echo $no_access_page_name ?></div> 
            <input type="hidden" id="custom-404-page-id" value="<?php echo esc_attr($no_access_page_id) ?>">
            <input type="hidden" id="custom-404-page-name" value="<?php echo esc_attr($no_access_page_name) ?>">
          </div>
                                
        </div><!--bda-column-->
      
      </div><!--bda-settings-row-->
      
      <div>
              
        <hr>      

        <div id="albdarwrap">
          <div style="display:none" id="ajax-bda-report"></div>
        </div>


        <p>
          <a class="button" id="bda-file-report"><?php esc_html_e('View Protected Files','maxgalleria-media-library'); ?></a>			                
        </p>

        <div id="bda-list"></div>
      </div>


      <script>
        
      // Initialize select2
      jQuery("#selectPage").select2();
        
      jQuery(document).ready(function(){
                
        jQuery('.bda-option').click(function () {
          console.log('bda-option change');
        });  
        
        jQuery('#select-page-btn').click(function () {
          jQuery("#ajaxloaderbda").show();
          var page_name = jQuery('#selectPage option:selected').text();
          var no_access_page_id = jQuery('#selectPage').val();
          jQuery('#custom-404-page-id').val(no_access_page_id);
          jQuery('#custom-404-page-name').val(page_name);
          jQuery('#result').html("Page: " + page_name);
                    
          jQuery.ajax({
            type: "POST",
            async: true,
            data: { action: "mlfp_save_noaccess_page", 
                    no_access_page_id: no_access_page_id,
                    no_access_page_name: page_name,
                    nonce: mgmlp_ajax.nonce },
            url : mgmlp_ajax.ajaxurl,
            dataType: "html",
            success: function (data) {
              jQuery("#ajaxloaderbda").hide();
            },
            error: function (err)
            { 
              jQuery("#ajaxloaderbda").hide();
              alert(err.responseText);
            }
          });                                  
          
        });        
                
        jQuery(document).on("click", "#update-bda-settings", function (e) {						
          e.stopImmediatePropagation();
          jQuery("#ajaxloaderbda").show();

          var activate_mlfp_bdp = jQuery('#activate-mlfp-bdp').prop('checked');
          var disable_listing = jQuery('#disable-listing').prop('checked');
          var disable_hotlinking = jQuery('#disable-hotlinking').prop('checked');
          var auto_protect = jQuery('#auto-protect').prop('checked');
          var display_fe_protected_images = jQuery('#display_fe_protected_images').prop('checked');   
          var prevent_right_click = jQuery('#prevent_right_click').prop('checked');
          var bda_role = jQuery('#bdp-user-roles').val();
          var ip_changes = jQuery("#ip-changes").val();   
          
          jQuery.ajax({
            type: "POST",
            async: true,
            data: { action: "mlfp_process_bdp", 
                    activate_mlfp_bdp: activate_mlfp_bdp, 
                    disable_listing: disable_listing,
                    disable_hotlinking: disable_hotlinking,
                    auto_protect: auto_protect,
                    bda_role: bda_role,
                    display_fe_protected_images: display_fe_protected_images,
                    prevent_right_click: prevent_right_click,
                    ip_changes: ip_changes,
                    nonce: mgmlp_ajax.nonce },
            url : mgmlp_ajax.ajaxurl,
            dataType: "html",
            success: function (data) {
              jQuery("#ajaxloaderbda").hide();
              jQuery("#bda-message").html(data);
              window.location.reload();
            },
            error: function (err)
            { 
              jQuery("#ajaxloaderbda").hide();
              alert(err.responseText);
            }
          });                

        });
                
        jQuery(document).on("click", "#bda-file-report", function (e) {
          
          e.stopImmediatePropagation();
          jQuery("#ajax-bda-report").show();
          
          jQuery.ajax({
            type: "POST",
            async: true,
            data: { action: "mlfp_bdp_report", 
                    nonce: mgmlp_ajax.nonce },
            url : mgmlp_ajax.ajaxurl,
            dataType: "html",
            success: function (data) {
              jQuery("#ajax-bda-report").hide();
              jQuery("#bda-list").html(data);
            },
            error: function (err)
            { 
              jQuery("#ajax-bda-report").hide();
              alert(err.responseText);
            }
          });                
                    
        });
        
        jQuery(document).on("click", "#mlfp-previous, #mlfp-next", function (e) {
          console.log('next click');
          e.stopImmediatePropagation();
          jQuery("#ajax-bda-report").show();

          var page_id = jQuery(this).attr("page-id");

          jQuery.ajax({
            type: "POST",
            async: true,
            data: { action: "mlfp_bdp_report",
                    page_id: page_id,
                    nonce: mgmlp_ajax.nonce },
            url : mgmlp_ajax.ajaxurl,
            dataType: "html",
            success: function (data) {
              jQuery("#ajax-bda-report").hide();
              jQuery("#bda-list").html(data);
            },
            error: function (err)
            { 
              jQuery("#ajax-bda-report").hide();
              alert(err.responseText);
            }
          });                

        });
                    
        jQuery(document).on("click", "#add-new-ip", function (e) {
          e.stopImmediatePropagation();
          jQuery("#ajaxloaderbda").show();
          jQuery("#bda-message").html('');
                    
          var new_block_ip = jQuery("#new-block-ip").val();
          
          jQuery.ajax({
            type: "POST",
            async: true,
            data: { action: "mlfp_block_new_ip",
                    new_block_ip: new_block_ip,
                    nonce: mgmlp_ajax.nonce },
            url : mgmlp_ajax.ajaxurl,
            dataType: "json",
            success: function (data) {
              jQuery("#ajaxloaderbda").hide();
              jQuery("#bda-message").html(data.message);
              if(data.result == true) {
                jQuery("#new-block-ip").val('');
                jQuery("#ip-changes").val('true');                                
                refresh_ips ();
              }
            },
            error: function (err)
            { 
              jQuery("#ajaxloaderbda").hide();
              alert(err.responseText);
            }
          });                
          
        });
        
        jQuery(document).on("click", "#remove-ips", function (e) {
          e.stopImmediatePropagation();
          jQuery("#ajaxloaderbda").show();
          var ips = jQuery('#blocked-ip-list').val();
				  var serial_ips = JSON.stringify(ips.join());          
          console.log('ips',ips,serial_ips);
          
          
          jQuery.ajax({
            type: "POST",
            async: true,
            data: { action: "mlfp_unblock_ips",
                    serial_ips: serial_ips,
                    nonce: mgmlp_ajax.nonce },
            url : mgmlp_ajax.ajaxurl,
            dataType: "json",
            success: function (data) {
              jQuery("#ajaxloaderbda").hide();
              jQuery("#bda-message").html(data.message);
              if(data.result == true) {
                jQuery("#ip-changes").val('true');                                
                refresh_ips();
              }
            },
            error: function (err)
            { 
              jQuery("#ajaxloaderbda").hide();
              alert(err.responseText);
            }
          });                
                    
        });
                
      });  
            
      function refresh_ips () {
        
        jQuery.ajax({
          type: "POST",
          async: true,
          data: { action: "mlfp_get_block_ips",
                  nonce: mgmlp_ajax.nonce },
          url : mgmlp_ajax.ajaxurl,
          dataType: "html",
          success: function (data) {
             jQuery('#blocked-ip-list').html(data);
          },
          error: function (err)
          { 
            alert(err.responseText);
          }
        });                
                
      }
      </script>   

    </div><!--mgmlp-library-container-->
