if ( typeof wpActiveEditor != 'undefined') {
  var wpActiveEditor = 'undefined';
}    
(function ($) {
  
  $(document).ready(function(){
        
    if(mlfpmedia.bda == 'on') {
      $('div.media-frame-content div.media-toolbar-secondary').append('<button class="bda-display media-button button-large" type="button">' + mlfpmedia.display_btn_text + '</button>');
      //$('div.media-frame-content div.media-toolbar').append('<div style="float:left;">' + mlfpmedia.upload_message +'</div><div id="mlfp-tool-bar"><input type="hidden" id="folder_id" value="' + mlfpmedia.uploads_folder_id + '"><ul id="folder-tree"></ul></div>');
    } 
    //else
      //$('div.media-frame-content div.media-toolbar').append('<div style="float:left;">' + mlfpmedia.upload_message +'</div><div id="mlfp-tool-bar"><input type="hidden" id="folder_id" value="' + mlfpmedia.uploads_folder_id + '"><ul id="folder-tree"></ul></div>');
    
        
	});
        			
  jQuery(document).on("click", ".bda-display", function (e) {	
    e.stopImmediatePropagation();
    
    jQuery('li.attachment.mlfp-protected').each(function() { 
      var that = this;
      if(!jQuery(that).hasClass('mlfp-visible')) {
        var image_id = jQuery(this).attr("data-id");
        var element = jQuery(this).find("img");
        var src = jQuery(element).attr("src");
        console.log('src', src);
        
        

        jQuery.ajax({
          type: "POST",
          async: true,
          data: { action: "mlfp_load_fe_image", src: src, image_id: image_id, nonce: mlfpmedia.nonce },
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
    });
    
  });
  
}(jQuery));
