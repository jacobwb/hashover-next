// Displays reply form (replytocomment.js)
HashOver.prototype.replyToComment = function (permalink)
{
	// Reference to this object
	var hashover = this;

	// Get reply link element
	var link = this.elements.get ('reply-link-' + permalink);

	// Get file
	var file = this.permalinks.getFile (permalink);

	// Create reply form element
	var form = this.elements.create ('form', {
		id: 'hashover-reply-' + permalink,
		className: 'hashover-reply-form',
		action: this.setup['http-backend'] + '/form-actions.php',
		method: 'post'
	});

	// Place reply fields into form
	form.innerHTML = hashover.strings.parseTemplate (hashover.ui['reply-form'], {
		permalink: permalink,
		file: file
	});

	// Prevent input submission
	this.preventSubmit (form);

	// Get form by its permalink ID
	var replyForm = this.elements.get ('placeholder-reply-form-' + permalink);

	// Add form to page
	replyForm.appendChild (form);

	// Change "Reply" link to "Cancel" link
	this.cancelSwitcher ('reply', link, replyForm, permalink);

	// Attach event listeners to "Post Reply" button
	var postReply = this.elements.get ('reply-post-' + permalink);

	// Get the element of comment being replied to
	var destination = this.elements.get (permalink);

	// Attach click event to formatting revealer hyperlink
	this.formattingOnclick ('reply', permalink);

	// Set onclick and onsubmit event handlers
	this.elements.duplicateProperties (postReply, [ 'onclick', 'onsubmit' ], function () {
		return hashover.postComment (destination, form, this, hashover.AJAXPost, 'reply', permalink, link.onclick, true, false);
	});

	// Focus comment field
	form.comment.focus ();

	return true;
};
