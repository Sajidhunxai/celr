jQuery(document).ready(function(){
  				    
    jQuery(document).on("click", "#select-media", function () {
      jQuery(".media-attachment, .mgmlp-media").prop("checked", !jQuery(".media-attachment").prop("checked"));
    });
				            
    jQuery(document).on("click", "#mgmlp_ajax_upload", function () {
        		
			jQuery("#folder-message").html('');			
			if(jQuery("#current-folder-id").val() === undefined) 
	      var folder_id = sessionStorage.getItem('folder_id');
			else
        var folder_id = jQuery('#current-folder-id').val();
			
			var mlp_title_text = jQuery('#mlp_title_text').val();      
			var mlp_alt_text = jQuery('#mlp_alt_text').val();      			
												
      //var folder_id = jQuery('#folder_id').val();      
      var file_data = jQuery('#fileToUpload').prop('files')[0];   
      var form_data = new FormData();                  
      
      form_data.append('file', file_data);
      form_data.append('action', 'upload_attachment');
      form_data.append('folder_id', folder_id);
      form_data.append('title_text', mlp_title_text);
      form_data.append('alt_text', mlp_alt_text);
      form_data.append('nonce', mgmlp_ajax.nonce);
      jQuery("#ajaxloader").show();
      
      jQuery.ajax({
          url : mgmlp_ajax.ajaxurl,
          dataType: 'html',  
          cache: false,
          contentType: false,
          processData: false,
          data: form_data,                         
          type: 'post',
          success: function (data) {
            jQuery("#mgmlp-file-container").html(data);
            jQuery("#ajaxloader").hide();
            jQuery('#fileToUpload').val("");
          }
       });
            
    });				
        
    jQuery(document).on("click", "#delete-media", function (e) {
        
			jQuery("#folder-message").html('');			

				if(jQuery("#current-folder-id").val() === undefined) 
					var current_folder = sessionStorage.getItem('folder_id');
				else
					var current_folder = jQuery('#current-folder-id').val();
				
        jQuery(".mgmlp-folder").prop('disabled', false);
        
        jQuery('.input-area').each(function(index) {
          jQuery(this).slideUp(600);
        });
        
        
        var delete_ids = new Array();
        jQuery('input[type=checkbox].mgmlp-media:checked').each(function() {  
          delete_ids[delete_ids.length] = jQuery(this).attr("id");
        });
								        
        if(delete_ids.length === 0) {
          alert(mgmlp_ajax.nothing_selected);
          return false;
        }
        if(confirm(mgmlp_ajax.confirm_file_delete)) {
          var serial_delete_ids = JSON.stringify(delete_ids.join());
          jQuery("#ajaxloader").show();
          jQuery.ajax({
            type: "POST",
            async: true,
            data: { action: "delete_maxgalleria_media", serial_delete_ids: serial_delete_ids, parent_id: current_folder, nonce: mgmlp_ajax.nonce },
            //var delete_data = jQuery.serialize(data);
            url : mgmlp_ajax.ajaxurl,
            dataType: "json",
            success: function (data) {
							
							jQuery("#folder-message").html(data.message);
							if(data.refresh)
								jQuery("#mgmlp-file-container").html(data.files);						
              jQuery("#ajaxloader").hide();            
																						
            },
            error: function (err)
              { alert(err.responseText);}
          });
      } 
    });	
        
    jQuery(document).on("click", "#copy-media", function (e) {
      var copy_ids = new Array();
      jQuery('input[type=checkbox].mgmlp-media:checked').each(function() {  
        copy_ids[copy_ids.length] = jQuery(this).attr("id");
      });
            
			jQuery("#folder-message").html('');			
      var serial_copy_ids = JSON.stringify(copy_ids.join());
      var folder_id = jQuery('#copy-select').val();
      var destination = jQuery("#copy-select option:selected").text();
      jQuery("#ajaxloader").show();
      
      jQuery.ajax({
        type: "POST",
        async: true,
        data: { action: "copy_media", folder_id: folder_id, destination: destination, serial_copy_ids: serial_copy_ids, nonce: mgmlp_ajax.nonce },
        url : mgmlp_ajax.ajaxurl,
        dataType: "json",
        success: function (data) {
          jQuery("#ajaxloader").hide();
          jQuery(".mgmlp-media").prop('checked', false);
          jQuery(".mgmlp-folder").prop('checked', false);
          jQuery("#folder-message").html(data.message);
					
        },
        error: function (err)
          { 
            jQuery("#ajaxloader").hide();
            alert(err.responseText);
          }
      });                
    });	
    
    jQuery(document).on("click", "#move-media", function (e) {
      var move_ids = new Array();
      jQuery('input[type=checkbox].mgmlp-media:checked').each(function() {  
        move_ids[move_ids.length] = jQuery(this).attr("id");
      });
            
      var serial_copy_ids = JSON.stringify(move_ids.join());
      var folder_id = jQuery('#move-select').val();
      var destination = jQuery("#move-select option:selected").text();
			
			if(jQuery("#current-folder-id").val() === undefined) 
				var current_folder = sessionStorage.getItem('folder_id');
			else
				var current_folder = jQuery('#current-folder-id').val();
			      
			jQuery("#folder-message").html('');			
      jQuery("#ajaxloader").show();
      
      jQuery.ajax({
        type: "POST",
        async: true,
        data: { action: "move_media", current_folder: current_folder, folder_id: folder_id, destination: destination, serial_copy_ids: serial_copy_ids, nonce: mgmlp_ajax.nonce },
        url : mgmlp_ajax.ajaxurl,
        dataType: "json",
        success: function (data) {
          jQuery(".mgmlp-media").prop('checked', false);
          jQuery(".mgmlp-folder").prop('checked', false);
          jQuery("#folder-message").html(data.message);
					if(data.refresh)
					  jQuery("#mgmlp-file-container").html(data.files);						
          jQuery("#ajaxloader").hide();
					
        },
        error: function (err)
          { 
            jQuery("#ajaxloader").hide();
            alert(err.responseText);
          }
      });                
    });	
        	        
	  jQuery(document).on("change", "#mgmlp-sort-order", function () {						
      
      var sort_order = jQuery('#mgmlp-sort-order').val();
      
			if(jQuery("#current-folder-id").val() === undefined) 
				var current_folder = sessionStorage.getItem('folder_id');
			else
				var current_folder = jQuery('#current-folder-id').val();
            
      jQuery("#ajaxloader").show();
      
      jQuery.ajax({
        type: "POST",
        async: true,
        data: { action: "sort_contents", sort_order: sort_order, folder: current_folder, nonce: mgmlp_ajax.nonce },        
        url : mgmlp_ajax.ajaxurl,
        dataType: "html",
        success: function (data) {
				  jQuery("#mgmlp-file-container").html(data); 
          jQuery("#ajaxloader").hide();
        },
        error: function (err) { 
          jQuery("#ajaxloader").hide();
          alert(err.responseText);
        }
      });                
      
    });
		
	  jQuery(document).on("change", "#move-copy-switch", function () {						
			
      var move_copy_switch = jQuery('input[type=checkbox]#move-copy-switch:checked').length > 0;
			
			if(move_copy_switch)
				move_copy_switch = 'on'
			else
				move_copy_switch = 'off'
            
      jQuery.ajax({
        type: "POST",
        async: true,
        data: { action: "mgmlp_move_copy", move_copy_switch: move_copy_switch, nonce: mgmlp_ajax.nonce },
        url : mgmlp_ajax.ajaxurl,
        dataType: "html",
        success: function (data) {
        },
        error: function (err) { 
          alert(err.responseText);
        }
      });                
    });
		
	  jQuery(document).on("mouseenter", "#above-toolbar a", function () {						
       jQuery('#folder-message').html(jQuery(this).attr('help')).fadeIn(200);
    });

	  jQuery(document).on("mouseleave", "#above-toolbar a", function () {						
       jQuery('#folder-message').html('');
    });
    
	  jQuery(document).on("mouseenter", "#mgmlp-toolbar a", function () {						
       jQuery('#folder-message').html(jQuery(this).attr('help')).fadeIn(200);
    });

	  jQuery(document).on("mouseleave", "#mgmlp-toolbar a", function () {						
       jQuery('#folder-message').html('');
    });
    		
	  jQuery(document).on("mouseenter", "#mgmlp-toolbar .onoffswitch", function () {						
       jQuery('#folder-message').html(jQuery(this).attr('help')).fadeIn(200);
    });

	  jQuery(document).on("mouseleave", "#mgmlp-toolbar .onoffswitch", function () {						
       jQuery('#folder-message').html('');
    });
            								
    jQuery(document).on("click", "#mgmlp-regen-thumbnails", function (e) {
      var image_ids = new Array();
      jQuery('input[type=checkbox].mgmlp-media:checked').each(function() {   
        image_ids[image_ids.length] = jQuery(this).attr("id");
      });
			
			if(image_ids.length < 1) {
        jQuery("#folder-message").html("No files were selected.");
				return false;
			}	
			            
      var serial_image_ids = JSON.stringify(image_ids.join());
      
      jQuery("#ajaxloader").show();
      
      jQuery.ajax({
        type: "POST",
        async: true,
        data: { action: "regen_mlp_thumbnails", serial_image_ids: serial_image_ids, nonce: mgmlp_ajax.nonce },
        url : mgmlp_ajax.ajaxurl,
        dataType: "html",
        success: function (data) {
          jQuery(".mgmlp-media").prop('checked', false);
          jQuery("#folder-message").html(data);
          jQuery("#ajaxloader").hide();
        },
        error: function (err)
          { 
            jQuery("#ajaxloader").hide();
            alert(err.responseText);
          }
      });                
    });
				    
    jQuery(document).on("click", ".mgmlp-media", function (e) {
      var current_element = jQuery(this);
      var check_next = false;
      var search_for_next_checked_item = false;
      var shift_after = false;
      var shiftHeld = e.shiftKey;    
      if(shiftHeld) {
        jQuery('input[type=checkbox].mgmlp-media').each(function() {          
          if(!search_for_next_checked_item && !shift_after && jQuery(this).is(':checked') && current_element.is(this)) {
            search_for_next_checked_item = true;
            return; // continue the each loop
          } else if(search_for_next_checked_item) {  
              if(jQuery(this).is(':checked'))
                return false;
              jQuery(this).prop('checked', true);
          } else {                    
            if(!check_next && jQuery(this).is(':checked')) {
              check_next = true;
              shift_after = true;
            } else if(current_element.is(this)) {
              return false;
            } else if(check_next) {
              jQuery(this).prop('checked', true);
            }
          }
        });
      }
    });   
    								
		jQuery("#mgmlp-file-container").on("click", "#display_mlpp_images", function(){
			var folder_id = jQuery(this).attr('folder_id');
			//var image_link = jQuery(this).attr('image_link');
						
			jQuery.ajax({
				type: "POST",
				async: true,
				//data: { action: "mlp_display_folder_contents_ajax", current_folder_id: folder_id, image_link: image_link, display_type: 1, nonce: mgmlp_ajax.nonce },
				data: { action: "mlp_display_folder_contents_ajax", current_folder_id: folder_id, display_type: 1, nonce: mgmlp_ajax.nonce },
				url: mgmlp_ajax.ajaxurl,
				dataType: "html",
				success: function (data) 
					{ 
						jQuery("#mgmlp-file-container").html(data); 
					},
						error: function (err)
					{ alert(err.responseText)}
					});
    });
		
		jQuery("#mgmlp-file-container").on("click", "#display_mlpp_titles", function(e){
      e.stopImmediatePropagation();
			var folder_id = jQuery(this).attr('folder_id');
			//var image_link = jQuery(this).attr('image_link');
						
			jQuery.ajax({
				type: "POST",
				async: true,
				//data: { action: "mlp_display_folder_contents_images_ajax", current_folder_id: folder_id, image_link: image_link, display_type: 2, nonce: mgmlp_ajax.nonce },
				data: { action: "mlp_display_folder_contents_images_ajax", current_folder_id: folder_id, display_type: 2, nonce: mgmlp_ajax.nonce },
				url: mgmlp_ajax.ajaxurl,
				dataType: "html",
				success: function (data) 
					{ 
						jQuery("#mgmlp-file-container").html(data); 						
					},
						error: function (err)
					{ alert(err.responseText)}
					});
    });
	
    jQuery(document).on("click", "#mgmlp-create-new-gallery", function (e) {
      
			jQuery("#folder-message").html('');			
			
      var new_gallery_name = jQuery('#new-gallery-name').val();
      //var parent_folder = jQuery('#current-folder-id').val();
			
			if(jQuery("#current-folder-id").val() === undefined) 
				var parent_folder = sessionStorage.getItem('folder_id');
			else
				var parent_folder = jQuery('#current-folder-id').val();
			
      
      jQuery("#ajaxloader").show();
      
      jQuery.ajax({
        type: "POST",
        async: true,
        data: { action: "mlpp_create_new_ng_gallery", new_gallery_name: new_gallery_name, parent_folder: parent_folder, nonce: mgmlp_ajax.nonce },
        url : mgmlp_ajax.ajaxurl,
        dataType: "html",
        success: function (data) {
          jQuery("#ajaxloader").hide();          
          jQuery("#folder-message").html(data);
        },
        error: function (err)
          { alert(err.responseText);}
      });
           	
    });
    
  });    
			       
