// Validate required comment credentials (validatecomment.js)
HashOver.prototype.validateComment = function (skipComment, form, type, permalink, isReply, isEdit)
{
	skipComment = skipComment || false;
	type = type || 'main';
	permalink = permalink || '';

	// Validate comment form
	var message = this.commentValidator (form, skipComment, isReply);

	// Display the validator's message
	if (message !== true) {
		this.showMessage (message, type, permalink, true, isReply, isEdit);
		return false;
	}

	// Validate e-mail if user isn't logged in or is editing
	if (this.setup['user-is-logged-in'] === false || isEdit === true) {
		// Return false on any failure
		if (this.validateEmail (type, permalink, form, isReply, isEdit) === false) {
			return false;
		}
	}

	return true;
};
