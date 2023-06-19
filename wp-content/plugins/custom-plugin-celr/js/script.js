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