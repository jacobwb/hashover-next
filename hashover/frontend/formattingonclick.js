// Attach click event to formatting revealer hyperlinks (formattingonclick.js)
HashOver.prototype.formattingOnclick = function (type, permalink)
{
	permalink = (permalink !== undefined) ? '-' + permalink : '';

	// Reference to this object
	var hashover = this;

	// Get formatting message elements
	var formattingID = type + '-formatting';
	var formatting = this.elements.get (formattingID + permalink, true);
	var formattingMessage = this.elements.get (formattingID + '-message' + permalink, true);

	// Attach click event to formatting revealer hyperlink
	formatting.onclick = function ()
	{
		if (hashover.classes.contains (formattingMessage, 'hashover-message-open')) {
			hashover.messages.close (formattingMessage);
			return false;
		}

		hashover.messages.open (formattingMessage);
		return false;
	}
};
