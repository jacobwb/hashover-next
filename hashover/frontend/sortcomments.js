// Comment sorting (sortcomments.js)
HashOver.prototype.sortComments = function (method)
{
	// Initial sorted comments array
	var sortArray = [];

	// Configurable default name
	var defaultName = this.setup['default-name'];

	// Returns the sum number of replies in a comment thread
	function replyPropertySum (comment, callback)
	{
		var sum = 0;

		// Check if there are replies to the current comment
		if (comment.replies !== undefined) {
			// If so, run through them adding up the number of replies
			for (var i = 0, il = comment.replies.length; i < il; i++) {
				sum += replyPropertySum (comment.replies[i], callback);
			}
		}

		// Calculate the sum based on the give callback
		sum += callback (comment);

		return sum;
	}

	// Calculation callback for `replyPropertySum` function
	function replyCounter (comment)
	{
		return (comment.replies) ? comment.replies.length : 0;
	}

	// Calculation callback for `replyPropertySum` function
	function netLikes (comment)
	{
		return (comment.likes || 0) - (comment.dislikes || 0);
	}

	// Sort methods
	switch (method) {
		// Sort all comment in reverse order
		case 'descending': {
			// Get all comments
			var tmpArray = this.getAllComments (this.instance.comments.primary);

			// And reverse the comments
			sortArray = tmpArray.reverse ();

			break;
		}

		// Sort all comments by date
		case 'by-date': {
			sortArray = this.getAllComments (this.instance.comments.primary).sort (function (a, b) {
				if (a['sort-date'] === b['sort-date']) {
					return 1;
				}

				return b['sort-date'] - a['sort-date'];
			});

			break;
		}

		// Sort all comment by net number of likes
		case 'by-likes': {
			sortArray = this.getAllComments (this.instance.comments.primary).sort (function (a, b) {
				a.likes = a.likes || 0;
				b.likes = b.likes || 0;
				a.dislikes = a.dislikes || 0;
				b.dislikes = b.dislikes || 0;

				return (b.likes - b.dislikes) - (a.likes - a.dislikes);
			});

			break;
		}

		// Sort all comment by number of replies
		case 'by-replies': {
			// Clone the primary comments
			var tmpArray = this.cloneObject (this.instance.comments.primary);

			// And sort them by number of replies
			sortArray = tmpArray.sort (function (a, b) {
				var ac = (!!a.replies) ? a.replies.length : 0;
				var bc = (!!b.replies) ? b.replies.length : 0;

				return bc - ac;
			});

			break;
		}

		// Sort threads by the sum of replies to its comments
		case 'by-discussion': {
			// Clone the primary comments
			var tmpArray = this.cloneObject (this.instance.comments.primary);

			// And sort them by the sum of each comment's replies
			sortArray = tmpArray.sort (function (a, b) {
				var replyCountA = replyPropertySum (a, replyCounter);
				var replyCountB = replyPropertySum (b, replyCounter);

				return replyCountB - replyCountA;
			});

			break;
		}

		// Sort threads by the sum of likes to it's comments
		case 'by-popularity': {
			// Clone the primary comments
			var tmpArray = this.cloneObject (this.instance.comments.primary);

			// And sort them by the sum of each comment's net likes
			sortArray = tmpArray.sort (function (a, b) {
				var likeCountA = replyPropertySum (a, netLikes);
				var likeCountB = replyPropertySum (b, netLikes);

				return likeCountB - likeCountA;
			});

			break;
		}

		// Sort all comment by the commenter names
		case 'by-name': {
			// Get all comments
			var tmpArray = this.getAllComments (this.instance.comments.primary);

			// And sort them alphabetically by the commenter names
			sortArray = tmpArray.sort (function (a, b) {
				var nameA = (a.name || defaultName).toLowerCase ();
				var nameB = (b.name || defaultName).toLowerCase ();

				nameA = (nameA.charAt (0) === '@') ? nameA.slice (1) : nameA;
				nameB = (nameB.charAt (0) === '@') ? nameB.slice (1) : nameB;

				if (nameA > nameB) {
					return 1;
				}

				if (nameA < nameB) {
					return -1;
				}

				return 0;
			});

			break;
		}

		// Sort threads in reverse order
		case 'threaded-descending': {
			// Clone the primary comments
			var tmpArray = this.cloneObject (this.instance.comments.primary);

			// And reverse the comments
			sortArray = tmpArray.reverse ();

			break;
		}

		// Sort threads by date
		case 'threaded-by-date': {
			// Clone the primary comments
			var tmpArray = this.cloneObject (this.instance.comments.primary);

			// And sort them by date
			sortArray = tmpArray.sort (function (a, b) {
				if (a['sort-date'] === b['sort-date']) {
					return 1;
				}

				return b['sort-date'] - a['sort-date'];
			});

			break;
		}

		// Sort threads by net likes
		case 'threaded-by-likes': {
			// Clone the primary comments
			var tmpArray = this.cloneObject (this.instance.comments.primary);

			// And sort them by the net number of likes
			sortArray = tmpArray.sort (function (a, b) {
				a.likes = a.likes || 0;
				b.likes = b.likes || 0;
				a.dislikes = a.dislikes || 0;
				b.dislikes = b.dislikes || 0;

				return (b.likes - b.dislikes) - (a.likes - a.dislikes);
			});

			break;
		}

		// Sort threads by commenter names
		case 'threaded-by-name': {
			// Clone the primary comments
			var tmpArray = this.cloneObject (this.instance.comments.primary);

			// And sort them alphabetically by the commenter names
			sortArray = tmpArray.sort (function (a, b) {
				var nameA = (a.name || defaultName).toLowerCase ();
				var nameB = (b.name || defaultName).toLowerCase ();

				nameA = (nameA.charAt (0) === '@') ? nameA.slice (1) : nameA;
				nameB = (nameB.charAt (0) === '@') ? nameB.slice (1) : nameB;

				if (nameA > nameB) {
					return 1;
				}

				if (nameA < nameB) {
					return -1;
				}

				return 0;
			});

			break;
		}

		// By default simply use the primary comments as-is
		default: {
			sortArray = this.instance.comments.primary;
			break;
		}
	}

	// Parse the sorted comments
	this.parseAll (sortArray, this.instance['sort-section'], false, false, true, method);
};
