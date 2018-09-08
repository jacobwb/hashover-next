// Handles display of various e-mail warnings (emailvalidator.js)
HashOver.prototype.emailValidator = function (form, subscribe, type, permalink, isReply, isEdit)
{
	if (form.email === undefined) {
		return true;
	}

	// Whether the e-mail form is empty
	if (form.email.value === '') {
		// Return true if user unchecked the subscribe checkbox
		if (this.getElement(subscribe).checked === false) {
			return true;
		}

		// If so, warn the user that they won't receive reply notifications
		if (confirm (this.locale['no-email-warning']) === false) {
			form.email.focus ();
			return false;
		}
	} else {
		// If not, check if the e-mail is valid
		if (this.rx.email.test (form.email.value) === false) {
			// Return true if user unchecked the subscribe checkbox
			if (this.getElement(subscribe).checked === false) {
				form.email.value = '';
				return true;
			}

			// Get message from locales
			var message = this.locale['invalid-email'];

			// Show the message and focus the e-mail input
			this.showMessage (message, type, permalink, true, isReply, isEdit);
			form.email.focus ();

			return false;
		}
	}

	return true;
};
