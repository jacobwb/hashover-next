// Displays edit form (editcomment.js)
HashOver.prototype.editComment = function (comment, callback)
{
	// Do nothing if the comment isn't editable
	if (comment['editable'] !== true) {
		return false;
	}

	// Reference to this object
	var hashover = this;

	// Path to comment edit information backend script
	var editInfo = HashOver.backendPath + '/comment-info.php';

	// Get permalink from comment JSON object
	var permalink = comment.permalink;

	// Get file
	var file = this.permalinkFile (permalink);

	// Set request queries
	var queries = [
		'url=' + encodeURIComponent (this.instance['page-url']),
		'thread=' + encodeURIComponent (this.instance['thread-name']),
		'comment=' + encodeURIComponent (file)
	];

	// Get edit link element
	var link = this.getElement ('edit-link-' + permalink);

	// Set loading class to edit link
	this.classes.add (link, 'hashover-loading');

	// Send request for comment information
	this.ajax ('post', editInfo, queries, function (info) {
		// Check if request returned an error
		if (info.error !== undefined) {
			// If so, display error
			alert (info.error);

			// Remove loading class from edit link
			hashover.classes.remove (link, 'hashover-loading');

			// And do nothing else
			return;
		}

		// Get and clean comment body
		var body = info.body.replace (hashover.rx.links, '$1');

		// Get edit form placeholder
		var placeholder = hashover.getElement ('placeholder-edit-form-' + permalink);

		// Available comment status options
		var statuses = [ 'approved', 'pending', 'deleted' ];

		// Create edit form element
		var form = hashover.createElement ('form', {
			id: hashover.prefix ('edit-' + permalink),
			className: 'hashover-edit-form',
			action: hashover.setup['http-backend'] + '/form-actions.php',
			method: 'post'
		});

		// Place edit form fields into form
		form.innerHTML = hashover.strings.parseTemplate (
			hashover.ui['edit-form'], {
				hashover: hashover.prefix (),
				permalink: permalink,
				url: hashover.instance['page-url'],
				thread: hashover.instance['thread-name'],
				title: hashover.instance['page-title'],
				file: file,
				name: info.name || '',
				email: info.email || '',
				website: info.website || '',
				body: body
			}
		);

		// Prevent input submission
		hashover.preventSubmit (form);

		// Add edit form to placeholder
		placeholder.appendChild (form);

		// Set status dropdown menu option to comment status
		hashover.elementExists ('edit-status-' + permalink, function (status) {
			if (comment.status !== undefined) {
				status.selectedIndex = statuses.indexOf (comment.status);
			}
		});

		// Uncheck subscribe checkbox if user isn't subscribed
		hashover.elementExists ('edit-subscribe-' + permalink, function (sub) {
			if (comment.subscribed !== true) {
				sub.checked = null;
			}
		});

		// Get delete button
		var editDelete = hashover.getElement('edit-delete-' + permalink);

		// Get "Save Edit" button
		var saveEdit = hashover.getElement ('edit-post-' + permalink);

		// Change "Edit" link to "Cancel" link
		hashover.cancelSwitcher ('edit', link, placeholder, permalink);

		// Displays confirmation dialog for comment deletion
		editDelete.onclick = function () {
			return confirm (hashover.locale['delete-comment']);
		};

		// Attach click event to formatting revealer hyperlink
		hashover.formattingOnclick ('edit', permalink);

		// Set onclick and onsubmit event handlers
		hashover.duplicateProperties (saveEdit, [ 'onclick', 'onsubmit' ], function () {
			return hashover.postComment (form, this, 'edit', permalink, link.onclick);
		});

		// Remove loading class from edit link
		hashover.classes.remove (link, 'hashover-loading');

		// And execute callback if one was given
		if (typeof (callback) === 'function') {
			callback ();
		}
	}, true);

	// And return false
	return false;
};
