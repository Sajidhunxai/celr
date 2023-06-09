"use strict";

const initializeHiddenLabelField = (field, formWrapper) => {
	const input = field.getElementsByTagName('input')[0];
	const fieldId = input.getAttribute('data-field-id');
	const hiddenField = formWrapper.getElementsByClassName('elementor-field-group-' + fieldId)[0];
	if (! hiddenField) {
		let span = document.createElement('span');
		span.textContent = 'Hiddel Label: Field not found.';
		formWrapper.prepend(span);
		return;
	}
	const hiddenInputs = hiddenField.querySelectorAll(`[name^=form_fields]`)
	const getLabel = () => {
		if (hiddenInputs.length >= 1) {
			if (hiddenInputs[0].tagName === 'SELECT') {
				if (hiddenInputs[0].selectedIndex >= 0) {
					// if there are no option selectedIndex is -1.
					return hiddenInputs[0].options[hiddenInputs[0].selectedIndex].innerHTML;
				} else {
					return '';
				}
			} else { // Checkbox or Radio
				let labels = [];
				for (let input of hiddenInputs) {
					if (input.checked) {
						let id = input.getAttribute('id');
						let label = formWrapper.querySelector(`label[for=${id}]`).innerHTML;
						labels.push(label);
					}
				}
				return labels.join(', ');
			}
		}
	}
	let prevValue = getLabel();
	input.value = prevValue;
	const updateLabel = () => {
		let newValue = getLabel();
		if (newValue === prevValue) {
			return;
		}
		prevValue = newValue;
		input.value = newValue;
		const evt = new Event('change');
		const form = formWrapper.getElementsByTagName('form')[0];
		form.dispatchEvent(evt);
	}
	if (hiddenField) {
		formWrapper.addEventListener('change', updateLabel);
	} else {
		alert(`Hidden Label, Could not find selector ${fieldId}`);
	}
}

const initializeHiddenLabelFields = ($form) => {
	$form.find('.elementor-field-type-hidden_label').each((_, f) => initializeHiddenLabelField(f, $form[0]));
};

jQuery(window).on('elementor/frontend/init', function() {
	elementorFrontend.hooks.addAction('frontend/element_ready/form.default', initializeHiddenLabelFields);
});
