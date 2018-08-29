// Sort any given comments (sortcomments.js)
HashOver.prototype.sortComments = function (method)
{
	// Initial sorted comments array
	var sortArray = [];

	// Configurable default name
	var defaultName = this.setup['default-name'];

	// Sorts comments by date
	function sortByDate (a, b)
	{
		// Return microtime difference if dates are different
		if (b['sort-date'] !== a['sort-date']) {
			return b['sort-date'] - a['sort-date'];
		}

		// Otherwise, return 1
		return 1;
	}

	// Returns a comment's number of likes minus dislikes
	function netLikes (comment)
	{
		// Number of likes or zero
		var likes = comment.likes || 0;

		// Number of dislikes or zero
		var dislikes = comment.dislikes || 0;

		// Return the difference
		return likes - dislikes;
	}

	// Returns a comment's number of replies
	function replyCounter (comment)
	{
		return comment.replies ? comment.replies.length : 0;
	}

	// Returns the sum number of replies in a comment thread
	function replySum (comment, callback)
	{
		// Initial sum
		var sum = 0;

		// Check if there are replies to the current comment
		if (comment.replies !== undefined) {
			// If so, run through them adding up the number of replies
			for (var i = 0, il = comment.replies.length; i < il; i++) {
				sum += replySum (comment.replies[i], callback);
			}
		}

		// Calculate the sum based on the give callback
		sum += callback (comment);

		return sum;
	}

	// Sorts comments alphabetically by commenters names
	function sortByCommenter (a, b)
	{
		// Lowercase commenter name or default name
		var nameA = (a.name || defaultName).toLowerCase ();
		var nameB = (b.name || defaultName).toLowerCase ();

		// Remove @ character if present
		nameA = (nameA.charAt (0) === '@') ? nameA.slice (1) : nameA;
		nameB = (nameB.charAt (0) === '@') ? nameB.slice (1) : nameB;

		// Return 1 or -1 based on lexicographical difference
		if (nameA !== nameB) {
			return (nameA > nameB) ? 1 : -1;
		}

		// Otherwise, return 0
		return 0;
	}

	// Decide how to sort the comments
	switch (method) {
		// Sort all comments in reverse order
		case 'descending': {
			// Get all comments
			var tmpArray = this.getAllComments (this.instance.comments.primary);

			// And return reversed comments
			sortArray = tmpArray.reverse ();

			break;
		}

		// Sort all comments by date
		case 'by-date': {
			// Get all comments
			var tmpArray = this.getAllComments (this.instance.comments.primary);

			// And return comments sorted by date
			sortArray = tmpArray.sort (sortByDate);

			break;
		}

		// Sort all comments by net number of likes
		case 'by-likes': {
			// Get all comments
			var tmpArray = this.getAllComments (this.instance.comments.primary);

			// And return sorted comments
			sortArray = tmpArray.sort (function (a, b) {
				return netLikes (b) - netLikes (a);
			});

			break;
		}

		// Sort all comments by number of replies
		case 'by-replies': {
			// Clone the comments
			var tmpArray = this.cloneObject (this.instance.comments.primary);

			// And return comments sorted by number of replies
			sortArray = tmpArray.sort (function (a, b) {
				return replyCounter (b) - replyCounter (a);
			});

			break;
		}

		// Sort threads by the sum of replies to its comments
		case 'by-discussion': {
			// Clone the comments
			var tmpArray = this.cloneObject (this.instance.comments.primary);

			// And return comments sorted by the sum of each comment's replies
			sortArray = tmpArray.sort (function (a, b) {
				var replyCountA = replySum (a, replyCounter);
				var replyCountB = replySum (b, replyCounter);

				return replyCountB - replyCountA;
			});

			break;
		}

		// Sort threads by the sum of likes to it's comments
		case 'by-popularity': {
			// Clone the comments
			var tmpArray = this.cloneObject (this.instance.comments.primary);

			// And return comments sorted by the sum of each comment's net likes
			sortArray = tmpArray.sort (function (a, b) {
				var likeCountA = replySum (a, netLikes);
				var likeCountB = replySum (b, netLikes);

				return likeCountB - likeCountA;
			});

			break;
		}

		// Sort all comments by the commenter names
		case 'by-name': {
			// Get all comments
			var tmpArray = this.getAllComments (this.instance.comments.primary);

			// And return comments sorted by the commenter names
			sortArray = tmpArray.sort (sortByCommenter);

			break;
		}

		// Sort threads in reverse order
		case 'threaded-descending': {
			// Clone the comments
			var tmpArray = this.cloneObject (this.instance.comments.primary);

			// And return reversed comments
			sortArray = tmpArray.reverse ();

			break;
		}

		// Sort threads by date
		case 'threaded-by-date': {
			// Clone the comments
			var tmpArray = this.cloneObject (this.instance.comments.primary);

			// And return comments sorted by date
			sortArray = tmpArray.sort (sortByDate);

			break;
		}

		// Sort threads by net likes
		case 'threaded-by-likes': {
			// Clone the comments
			var tmpArray = this.cloneObject (this.instance.comments.primary);

			// And return comments sorted by the net number of likes
			sortArray = tmpArray.sort (function (a, b) {
				return netLikes (b) - netLikes (a);
			});

			break;
		}

		// Sort threads by commenter names
		case 'threaded-by-name': {
			// Clone the comments
			var tmpArray = this.cloneObject (this.instance.comments.primary);

			// And return comments sorted by the commenter names
			sortArray = tmpArray.sort (sortByCommenter);

			break;
		}
	}

	// Parse the sorted comments
	this.parseAll (sortArray, this.instance['sort-section'], false, false, true, method);
};