function slideonlyone(thechosenone) {
  jQuery('.input-area').each(function(index) {
    if (jQuery(this).attr("id") == thechosenone) {
			 if(jQuery(this).is(":visible")) {
         jQuery(this).slideUp(600);
       } else {
         jQuery(this).slideDown(200);
       }  
			 if(thechosenone == 'new-folder-area')
				 jQuery("#new-folder-name").focus();
			 if(thechosenone == 'rename-area')
				 jQuery("#new-file-name").focus();			 			 
    }
    else {
       jQuery(this).slideUp(600);
    }
  });
}

var obj = jQuery("#dragandrophandler");
obj.on('dragenter', function (e) 
{
    e.stopPropagation();
    e.preventDefault();
    jQuery(this).css('border', '2px solid #0B85A1');
});
obj.on('dragover', function (e) 
{
     e.stopPropagation();
     e.preventDefault();
});
obj.on('drop', function (e) 
{
 
     jQuery(this).css('border', '2px solid #0B85A1');
     e.preventDefault();
     var files = e.originalEvent.dataTransfer.files;
 
     //We need to send dropped files to Server
     handleFileUpload(files,obj);
});


jQuery(document).on('dragenter', function (e) 
{
    e.stopPropagation();
    e.preventDefault();
});
jQuery(document).on('dragover', function (e) 
{
  e.stopPropagation();
  e.preventDefault();
  obj.css('border', '2px solid #0B85A1');
});
jQuery(document).on('drop', function (e, ui) 
{
    e.stopPropagation();
    e.preventDefault();
});

