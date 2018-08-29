// For posting comments (ajaxpost.js)
HashOver.prototype.AJAXPost = function (json, permalink, dest, isReply)
{
	// Check if there are no comments
	if (this.instance['total-count'] === 0) {
		// If so, add first comment message
		this.instance.comments.primary[0] = json.comment;

		// And place the comment on the page
		dest.innerHTML = this.comments.parse (json.comment);
	} else {
		// Add comment to comments array
		this.addComments (json.comment, isReply);

		// Create div element for comment
		var elements = this.HTMLToNodeList (this.comments.parse (json.comment));

		// Append comment to parent element
		if (this.setup['stream-mode'] === true && permalink.split('r').length > this.setup['stream-depth']) {
			dest.parentNode.insertBefore (elements[0], dest.nextSibling);
		} else {
			// If not, append to destination element
			dest.appendChild (elements[0]);
		}
	}

	// Add controls to the new comment
	this.addControls (json.comment);

	// Update comment count
	this.elements.get ('count').textContent = json.count;
	this.incrementCounts (isReply);
};
