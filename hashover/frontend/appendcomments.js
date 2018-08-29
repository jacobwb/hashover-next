// For appending new comments to the thread on page (appendcomments.js)
HashOver.prototype.appendComments = function (comments)
{
	// Shorter variables
	var primary = this.instance.comments.primary;

	// Run through each comment
	for (var i = 0, il = comments.length; i < il; i++) {
		// Check if comment exists
		if (this.permalinks.getComment (comments[i].permalink, primary) !== null) {
			// Check comment's replies
			if (comments[i].replies !== undefined) {
				this.appendComments (comments[i].replies);
			}

			// And do nothing else
			continue;
		}

		// Check if comment is a reply
		var isReply = (comments[i].permalink.indexOf ('r') > -1);

		// Set append element to more section
		var element = this.instance['sort-section'];

		// Add comment to comments array
		this.addComments (comments[i], isReply, i);

		// Check if the comment is a reply
		if (isReply === true) {
			// If so, get comment's parent element
			var parent = this.permalinks.getParent (comments[i].permalink, true);

			// Set append to parent element or more section
			element = this.elements.get (parent, true) || element;
		}

		// Parse comment
		var html = this.comments.parse (comments[i], null, true);

		// Check if we can insert HTML adjacently
		if ('insertAdjacentHTML' in element) {
			// If so, just do so
			element.insertAdjacentHTML ('beforeend', html);
		} else {
			// If not, convert HTML to NodeList
			var comment = this.HTMLToNodeList (html);

			// And append the first node
			element.appendChild (comment[0]);
		}

		// Add controls to the comment
		this.addControls (comments[i]);
	}
};
