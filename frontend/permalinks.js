// Returns the permalink of a comment's parent (permalinks.js)
HashOverConstructor.prototype.permalinkParent = function (permalink)
{
	// Split permalink by reply 'r'
	var parent = permalink.split ('r');

	// Number of replies
	var length = parent.length - 1;

	// Limit depth if in stream mode
	if (this.setup['stream-mode'] === true) {
		length = Math.min (this.setup['stream-depth'], length);
	}

	// Check if there is a parent after flatten
	if (length > 0) {
		// If so, remove child from permalink
		parent = parent.slice (0, length);

		// Return parent permalink as string
		return parent.join ('r');
	}

	return null;
};

// Find a comment by its permalink (permalinks.js)
HashOverConstructor.prototype.permalinkComment = function (permalink, comments)
{
	// Run through all comments
	for (var i = 0, il = comments.length; i < il; i++) {
		// Current comment
		var comment = comments[i];

		// Return comment if its permalink matches
		if (comment.permalink === permalink) {
			return comment;
		}

		// Recursively check replies when present
		if (comment.replies !== undefined) {
			// Get attempt to get reply by permalink
			var reply = this.permalinkComment (permalink, comment.replies);

			// Return reply if its permalink matches
			if (reply !== null) {
				return reply;
			}
		}
	}

	// Otherwise return null
	return null;
};
