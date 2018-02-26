// For posting comments (ajaxpost.js)
HashOver.prototype.AJAXPost = function (json, permalink, destination, isReply)
{
	// If there aren't any comments, replace first comment message
	if (this.instance['total-count'] === 0) {
		this.instance.comments.primary[0] = json.comment;
		destination.innerHTML = this.comments.parse (json.comment);
	} else {
		// Add comment to comments array
		this.addComments (json.comment, isReply);

		// Create div element for comment
		var commentNode = this.HTMLToNodeList (this.comments.parse (json.comment));

		// Append comment to parent element
		if (this.setup['stream-mode'] === true && permalink.split('r').length > this.setup['stream-depth']) {
			destination.parentNode.insertBefore (commentNode[0], destination.nextSibling);
		} else {
			destination.appendChild (commentNode[0]);
		}
	}

	// Add controls to the new comment
	this.addControls (json.comment);

	// Update comment count
	this.elements.get ('count').textContent = json.count;
	this.incrementCounts (isReply);
};
