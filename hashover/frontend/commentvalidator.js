// Validate a comment form (commentvalidator.js)
HashOver.prototype.commentValidator = function (form, skipComment, isReply)
{
	skipComment = skipComment || false;

	// Check each input field for if they are required
	for (var field in this.setup['field-options']) {
		// Skip other people's prototypes
		if (this.setup['field-options'].hasOwnProperty (field) !== true) {
			continue;
		}

		// Check if the field is required, and that the input exists
		if (this.setup['field-options'][field] === 'required' && form[field] !== undefined) {
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

			// Remove class indicating a failed post
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
		var localeKey = (isReply === true) ? 'reply-needed' : 'comment-needed';
		var errorMessage = this.locale[localeKey];

		// Return a error message to display to the user
		return errorMessage;
	}

	return true;
};
