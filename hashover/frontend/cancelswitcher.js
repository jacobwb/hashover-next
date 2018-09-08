// Changes a given hyperlink into a "Cancel" hyperlink (cancelswitcher.js)
HashOver.prototype.cancelSwitcher = function (form, link, wrapper, permalink)
{
	// Initial state properties of hyperlink
	var reset = {
		textContent: link.textContent,
		title: link.title,
		onclick: link.onclick
	};

	function linkOnClick ()
	{
		// Remove fields from form wrapper
		wrapper.textContent = '';

		// Reset button
		link.textContent = reset.textContent;
		link.title = reset.title;
		link.onclick = reset.onclick;

		return false;
	}

	// Change hyperlink to "Cancel" hyperlink
	link.textContent = this.locale['cancel'];
	link.title = this.locale['cancel'];

	// This resets the "Cancel" hyperlink to initial state onClick
	link.onclick = linkOnClick;

	// Check if cancel buttons are enabled
	if (this.setup['uses-cancel-buttons'] !== false) {
		// If so, get "Cancel" button
		var cancelButtonId = form + '-cancel-' + permalink;
		var cancelButton = this.getElement (cancelButtonId);

		// Attach event listeners to "Cancel" button
		cancelButton.onclick = linkOnClick;
	}
};