function handleFileUpload(files,obj)
{
   var folder_id = jQuery('#folder_id').val();      
	 
	 var mlp_title_text = jQuery('#mlp_title_text').val();      
	 var mlp_alt_text = jQuery('#mlp_alt_text').val();      
	 
   for (var i = 0; i < files.length; i++) 
   {
        var fd = new FormData();
        fd.append('file', files[i]);
        fd.append('action', 'upload_attachment');
        fd.append('folder_id', folder_id);
        fd.append('title_text', mlp_title_text);
        fd.append('alt_text', mlp_alt_text);
        fd.append('nonce', mgmlp_ajax.nonce);

        var status = new createStatusbar(obj); //Using this we can set progress.
        status.setFileNameSize(files[i].name,files[i].size);
        sendFileToServer(fd,status);
 
   }
}

function sendFileToServer(formData,status)
{
    jQuery("#ajaxloader").show();
    var extraData ={}; //Extra Data.
    var jqXHR=jQuery.ajax({
            xhr: function() {
            var xhrobj = jQuery.ajaxSettings.xhr();
            if (xhrobj.upload) {
                    xhrobj.upload.addEventListener('progress', function(event) {
                        var percent = 0;
                        var position = event.loaded || event.position;
                        var total = event.total;
                        if (event.lengthComputable) {
                            percent = Math.ceil(position / total * 100);
                        }
                        //Set progress
                        status.setProgress(percent);
                    }, false);
                }
            return xhrobj;
        },
        url : mgmlp_ajax.ajaxurl,
        type: "POST",
        contentType:false,
        processData: false,
        cache: false,
        data: formData,
        success: function(data){
            status.setProgress(100);
            jQuery("#mgmlp-file-container").html(data);

		        jQuery('li a.media-attachment').draggable({
							cursor: 'move',
							//helper: 'clone'
			        helper: function() {
								// allows the checkboxes to be used in multi select drag and drop
								var selected = jQuery('.mg-media-list input:checked').parents('li');
								if (selected.length === 0) {
									selected = jQuery(this);
								}
								var container = jQuery('<div/>').attr('id', 'draggingContainer');
								container.append(selected.clone());
								return container;
							}										
						});
						jQuery('.media-link').droppable( {
								accept: 'li a.media-attachment',
								hoverClass: 'droppable-hover',
								drop: handleDropEvent
						});
            jQuery("#ajaxloader").hide();
						
        },
        error: function (err){ 
          jQuery("#ajaxloader").hide();
          alert(err.responseText);
        }        
    }); 
 
    status.setAbort(jqXHR);
}

