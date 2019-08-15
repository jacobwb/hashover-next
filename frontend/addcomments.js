// For adding new comments to comments array (addcomments.js)
HashOver.prototype.addComments = function (comment, type)
{
	// Check if comment is a reply
	if (type === 'reply') {
		// If so, fetch parent comment by its permalink
		var parent = this.permalinkComment (
			this.permalinkParent (comment.permalink),
			this.instance.comments.primary
		);

		// Check if the parent comment exists
		if (parent !== null) {
			// If so, check if comment has replies
			if (parent.replies !== undefined) {
				// If so, append comment to replies
				parent.replies.push (comment);
			} else {
				// If not, create replies array
				parent.replies = [ comment ];
			}

			// And do nothing else
			return;
		}
	}

	// Otherwise, append to primary comments
	this.instance.comments.primary.push (comment);
};
