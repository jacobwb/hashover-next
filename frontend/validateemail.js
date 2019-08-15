// Handles display of various email warnings (validateemail.js)
HashOver.prototype.emailValidator = function (form, subscribe, type, permalink)
{
	// Do nothing if email form doesn't exist
	if (form.email === undefined) {
		return true;
	}

	// Check if email form is empty
	if (form.email.value === '') {
		// If so, return true if user unchecked subscribe checkbox
		if (this.getElement(subscribe).checked === false) {
			return true;
		}

		// Ask user if they are sure they don't want reply notifications
		var notifications = confirm (this.locale['no-email-warning']);

		// Check if user did not confirm
		if (notifications === false) {
			// If so, focus email field
			form.email.focus ();

			// And return false
			return false;
		}
	} else {
		// If not, check if email is valid
		if (this.rx.email.test (form.email.value) === false) {
			// If so, check if user unchecked subscribe checkbox
			if (this.getElement(subscribe).checked === false) {
				// If so, remove email address
				form.email.value = '';

				// And return true
				return true;
			}

			// Otherwise, get message from locales
			var message = this.locale['invalid-email'];

			// Show message
			this.showMessage (message, type, permalink, true);

			// Focus email input
			form.email.focus ();

			// And return false
			return false;
		}
	}

	// Otherwise, return true
	return true;
};

// Validate a comment form e-mail field (validateemail.js)
HashOver.prototype.validateEmail = function (type, permalink, form)
{
	// Subscribe checkbox ID
	var subscribe = type + '-subscribe';

	// Append permalink if form is a reply or edit
	if (type === 'reply' || type === 'edit') {
		subscribe += '-' + permalink;
	}

	// Attempt to validate form fields
	var valid = this.emailValidator (form, subscribe, type, permalink);

	// And return validity
	return valid;
};
