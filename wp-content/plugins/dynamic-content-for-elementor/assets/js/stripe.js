"use strict";
// the key will not get defined on the backend so let's use Stripe sample key:
if ( typeof(dceStripePublishableKey) === "undefined" ) {
	var dceStripePublishableKey = 'pk_test_TYooMQauvdEDq54NiTphI7jx';
}

const stripe = Stripe(dceStripePublishableKey);

function makeElementsStyle(style) {
	return {
		base: {
			color: style.color,
			fontFamily: style.fontFamily + ' sans-serif',
			fontSmoothing: "antialiased",
			fontSize: style.fontSize,
			fontWeight: style.fontWeight,
			fontStyle: style.fontStyle,
			"::placeholder": {
				color: "#aab7c4"
			}
		},
		invalid: {
			color: "#fa755a",
			iconColor: "#fa755a"
		},
	};
}

function initializeStripeField(wrapper, $scope) {
	const $form = $scope.find('form');
	const postid = $form.find('[name=post_id]').val();
	const formid = $form.find('[name=form_id]').val();
	const queriedid = $form.find('[name=queried_id]').val();
	const $submitButton = $scope.find('.elementor-field-type-submit button');
	const $wrapper = jQuery(wrapper);
	const $hiddenInput = $wrapper.find('input');
	const $error = $wrapper.find('.stripe-error');
	const elements = stripe.elements();
	const $elementsWrapper = $wrapper.find('.dce-stripe-elements');
	const required = $elementsWrapper.attr('data-required') === 'true';
	const fieldIndex = $elementsWrapper.attr('data-field-index');
	const intentNonce = $elementsWrapper.attr('data-nonce');
	const intentURL = $elementsWrapper.attr('data-intent-url');
	const style = makeElementsStyle( {
		color: $elementsWrapper.css('color'),
		fontFamily: $elementsWrapper.css('font-family'),
		fontSize: $elementsWrapper.css('font-size'),
		fontWeight: $elementsWrapper.css('font-weight'),
		fontStyle: $elementsWrapper.css('font-style'),
	});
	const cardElement = elements.create('card', {style: style, hidePostalCode: false });
	const isCardEmpty = () => {
		return $elementsWrapper.hasClass('StripeElement--empty');
	}
	cardElement.mount($elementsWrapper[0]);

	const confirmPayment = (clientSecret, subscriptionId) => {
		stripe.confirmCardPayment(clientSecret, { payment_method: {
			type: 'card',
			card: cardElement,
		}}).then((result) => {
			if (result.error) {
				$submitButton.removeAttr('disabled');
				$error.text(result.error.message);
				$error.show();
			} else {
				$error.hide();
				const fieldValue = subscriptionId ? subscriptionId : result.paymentIntent.id;
				$hiddenInput.val(fieldValue);
				$form.trigger('submit');
			}
		});
	}

	const fetchClientSecret = () => {
		// Backend might need current get parameters for setting the item value:
		let query = window.location.search.slice(1);
		let url = intentURL + '&' + query;
		let data = new FormData($form[0]);
		data.set('intent_nonce', intentNonce);
		data.set('field_index', fieldIndex);
		return fetch(url, {
			method: 'POST',
			body: data,
		}).then(response => response.json());
	}

	$form.on('submit', (event) => {
		// if invisibile because conditional fields:
		if (wrapper.dataset.dceConditionsFieldStatus === 'inactive' || wrapper.style.display == 'none') {
			return;
		}
		if (!required && isCardEmpty()) {
			return;
		}
		if ($hiddenInput.val()) {
			return; // already processed.
		}
		event.preventDefault();
		event.stopImmediatePropagation();
		$submitButton.attr('disabled', 'disabled');
		$error.hide();
		fetchClientSecret().then(data => {
			if (! data.success) {
				$error.text(data.data.message);
				$error.show();
				$submitButton.removeAttr('disabled');
			} else {
				confirmPayment(data.data.client_secret, data.data.subscription_id);
			}
		}).catch(error => console.log(error));
	});
}

function initializeAllStripeFields($scope) {
	$scope.find('.elementor-field-type-dce_form_stripe').each((_, w) => initializeStripeField(w, $scope));
}

jQuery(window).on('elementor/frontend/init', function() {
	elementorFrontend.hooks.addAction('frontend/element_ready/form.default', initializeAllStripeFields);
});
