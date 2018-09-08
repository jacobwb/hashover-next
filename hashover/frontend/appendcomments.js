// For appending new comments to the thread on page (appendcomments.js)
HashOver.prototype.appendComments = function (comments, dest, parent)
{
	// Set append element to more section
	dest = dest || this.instance['sort-section'];

	// Run through each comment
	for (var i = 0, il = comments.length; i < il; i++) {
		// Current comment
		var comment = comments[i];

		// Attempt to get the comment element
		var element = this.getElement (comment.permalink);

		// Check if comment exists
		if (element !== null) {
			// If so, re-append the comment element
			element.parentNode.appendChild (element);

			// Check comment's replies
			if (comment.replies !== undefined) {
				this.appendComments (comment.replies, element, comment);
			}

			// And do nothing else
			continue;
		}

		// Parse comment
		var html = this.comments.parse (comment, parent);

		// Check if we can insert HTML adjacently
		if ('insertAdjacentHTML' in dest) {
			// If so, just do so
			dest.insertAdjacentHTML ('beforeend', html);
		} else {
			// If not, convert HTML to NodeList
			var element = this.htmlChildren (html);

			// And append the first node
			dest.appendChild (element[0]);
		}

		// Add controls to the comment
		this.addControls (comment);
	}
};
