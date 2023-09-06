jQuery(document).ready(function($) {
    var currentStep = 1;
    var totalSteps = $('.step').length;

    $('.next-step').on('click', function() {
        var isValid = validateStep(currentStep);

        if (isValid) {
            $('.step').hide();
            $('#step' + (currentStep + 1)).show();
            currentStep++;

            if (currentStep === totalSteps) {
                $('.next-step').hide();
                $('.submit-step').show();
            }

            $('.prev-step').show();
        }
    });

    $('.prev-step').on('click', function() {
        var isValid = validateStep(currentStep);

        if (currentStep > 1) {
            $('.step').hide();
            $('#step' + (currentStep - 1)).show();
            currentStep--;

            if (currentStep === 1) {
                $('.prev-step').hide();
            }

            $('.next-step').show();
            $('.submit-step').hide();
        }
    });

    function validateStep(stepNumber) {
        var isValid = true;
        var errorMessage = '';
      
        
        if (stepNumber === 1) {
            // Validation checks for step 1 fields
            if ($('#user_email').val() === '' || $('#confirm_user_email').val() === '' || $('#user_password').val() === '' || $('#confirm_user_password').val() === '' 
           
            ) {
                isValid = false;
                errorMessage = 'Please fill in all required fields.';
            }
    
            if ($('#user_email').val() !== $('#confirm_user_email').val()) {
                isValid = false;
                errorMessage = 'Email and Confirm Email do not match.';
            }
    
            if ($('#user_password').val() !== $('#confirm_user_password').val()) {
                isValid = false;
                errorMessage = 'Password and Confirm Password do not match.';
            }
            if (!$('#agree_to_terms').is(':checked')) {
                isValid = false;
                errorMessage = 'You must agree to the terms and conditions.';
            }

			if ($('#user_email').val().indexOf('@') === -1) {
				isValid = false;
                errorMessage = 'Not a valid email address.';
			}
        }
        if (stepNumber === 2) {
            // Validation checks for step 1 fields
            if (  $('#first_name').val() === '' ||  $('#last_name').val() === '' ) {
                isValid = false;
                errorMessage = 'Please fill in all required fields.';
            }
    
            // if ($('#user_email').val() !== $('#confirm_user_email').val()) {
            //     isValid = false;
            //     errorMessage = 'Email and Confirm Email do not match.';
            // }
    
            // if ($('#user_password').val() !== $('#confirm_user_password').val()) {
            //     isValid = false;
            //     errorMessage = 'Password and Confirm Password do not match.';
            // }
    
      
        }
    
        // // Add more validation for other steps as needed
    
        if (!isValid) {
            $('#step' + stepNumber + '-error').html('<p class="error">' + errorMessage + '</p>');
        } else {
            $('#step' + stepNumber + '-error').empty();
        }
    
        return isValid;
    }
    
});