var rowCount=0;
function createStatusbar(obj)
{
     rowCount++;
     var row="odd";
     if(rowCount %2 ==0) row ="even";
     this.statusbar = jQuery("<div class='statusbar "+row+"'></div>");
     this.filename = jQuery("<div class='filename'></div>").appendTo(this.statusbar);
     this.size = jQuery("<div class='filesize'></div>").appendTo(this.statusbar);
     this.progressBar = jQuery("<div class='progressBar'><div></div></div>").appendTo(this.statusbar);
     this.abort = jQuery("<div class='abort'>Abort</div>").appendTo(this.statusbar);
     obj.after(this.statusbar);
 
    this.setFileNameSize = function(name,size)
    {
        var sizeStr="";
        var sizeKB = size/1024;
        if(parseInt(sizeKB) > 1024)
        {
            var sizeMB = sizeKB/1024;
            sizeStr = sizeMB.toFixed(2)+" MB";
        }
        else
        {
            sizeStr = sizeKB.toFixed(2)+" KB";
        }
 
        this.filename.html(name);
        this.size.html(sizeStr);
    }
    this.setProgress = function(progress)
    {       
        var progressBarWidth =progress*this.progressBar.width()/ 100;  
        this.progressBar.find('div').animate({ width: progressBarWidth }, 10).html(progress + "% ");
        if(parseInt(progress) >= 100)
        {            
            this.abort.hide();            
            //jQuery(".statusbar").remove();
            this.statusbar.remove();
        }
    }
    this.setAbort = function(jqxhr)
    {
        var sb = this.statusbar;
        this.abort.click(function()
        {
            jqxhr.abort();
            sb.hide();
            jQuery("#ajaxloader").hide();
        });
    }
}

