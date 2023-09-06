jQuery(document).ready(function(){
  console.log('mlfp-lpf');
        var image_index = 1;
        jQuery("img").each(function () {
          var element = jQuery(this);
          var src = jQuery(this).attr('src');
          console.log('src',src);
          var clone = jQuery(this).clone();        

          jQuery.ajax({
            url:src,
            type:'GET',
            async: false,
            error:function(response){
              console.log('error src', src);            
              var image_id = 'image' + image_index;
              jQuery(clone).attr('id', image_id);
              jQuery(clone).attr('src', '');
              // replace with new element in order to load the image
              jQuery(element).replaceWith(clone);

              jQuery.ajax({
                type: "POST",
                async: false,
                data: { action: "mlfp_load_image", src: src, nonce: lpf_ajax.nonce },
                url: lpf_ajax.ajaxurl,
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
});        
