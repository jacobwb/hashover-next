// "Flatten" the comments object (getallcomments.js)
HashOver.prototype.getAllComments = function (comments)
{
	// Initial flattened comments
	var output = [];

	// Clone the comments
	var tmpArray = this.cloneObject (comments);

	// Recursively descend into comment replies
	function descend (comment)
	{
		// Add the current comment to flattened output
		output.push (comment);

		// Check if comment has replies
		if (comment.replies !== undefined) {
			// If so, descend into the replies
			for (var i = 0, il = comment.replies.length; i < il; i++) {
				descend (comment.replies[i]);
			}

			// And remove replies from flattened output
			delete comment.replies;
		}
	}

	// Initial descent into comments
	for (var i = 0, il = tmpArray.length; i < il; i++) {
		descend (tmpArray[i]);
	}

	// Return flattened comments
	return output;
};
