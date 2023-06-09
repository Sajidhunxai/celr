function openPopup(productId, vendorName) {
  console.log("open");
  var popupContainer = document.getElementById('popup-container');
  var body = document.getElementById('single-product-main');
  
  popupContainer.classList.add('popup');
  // body.classList.add('blur');
    
  popupContainer.style.display = 'block';
  
  var offerForm = document.getElementById('offer-form');
  offerForm.addEventListener('submit', function(event) {
    event.preventDefault();
    var formData = new FormData(offerForm);
  
    var xhr = new XMLHttpRequest();
    xhr.open('POST', 'YOUR_EMAIL_HANDLING_SCRIPT_URL', true);
    xhr.onreadystatechange = function() {
      if (xhr.readyState === 4) {
        if (xhr.status === 200) {
          console.log('Form submitted successfully');
        } else {
          console.error('Form submission failed');
        }
        closePopup();
      }
    };
    xhr.send(formData);
  });

  // Close the popup when the user clicks outside of it
  window.addEventListener('mousedown', function(event) {
    if (!popupContainer.contains(event.target)) {
      closePopup();
    }
  });
}

function closePopup() {
  console.log("close");

  var popupContainer = document.getElementById('popup-container');
  var body = document.getElementById('single-product-main');
  
  popupContainer.style.display = 'none';
  
  popupContainer.classList.remove('popup');
  body.classList.remove('blur');
}
