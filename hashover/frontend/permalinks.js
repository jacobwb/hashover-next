// Collection of permalink-related functions (permalinks.js)
HashOverConstructor.prototype.permalinks = {
	// Returns the permalink of a comment's parent
	getParent: function (permalink, flatten)
	{
		flatten = flatten || false;

		var parent = permalink.split ('r');
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
			// Return comment if its permalink matches
			if (comments[i].permalink === permalink) {
				return comments[i];
			}

			// Recursively check replies when present
			if (comments[i].replies !== undefined) {
				var comment = this.getComment (permalink, comments[i].replies);

				if (comment !== null) {
					return comment;
				}
			}
		}

		// Otherwise return null
		return null;
	},

	// Generate file from permalink
	getFile: function (permalink)
	{
		return permalink.slice(1).replace(/r/g, '-').replace ('-pop', '');
	}
};
