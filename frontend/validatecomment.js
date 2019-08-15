// Validate a comment form (validatecomment.js)
HashOver.prototype.commentValidator = function (form, type, skipComment)
{
	// Check each input field for if they are required
	for (var field in this.setup['form-fields']) {
		// Skip other people's prototypes
		if (this.setup['form-fields'].hasOwnProperty (field) !== true) {
			continue;
		}

		// Check if the field is required, and that the input exists
		if (this.setup['form-fields'][field] === 'required' && form[field] !== undefined) {
			// Check if it has a value
			if (form[field].value === '') {
				// If not, add a class indicating a failed post
				this.classes.add (form[field], 'hashover-emphasized-input');

				// Focus the input
				form[field].focus ();

				// Return error message to display to the user
				return this.strings.sprintf (this.locale['field-needed'], [
					this.locale[field]
				]);
			}

			// And remove class indicating a failed post
			this.classes.remove (form[field], 'hashover-emphasized-input');
		}
	}

	// Check if a comment was given
	if (skipComment !== true && form.comment.value === '') {
		// If not, add a class indicating a failed post
		this.classes.add (form.comment, 'hashover-emphasized-input');

		// Focus the comment textarea
		form.comment.focus ();

		// Error message to display to the user
		var localeKey = (type === 'reply') ? 'reply-needed' : 'comment-needed';
		var errorMessage = this.locale[localeKey];

		// Return a error message to display to the user
		return errorMessage;
	}

	// And return true
	return true;
};

// Validate required comment credentials (validatecomment.js)
HashOver.prototype.validateComment = function (form, type, permalink, skipComment)
{
	// Attempt to validate comment
	var message = this.commentValidator (form, type, skipComment);

	// Check if comment is invalid
	if (message !== true) {
		// If so, display validator's message
		this.showMessage (message, type, permalink, true);

		// And return false
		return false;
	}

	// Validate e-mail if user isn't logged in or is editing
	if (this.setup['user-is-logged-in'] === false || type === 'edit') {
		// Return false on any failure
		if (this.validateEmail (type, permalink, form) === false) {
			return false;
		}
	}

	// And return true
	return true;
};
