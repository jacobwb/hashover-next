// Validate a comment form e-mail field (validateemail.js)
HashOver.prototype.validateEmail = function (type, permalink, form, isReply, isEdit)
{
	type = type || 'main';
	permalink = permalink || '';

	// Subscribe checkbox ID
	var subscribe = type + '-subscribe';

	// Use reply form checkbox if comment is a reply
	if (isReply === true || isEdit === true) {
		subscribe += '-' + permalink;
	}

	// Validate form fields
	return this.emailValidator (form, subscribe, type, permalink, isReply, isEdit);
};
