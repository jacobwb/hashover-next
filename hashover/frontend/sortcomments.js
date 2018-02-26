// Comment sorting (sortcomments.js)
HashOver.prototype.sortComments = function (method)
{
	var tmpArray;
	var sortArray;
	var defaultName = this.setup['default-name'];

	function replyPropertySum (comment, callback)
	{
		var sum = 0;

		if (comment.replies !== undefined) {
			for (var i = 0, il = comment.replies.length; i < il; i++) {
				sum += replyPropertySum (comment.replies[i], callback);
			}
		}

		sum += callback (comment);

		return sum;
	}

	function replyCounter (comment)
	{
		return (comment.replies) ? comment.replies.length : 0;
	}

	function netLikes (comment)
	{
		var likes = comment.likes || 0;
		var dislikes = comment.dislikes || 0;

		return likes - dislikes;
	}

	// Sort methods
	switch (method) {
		case 'descending': {
			tmpArray = this.getAllComments (this.instance.comments.primary);
			sortArray = tmpArray.reverse ();
			break;
		}

		case 'by-date': {
			sortArray = this.getAllComments (this.instance.comments.primary).sort (function (a, b) {
				if (a['sort-date'] === b['sort-date']) {
					return 1;
				}

				return b['sort-date'] - a['sort-date'];
			});

			break;
		}

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

		case 'by-replies': {
			tmpArray = this.cloneObject (this.instance.comments.primary);

			sortArray = tmpArray.sort (function (a, b) {
				var ac = (!!a.replies) ? a.replies.length : 0;
				var bc = (!!b.replies) ? b.replies.length : 0;

				return bc - ac;
			});

			break;
		}

		case 'by-discussion': {
			tmpArray = this.cloneObject (this.instance.comments.primary);

			sortArray = tmpArray.sort (function (a, b) {
				var replyCountA = replyPropertySum (a, replyCounter);
				var replyCountB = replyPropertySum (b, replyCounter);

				return replyCountB - replyCountA;
			});

			break;
		}

		case 'by-popularity': {
			tmpArray = this.cloneObject (this.instance.comments.primary);

			sortArray = tmpArray.sort (function (a, b) {
				var likeCountA = replyPropertySum (a, netLikes);
				var likeCountB = replyPropertySum (b, netLikes);

				return likeCountB - likeCountA;
			});

			break;
		}

		case 'by-name': {
			tmpArray = this.getAllComments (this.instance.comments.primary);

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

		case 'threaded-descending': {
			tmpArray = this.cloneObject (this.instance.comments.primary);
			sortArray = tmpArray.reverse ();
			break;
		}

		case 'threaded-by-date': {
			tmpArray = this.cloneObject (this.instance.comments.primary);

			sortArray = tmpArray.sort (function (a, b) {
				if (a['sort-date'] === b['sort-date']) {
					return 1;
				}

				return b['sort-date'] - a['sort-date'];
			});

			break;
		}

		case 'threaded-by-likes': {
			tmpArray = this.cloneObject (this.instance.comments.primary);

			sortArray = tmpArray.sort (function (a, b) {
				a.likes = a.likes || 0;
				b.likes = b.likes || 0;
				a.dislikes = a.dislikes || 0;
				b.dislikes = b.dislikes || 0;

				return (b.likes - b.dislikes) - (a.likes - a.dislikes);
			});

			break;
		}

		case 'threaded-by-name': {
			tmpArray = this.cloneObject (this.instance.comments.primary);

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

		default: {
			sortArray = this.instance.comments.primary;
			break;
		}
	}

	this.parseAll (sortArray, this.instance['sort-section'], false, false, true, method);
};
