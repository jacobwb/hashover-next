// Validate a comment form e-mail field (validateemail.js)
HashOver.prototype.validateEmail = function (type, permalink, form, isReply, isEdit)
{
	type = type || 'main';
	permalink = permalink || null;
	isReply = isReply || false;
	isEdit = isEdit || false;

	// Subscribe checkbox ID
	var subscribe = type + '-subscribe';

	// Check whether comment is an reply or edit
	if (isReply === true || isEdit === true) {
		// If so, use form subscribe checkbox
		subscribe += '-' + permalink;
	}

	// Validate form fields
	return this.emailValidator (form, subscribe, type, permalink, isReply, isEdit);
};
