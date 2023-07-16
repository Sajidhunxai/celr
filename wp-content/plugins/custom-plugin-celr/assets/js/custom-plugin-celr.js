function openPopup(productId, vendorName) {
  const popupContainer = document.getElementById('popup-container');
  const overlay = document.getElementById('overlay-offer');
  const body = document.querySelector('.main-page-wrapper');

  popupContainer.classList.add('popup');
  body.classList.add('blur');

  popupContainer.style.display = 'block';
  overlay.style.display = 'block';

  const offerForm = document.getElementById('offer-form');
  offerForm.addEventListener('submit', async (event) => {
    event.preventDefault();
  
    const formData = new FormData(offerForm);
    formData.append('action', 'process_offer_form');
    
    try {
      const response = await fetch(ajax_object.ajax_url, {
        method: 'POST',
        body: formData
      });
      
      if (response.ok) {
        alert('Form submitted successfully, Your Offer is Send to Site admin and Vendor');
      } else {
        alert('You have to Sign In to Send an Offer');
      }
    } catch (error) {
      alert('An error occurred while submitting the form:', error);
    }
  
    closePopup();
  });
  

  // Close the popup when the user clicks outside of it
  window.addEventListener('mousedown', (event) => {
    if (!popupContainer.contains(event.target)) {
      closePopup();
    }
  });
}

function closePopup() {
  const popupContainer = document.getElementById('popup-container');
  const body = document.getElementById('single-product-main');
  const overlay = document.getElementById('overlay-offer');

  popupContainer.style.display = 'none';
  overlay.style.display = 'none';
  popupContainer.classList.remove('popup');
  body.classList.remove('blur');
}

jQuery(document).ready(function($) {
  $('.vintage-link').on('click', function(e) {
      e.preventDefault();

      var vintage = $(this).data('vintage');
      var product_id = $(this).data('product-id');

      // AJAX request to filter variations based on vintage
      $.ajax({
          url: ajax_object.ajax_url,
          type: 'POST',
          data: {
              action: 'filter_variations',
              pa_vintage: vintage,
              product_id: product_id,
          },
          success: function(response) {
              // Handle the response
              if (response.error) {
                  console.log(response.error);
              } else {
                  // Loop through the variations and display the data
                  $.each(response, function(index, variation) {
                      var variationVintage = variation.vintage;
                      var variationTitle = variation.title;
                      var variationPrice = variation.price;
                      var variationImage = variation.image;

                      // Display the variation data
                      console.log('Vintage: ' + variationVintage);
                      console.log('Title: ' + variationTitle);
                      console.log('Price: ' + variationPrice);
                      console.log('Image: ' + variationImage);
                  });
              }
          },
          error: function(xhr, status, error) {
              // Handle the error
              console.log(error);
          }
      });
  });
});
// dashboard
document.addEventListener("DOMContentLoaded", function() {
  var buttons = document.getElementsByClassName("show-details-button");

  for (var i = 0; i < buttons.length; i++) {
    buttons[i].addEventListener("click", function() {
      var overlayId = this.getAttribute("data-overlay-id");
      var overlay = document.getElementById(overlayId);
      if (overlay) {
        overlay.style.display = "block";
      }
    });
  }

  var overlays = document.getElementsByClassName("close-button");

  for (var j = 0; j < overlays.length; j++) {
    overlays[j].addEventListener("click", function() {
      var overlay = this.closest(".remaining-details-overlay");
      if (overlay) {
        overlay.style.display = "none";
      }
    });
  }
});
var elements = document.getElementsByClassName("wc-item-meta");
for (var i = 0; i < elements.length; i++) {
    elements[i].style.display = "none";
}

