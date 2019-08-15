// Displays edit form (editcomment.js)
HashOver.prototype.editComment = function (comment)
{
	// Do nothing if the comment isn't editable
	if (comment['editable'] !== true) {
		return false;
	}

	// Reference to this object
	var hashover = this;

	// Get permalink from comment JSON object
	var permalink = comment.permalink;

	// Get file
	var file = this.permalinkFile (permalink);

	// Get edit link element
	var link = this.getElement ('edit-link-' + permalink);

		// Get and clean comment body
		var body = comment.body.replace (this.rx.links, '$1');

		// Get edit form placeholder
		var placeholder = this.getElement ('placeholder-edit-form-' + permalink);

		// Available comment status options
		var statuses = [ 'approved', 'pending', 'deleted' ];

		// Create edit form element
		var form = this.createElement ('form', {
			id: this.prefix ('edit-' + permalink),
			className: 'hashover-edit-form',
			action: this.setup['http-backend'] + '/form-actions.php',
			method: 'post'
		});

		// Place edit form fields into form
		form.innerHTML = this.strings.parseTemplate (
			this.ui['edit-form'], {
				hashover: this.prefix (),
				permalink: permalink,
				url: this.instance['page-url'],
				thread: this.instance['thread-name'],
				title: this.instance['page-title'],
				file: file,
				name: comment.name || '',
				website: comment.website || '',
				body: body
			}
		);

		// Prevent input submission
		this.preventSubmit (form);

		// Add edit form to placeholder
		placeholder.appendChild (form);

		// Set status dropdown menu option to comment status
		this.elementExists ('edit-status-' + permalink, function (status) {
			if (comment.status !== undefined) {
				status.selectedIndex = statuses.indexOf (comment.status);
			}
		});

		// Blank out password field
		setTimeout (function () {
			if (form.password !== undefined) {
				form.password.value = '';
			}
		}, 100);

		// Uncheck subscribe checkbox if user isn't subscribed
		this.elementExists ('edit-subscribe-' + permalink, function (sub) {
			if (comment.subscribed !== true) {
				sub.checked = null;
			}
		});

		// Get delete button
		var editDelete = this.getElement('edit-delete-' + permalink);

		// Get "Save Edit" button
		var saveEdit = this.getElement ('edit-post-' + permalink);

		// Change "Edit" link to "Cancel" link
		this.cancelSwitcher ('edit', link, placeholder, permalink);

		// Displays confirmation dialog for comment deletion
		editDelete.onclick = function () {
			return confirm (hashover.locale['delete-comment']);
		};

		// Attach click event to formatting revealer hyperlink
		this.formattingOnclick ('edit', permalink);

		// Set onclick and onsubmit event handlers
		this.duplicateProperties (saveEdit, [ 'onclick', 'onsubmit' ], function () {
			return hashover.postComment (form, this, 'edit', permalink, link.onclick);
		});

	// And return false
	return false;
};
