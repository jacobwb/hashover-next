// Displays reply form (replytocomment.js)
HashOver.prototype.replyToComment = function (permalink)
{
	// Reference to this object
	var hashover = this;

	// Get reply link element
	var link = this.getElement ('reply-link-' + permalink);

	// Get file
	var file = this.permalinkFile (permalink);

	// Create reply form element
	var form = this.createElement ('form', {
		id: this.prefix ('reply-' + permalink),
		className: 'hashover-reply-form',
		action: this.setup['http-backend'] + '/form-actions.php',
		method: 'post'
	});

	// Place reply fields into form
	form.innerHTML = this.strings.parseTemplate (
		this.ui['reply-form'], {
			hashover: this.prefix (),
			permalink: permalink,
			url: this.instance['page-url'],
			thread: this.instance['thread-name'],
			title: this.instance['page-title'],
			file: file
		}
	);

	// Prevent input submission
	this.preventSubmit (form);

	// Get form by its permalink ID
	var replyForm = this.getElement ('placeholder-reply-form-' + permalink);

	// Add form to page
	replyForm.appendChild (form);

	// Change "Reply" link to "Cancel" link
	this.cancelSwitcher ('reply', link, replyForm, permalink);

	// Attach event listeners to "Post Reply" button
	var postReply = this.getElement ('reply-post-' + permalink);

	// Attach click event to formatting revealer hyperlink
	this.formattingOnclick ('reply', permalink);

	// Set onclick and onsubmit event handlers
	this.duplicateProperties (postReply, [ 'onclick', 'onsubmit' ], function () {
		return hashover.postComment (form, this, 'reply', permalink, link.onclick);
	});

	// Focus comment field
	form.comment.focus ();

	// And return false
	return true;
};