function handleDropEvent(event, ui ) {
  
	var move_ids = new Array();
	var items = ui.helper.children();
	items.each(function() {  
		move_ids[move_ids.length] = jQuery(this).find( "a.media-attachment" ).attr("id");
	});
	
	if(move_ids.length < 2) {
	  move_ids = new Array();
		move_ids[move_ids.length] =  ui.draggable.attr("id");
	}	
		
  var droppableId = jQuery(this).attr("folder");	
	var serial_copy_ids = JSON.stringify(move_ids.join());
	var folder_id = droppableId;
	var destination = '';
	
	if(jQuery("#current-folder-id").val() === undefined) 
		var current_folder = sessionStorage.getItem('folder_id');
	else
		var current_folder = jQuery('#current-folder-id').val();
		
	//var operation_type = jQuery('#move-copy-switch:checkbox:checked').length > 0;
  var move_or_copy_status = jQuery('#move-or-copy-status').val();
  
//	if(operation_type)
//	  console.log('move');
//	else
//	  console.log('copy');
	
	jQuery("#ajaxloader").show();

	jQuery.ajax({
		type: "POST",
		async: true,
		data: { action: "move_media", current_folder: current_folder, folder_id: folder_id, destination: destination, serial_copy_ids: serial_copy_ids, nonce: mgmlp_ajax.nonce },
		url : mgmlp_ajax.ajaxurl,
		dataType: "json",
		success: function (data) {
			jQuery("#ajaxloader").hide();
			jQuery(".mgmlp-media").prop('checked', false);
			jQuery(".mgmlp-folder").prop('checked', false);
			jQuery("#folder-message").html(data.message);
		},
		error: function (err)
			{ 
				jQuery("#ajaxloader").hide();
				alert(err.responseText);
			}
	});                	
}

