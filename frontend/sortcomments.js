// Sort any given comments (sortcomments.js)
HashOver.prototype.sortComments = function (comments, method)
{
	// Sort method or default
	method = method || this.setup['default-sorting'];

	// Configurable default name
	var defaultName = this.setup['default-name'];

	// Sorts comments by date
	function sortByDate (a, b)
	{
		// Return microtime difference if dates are different
		if (b.timestamp !== a.timestamp) {
			return b.timestamp - a.timestamp;
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
			var sortArray = this.getAllComments (comments);

			// And return reversed comments
			return sortArray.reverse ();
		}

		// Sort all comments by date
		case 'by-date': {
			// Get all comments
			var sortArray = this.getAllComments (comments);

			// And return comments sorted by date
			return sortArray.sort (sortByDate);
		}

		// Sort all comments by net number of likes
		case 'by-likes': {
			// Get all comments
			var sortArray = this.getAllComments (comments);

			// And return sorted comments
			return sortArray.sort (function (a, b) {
				return netLikes (b) - netLikes (a);
			});
		}

		// Sort all comments by number of replies
		case 'by-replies': {
			// Clone the comments
			var sortArray = this.cloneObject (comments);

			// And return comments sorted by number of replies
			return sortArray.sort (function (a, b) {
				return replyCounter (b) - replyCounter (a);
			});
		}

		// Sort threads by the sum of replies to its comments
		case 'by-discussion': {
			// Clone the comments
			var sortArray = this.cloneObject (comments);

			// And return comments sorted by the sum of each comment's replies
			return sortArray.sort (function (a, b) {
				var replyCountA = replySum (a, replyCounter);
				var replyCountB = replySum (b, replyCounter);

				return replyCountB - replyCountA;
			});
		}

		// Sort threads by the sum of likes to it's comments
		case 'by-popularity': {
			// Clone the comments
			var sortArray = this.cloneObject (comments);

			// And return comments sorted by the sum of each comment's net likes
			return sortArray.sort (function (a, b) {
				var likeCountA = replySum (a, netLikes);
				var likeCountB = replySum (b, netLikes);

				return likeCountB - likeCountA;
			});
		}

		// Sort all comments by the commenter names
		case 'by-name': {
			// Get all comments
			var sortArray = this.getAllComments (comments);

			// And return comments sorted by the commenter names
			return sortArray.sort (sortByCommenter);
		}

		// Sort threads in reverse order
		case 'threaded-descending': {
			// Clone the comments
			var sortArray = this.cloneObject (comments);

			// And return reversed comments
			return sortArray.reverse ();
		}

		// Sort threads by date
		case 'threaded-by-date': {
			// Clone the comments
			var sortArray = this.cloneObject (comments);

			// And return comments sorted by date
			return sortArray.sort (sortByDate);
		}

		// Sort threads by net likes
		case 'threaded-by-likes': {
			// Clone the comments
			var sortArray = this.cloneObject (comments);

			// And return comments sorted by the net number of likes
			return sortArray.sort (function (a, b) {
				return netLikes (b) - netLikes (a);
			});
		}

		// Sort threads by commenter names
		case 'threaded-by-name': {
			// Clone the comments
			var sortArray = this.cloneObject (comments);

			// And return comments sorted by the commenter names
			return sortArray.sort (sortByCommenter);
		}
	}

	// By default simply return the comments as-is
	return comments;
};

// Sort primary comments (sortcomments.js)
HashOver.prototype.sortPrimary = function (method, collapse)
{
	// Sorted comment destination
	var dest = this.instance['sort-section'];

	// Comment sorting start time
	var sortStart = Date.now ();

	// Sort the primary comments
	var sorted = this.sortComments (this.instance.comments.primary, method);

	// Reset collapsed comments count if comments are to be collapsed
	if (collapse === true) {
		this.instance.collapseLimit = 0;
	}

	// Get comment sorting time
	var sortTime = Date.now () - sortStart;

	// Parse the sorted comments
	var htmlTime = this.parseAll (sorted, dest, collapse);

	// Log execution time in console
	console.log (this.strings.sprintf (
		'HashOver: sorting %d ms, HTML %d ms', [ sortTime, htmlTime ]
	));
};
