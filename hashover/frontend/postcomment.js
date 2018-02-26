// For posting comments, both traditionally and via AJAX (postcomment.js)
HashOver.prototype.postComment = function (destination, form, button, callback, type, permalink, close, isReply, isEdit)
{
	type = type || 'main';
	permalink = permalink || '';
	isReply = isReply || false;
	isEdit = isEdit || false;

	// Return false if comment is invalid
	if (this.validateComment (false, form, type, permalink, isReply, isEdit) === false) {
		return false;
	}

	// Disable button
	setTimeout (function () {
		button.disabled = true;
	}, 500);

	// Post by sending an AJAX request if enabled
	if (this.postRequest) {
		return this.postRequest.apply (this, arguments);
	}

	// Re-enable button after 20 seconds
	setTimeout (function () {
		button.disabled = false;
	}, 20000);

	return true;
};
