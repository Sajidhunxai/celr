jQuery(document).ready(function($) {
    // Add your custom JavaScript code here
    
    // Example: Submit form via AJAX
    $('#custom-sell-form').on('submit', function(event) {
      event.preventDefault();
      
      // Get form data
      var formData = $(this).serialize();
      
      // Perform AJAX request
      $.ajax({
        url: $(this).attr('action'),
        type: $(this).attr('method'),
        data: formData,
        success: function(response) {
          // Handle success response
          console.log('Form submitted successfully!');
          console.log(response);
        },
        error: function(xhr, status, error) {
          // Handle error response
          console.log('Error submitting form:');
          console.log(xhr.responseText);
        }
      });
    });
    
    // Add any additional custom code or event listeners as needed
  });
  