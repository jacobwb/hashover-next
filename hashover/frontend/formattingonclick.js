// Attach click event to formatting revealer hyperlinks (formattingonclick.js)
HashOver.prototype.formattingOnclick = function (type, permalink)
{
	// Prepend dash to permalink if present
	permalink = permalink ? '-' + permalink : '';

	// Reference to this object
	var hashover = this;

	// Get "Formatting" hyperlink element
	var link = this.getElement (type + '-formatting' + permalink);

	// Get formatting message element
	var message = this.getElement (type + '-formatting-message' + permalink);

	// Attach click event to formatting revealer hyperlink
	link.onclick = function ()
	{
		// Check if message is open
		if (hashover.classes.contains (message, 'hashover-message-open')) {
			// If so, close it
			hashover.closeMessage (message);

			// And do nothing else
			return false;
		}

		// Otherwise, open it
		hashover.openMessage (message);
		return false;
	}
};
