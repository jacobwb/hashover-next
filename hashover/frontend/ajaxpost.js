// For posting comments (ajaxpost.js)
HashOver.prototype.AJAXPost = function (json, permalink, dest, isReply)
{
	// Reference to this object
	var hashover = this;

	// Check if there are no comments
	if (this.instance['total-count'] === 0) {
		// If so, add first comment message
		this.instance.comments.primary[0] = json.comment;

		// And place the comment on the page
		dest.innerHTML = this.parseComment (json.comment);
	} else {
		// Add comment to comments array
		this.addComments (json.comment, isReply);

		// Fetch parent comment by its permalink
		var parent = this.permalinkComment (
			this.permalinkParent (json.comment.permalink),
			this.instance.comments.primary
		);

		// Parse comment
		var comment = this.parseComment (json.comment, parent);

		// Get comment child elements
		var elements = this.htmlChildren (comment);

		// Check if the comment is a reply and has a permalink
		if (isReply === true && permalink !== undefined) {
			// If so, store indicator of when comment is out of stream depth
			var outOfDepth = (permalink.split ('r').length > this.setup['stream-depth']);

			// Check if we are in stream mode and out of the allowed depth
			if (this.setup['stream-mode'] === true && outOfDepth === true) {
				// If so, append comment to parent element
				dest.parentNode.insertBefore (elements[0], dest.nextSibling);
			} else {
				// If not, append to destination element
				dest.appendChild (elements[0]);
			}
		} else {
			// If not, append to destination element
			dest.appendChild (elements[0]);
		}
	}

	// Add class to indicate comment is out of order
	if (isReply !== true && this.instance['showing-more'] === false) {
		this.elementExists (json.comment.permalink, function (comment) {
			hashover.classes.add (comment, 'hashover-disjoined');
		});
	}

	// Add controls to the new comment
	this.addControls (json.comment);

	// Update comment count
	this.getElement('count').textContent = json.count;
	this.incrementCounts (isReply);
};
