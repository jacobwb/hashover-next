// For liking comments (likecomment.js)
HashOver.prototype.likeComment = function (action, permalink)
{
	// Reference to this object
	var hashover = this;

	var file = this.permalinks.getFile (permalink);
	var actionLink = this.elements.get (action + '-' + permalink, true);
	var likesElement = this.elements.get (action + 's-' + permalink, true);
	var likePath = this.setup['http-backend'] + '/like.php';

	// Set request queries
	var queries = [
		'url=' + encodeURIComponent (this.instance['page-url']),
		'thread=' + this.instance['thread-name'],
		'comment=' + file,
		'action=' + action
	];

	// When loaded update like count
	this.ajax ('POST', likePath, queries, function (likeResponse) {
		// If a message is returned display it to the user
		if (likeResponse.message !== undefined) {
			alert (likeResponse.message);
			return;
		}

		// If an error is returned display a standard error to the user
		if (likeResponse.error !== undefined) {
			alert ('Error! Something went wrong!');
			return;
		}

		// Get number of likes
		var likesKey = (action !== 'dislike') ? 'likes' : 'dislikes';
		var likes = likeResponse[likesKey] || 0;

		// Change "Like" button title and class
		if (hashover.classes.contains (actionLink, 'hashover-' + action) === true) {
			// Change class to indicate the comment has been liked/disliked
			hashover.classes.add (actionLink, 'hashover-' + action + 'd');
			hashover.classes.remove (actionLink, 'hashover-' + action);
			actionLink.title = (action === 'like') ? hashover.locale['liked-comment'] : hashover.locale['disliked-comment'];
			actionLink.textContent = (action === 'like') ? hashover.locale['liked'] : hashover.locale['disliked'];

			// Add listener to change link text to "Unlike" on mouse over
			if (action === 'like') {
				hashover.mouseOverChanger (actionLink, 'unlike', 'liked');
			}
		} else {
			// Change class to indicate the comment is unliked
			hashover.classes.add (actionLink, 'hashover-' + action);
			hashover.classes.remove (actionLink, 'hashover-' + action + 'd');
			actionLink.title = (action === 'like') ? hashover.locale['like-comment'] : hashover.locale['dislike-comment'];
			actionLink.textContent = (action === 'like') ? hashover.locale['like'][0] : hashover.locale['dislike'][0];

			// Add listener to change link text to "Unlike" on mouse over
			if (action === 'like') {
				hashover.mouseOverChanger (actionLink, null, null);
			}
		}

		if (likes > 0) {
			// Decide if locale is pluralized
			var plural = (likes !== 1) ? 1 : 0;
			var likeLocale = (action !== 'like') ? 'dislike' : 'like';
			var likeCount = likes + ' ' + hashover.locale[likeLocale][plural];

			// Change number of likes; set font weight bold
			likesElement.textContent = likeCount;
			likesElement.style.fontWeight = 'bold';
		} else {
			// Remove like count; set font weight normal
			likesElement.textContent = '';
			likesElement.style.fontWeight = '';
		}
	}, true);
};
