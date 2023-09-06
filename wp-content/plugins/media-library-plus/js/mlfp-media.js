  jQuery(document).ready(function(){
    jQuery.extend( wp.Uploader.prototype, {
      init : function(){
        console.log('init');
        mlfp_display_folder_tree();
      },
      success : function(){
        wp.media.frame.content.get().collection.props.set({ignore: (+ new Date())});                
      },
      refresh : function(){
        //new_folder_check();
        //mlfp_display_folder_tree();  
        //console.log('starting folder id', mlfpmedia.new_folder_id)
      }      
    });
    
  });     
    
// for beaver builder plugin    
jQuery('body', window.parent.document).on( 'click', '.fl-photo-field .fl-photo-select, .fl-photo-edit, .fl-video-select, .fl-video-replace, .fl-multiple-photos-edit, .fl-multiple-photos-add', function () {
  setTimeout(function(){mlfp_display_folder_tree()}, 3000);
});     

function mlfp_display_folder_tree() {
    
    var parent_id;        
    
    jQuery('.media-modal.wp-core-ui').each(function() {
      if(jQuery(this).is(':visible')) {
        parent_id = jQuery(this).parent().attr('id');

        if(jQuery(this).find('div.mlfp-ft').length === 0) {

          if(mlfpmedia.bda == 'on') {
            jQuery(this).find('.media-frame-router').append('<button class="bda-display button media-button" type="button" style="float:right;margin-right:10px;">' + mlfpmedia.display_btn_text + '</button>')
          }

          return false;
        }          
      }  
    });                                  
  }
        
  
  jQuery(document).on("click", ".bda-display", function (e) {	
    e.stopImmediatePropagation();
    console.log('bda-display');
    
    jQuery('li.attachment.mlfp-protected').each(function() {
      var that = this;
      if(!jQuery(that).hasClass('mlfp-visible')) {
        var element = jQuery(this).find("img");
        var src = jQuery(element).attr("src");
        if(mlfpmedia.bda_user_role == 'admins') {
          console.log('admins src', src);          
          bda_display_image(src, element, that);
        } else {
          // check for author class before display
          if(jQuery(this).hasClass('author')) {          
            console.log('author src', src);          
            bda_display_image(src, element, that);
          }  
        }
      }            
    });
    
  });
  
  function bda_display_image(src, element, that) {
          jQuery.ajax({
            type: "POST",
            async: true,
            data: { action: "mlfp_load_image", src: src, nonce: mlfpmedia.nonce },
            url: mlfpmedia.ajaxurl,
            success: function (data) {
              if(data.length > 0) {
                jQuery(element).attr("src", data);
                jQuery(that).addClass('mlfp-visible');
              }
            },
            error: function (err){
              alert(err.responseText);
            }
          });
    
  }
