(function($) {
  $(document).ready( function() {
    if ( undefined !== wp.media ) {
      wp.media.view.Attachment.Library = wp.media.view.Attachment.Library.extend({
        className: function () { return 'attachment ' + this.model.get( 'customClass' ); }
      });
      //console.log('attachment',wp.media.view.Attachment.Library);      
    }    
  });
})(jQuery);