function mlf_refresh(folder_id) {
	//var image_link = '1';
  jQuery("#folder-message").html('Refreshing...');
	
	jQuery.ajax({
		type: "POST",
		async: true,
		data: { action: "mlp_load_folder", folder: folder_id, nonce: mgmlp_ajax.nonce },
		url: mgmlp_ajax.ajaxurl,
		dataType: "html",
		success: function (data) 
			{ 
				jQuery("#mgmlp-file-container").html(data); 
				jQuery("#folder-message").html(''); 				
			},
				error: function (err)
			{ alert(err.responseText)}
			});
	
}

function mlf_refresh_folders(folder_id) {
  jQuery("#folder-message").html('Refreshing folders...');
	
	if(jQuery("#current-folder-id").val() === undefined) 
		var folder_id = sessionStorage.getItem('folder_id');
	else
		var folder_id = jQuery('#current-folder-id').val();
	
	jQuery.ajax({
		type: "POST",
		async: true,
		data: { action: "mlp_get_folder_data", current_folder_id: folder_id, nonce: mgmlp_ajax.nonce },
		url: mgmlp_ajax.ajaxurl,
		dataType: "json",
		success: function (data) { 
			jQuery('#folder-tree').jstree(true).settings.core.data = data;
			jQuery('#folder-tree').jstree(true).refresh();			
						
      jQuery("#folder-message").html('');
		},
		error: function (err){ 
			alert(err.responseText)
		}
	});
	
}

function run_sync_process(phase, parent_folder, mlp_title_text, mlp_alt_text) {
	
  jQuery("#ajaxloader").show();
  
	jQuery.ajax({
		type: "POST",
		async: true,
		data: { action: "mlfp_run_sync_process", phase: phase, parent_folder: parent_folder, mlp_title_text: mlp_title_text, mlp_alt_text: mlp_alt_text, nonce: mgmlp_ajax.nonce },
		url: mgmlp_ajax.ajaxurl,
		dataType: "json",
		success: function (data) { 
			if(data != null && data.phase != null) {
			  jQuery("#folder-message").html(data.message);
        run_sync_process(data.phase, parent_folder, mlp_title_text, mlp_alt_text);
      } else {        
			  jQuery("#folder-message").html(data.message);        
				mlf_refresh_folders(parent_folder);
		    jQuery("#ajaxloader").hide();
				return false;
      }
      
		},
		error: function (err){ 
		  jQuery("#ajaxloader").hide();
			alert(err.responseText)
		}    
	});																											
	
}
