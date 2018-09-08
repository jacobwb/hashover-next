// Collection of permalink-related functions (permalinks.js)
HashOverConstructor.prototype.permalinks = {
	// Returns the permalink of a comment's parent
	getParent: function (permalink, flatten)
	{
		// Split permalink by reply 'r'
		var parent = permalink.split ('r');

		// Number of replies
		var length = parent.length - 1;

		// Limit depth if in stream mode
		if (this.parent.setup['stream-mode'] === true && flatten === true) {
			length = Math.min (this.parent.setup['stream-depth'], length);
		}

		// Check if there is a parent after flatten
		if (length > 0) {
			// If so, remove child from permalink
			parent = parent.slice (0, length);

			// Return parent permalink as string
			return parent.join ('r');
		}

		return null;
	},

	// Find a comment by its permalink
	getComment: function (permalink, comments)
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
				var reply = this.getComment (permalink, comment.replies);

				// Return reply if its permalink matches
				if (reply !== null) {
					return reply;
				}
			}
		}

		// Otherwise return null
		return null;
	},

	// Generate file from permalink
	getFile: function (permalink)
	{
		// Remove leading 'c'
		var file = permalink.slice (1);

		// Replace 'r' by '-'
		file = file.replace (/r/g, '-');

		// Remove "-pop" if present
		file = file.replace ('-pop', '');

		return file;
	}
};
