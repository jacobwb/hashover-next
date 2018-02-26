// For adding new comments to comments array (addcomments.js)
HashOver.prototype.addComments = function (comment, isReply, index)
{
	isReply = isReply || false;
	index = index || null;

	// Check that comment is not a reply
	if (isReply !== true) {
		// If so, add to primary comments
		if (index !== null) {
			this.instance.comments.primary.splice (index, 0, comment);
			return;
		}

		this.instance.comments.primary.push (comment);
		return;
	}

	// If not, fetch parent comment
	var parentPermalink = this.permalinks.getParent (comment.permalink);
	var parent = this.permalinks.getComment (parentPermalink, this.instance.comments.primary);

	// Check if the parent comment exists
	if (parent !== null) {
		// If so, check if comment has replies
		if (parent.replies !== undefined) {
			// If so, add comment to reply array
			if (index !== null) {
				parent.replies.splice (index, 0, comment);
				return;
			}

			parent.replies.push (comment);
			return;
		}

		// If not, create reply array
		parent.replies = [comment];
	}

	// Otherwise, add to primary comments
	this.instance.comments.primary.push (comment);
};
